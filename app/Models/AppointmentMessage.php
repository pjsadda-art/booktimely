<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One WhatsApp message, in or out, threaded per appointment.
 *
 * This table is the audit trail for the WhatsApp integration — nothing else
 * logs a send or an inbound receipt separately, so nothing here is ever
 * deleted, only appended to or status-updated.
 */
class AppointmentMessage extends Model
{
    use HasFactory;

    public const SENDER_STAFF = 'staff';
    public const SENDER_CUSTOMER = 'customer';

    public const DIRECTION_OUTBOUND = 'outbound';
    public const DIRECTION_INBOUND = 'inbound';

    public const QUEUED = 'queued';
    public const SENT = 'sent';
    public const DELIVERED = 'delivered';
    public const READ = 'read';
    public const FAILED = 'failed';
    public const RECEIVED = 'received';

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'sender_type',
        'sender_id',
        'message_type',
        'message_content',
        'whatsapp_message_id',
        'direction',
        'status',
        'error',
        'business_id',
        'created_by',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    /** customer_id is customers.id, matching DepositInvoice's convention. */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /** Only set when sender_type = staff. */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
