<?php

namespace App\Console\Commands;

use App\Models\Sighting;
use App\Models\SightingEvidence;
use App\Services\Privacy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PruneReportingData extends Command
{
    protected $signature = 'reporting:prune {--dry-run : List counts without changing data}';
    protected $description = 'Apply evidence, witness and access-log retention periods';

    public function handle(): int
    {
        $evidence = SightingEvidence::whereNull('purged_at')->where('expires_at', '<=', now());
        $witnesses = Sighting::whereNull('privacy_purged_at')->where('created_at', '<=', now()->subDays(config('reporting.witness_retention_days')));
        $logs = DB::table('privacy_access_logs')->where('created_at', '<', now()->subDays(config('reporting.access_log_retention_days')));
        $this->info("Expired evidence: {$evidence->count()}; witness records: {$witnesses->count()}; old access logs: {$logs->count()}");
        if ($this->option('dry-run')) return self::SUCCESS;
        foreach ($evidence->cursor() as $item) {
            $disk = Storage::disk('local');
            if ($disk->exists($item->path) && !$disk->delete($item->path)) {
                $this->error('Unable to remove evidence '.$item->id); continue;
            }
            $item->update(['purged_at' => now()]);
            Privacy::log('retention_purge', 'evidence', $item->id);
        }
        $witnesses->update(['reporter_name' => null, 'reporter_phone' => null, 'notes' => null, 'privacy_purged_at' => now()]);
        $logs->delete();
        return self::SUCCESS;
    }
}
