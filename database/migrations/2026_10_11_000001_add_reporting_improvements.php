<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detections', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
        });
        Schema::table('missing_persons', function (Blueprint $table) {
            $table->text('national_id_encrypted')->nullable();
            $table->string('national_id_hash', 64)->nullable()->index();
            $table->timestamp('consented_at')->nullable();
            $table->json('duplicate_review')->nullable();
        });
        DB::table('missing_persons')->whereNotNull('national_id')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('missing_persons')->where('id', $row->id)->update([
                    'national_id_encrypted' => Crypt::encryptString($row->national_id),
                    'national_id_hash' => hash_hmac('sha256', preg_replace('/[^A-Z0-9]/', '', strtoupper($row->national_id)), config('app.key')),
                    'national_id' => null,
                ]);
            }
        });
        Schema::table('sightings', function (Blueprint $table) {
            $table->text('reporter_name')->nullable()->change();
            $table->text('reporter_phone')->nullable()->change();
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('privacy_purged_at')->nullable();
        });
        DB::table('sightings')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('sightings')->where('id', $row->id)->update(collect(['reporter_name', 'reporter_phone', 'notes'])->mapWithKeys(
                    fn ($field) => [$field => $row->$field === null ? null : Crypt::encryptString($row->$field)]
                )->all());
            }
        });
        Schema::create('sighting_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sighting_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('sha256', 64);
            $table->timestamp('expires_at')->index();
            $table->timestamp('purged_at')->nullable();
            $table->timestamps();
        });
        Schema::create('privacy_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->string('resource_type', 100);
            $table->string('resource_id', 100)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->index();
        });
        Schema::create('submission_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('fingerprint', 64);
            $table->json('response');
            $table->unsignedSmallInteger('status');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        // Encryption is intentionally not reversed by a schema rollback.
        throw new RuntimeException('Restore a verified backup to roll back encrypted reporting data.');
    }
};
