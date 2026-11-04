<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE detections ADD COLUMN IF NOT EXISTS missing_person_id BIGINT');
        DB::statement('ALTER TABLE alerts ADD COLUMN IF NOT EXISTS missing_person_id BIGINT');

        DB::statement(<<<'SQL'
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM information_schema.columns
                    WHERE table_name = 'detections' AND column_name = 'reference_type'
                ) THEN
            UPDATE detections
            SET missing_person_id = reference_id
            WHERE reference_type IN ('missing_person', 'App\Models\MissingPerson')
              AND reference_id IN (SELECT id FROM missing_persons);
                END IF;
            END $$;
        SQL);

        DB::statement(<<<'SQL'
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM information_schema.columns
                    WHERE table_name = 'alerts' AND column_name = 'reference_type'
                ) THEN
            UPDATE alerts
            SET missing_person_id = reference_id
            WHERE reference_type IN ('missing_person', 'App\Models\MissingPerson')
              AND reference_id IN (SELECT id FROM missing_persons);
                END IF;
            END $$;
        SQL);

        // Rows that only point to the retired POI domain cannot be retained.
        DB::table('alerts')->whereNull('missing_person_id')->delete();
        DB::table('detections')->whereNull('missing_person_id')->delete();

        DB::statement(<<<'SQL'
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'detections_missing_person_id_foreign') THEN
                    ALTER TABLE detections ADD CONSTRAINT detections_missing_person_id_foreign
                        FOREIGN KEY (missing_person_id) REFERENCES missing_persons(id) ON DELETE CASCADE;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'alerts_missing_person_id_foreign') THEN
                    ALTER TABLE alerts ADD CONSTRAINT alerts_missing_person_id_foreign
                        FOREIGN KEY (missing_person_id) REFERENCES missing_persons(id) ON DELETE CASCADE;
                END IF;
            END $$;
        SQL);

        DB::statement('DROP INDEX IF EXISTS detections_reference_type_reference_id_index');
        DB::statement('DROP INDEX IF EXISTS alerts_reference_type_reference_id_index');
        DB::statement('DROP INDEX IF EXISTS alerts_alert_type_index');
        DB::statement('ALTER TABLE detections DROP COLUMN IF EXISTS reference_type, DROP COLUMN IF EXISTS reference_id');
        DB::statement('ALTER TABLE alerts DROP COLUMN IF EXISTS alert_type, DROP COLUMN IF EXISTS reference_type, DROP COLUMN IF EXISTS reference_id');
        DB::statement('ALTER TABLE detections ALTER COLUMN missing_person_id SET NOT NULL');
        DB::statement('ALTER TABLE alerts ALTER COLUMN missing_person_id SET NOT NULL');
    }

    public function down(): void
    {
        // The retired polymorphic/POI shape cannot be restored without data that
        // this migration intentionally removes.
    }
};
