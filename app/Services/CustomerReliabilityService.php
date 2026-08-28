<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

/**
 * The green / orange / red signal summarising whether a customer turns up.
 *
 * Feeds the deposit rules engine as well as the profile badge, so it shares
 * AppointmentStatusClassifier with the engine rather than re-deriving the rules.
 *
 * Counts are windowed rather than lifetime. The source system counted no-shows
 * over all history, which left a customer who missed three appointments years
 * ago permanently red with no way back; the deposit engine meanwhile used
 * rolling windows, so the badge and the engine could disagree about the same
 * person. One window setting drives both. Set `reliability_window_months` to 0
 * for the old lifetime behaviour.
 */
class CustomerReliabilityService
{
    public const GREEN = 'green';
    public const ORANGE = 'orange';
    public const RED = 'red';

    /** Counts at or above this are red; at or above the watch level, orange. */
    protected const RED_THRESHOLD = 3;
    protected const WATCH_THRESHOLD = 2;

    protected const DEFAULT_WINDOW_MONTHS = 6;

    protected AppointmentStatusClassifier $classifier;

    public function __construct(?AppointmentStatusClassifier $classifier = null)
    {
        $this->classifier = $classifier ?: new AppointmentStatusClassifier();
    }

    /**
     * The signal for one customer.
     *
     * @param  int  $customerId  a users.id — appointments key on the user row
     * @return array{signal:string,label:string,no_shows:int,cancelled_run:int,total:int}
     */
    public function forCustomer($customerId, $businessId, $createdBy): array
    {
        $all = $this->forCustomers([$customerId], $businessId, $createdBy);

        return $all[$customerId] ?? $this->emptyResult();
    }

    /**
     * The signal for many customers in one query.
     *
     * The customer list renders a badge per row; doing this per row would be one
     * query per customer on a page of 25. Callers should always batch.
     *
     * @param  array<int,int>  $customerIds
     * @return array<int,array{signal:string,label:string,no_shows:int,cancelled_run:int,total:int}>
     */
    public function forCustomers(array $customerIds, $businessId, $createdBy): array
    {
        $customerIds = array_values(array_unique(array_filter($customerIds)));

        if (empty($customerIds)) {
            return [];
        }

        $rows = Appointment::query()
            ->leftJoin('custom_statuses', 'custom_statuses.id', '=', 'appointments.appointment_status')
            ->where('appointments.business_id', $businessId)
            ->where('appointments.created_by', $createdBy)
            ->whereIn('appointments.customer_id', $customerIds)
            ->get([
                'appointments.id',
                'appointments.customer_id',
                'appointments.date',
                'appointments.appointment_status',
                'appointments.created_at',
                'custom_statuses.title as status_title',
            ]);

        $slugMap = $this->classifier->slugMap($businessId, $createdBy);
        $cutoff = $this->windowCutoff($businessId);

        // Group first so each customer's run of cancellations is counted against
        // their own history and nobody else's.
        $byCustomer = [];

        foreach ($rows as $row) {
            $byCustomer[$row->customer_id][] = [
                'class' => $this->classifier->classify($row->status_title, $row->appointment_status, $slugMap),
                'when' => $this->sortableDate($row),
            ];
        }

        $results = [];

        foreach ($customerIds as $customerId) {
            $results[$customerId] = $this->summarise($byCustomer[$customerId] ?? [], $cutoff);
        }

        return $results;
    }

    /**
     * Turn one customer's classified history into a signal.
     *
     * @param  array<int,array{class:string,when:string}>  $history
     */
    protected function summarise(array $history, ?Carbon $cutoff): array
    {
        if (empty($history)) {
            return $this->emptyResult();
        }

        // Newest first: the consecutive-cancellation run is counted backwards
        // from the most recent appointment and stops at the first one that was
        // not cancelled.
        usort($history, function ($a, $b) {
            return strcmp($b['when'], $a['when']);
        });

        $cutoffKey = $cutoff ? $cutoff->format('Y-m-d H:i:s') : null;

        $noShows = 0;
        $cancelledRun = 0;
        $runOpen = true;

        foreach ($history as $entry) {
            $inWindow = $cutoffKey === null || $entry['when'] >= $cutoffKey;

            if ($inWindow && $entry['class'] === AppointmentStatusClassifier::NO_SHOW) {
                $noShows++;
            }

            if ($runOpen) {
                if ($entry['class'] === AppointmentStatusClassifier::CANCELLED) {
                    // The run is about consecutiveness, not recency, so it is
                    // counted without the window filter — but it can only ever
                    // start at the most recent appointment.
                    $cancelledRun++;
                } else {
                    $runOpen = false;
                }
            }
        }

        $worst = max($noShows, $cancelledRun);

        if ($worst >= self::RED_THRESHOLD) {
            $signal = self::RED;
            $label = __('High risk');
        } elseif ($worst >= self::WATCH_THRESHOLD) {
            $signal = self::ORANGE;
            $label = __('Watch');
        } else {
            $signal = self::GREEN;
            $label = __('Good');
        }

        return [
            'signal' => $signal,
            'label' => $label,
            'no_shows' => $noShows,
            'cancelled_run' => $cancelledRun,
            'total' => count($history),
        ];
    }

    /**
     * Start of the counting window, or null to count lifetime.
     */
    protected function windowCutoff($businessId): ?Carbon
    {
        $months = company_setting('reliability_window_months', null, $businessId);
        $months = is_numeric($months) ? (int) $months : self::DEFAULT_WINDOW_MONTHS;

        return $months > 0 ? Carbon::now()->subMonths($months)->startOfDay() : null;
    }

    /**
     * A sortable Y-m-d H:i:s key for an appointment.
     *
     * `appointments.date` is a d-m-Y string, so it cannot be compared directly;
     * created_at is the tiebreak for two appointments on the same day, and the
     * fallback when the date column is unparseable.
     */
    protected function sortableDate($row): string
    {
        $time = $row->created_at ? Carbon::parse($row->created_at)->format('H:i:s') : '00:00:00';

        if (!empty($row->date)) {
            try {
                return Carbon::createFromFormat('d-m-Y', $row->date)->format('Y-m-d') . ' ' . $time;
            } catch (\Exception $e) {
                // A few rows may predate the d-m-Y convention.
            }

            try {
                return Carbon::parse($row->date)->format('Y-m-d') . ' ' . $time;
            } catch (\Exception $e) {
                // Fall through to created_at.
            }
        }

        return $row->created_at
            ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s')
            : '0000-00-00 00:00:00';
    }

    protected function emptyResult(): array
    {
        return [
            'signal' => self::GREEN,
            'label' => __('Good'),
            'no_shows' => 0,
            'cancelled_run' => 0,
            'total' => 0,
        ];
    }
}
