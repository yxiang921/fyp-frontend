-- FindSight cleanup: remove the retired Person of Interest domain.
-- Review the SELECT results first, then run the transaction.
--
-- This script is for an existing PostgreSQL database. New installations use
-- the Laravel migrations, which no longer create POI tables or polymorphic
-- detection/alert references.

BEGIN;

-- These tables are not represented by any current Laravel model, route, or
-- migration and are the obsolete POI-only structures from the old schema.
DROP TABLE IF EXISTS person_of_interest_timeline CASCADE;
DROP TABLE IF EXISTS person_of_interest_photos CASCADE;
DROP TABLE IF EXISTS persons_of_interest CASCADE;

-- These tables were present in the diagram but have no active code path.
DROP TABLE IF EXISTS verification_documents CASCADE;
DROP TABLE IF EXISTS system_settings CASCADE;

-- Convert shared detection/alert records to an explicit missing-person FK.
ALTER TABLE detections ADD COLUMN IF NOT EXISTS missing_person_id BIGINT;
ALTER TABLE alerts ADD COLUMN IF NOT EXISTS missing_person_id BIGINT;

UPDATE detections
SET missing_person_id = reference_id
WHERE reference_type IN ('missing_person', 'App\Models\MissingPerson')
  AND reference_id IN (SELECT id FROM missing_persons);

UPDATE alerts
SET missing_person_id = reference_id
WHERE reference_type IN ('missing_person', 'App\Models\MissingPerson')
  AND reference_id IN (SELECT id FROM missing_persons);

-- Do not retain records that could only refer to the deleted POI domain.
DELETE FROM alerts WHERE missing_person_id IS NULL;
DELETE FROM detections WHERE missing_person_id IS NULL;

ALTER TABLE detections
    DROP CONSTRAINT IF EXISTS detections_missing_person_id_fkey;
ALTER TABLE detections
    ADD CONSTRAINT detections_missing_person_id_fkey
    FOREIGN KEY (missing_person_id) REFERENCES missing_persons(id) ON DELETE CASCADE;

ALTER TABLE alerts
    DROP CONSTRAINT IF EXISTS alerts_missing_person_id_fkey;
ALTER TABLE alerts
    ADD CONSTRAINT alerts_missing_person_id_fkey
    FOREIGN KEY (missing_person_id) REFERENCES missing_persons(id) ON DELETE CASCADE;

DROP INDEX IF EXISTS detections_reference_type_reference_id_index;
DROP INDEX IF EXISTS alerts_reference_type_reference_id_index;
DROP INDEX IF EXISTS alerts_alert_type_index;

ALTER TABLE detections
    DROP COLUMN IF EXISTS reference_type,
    DROP COLUMN IF EXISTS reference_id;
ALTER TABLE alerts
    DROP COLUMN IF EXISTS alert_type,
    DROP COLUMN IF EXISTS reference_type,
    DROP COLUMN IF EXISTS reference_id;

ALTER TABLE detections ALTER COLUMN missing_person_id SET NOT NULL;
ALTER TABLE alerts ALTER COLUMN missing_person_id SET NOT NULL;

CREATE INDEX IF NOT EXISTS detections_missing_person_id_index ON detections(missing_person_id);
CREATE INDEX IF NOT EXISTS alerts_missing_person_id_index ON alerts(missing_person_id);

COMMIT;

-- Tables currently safe to remove because they are not used by the code:
-- persons_of_interest, person_of_interest_photos,
-- person_of_interest_timeline, verification_documents, system_settings.
--
-- Do not remove cache, sessions, jobs, failed_jobs, job_batches,
-- password_reset_tokens, face_profiles, or face_embeddings: they are
-- framework/runtime tables or are used by the Python recognition service.
