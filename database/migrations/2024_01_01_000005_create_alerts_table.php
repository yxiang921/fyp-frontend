<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_id', 50)->unique();
            $table->foreignId('detection_id')->nullable()->constrained('detections')->onDelete('set null');
            $table->foreignId('missing_person_id')->constrained('missing_persons')->onDelete('cascade');
            $table->decimal('similarity_score', 5, 2)->default(0);
            $table->foreignId('camera_id')->constrained('cameras')->onDelete('cascade');
            $table->string('status')->default('new');
            $table->string('priority')->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('priority');
            $table->index('missing_person_id');
            $table->index('alert_id');
        });

        Schema::create('alert_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('alerts')->onDelete('cascade');
            $table->string('event_type');
            $table->text('description');
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_timeline');
        Schema::dropIfExists('alerts');
    }
};
