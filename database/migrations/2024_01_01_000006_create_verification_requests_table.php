<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 50)->unique();
            $table->foreignId('detection_id')->constrained('detections')->onDelete('cascade');
            $table->foreignId('alert_id')->nullable()->constrained('alerts')->onDelete('set null');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->text('jpn_notes')->nullable();
            $table->text('police_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('request_id');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('verification_requests');
    }
};
