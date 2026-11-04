<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Public mobile/web submissions do not have an authenticated user.
        // Keep the audit relationship when available, but permit anonymous
        // records and preserve them if the user is later removed.
        DB::statement('ALTER TABLE missing_person_photos ALTER COLUMN uploaded_by DROP NOT NULL');
        DB::statement('ALTER TABLE missing_person_timeline ALTER COLUMN recorded_by DROP NOT NULL');

        DB::statement('ALTER TABLE missing_person_photos DROP CONSTRAINT IF EXISTS missing_person_photos_uploaded_by_foreign');
        DB::statement('ALTER TABLE missing_person_photos ADD CONSTRAINT missing_person_photos_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL');

        DB::statement('ALTER TABLE missing_person_timeline DROP CONSTRAINT IF EXISTS missing_person_timeline_recorded_by_foreign');
        DB::statement('ALTER TABLE missing_person_timeline ADD CONSTRAINT missing_person_timeline_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE missing_person_photos DROP CONSTRAINT IF EXISTS missing_person_photos_uploaded_by_foreign');
        DB::statement('ALTER TABLE missing_person_photos ADD CONSTRAINT missing_person_photos_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE missing_person_photos ALTER COLUMN uploaded_by SET NOT NULL');

        DB::statement('ALTER TABLE missing_person_timeline DROP CONSTRAINT IF EXISTS missing_person_timeline_recorded_by_foreign');
        DB::statement('ALTER TABLE missing_person_timeline ADD CONSTRAINT missing_person_timeline_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE missing_person_timeline ALTER COLUMN recorded_by SET NOT NULL');
    }
};
