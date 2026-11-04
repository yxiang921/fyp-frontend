<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrol_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->decimal('center_latitude', 10, 7)->nullable();
            $table->decimal('center_longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('patrol_area_user', function (Blueprint $table) {
            $table->foreignId('patrol_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['patrol_area_id', 'user_id']);
        });

        Schema::table('cameras', function (Blueprint $table) {
            $table->foreignId('patrol_area_id')->nullable()->after('location')
                ->constrained('patrol_areas')->nullOnDelete()->index();
        });
    }

    public function down(): void
    {
        Schema::table('cameras', fn (Blueprint $table) => $table->dropConstrainedForeignId('patrol_area_id'));
        Schema::dropIfExists('patrol_area_user');
        Schema::dropIfExists('patrol_areas');
    }
};
