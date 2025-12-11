<?php

namespace App\Services\NewsProviders;

use App\Dto\NewsArticleDto;
use App\Services\Normalizer;
use Carbon\Carbon;

class NewsApiProvider extends BaseNewsProvider
{
    protected function providerKey(): string
    {
        return 'newsapi';
    }

    protected function endpoint(): string
    {
        return 'everything';
    }


    protected function buildQuery(?Carbon $since, int $pageSize): array
    {
        $query = [
            'apiKey' => $this->config['key'] ?? null,
            'q' => 'artificial intelligence',
            'language' => 'en',
            'sortBy' => 'publishedAt',
            'pageSize' => min($pageSize, 100),
        ];

        if ($since) {
            $query['from'] = $since->toIso8601String();
        }

        return $query;
    }

    protected function mapResponse(array $body): array
    {
        $articles = [];

        foreach (($body['articles'] ?? []) as $a) {
            $canonical = Normalizer::canonicalizeUrl($a['url'] ?? null);

            $articles[] = new NewsArticleDto(
                externalId: $a['url'] ?? null,
                url: $a['url'] ?? null,
                canonicalUrl: $canonical,
                title: $a['title'] ?? '',
                summary: $a['description'] ?? null,
                content: $a['content'] ?? $a['description'] ?? null,
                publishedAt: $a['publishedAt'] ?? null,
                author: $a['author'] ?? null,
                imageUrl: $a['urlToImage'] ?? null,
                language: $a['language'] ?? null,
                category: 'general',
                raw: $a
            );
        }

        return $articles;
    }
}
