<?php

namespace App\Console\Commands;

use App\Models\TemporaryUpload;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('uploads:prune-temporary {--hours=24 : Prune uploads older than this many hours}')]
#[Description('Prune old temporary uploads and remove their files')]
class PruneTemporaryUploads extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $cutoff = now()->subHours($hours);
        $prunedCount = 0;

        foreach (TemporaryUpload::query()->where('created_at', '<', $cutoff)->cursor() as $temporaryUpload) {
            Storage::disk($temporaryUpload->disk)->delete($temporaryUpload->path);
            $temporaryUpload->delete();
            $prunedCount++;
        }

        $this->info("Pruned {$prunedCount} temporary upload(s).");

        return self::SUCCESS;
    }
}
