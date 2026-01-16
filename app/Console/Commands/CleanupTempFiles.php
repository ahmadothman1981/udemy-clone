<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupTempFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:temp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary uploaded files older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Storage::disk('local');
        $files = $disk->files('tmp');
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);
            $lastModifiedDate = Carbon::createFromTimestamp($lastModified);

            if ($lastModifiedDate->lt(now()->subHours(24))) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} old temporary files.");
    }
}
