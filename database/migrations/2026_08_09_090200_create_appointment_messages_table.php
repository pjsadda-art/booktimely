<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The WhatsApp chat thread, one row per message.
 *
 * Doubles as the audit trail (NFR4) — nothing else logs a send/receive
 * separately, this table is the record. appointment_id is nullable because an
 * inbound message arrives from a phone number before it's matched to any
 * booking; customer_id is customers.id (not users.id), matching how
 * DepositInvoice links a customer.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('appointment_messages')) {
            return;
        }

        Schema::create('appointment_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('sender_type', 16);              // staff | customer
            $table->unsignedBigInteger('sender_id')->nullable();   // users.id when sender_type = staff
            $table->string('message_type', 16)->default('text');  // text now; image, file later
            $table->longText('message_content');
            $table->string('whatsapp_message_id')->nullable();     // Meta's wamid
            $table->string('direction', 8);                 // outbound | inbound
            $table->string('status', 16)->default('queued'); // queued|sent|delivered|read|failed|received
            $table->text('error')->nullable();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->index(['business_id', 'appointment_id'], 'appointment_messages_appointment_index');
            $table->index('whatsapp_message_id', 'appointment_messages_wamid_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_messages');
    }
};
