<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'common_number',
        'location_id',
        'service_id',
        'staff_id',
        'date',
        'time',
        'notes',
        'payment_type',
        'appointment_status',
        'business_id',
        'created_by',
        'converted_invoice',
        'sms_message_id',
        'is_reminder',
        'deposit_required',
        'deposit_percentage',
        'deposit_amount',
        'deposit_status',
        'deposit_invoice_id',
        'deposit_confirm_status_id',
        'deposit_requested_by',
        'deposit_message',
        'deposit_method',
        'deposit_payment_notes',
        'deposit_paid_at',
        'deposit_forfeit_reason',
        'deposit_refund_reference',
    ];

    protected $casts = [
        'deposit_required' => 'boolean',
        'deposit_paid_at' => 'datetime',
    ];

    /* --------------------------------------------------------------------- */
    /* Booking groups                                                        */
    /* --------------------------------------------------------------------- */

    /**
     * Guarantee every appointment has a booking group.
     *
     * Done as a model event rather than at each call site because there are
     * several creation paths — the admin form, the public booking form, the
     * calendar panel, the API — and a single one of them forgetting would
     * reintroduce null groups. A null group is what turns
     * "where common_number = ?" into "where common_number is null", which
     * matches *every* ungrouped appointment in the business; as the target of a
     * grouped write, that mass-updates all of them.
     *
     * Callers creating a multi-service booking set the same common_number on
     * every row before saving, and this leaves it alone.
     */
    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            if (empty($appointment->common_number)) {
                $appointment->common_number = self::newCommonNumber();
            }
        });

        // Forfeit a paid deposit when the booking is marked No Show, or
        // Cancelled inside the penalty window.
        //
        // An observer rather than a call in each controller, for the same reason
        // as the group number above: status is changed from the admin form, the
        // calendar, the kanban board and the API, and one of them forgetting
        // would quietly let a no-show keep their money.
        //
        // Only fires on transitions into those statuses, so re-saving an
        // already-cancelled appointment cannot forfeit twice. DepositService's
        // own writes go through a mass update, which raises no model events at
        // all, so this cannot recurse.
        static::updated(function (Appointment $appointment) {
            if (!$appointment->wasChanged('appointment_status')) {
                return;
            }

            if ($appointment->deposit_status !== 'paid') {
                return;
            }

            try {
                $status = CustomStatus::find($appointment->appointment_status);

                $classification = app(\App\Services\AppointmentStatusClassifier::class)->classify(
                    $status->title ?? null,
                    $appointment->appointment_status,
                    app(\App\Services\AppointmentStatusClassifier::class)
                        ->slugMap($appointment->business_id, $appointment->created_by)
                );

                app(\App\Services\DepositService::class)->autoForfeit($appointment, $classification);
            } catch (\Exception $e) {
                // A status change must still succeed if the forfeit fails.
                report($e);
            }
        });
    }

    /**
     * The customer-facing payment link for this booking's deposit.
     *
     * Derived from the deposit invoice on every read rather than stored in a
     * column. A stored link survives a token rotation and then points at a URL
     * that no longer works, with nothing to indicate it has gone stale — and
     * reissuing a link is precisely when staff most need the one they can see
     * to be the one that works.
     *
     * Deliberately not in $appends: resolving it costs a query, and appending it
     * would run that query for every row of every appointment listing.
     */
    public function getDepositLinkAttribute(): ?string
    {
        if (empty($this->attributes['deposit_invoice_id'])) {
            return null;
        }

        $invoice = DepositInvoice::find($this->attributes['deposit_invoice_id']);

        return $invoice ? $invoice->payUrl() : null;
    }

    /**
     * A fresh booking-group identifier.
     *
     * Every appointment gets one, even a booking of a single service: a group of
     * one is still a group, and having no null groups is what stops a grouped
     * write from matching every ungrouped row in the table.
     */
    public static function newCommonNumber(): string
    {
        return 'BKG-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    /**
     * Every appointment sharing this one's booking group, this row included.
     *
     * Guards the null case explicitly. A "where common_number = null" is
     * rewritten by the query builder to "is null", which would return every
     * ungrouped appointment in the business — and a grouped *write* built on
     * that would update all of them. When the group is missing, the group is
     * this row alone.
     */
    public function groupQuery()
    {
        $query = static::where('business_id', $this->business_id)
            ->where('created_by', $this->created_by);

        if (empty($this->common_number)) {
            return $query->where('id', $this->id);
        }

        return $query->where('common_number', $this->common_number);
    }

    /**
     * Resolve a booking group by its number, with the same null guard.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int,Appointment>
     */
    public static function group($commonNumber, $businessId, $createdBy)
    {
        if (empty($commonNumber)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return static::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->where('common_number', $commonNumber)
            ->orderBy('id')
            ->get();
    }

    /**
     * The row a customer-facing message should quote: anything shown to the
     * customer — a reminder time, an SMS — must resolve to the earliest-starting
     * appointment in the group, or it quotes the wrong arrival time.
     */
    public static function earliestOf($group): ?Appointment
    {
        $group = collect($group)->filter();

        if ($group->isEmpty()) {
            return null;
        }

        return $group->sortBy(function ($appointment) {
            $start = explode('-', (string) $appointment->time)[0] ?? '00:00';

            try {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', $appointment->date)->format('Y-m-d');
            } catch (\Exception $e) {
                $date = '9999-12-31';
            }

            return $date . ' ' . trim($start);
        })->first();
    }

    public function depositLogs()
    {
        return $this->hasMany(AppointmentDepositLog::class, 'appointment_id', 'id');
    }

    public function depositInvoice()
    {
        return $this->belongsTo(DepositInvoice::class, 'deposit_invoice_id', 'id');
    }


    public static function appointmentNumberFormat($number, $Id = null, $businessId = null)
    {
        $company_settings = getCompanyAllSetting($Id, $businessId);
        $data = !empty($company_settings['appointment_prefix']) ? $company_settings['appointment_prefix'] : '#APP0000';

        return $data . sprintf("%01d", $number);
    }

    // this function is created for query optimization
    public static function appointmentNumberWithFormat($number, $company_settings)
    {
        $data = !empty($company_settings['appointment_prefix']) ? $company_settings['appointment_prefix'] : '#APP0000';
        return $data . sprintf("%01d", $number);
    }

    public function CustomerData()
    {
        return $this->hasOne(Customer::class, 'user_id', 'customer_id');
    }

    public function StaffData()
    {
        return $this->hasOne(Staff::class, 'user_id', 'staff_id');
    }

    public function ServiceData()
    {
        return $this->hasOne(Service::class, 'id', 'service_id');
    }

    public function LocationData()
    {
        return $this->hasOne(Location::class, 'id', 'location_id');
    }

    public function StatusData()
    {
        return $this->hasOne(CustomStatus::class, 'id', 'appointment_status');
    }

    public function payment()
    {
        return $this->hasOne(AppointmentPayment::class, 'appointment_id', 'id');
    }

    public function paymentsInfo()
    {
        return $this->hasMany(AppointmentPayment::class, 'appointment_id', 'id');
    }

    public function payments($id)
    {
        return AppointmentPayment::whereRaw("FIND_IN_SET($id, appointment_ids)")->first();
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    public function jobCard()
    {
        return $this->hasOne(JobCard::class);
    }

    public static function ColorCode()
    {
        $Color = [];
        $Color = [
            '#21c9b0',
            '#f04c43',
            '#fa9c30',
            '#a969ba',
            '#0080b6',
            '#27a93dc9',
            '#df3e9d',
            '#5c6bc0',
            '#f6c436'
        ];

        return $Color;
    }
}
