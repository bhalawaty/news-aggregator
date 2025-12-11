<?php

namespace App\Console\Commands;

use App\Models\Source;
use App\Jobs\FetchSourceJob;
use Illuminate\Console\Command;

/**
 * Test fetching new articles from a single source
 * Usage: php artisan news:fetch {source_id}
 */
class FetchNewsCommand extends Command
{
    protected $signature = 'news:fetch {source? : Source slug to fetch}';
    protected $description = 'Fetch new articles from sources';

    public function handle(): int
    {
        $sourceSlug = $this->argument('source');

        if ($sourceSlug) {
            $source = Source::query()
                ->where('slug', $sourceSlug)
                ->where('enabled', true)
                ->first();

            if (!$source) {
                $this->error("Source '{$sourceSlug}' not found or disabled");
                return self::FAILURE;
            }

            $this->info("Fetching articles from {$source->name}...");
            FetchSourceJob::dispatch($source->id);
            $this->info("Job dispatched!");

        } else {
            $sources = Source::where('enabled', true)->get();

            $this->info("Dispatching jobs for " . $sources->count() . " sources...");

            foreach ($sources as $source) {
                FetchSourceJob::dispatch($source->id);
                $this->line("  ✓ {$source->name}");
            }
        }

        return 0;
    }
}
