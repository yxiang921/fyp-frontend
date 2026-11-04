<?php

namespace Tests\Feature;

use App\Models\{MissingPerson, Sighting, SightingEvidence, User, Camera, Detection, PatrolArea, Notification};
use App\Services\{DuplicateCaseService, ReportingAnalytics, PatrolRoutingService, PythonApiService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Schema, Storage, Http, Mail, Queue};
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportingImprovementsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null,
            'services.python_api.api_key' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        foreach (['0001_01_01_000000_create_users_table', '2024_01_01_000001_create_missing_persons_table',
            '2024_01_01_000003_create_cameras_table', '2024_01_01_000004_create_detections_table',
            '2024_01_01_000005_create_alerts_table', '2024_01_01_000007_create_notifications_table',
            '2026_07_24_000001_create_email_logs_table',
            '2026_08_06_000000_add_coordinates_to_missing_persons_table', '2026_08_23_000002_create_sightings_table',
            '2026_10_10_000001_add_identifying_details_to_missing_persons_table', '2026_10_11_000001_add_reporting_improvements',
            '2026_11_01_000001_create_patrol_areas', '2026_11_01_000002_add_sighting_alerts_and_multi_frame_detections'] as $name) {
            (require database_path('migrations/'.$name.'.php'))->up();
        }
        Schema::table('missing_persons', fn (Blueprint $t) => $t->unsignedBigInteger('created_by')->nullable()->change());
        Schema::table('missing_person_timeline', fn (Blueprint $t) => $t->unsignedBigInteger('recorded_by')->nullable()->change());
        Storage::fake('local');
        Http::preventStrayRequests();
    }

    private function person(array $extra = []): MissingPerson
    {
        return MissingPerson::create(array_merge(['case_number' => 'MP-TEST-'.bin2hex(random_bytes(4)), 'full_name' => 'Test Person', 'gender' => 'male', 'status' => 'active'], $extra));
    }

    public function test_guest_tracking_discloses_only_status_and_dates(): void
    {
        $person = $this->person(['status' => 'pending', 'national_id' => '990101-01-1234', 'description' => 'Private detail']);
        $response = $this->postJson('/api/track-case', ['case_number' => $person->case_number])->assertOk();
        $this->assertSame(['case_number', 'status', 'submitted_at', 'updated_at'], array_keys($response->json('data')));
        $response->assertDontSee('Private detail')->assertDontSee('Test Person')->assertDontSee('990101');
        $this->getJson('/api/missing-persons/'.$person->id)->assertNotFound();
    }

    public function test_sensitive_fields_are_encrypted_and_duplicate_ic_uses_normalised_hash(): void
    {
        $person = $this->person(['national_id' => '990101-01-1234']);
        $raw = DB::table('missing_persons')->find($person->id);
        $this->assertNull($raw->national_id);
        $this->assertStringNotContainsString('990101', $raw->national_id_encrypted);
        $this->assertSame('990101-01-1234', $person->fresh()->national_id);
        $result = app(DuplicateCaseService::class)->inspect(['full_name' => 'Different Name', 'national_id' => '990101011234']);
        $this->assertContains('national_id', $result['matches'][0]['reasons']);
    }

    public function test_offline_retry_is_idempotent_and_changed_payload_is_rejected(): void
    {
        $payload = ['full_name' => 'Offline Test', 'gender' => 'male', 'last_seen_location' => 'Test location', 'consent' => true,
            'submission_id' => 'f852923d-ae86-4af4-962b-efbc50e4a552'];
        $first = $this->postJson('/api/missing-persons', $payload)->assertCreated();
        $again = $this->postJson('/api/missing-persons', $payload)->assertCreated();
        $this->assertSame($first->json('data.case_number'), $again->json('data.case_number'));
        $this->assertSame(1, MissingPerson::count());
        $this->postJson('/api/missing-persons', array_merge($payload, ['full_name'=>'Changed']))->assertStatus(409);
    }

    public function test_duplicate_requires_explicit_acknowledgement_and_consent(): void
    {
        $this->person();
        $payload=['full_name'=>'Test Person','gender'=>'male','last_seen_location'=>'Test', 'consent'=>true];
        $this->postJson('/api/missing-persons',$payload)->assertUnprocessable()->assertJsonValidationErrors('duplicate_acknowledged');
        $this->postJson('/api/missing-persons',array_merge($payload,['duplicate_acknowledged'=>true]))->assertCreated();
        $this->postJson('/api/missing-persons',array_merge($payload,['consent'=>false]))->assertUnprocessable()->assertJsonValidationErrors('consent');
    }

    public function test_sighting_evidence_is_private_hashed_and_checked_on_download(): void
    {
        $person=$this->person();
        $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRz8AAAAASUVORK5CYII=');
        $this->postJson('/api/missing-persons/'.$person->id.'/sightings', [
            'location'=>'Test park','sighted_at'=>now()->subMinute()->toIso8601String(),'latitude'=>0,'longitude'=>0,
            'reporter_name'=>'Witness','reporter_phone'=>'0123456789','notes'=>'Private notes','consent'=>true,
            'evidence'=>[UploadedFile::fake()->createWithContent('test.png',$png)],
        ])->assertCreated()->assertJsonMissingPath('data.reporter_phone');
        $item=SightingEvidence::firstOrFail();$sighting=Sighting::firstOrFail();
        $this->assertSame(hash('sha256',$png),$item->sha256);
        $this->assertNotSame('0123456789',DB::table('sightings')->value('reporter_phone'));
        $this->assertSame('0123456789',$sighting->reporter_phone);
        $this->getJson('/api/evidence/'.$item->id)->assertUnauthorized();
        $public=User::factory()->create(['role'=>'public_user']);Sanctum::actingAs($public);
        $this->getJson('/api/evidence/'.$item->id)->assertForbidden();
        $staff=User::factory()->create(['role'=>'pdrm_officer']);Sanctum::actingAs($staff);
        $this->getJson('/api/evidence/'.$item->id)->assertOk()->assertHeader('content-type','image/png');
        $this->assertDatabaseHas('privacy_access_logs',['action'=>'download','resource_id'=>(string)$item->id]);
        Storage::disk('local')->put($item->path,'tampered');
        $this->getJson('/api/evidence/'.$item->id)->assertStatus(409);
        $item->update(['expires_at'=>now()->subDay()]);
        $this->getJson('/api/evidence/'.$item->id)->assertStatus(410);
        $this->artisan('reporting:prune',['--dry-run'=>true])->assertSuccessful();
        Storage::disk('local')->assertExists($item->path);
        $this->artisan('reporting:prune')->assertSuccessful();
        Storage::disk('local')->assertMissing($item->path);
        $this->assertNotNull($item->fresh()->purged_at);
    }

    public function test_maps_protect_coordinates_and_include_zero_coordinates_and_both_sources(): void
    {
        $person=$this->person();
        Sighting::create(['missing_person_id'=>$person->id,'location'=>'Test','latitude'=>0,'longitude'=>0,'sighted_at'=>now(),'status'=>'new']);
        $staff=User::factory()->create(['role'=>'pdrm_officer']);
        $camera=Camera::create(['camera_id'=>'CAM-TEST','name'=>'Test camera','location'=>'Test','latitude'=>1,'longitude'=>2,'registered_by'=>$staff->id]);
        Detection::create(['detection_id'=>'DET-TEST','camera_id'=>$camera->id,'missing_person_id'=>$person->id,'status'=>'new','detected_at'=>now()]);
        $this->getJson('/api/missing-persons/'.$person->id.'/map')->assertUnauthorized();
        Sanctum::actingAs($staff);
        $response=$this->getJson('/api/missing-persons/'.$person->id.'/map')->assertOk()->assertJsonCount(2,'data');
        $this->assertSame(['sighting','detection'],array_column($response->json('data'),'type'));
    }

    public function test_analytics_rates_and_timestamps_have_explicit_denominators(): void
    {
        $person=$this->person();$user=User::factory()->create();
        $camera=Camera::create(['camera_id'=>'CAM-TEST','name'=>'Test','location'=>'Test','registered_by'=>$user->id]);
        $d=Detection::create(['detection_id'=>'D-1','camera_id'=>$camera->id,'missing_person_id'=>$person->id,'status'=>'new','detected_at'=>now()->subMinutes(10)]);
        $d->update(['status'=>'confirmed']);
        Detection::create(['detection_id'=>'D-2','camera_id'=>$camera->id,'missing_person_id'=>$person->id,'status'=>'false_positive','detected_at'=>now()->subMinutes(10)]);
        Detection::create(['detection_id'=>'D-3','camera_id'=>$camera->id,'missing_person_id'=>$person->id,'status'=>'new','detected_at'=>now()]);
        $a=app(ReportingAnalytics::class)->summary();
        $this->assertSame(33.3,$a['confirmationRate']);$this->assertEquals(50,$a['falseRate']);$this->assertSame(2,$a['responseSamples']);
        $this->assertNotNull($d->fresh()->confirmed_at);
    }

    public function test_public_payload_redacts_identifiers_and_precise_birthdate(): void
    {
        $person=$this->person(['description'=>'Contact test@example.com or 012-3456789. IC 990101-01-1234.', 'date_of_birth'=>'1999-01-01', 'last_seen_latitude'=>3.1,'last_seen_longitude'=>101.1]);
        $response=$this->getJson('/api/missing-persons/'.$person->id)->assertOk();
        $response->assertJsonPath('data.date_of_birth',null)->assertJsonPath('data.last_seen_latitude',null)->assertDontSee('test@example.com')->assertDontSee('012-3456789')->assertDontSee('990101');
    }

    public function test_detection_notifies_only_patrol_units_in_the_camera_area(): void
    {
        Mail::fake();
        Queue::fake();
        $skudai = PatrolArea::create(['name'=>'Skudai','code'=>'SKUDAI','is_active'=>true]);
        $jb = PatrolArea::create(['name'=>'Johor Bahru','code'=>'JB','is_active'=>true]);
        $skudaiPatrol = User::factory()->create(['role'=>'patrol_unit','is_active'=>true]);
        $jbPatrol = User::factory()->create(['role'=>'patrol_unit','is_active'=>true]);
        $officer = User::factory()->create(['role'=>'pdrm_officer','is_active'=>true]);
        $skudaiPatrol->patrolAreas()->attach($skudai);
        $jbPatrol->patrolAreas()->attach($jb);
        $camera = Camera::create(['camera_id'=>'CAM-SKUDAI','name'=>'Skudai Camera','location'=>'Skudai','patrol_area_id'=>$skudai->id,'registered_by'=>$officer->id]);
        $person = $this->person(['created_by'=>$officer->id]);
        $detection = Detection::create(['detection_id'=>'DET-AREA','camera_id'=>$camera->id,'missing_person_id'=>$person->id,'status'=>'new','detected_at'=>now(),'similarity_score'=>88]);

        $recipients = app(PatrolRoutingService::class)->recipientsForCamera($camera);
        $this->assertSame([$skudaiPatrol->id], $recipients->pluck('id')->all());

        $alert = PythonApiService::createAlertAndNotify($detection, $person, $camera, 88, 'test', $officer->id);
        $this->assertTrue(Notification::where('user_id',$skudaiPatrol->id)->where('data->alert_id',$alert->id)->exists());
        $this->assertFalse(Notification::where('user_id',$jbPatrol->id)->exists());

        $visible = app(PatrolRoutingService::class)->restrictAlertsToPatrolAreas(\App\Models\Alert::query(), $skudaiPatrol)->pluck('id')->all();
        $hidden = app(PatrolRoutingService::class)->restrictAlertsToPatrolAreas(\App\Models\Alert::query(), $jbPatrol)->pluck('id')->all();
        $this->assertSame([$alert->id], $visible);
        $this->assertSame([], $hidden);
    }

    public function test_confirmed_sighting_creates_one_area_scoped_alert_and_notifications(): void
    {
        Queue::fake();
        $skudai = PatrolArea::create(['name'=>'Skudai','code'=>'SKUDAI','center_latitude'=>1.537,'center_longitude'=>103.661,'is_active'=>true]);
        $jb = PatrolArea::create(['name'=>'Johor Bahru','code'=>'JB','center_latitude'=>1.492,'center_longitude'=>103.741,'is_active'=>true]);
        $officer = User::factory()->create(['role'=>'pdrm_officer','is_active'=>true]);
        $skudaiPatrol = User::factory()->create(['role'=>'patrol_unit','is_active'=>true]);
        $jbPatrol = User::factory()->create(['role'=>'patrol_unit','is_active'=>true]);
        $skudaiPatrol->patrolAreas()->attach($skudai);
        $jbPatrol->patrolAreas()->attach($jb);
        $person = $this->person(['created_by'=>$officer->id]);
        $sighting = Sighting::create(['missing_person_id'=>$person->id,'location'=>'Skudai Parade','latitude'=>1.537,'longitude'=>103.661,'sighted_at'=>now(),'status'=>'new']);

        $this->actingAs($officer)->patch(route('sightings.update-status', $sighting), [
            'status'=>'confirmed', 'notes'=>'Witness and evidence checked.',
        ])->assertRedirect(route('sightings.show', $sighting));

        $alert = \App\Models\Alert::where('sighting_id', $sighting->id)->firstOrFail();
        $this->assertSame($skudai->id, $alert->patrol_area_id);
        $this->assertTrue(Notification::where('user_id',$skudaiPatrol->id)->where('data->alert_id',$alert->id)->exists());
        $this->assertFalse(Notification::where('user_id',$jbPatrol->id)->exists());
        $this->assertDatabaseHas('sighting_status_history',['sighting_id'=>$sighting->id,'to_status'=>'confirmed']);
        $this->get(route('alerts.show', $alert))->assertOk()->assertSee('confirmed public sighting');

        $this->patch(route('sightings.update-status', $sighting), ['status'=>'confirmed']);
        $this->assertSame(1, \App\Models\Alert::where('sighting_id', $sighting->id)->count());
    }

    public function test_map_tile_proxy_caches_upstream_tiles(): void
    {
        Http::fake(['tile.openstreetmap.org/*' => Http::response('PNGDATA', 200, ['Content-Type'=>'image/png'])]);
        $this->get('/map-tiles/1/0/0.png')->assertOk()->assertHeader('Content-Type','image/png');
        $this->get('/map-tiles/1/0/0.png')->assertOk();
        Http::assertSentCount(1);
        Storage::disk('local')->assertExists('map-tiles/1/0/0.png');
    }
}
