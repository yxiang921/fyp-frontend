<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make missing_persons.created_by nullable, change ON DELETE to SET NULL
        DB::statement('ALTER TABLE missing_persons ALTER COLUMN created_by DROP NOT NULL');
        DB::statement('ALTER TABLE missing_persons DROP CONSTRAINT IF EXISTS missing_persons_created_by_foreign');
        DB::statement('ALTER TABLE missing_persons ADD CONSTRAINT missing_persons_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL');

        // Make missing_person_photos.uploaded_by nullable, change ON DELETE to SET NULL
        DB::statement('ALTER TABLE missing_person_photos ALTER COLUMN uploaded_by DROP NOT NULL');
        DB::statement('ALTER TABLE missing_person_photos DROP CONSTRAINT IF EXISTS missing_person_photos_uploaded_by_foreign');
        DB::statement('ALTER TABLE missing_person_photos ADD CONSTRAINT missing_person_photos_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL');

        // Make missing_person_timeline.recorded_by nullable, change ON DELETE to SET NULL
        DB::statement('ALTER TABLE missing_person_timeline ALTER COLUMN recorded_by DROP NOT NULL');
        DB::statement('ALTER TABLE missing_person_timeline DROP CONSTRAINT IF EXISTS missing_person_timeline_recorded_by_foreign');
        DB::statement('ALTER TABLE missing_person_timeline ADD CONSTRAINT missing_person_timeline_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        // Revert missing_persons.created_by to NOT NULL, restore CASCADE
        DB::statement('ALTER TABLE missing_persons DROP CONSTRAINT IF EXISTS missing_persons_created_by_foreign');
        DB::statement('ALTER TABLE missing_persons ADD CONSTRAINT missing_persons_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE missing_persons ALTER COLUMN created_by SET NOT NULL');

        // Revert missing_person_photos.uploaded_by
        DB::statement('ALTER TABLE missing_person_photos DROP CONSTRAINT IF EXISTS missing_person_photos_uploaded_by_foreign');
        DB::statement('ALTER TABLE missing_person_photos ADD CONSTRAINT missing_person_photos_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE missing_person_photos ALTER COLUMN uploaded_by SET NOT NULL');

        // Revert missing_person_timeline.recorded_by
        DB::statement('ALTER TABLE missing_person_timeline DROP CONSTRAINT IF EXISTS missing_person_timeline_recorded_by_foreign');
        DB::statement('ALTER TABLE missing_person_timeline ADD CONSTRAINT missing_person_timeline_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE missing_person_timeline ALTER COLUMN recorded_by SET NOT NULL');
    }
};
