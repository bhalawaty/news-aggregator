<?php

namespace App\Jobs;

use App\Models\Source;
use App\Services\NewsFetchService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public array $backoff = [10, 30, 60];
    private const CHUNK_SIZE = 100;

    /**
     * @param int $sourceId
     */
    public function __construct(public int $sourceId)
    {
    }

    /**
     * @param NewsFetchService $fetchService
     * @return void
     * @throws BindingResolutionException
     */
    public function handle(NewsFetchService $fetchService): void
    {
        $source = Source::query()->find($this->sourceId);

        if (!$source || !$source->enabled) {
            return;
        }

        try {
            $articles = $fetchService->fetchNewArticles($source);

            collect($articles)
                ->chunk(self::CHUNK_SIZE)
                ->each(fn ($chunk) =>
                ProcessArticleChunkJob::dispatch($chunk->toArray(), $source->id));


            $source->update(['last_success_at' => now()]);

            Log::info("Fetched {count} articles from {source}", [
                'count' => count($articles),
                'source' => $source->name,
            ]);

        } catch (Exception $e) {
            Log::error("Failed to fetch from {source}: {error}", [
                'source' => $source->name,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }


    /**
     * @param Throwable $e
     * @return void
     */
    public function failed(Throwable $e): void
    {
        Log::critical("Permanently failed fetching source {$this->sourceId}: {$e->getMessage()}");
    }
}
