<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detections', function (Blueprint $table) {
            $table->text('captured_image')->nullable()->change();
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->foreignId('sighting_id')->nullable()->after('detection_id')
                ->constrained('sightings')->nullOnDelete()->unique();
            $table->foreignId('patrol_area_id')->nullable()->after('camera_id')
                ->constrained('patrol_areas')->nullOnDelete()->index();
            $table->foreignId('camera_id')->nullable()->change();
        });

        DB::table('alerts')->whereNotNull('camera_id')->update([
            'patrol_area_id' => DB::raw('(SELECT patrol_area_id FROM cameras WHERE cameras.id = alerts.camera_id)'),
        ]);

        Schema::create('sighting_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sighting_id')->constrained('sightings')->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sighting_status_history');
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patrol_area_id');
            $table->dropConstrainedForeignId('sighting_id');
        });
    }
};
