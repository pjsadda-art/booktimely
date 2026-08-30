<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\JobCard;
use App\Models\JobCardFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Scanned paper Job Cards, attached to an appointment and surfaced again on
 * the customer profile.
 *
 * Storage deliberately does not go through the shared upload_file() helper:
 * that helper's allowed-extension gate is the platform admin's generic
 * `*_storage_validation` setting, which is not guaranteed to include `pdf` —
 * the overwhelmingly common format for a scanned job card. This controller
 * validates its own fixed allow-list instead, then writes to whichever disk
 * (local/s3/wasabi) the admin has configured, the same way upload_file() does.
 */
class JobCardController extends Controller
{
    protected function authorize_(): bool
    {
        // Job Cards are an Auto Repair-specific feature: block the endpoints
        // outright for any other industry, not just the buttons that link to them.
        return Auth::user()->isAbleTo('appointment edit') && jobCardFeatureEnabled();
    }

    /**
     * GET appointment/{appointment}/job-card
     *
     * The upload/manage popup opened from the appointment list's action row —
     * same view partial the appointment details modal embeds, standalone so
     * a modal doesn't have to be opened just to attach a job card.
     */
    public function show($appointmentId)
    {
        // 200 either way, same as the deposit popups (deposit/request.blade.php):
        // the ajax-popup handler only reads xhr.responseJSON.error on failure, so
        // a non-2xx status here would throw in the browser rather than show
        // anything. An in-modal alert degrades correctly instead.
        if (!$this->authorize_()) {
            return view('appointment.job_card', ['appointment' => null, 'error' => __('Permission denied.')]);
        }

        $appointment = Appointment::where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->with('jobCard.files')
            ->find($appointmentId);

        if (empty($appointment)) {
            return view('appointment.job_card', ['appointment' => null, 'error' => __('Appointment not found.')]);
        }

        return view('appointment.job_card', ['appointment' => $appointment, 'error' => null]);
    }

    /**
     * POST appointment/{appointment}/job-card
     */
    public function store(Request $request, $appointmentId)
    {
        if (!$this->authorize_()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $appointment = Appointment::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->find($appointmentId);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        $request->validate([
            'files' => 'required|array|min:1|max:5',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $jobCard = JobCard::firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'customer_id' => $appointment->customer_id,
                'business_id' => $businessId,
                'created_by' => $createdBy,
            ]
        );

        $files = [];
        foreach ($request->file('files') as $file) {
            $filePath = $this->storeJobCardFile($file);

            $jobCardFile = JobCardFile::create([
                'job_card_id' => $jobCard->id,
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
            ]);

            $files[] = [
                'id' => $jobCardFile->id,
                'name' => $jobCardFile->original_name,
                'url' => check_file($jobCardFile->file_path) ? get_file($jobCardFile->file_path) : null,
            ];
        }

        return response()->json(['success' => true, 'job_card_id' => $jobCard->id, 'files' => $files]);
    }

    /**
     * DELETE job-card-file/{id}
     */
    public function destroyFile($id)
    {
        if (!$this->authorize_()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $file = JobCardFile::whereHas('jobCard', function ($query) use ($businessId, $createdBy) {
            $query->where('business_id', $businessId)->where('created_by', $createdBy);
        })->find($id);

        if (empty($file)) {
            return response()->json(['error' => __('File not found.')], 404);
        }

        $jobCard = $file->jobCard;

        delete_file($file->file_path);
        $file->delete();

        // No point keeping an empty job card around for the customer profile
        // and appointment views to skip over.
        if ($jobCard->files()->doesntExist()) {
            $jobCard->delete();
        }

        return response()->json(['success' => true]);
    }

    protected function storeJobCardFile(UploadedFile $file): string
    {
        $disk = $this->resolveStorageDisk();

        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
            . '_' . time() . '_' . uniqid() . '.' . $extension;

        $saved = Storage::disk($disk)->putFileAs('JobCard', $file, $fileName);

        return $disk === 'local' ? 'uploads/' . $saved : $saved;
    }

    protected function resolveStorageDisk(): string
    {
        $settings = getAdminAllSetting();
        $disk = $settings['storage_setting'] ?? 'local';

        if ($disk === 's3') {
            config([
                'filesystems.disks.s3.key' => $settings['s3_key'],
                'filesystems.disks.s3.secret' => $settings['s3_secret'],
                'filesystems.disks.s3.region' => $settings['s3_region'],
                'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
            ]);

            return 's3';
        }

        if ($disk === 'wasabi') {
            config([
                'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                'filesystems.disks.wasabi.root' => $settings['wasabi_root'],
                'filesystems.disks.wasabi.endpoint' => $settings['wasabi_url'],
            ]);

            return 'wasabi';
        }

        return 'local';
    }
}
