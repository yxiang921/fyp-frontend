<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missing_persons', function (Blueprint $table) {
            $table->string('ethnicity', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->string('hair_type', 100)->nullable();
            $table->string('hair_color', 50)->nullable();
            $table->string('eye_color', 50)->nullable();
            $table->text('clothing_description')->nullable();
            $table->text('identifying_features')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('missing_persons', function (Blueprint $table) {
            $table->dropColumn([
                'ethnicity',
                'nationality',
                'height_cm',
                'weight_kg',
                'hair_type',
                'hair_color',
                'eye_color',
                'clothing_description',
                'identifying_features',
            ]);
        });
    }
};
