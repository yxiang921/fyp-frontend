<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email_log_id')->unique();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->string('mailable_class');
            $table->foreignId('alert_id')->nullable()->constrained('alerts')->onDelete('set null');
            $table->string('status')->default('queued'); // queued, sent, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('trigger_type')->default('auto'); // auto, manual
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('status');
            $table->index('trigger_type');
            $table->index('recipient_email');
            $table->index('alert_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
