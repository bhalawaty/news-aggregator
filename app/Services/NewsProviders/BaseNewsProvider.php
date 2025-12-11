<?php

namespace App\Services\NewsProviders;

use App\Services\ArticleService;
use App\Services\NewsProviders\Concerns\HandlesHttpRequests;
use App\Services\NewsProviders\Concerns\HandlesDuplicates;
use App\Services\NewsProviders\Concerns\HandlesTimestamps;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

abstract class BaseNewsProvider implements ProviderInterface
{
    use HandlesHttpRequests, HandlesDuplicates, HandlesTimestamps;

    protected array $config;
    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
        $providerKey = $this->providerKey();
        $this->config = config("news.providers.{$providerKey}");

        if (!$this->config) {
            throw new \RuntimeException("Configuration missing for provider: {$providerKey}");
        }

        $this->initializeHttpClient();
    }

    abstract protected function providerKey(): string;

    abstract protected function endpoint(): string;

    abstract protected function mapResponse(array $body): array;

    abstract protected function buildQuery(?Carbon $since, int $pageSize): array;

    /**
     * @param int $sourceId
     * @param Carbon|null $since
     * @param int $pageSize
     * @return array
     */
    public function fetchNew(int $sourceId, ?Carbon $since = null, int $pageSize = 10): array
    {
        $since = $since ?? $this->getLastFetchTime($sourceId);

        $query = $this->buildQuery($since, $pageSize);
        $response = $this->makeRequest($this->endpoint(), $query);
        $articles = $this->mapResponse($response);

        if (empty($articles)) {
            Log::debug("No articles returned from {$this->providerKey()}", [
                'source_id' => $sourceId,
            ]);
            return [];
        }

        $unique = $this->filterUniqueArticles($articles);

        Log::info("Fetched articles from {$this->providerKey()}", [
            'source_id' => $sourceId,
            'total' => count($articles),
            'unique' => count($unique),
        ]);

        return $unique;
    }
}
