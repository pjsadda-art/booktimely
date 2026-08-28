<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only audit trail for deposits.
 *
 * The deposit itself is flattened onto the appointment, so re-raising a deposit
 * overwrites the previous attempt's columns. This table is what keeps the
 * history of those attempts — never update or delete a row here.
 */
class AppointmentDepositLog extends Model
{
    use HasFactory;

    public const REQUESTED_AUTO = 'requested_auto';
    public const REQUESTED_MANUAL = 'requested_manual';
    public const PAID = 'paid';
    public const FORFEITED = 'forfeited';
    public const REFUNDED = 'refunded';
    public const LINK_RESENT = 'link_resent';
    public const LINK_RESEND_FAILED = 'link_resend_failed';
    public const LINK_REGENERATED = 'link_regenerated';

    protected $fillable = [
        'appointment_id',
        'common_number',
        'event_type',
        'amount',
        'old_status_id',
        'new_status_id',
        'staff_id',
        'reason',
        'business_id',
        'created_by',
    ];

    /**
     * Human-readable event labels for the profile's deposit event log.
     */
    public static function labels(): array
    {
        return [
            self::REQUESTED_AUTO => __('Requested (automatic)'),
            self::REQUESTED_MANUAL => __('Requested (staff)'),
            self::PAID => __('Paid'),
            self::FORFEITED => __('Forfeited'),
            self::REFUNDED => __('Refunded'),
            self::LINK_RESENT => __('Payment link resent'),
            self::LINK_RESEND_FAILED => __('Payment link resend failed'),
            self::LINK_REGENERATED => __('Payment link reissued'),
        ];
    }

    public function label(): string
    {
        $labels = self::labels();

        return $labels[$this->event_type] ?? $this->event_type;
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }
}
