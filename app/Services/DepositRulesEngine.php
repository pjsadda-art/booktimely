<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use Carbon\Carbon;

/**
 * Decides whether a newly created booking must be paid for up front.
 *
 * Evaluated once, when an appointment is created, per business. Rules are
 * checked in order and the first match wins, so the customer-level flag can
 * never be overridden by a cheaper service or a clean recent history.
 *
 * Past appointments are classified with AppointmentStatusClassifier — the same
 * classifier the reliability badge uses. A business that renames "No Show" must
 * not get one answer from the badge and another from this engine.
 */
class DepositRulesEngine
{
    /** Percentage applied when the business has not configured one. */
    protected const DEFAULT_PERCENTAGE = 15;

    /** Rolling window, in months, for the history-based rules. */
    protected const DEFAULT_WINDOW_MONTHS = 6;

    protected AppointmentStatusClassifier $classifier;

    public function __construct(?AppointmentStatusClassifier $classifier = null)
    {
        $this->classifier = $classifier ?: new AppointmentStatusClassifier();
    }

    /**
     * Evaluate the rules for a booking.
     *
     * @return array{required:bool,reason:?string,rule:?string,percentage:?float}
     */
    public function evaluate(Appointment $appointment): array
    {
        $businessId = $appointment->business_id;
        $createdBy = $appointment->created_by;

        $none = ['required' => false, 'reason' => null, 'rule' => null, 'percentage' => null];

        if (company_setting('deposit_auto_enabled', null, $businessId) !== 'on') {
            return $none;
        }

        // Guest bookings have no history to evaluate, so they are skipped
        // silently rather than treated as a customer with a clean record.
        if (empty($appointment->customer_id)) {
            return $none;
        }

        $customer = Customer::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->where('user_id', $appointment->customer_id)
            ->first();

        if (empty($customer)) {
            return $none;
        }

        $percentage = $this->percentage($businessId);

        // Rule 1 — manually flagged high-risk. Always wins.
        if (!empty($customer->is_high_risk)) {
            return [
                'required' => true,
                'reason' => __('Customer is flagged as high risk.'),
                'rule' => 'high_risk',
                'percentage' => $percentage,
            ];
        }

        // Rule 2 — service price at or above a threshold.
        $threshold = company_setting('deposit_service_price_threshold', null, $businessId);

        if (is_numeric($threshold) && (float) $threshold > 0) {
            $service = Service::find($appointment->service_id);

            // A booking whose service cannot be resolved is skipped rather than
            // treated as a zero-price service.
            if (!empty($service) && (float) $service->price >= (float) $threshold) {
                return [
                    'required' => true,
                    'reason' => __('Service price is at or above the deposit threshold.'),
                    'rule' => 'service_price',
                    'percentage' => $percentage,
                ];
            }
        }

        // Rules 3 and 4 — history in a rolling window.
        $minCancellations = company_setting('deposit_min_cancellations', null, $businessId);
        $minNoShows = company_setting('deposit_min_noshows', null, $businessId);

        $needHistory = (is_numeric($minCancellations) && (int) $minCancellations > 0)
            || (is_numeric($minNoShows) && (int) $minNoShows > 0);

        if (!$needHistory) {
            return $none;
        }

        if (is_numeric($minCancellations) && (int) $minCancellations > 0) {
            $months = $this->windowMonths('deposit_min_cancellations_months', $businessId);
            $count = $this->countInWindow(
                $appointment,
                AppointmentStatusClassifier::CANCELLED,
                $months
            );

            if ($count >= (int) $minCancellations) {
                return [
                    'required' => true,
                    'reason' => __(':count cancellations in the last :months months.', [
                        'count' => $count,
                        'months' => $months,
                    ]),
                    'rule' => 'cancellations',
                    'percentage' => $percentage,
                ];
            }
        }

        if (is_numeric($minNoShows) && (int) $minNoShows > 0) {
            $months = $this->windowMonths('deposit_min_noshows_months', $businessId);
            $count = $this->countInWindow(
                $appointment,
                AppointmentStatusClassifier::NO_SHOW,
                $months
            );

            if ($count >= (int) $minNoShows) {
                return [
                    'required' => true,
                    'reason' => __(':count no-shows in the last :months months.', [
                        'count' => $count,
                        'months' => $months,
                    ]),
                    'rule' => 'no_shows',
                    'percentage' => $percentage,
                ];
            }
        }

        return $none;
    }

    /**
     * Count this customer's past appointments of one classification inside a
     * rolling window, excluding the booking being evaluated.
     */
    protected function countInWindow(Appointment $appointment, string $classification, int $months): int
    {
        $cutoff = Carbon::now()->subMonths($months)->startOfDay();

        $rows = Appointment::query()
            ->leftJoin('custom_statuses', 'custom_statuses.id', '=', 'appointments.appointment_status')
            ->where('appointments.business_id', $appointment->business_id)
            ->where('appointments.created_by', $appointment->created_by)
            ->where('appointments.customer_id', $appointment->customer_id)
            ->where('appointments.id', '!=', $appointment->id)
            ->get([
                'appointments.id',
                'appointments.date',
                'appointments.appointment_status',
                'appointments.created_at',
                'custom_statuses.title as status_title',
            ]);

        $slugMap = $this->classifier->slugMap($appointment->business_id, $appointment->created_by);
        $count = 0;

        foreach ($rows as $row) {
            if ($this->classifier->classify($row->status_title, $row->appointment_status, $slugMap) !== $classification) {
                continue;
            }

            if ($this->appointmentDate($row)->gte($cutoff)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * `appointments.date` is a d-m-Y string. Fall back to created_at when it
     * cannot be parsed, so an odd row is still counted rather than dropped.
     */
    protected function appointmentDate($row): Carbon
    {
        if (!empty($row->date)) {
            try {
                return Carbon::createFromFormat('d-m-Y', $row->date)->startOfDay();
            } catch (\Exception $e) {
                // Fall through.
            }

            try {
                return Carbon::parse($row->date)->startOfDay();
            } catch (\Exception $e) {
                // Fall through.
            }
        }

        return $row->created_at ? Carbon::parse($row->created_at) : Carbon::createFromTimestamp(0);
    }

    protected function windowMonths(string $key, $businessId): int
    {
        $months = company_setting($key, null, $businessId);

        return is_numeric($months) && (int) $months > 0 ? (int) $months : self::DEFAULT_WINDOW_MONTHS;
    }

    protected function percentage($businessId): float
    {
        $percentage = company_setting('deposit_default_percentage', null, $businessId);

        return is_numeric($percentage) ? (float) $percentage : self::DEFAULT_PERCENTAGE;
    }
}
