<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('location');
            $table->integer('port')->nullable()->default(80)->after('ip_address');
            $table->string('stream_url', 500)->nullable()->after('port');
            $table->string('stream_type', 20)->nullable()->default('mjpeg')->after('stream_url');
            $table->string('username', 100)->nullable()->after('stream_type');
            $table->string('password', 100)->nullable()->after('username');
        });
    }

    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'port',
                'stream_url',
                'stream_type',
                'username',
                'password',
            ]);
        });
    }
};
