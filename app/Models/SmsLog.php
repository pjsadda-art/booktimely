<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Every message to and from a customer's mobile, inbound replies included.
 *
 * Two things depend on this existing: the SMS tab on the customer profile, and
 * the deposit resend action, which sends synchronously and then reads the log
 * back so staff are told whether the message actually left rather than getting
 * an optimistic success.
 */
class SmsLog extends Model
{
    use HasFactory;

    public const SENT = 'sent';
    public const FAILED = 'failed';
    public const QUEUED = 'queued';
    public const RECEIVED = 'received';

    protected $fillable = [
        'user_id',
        'mobile_no',
        'direction',
        'message',
        'event',
        'status',
        'provider',
        'provider_message_id',
        'error',
        'appointment_id',
        'business_id',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function isSuccess(): bool
    {
        return in_array($this->status, [self::SENT, self::QUEUED], true);
    }

    /**
     * The most recent outbound message for an event on a mobile number.
     * Used both by the resend debounce and by the read-back after sending.
     */
    public static function latestFor($mobile, string $event, $businessId): ?SmsLog
    {
        if (empty($mobile)) {
            return null;
        }

        return self::where('business_id', $businessId)
            ->where('mobile_no', $mobile)
            ->where('event', $event)
            ->where('direction', 'out')
            ->latest('id')
            ->first();
    }
}
