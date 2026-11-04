<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sightings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('missing_person_id')->constrained('missing_persons')->onDelete('cascade');
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone', 20)->nullable();
            $table->string('location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('sighted_at');
            $table->text('notes')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index('missing_person_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sightings');
    }
};
