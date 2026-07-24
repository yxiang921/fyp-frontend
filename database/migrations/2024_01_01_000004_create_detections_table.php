<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detections', function (Blueprint $table) {
            $table->id();
            $table->string('detection_id', 50)->unique();
            $table->foreignId('camera_id')->constrained('cameras')->onDelete('cascade');
            $table->string('captured_image')->nullable();
            $table->string('reference_type'); // 'missing_person' or 'person_of_interest'
            $table->unsignedBigInteger('reference_id');
            $table->decimal('similarity_score', 5, 2)->default(0);
            $table->string('status')->default('new');
            $table->timestamp('detected_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('detection_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('similarity_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detections');
    }
};
