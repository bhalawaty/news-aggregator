<?php

namespace App\Jobs;

use App\Services\ArticleService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessArticleChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $articles;
    public int $sourceId;

    /**
     * @param array $articles
     * @param int $sourceId
     */
    public function __construct(array $articles, int $sourceId)
    {
        $this->articles = $articles;
        $this->sourceId = $sourceId;
    }

    /**
     * @param ArticleService $articleService
     * @return void
     * @throws Exception
     */
    public function handle(ArticleService $articleService): void
    {
        try {
            $articleService->saveBulkNormalizedArticles($this->articles, $this->sourceId);
        } catch (Exception $e) {
            \Log::error('Failed to process article chunk', [
                'source_id' => $this->sourceId,
                'article_count' => count($this->articles),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
