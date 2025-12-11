<?php

namespace App\Services\NewsProviders\Concerns;

use App\Dto\NewsArticleDto;
use App\Services\Normalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait HandlesDuplicates
{

    /**
     * @param NewsArticleDto[] $articles
     * @return NewsArticleDto[]
     */
    protected function filterUniqueArticles(array $articles): array
    {
        if (empty($articles)) {
            return [];
        }
        $lookupData = $this->prepareDuplicateLookup($articles);
        $existingArticles = $this->articleService->findDuplicatesBatch(
            $lookupData['hashes'],
            $lookupData['urls']
        );

        $existingHashes = $existingArticles->pluck('normalized_hash')->filter()->flip()->all();
        $existingUrls = $existingArticles->pluck('canonical_url')->filter(fn($url) => !empty($url))->flip()->all();

        $filtered = collect($articles)
            ->filter(fn($article, $index) => $this->isUniqueArticle($article, $index, $lookupData, $existingHashes, $existingUrls)
            )
            ->values()
            ->all();

        $this->logDeduplicationResults(count($articles), count($filtered));

        return $filtered;
    }

    /**
     * @param NewsArticleDto[] $articles
     * @return array
     */
    protected function prepareDuplicateLookup(array $articles): array
    {
        $hashes = [];
        $urls = [];
        $articleHashes = [];

        foreach ($articles as $index => $article) {
            if (empty($article->title) || empty($article->externalId)) {
                Log::warning("Skipping article with missing required fields", [
                    'index' => $index,
                    'title' => $article->title ?? null,
                    'external_id' => $article->externalId ?? null,
                    'url' => $article->url ?? null,
                ]);
                continue;
            }

            $hash = Normalizer::normalizedHash(
                $article->canonicalUrl,
                $article->title,
                $article->content
            );

            $hashes[] = $hash;
            $articleHashes[$index] = $hash;

            if (!empty($article->canonicalUrl)) {
                $urls[] = $article->canonicalUrl;
            }
        }

        return [
            'hashes' => array_unique($hashes),
            'urls' => array_unique(array_filter($urls)),
            'article_hashes' => $articleHashes,
        ];
    }

    /**
     * @param NewsArticleDto $article
     * @param int $index
     * @param array $lookupData
     * @param array $existingHashes
     * @param array $existingUrls
     * @return bool
     */
    protected function isUniqueArticle(NewsArticleDto $article, int $index, array $lookupData, array $existingHashes, array $existingUrls): bool
    {
        $hash = $lookupData['article_hashes'][$index] ?? null;

        if (!$hash) {
            Log::warning("No hash found for article", [
                'index' => $index,
                'title' => $article->title ?? 'unknown',
                'url' => $article->url ?? 'unknown',
            ]);
            return false;
        }

        if (isset($existingHashes[$hash])) {
            return false;
        }

        if ($article->canonicalUrl && isset($existingUrls[$article->canonicalUrl])) {
            return false;
        }

        return true;
    }

    /**
     * @param int $totalArticles
     * @param int $uniqueArticles
     */
    protected function logDeduplicationResults(int $totalArticles, int $uniqueArticles): void
    {
        $duplicateCount = $totalArticles - $uniqueArticles;

        if ($duplicateCount > 0) {
            Log::debug("Filtered duplicates from {$this->providerKey()}", [
                'total' => $totalArticles,
                'duplicates' => $duplicateCount,
                'unique' => $uniqueArticles,
                'duplicate_percentage' => round(($duplicateCount / $totalArticles) * 100, 2) . '%',
            ]);
        } else {
            Log::debug("All articles from {$this->providerKey()} are unique", [
                'total' => $totalArticles,
            ]);
        }
    }
}
