<?php

namespace App\Services\NewsProviders;

use App\Dto\NewsArticleDto;
use App\Services\Normalizer;
use Carbon\Carbon;

class GuardianProvider extends BaseNewsProvider
{
    protected function providerKey(): string
    {
        return 'guardian';
    }

    protected function endpoint(): string
    {
        return 'search';
    }

    protected function mapResponse(array $body): array
    {
        $articles = [];

        foreach ($body['response']['results'] ?? [] as $r) {
            $canonicalUrl = Normalizer::canonicalizeUrl($r['webUrl'] ?? null);

            $articles[] = new NewsArticleDto(
                externalId: $r['id'] ?? null,
                url: $r['webUrl'] ?? null,
                canonicalUrl: $canonicalUrl,
                title: $r['webTitle'] ?? '',
                summary: $r['fields']['trailText'] ?? null,
                content: $r['fields']['body'] ?? null,
                publishedAt: isset($r['webPublicationDate'])
                    ? Carbon::parse($r['webPublicationDate'])
                    : null,
                author: $r['fields']['byline'] ?? null,
                imageUrl: $r['fields']['thumbnail'] ?? null,
                language: null,
                category: $r['sectionName'] ?? null,
                raw: $r
            );
        }

        return $articles;
    }

    protected function buildQuery(?Carbon $since, int $pageSize): array
    {
        $query = [
            'api-key' => $this->config['key'] ?? null,
            'order-by' => 'newest',
            'page-size' => min($pageSize, 200),
            'show-fields' => 'thumbnail,trailText,body,byline',
        ];

        if ($since) {
            $query['from-date'] = $since->format('Y-m-d');
        }

        return $query;
    }


}
