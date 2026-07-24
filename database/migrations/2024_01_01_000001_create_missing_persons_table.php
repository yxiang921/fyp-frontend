<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missing_persons', function (Blueprint $table) {
            $table->id();
            $table->string('case_number', 50)->unique();
            $table->string('full_name');
            $table->string('gender', 20);
            $table->date('date_of_birth')->nullable();
            $table->string('national_id', 20)->nullable();
            $table->string('last_seen_location')->nullable();
            $table->date('last_seen_date')->nullable();
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('status');
            $table->index('case_number');
        });

        Schema::create('missing_person_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('missing_person_id')->constrained('missing_persons')->onDelete('cascade');
            $table->string('path');
            $table->string('type')->default('front');
            $table->boolean('is_primary')->default(false);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('missing_person_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('missing_person_id')->constrained('missing_persons')->onDelete('cascade');
            $table->string('event_type');
            $table->text('description');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missing_person_timeline');
        Schema::dropIfExists('missing_person_photos');
        Schema::dropIfExists('missing_persons');
    }
};
