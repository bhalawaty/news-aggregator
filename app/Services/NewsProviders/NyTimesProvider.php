<?php

namespace App\Services\NewsProviders;

use App\Dto\NewsArticleDto;
use App\Services\Normalizer;
use Carbon\Carbon;

class NyTimesProvider extends BaseNewsProvider
{
    protected function providerKey(): string
    {
        return 'nytimes';
    }

    protected function endpoint(): string
    {
        return 'svc/search/v2/articlesearch.json';
    }


    protected function mapResponse(array $body): array
    {
        $articles = [];

        foreach ($body['response']['docs'] ?? [] as $d) {

            $image = null;
            foreach ($d['multimedia'] ?? [] as $m) {
                if (($m['subtype'] ?? null) === 'thumbnail') {
                    $image = 'https://www.nytimes.com/' . $m['url'];
                    break;
                }
            }

            $canonicalUrl = Normalizer::canonicalizeUrl($d['web_url'] ?? null);

            $articles[] = new NewsArticleDto(
                externalId: $d['_id'] ?? null,
                url: $d['web_url'] ?? null,
                canonicalUrl: $canonicalUrl,
                title: $d['headline']['main'] ?? '',
                summary: $d['snippet'] ?? null,
                content: $d['lead_paragraph'] ?? null,
                publishedAt: isset($d['pub_date'])
                    ? Carbon::parse($d['pub_date'])
                    : null,
                author: $d['byline']['original'] ?? null,
                imageUrl: $image,
                language: $d['language'] ?? null,
                category: 'general',
                raw: $d
            );
        }

        return $articles;
    }


    protected function buildQuery(?Carbon $since, int $pageSize): array
    {
        $query = [
            'api-key' => $this->config['key'] ?? null,
            'sort' => 'newest',
        ];

        if ($since) {
            $query['begin_date'] = $since->format('Ymd');
        }

        return $query;
    }

}
