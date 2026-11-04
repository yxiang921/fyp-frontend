<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missing_persons', function (Blueprint $table) {
            $table->decimal('last_seen_latitude', 10, 7)->nullable()->after('last_seen_location');
            $table->decimal('last_seen_longitude', 10, 7)->nullable()->after('last_seen_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('missing_persons', function (Blueprint $table) {
            $table->dropColumn(['last_seen_latitude', 'last_seen_longitude']);
        });
    }
};
