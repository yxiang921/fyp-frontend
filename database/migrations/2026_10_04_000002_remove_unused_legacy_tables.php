<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP TABLE IF EXISTS verification_documents CASCADE');
        DB::statement('DROP TABLE IF EXISTS system_settings CASCADE');
    }

    public function down(): void
    {
        // These tables were unused and intentionally have no rollback schema.
    }
};
