<?php

namespace Workdo\AppointmentKanbanBoard\Http\Controllers;

use App\Models\Appointment;
use App\Models\CustomStatus;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\AppointmentStatus;
use App\Models\EmailTemplate;

class AppointmentKanbanBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('appointment kanban board manage')) {

            $statuses = CustomStatus::where('created_by', creatorId())->where('business_id', getActiveBusiness())->get();
            $pendingStatus = new CustomStatus();
            $pendingStatus->id = 'pending';
            $pendingStatus->title = 'Pending';
            $pendingStatus->tasks = Appointment::where('created_by', creatorId())
                ->where('business_id', getActiveBusiness())
                ->whereNull('appointment_status')
                ->get();

            $statusClass = [];
            if ($statuses) {
                foreach ($statuses as $status) {
                    $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);

                    if ($status->id !== 'pending') {
                        $status->tasks = Appointment::where('created_by', creatorId())
                            ->where('business_id', getActiveBusiness())
                            ->where('appointment_status', $status->id)
                            ->get();
                    }
                }
            }
            $company_settings = getCompanyAllSetting();
            return view('appointment-kanban-board::appointment.board', compact('statuses', 'statusClass', 'company_settings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function orderUpdate(Request $request)
    {
        if (isset($request->sort)) {
            foreach ($request->sort as $index => $taskID) {
                $task = Appointment::find($taskID);
                $task->save();
            }
        }

        if ($request->new_status != $request->old_status) {
            $newStatus = CustomStatus::find($request->new_status);
            $oldStatus = CustomStatus::find($request->old_status);

            $task = Appointment::find($request->id);
            $task->appointment_status = $request->new_status;
            $task->save();

            $company_settings = getCompanyAllSetting();

            $task = Appointment::with('CustomerData', 'LocationData')->find($request->id);

            event(new AppointmentStatus($task, $request));
            $status = CustomStatus::where('id', $request->new_status)->first();
            if ((!empty($company_settings['Appointment Status Change']) && $company_settings['Appointment Status Change'] == true) && $status->send_email == 1) {
                $uArr = [
                    'company_name' => $task->business->name ?? '',
                    'service' => $task->ServiceData ? $task->ServiceData->name : '-',
                    'appointment_date' => $task->date,
                    'appointment_time' => $task->time,
                    'appointment_number' => $task->appointment_number,
                    'client_name' => $task->CustomerData ? $task->CustomerData->customer->name : $task->name,
                    'location' => $task->LocationData ? $task->LocationData->name : '-',
                    'status' => $status ? $status->title : $request->status,
                ];

                $resp = EmailTemplate::sendEmailTemplate('Appointment Status Change', [$task->CustomerData ? $task->CustomerData->customer->email : $task->email], $uArr);

                // return redirect()->route('appointment.index')->with('success', __('Appointment successfully created.'). ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                return redirect()->back()->with('success', __('Appointment status change successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }


            return response()->json([
                'success' => true,
                'message' => __('Status change successfully'),
                'task' => $task
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated successfully']);
    }

}
