<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentArticleRepository implements ArticleRepositoryInterface
{

    /**
     * @param array $rows
     * @return void
     */
    public function bulkUpsert(array $rows): void
    {

        Article::query()->upsert(
            $rows,
            ['source_id', 'external_id'],
            ['category_id',
                'author',
                'url',
                'canonical_url',
                'title',
                'summary',
                'content',
                'published_at',
                'language',
                'image_url',
                'normalized_hash',
                'ingestion_metadata',
                'updated_at'
            ]
        );

        $this->indexArticles($rows);

    }

    /**
     * @param array $rows
     * @return void
     */
    private function indexArticles(array $rows): void
    {
        if (empty($rows)) {
            return;
        }
        $externalIds = collect($rows)->pluck('external_id')->filter()->toArray();

        if (empty($externalIds)) {
            return;
        }

        $sourceId = $rows[0]['source_id'] ?? null;
        if (!$sourceId) {
            \Log::warning('Cannot index articles: missing source_id');
            return;
        }

        Article::whereIn('external_id', $externalIds)
            ->where('source_id', $sourceId)
            ->searchable();
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @param bool $usePreferencesKeys
     * @return LengthAwarePaginator
     */
    public function getArticles(array $filters = [], int $perPage = 15, bool $usePreferencesKeys = false): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        if ($search) {
            return Article::search($search)
                ->query(fn($q) => $this->applyFilters($q, $filters, $usePreferencesKeys))
                ->paginate($perPage);
        }

        $query = Article::with(['source', 'category'])
            ->latest('published_at');

        $this->applyFilters($query, $filters, $usePreferencesKeys);

        return $query->paginate($perPage);
    }

    /**
     * @param int $id
     * @return Article
     */
    public function getArticleById(int $id): Article
    {
        return Article::with(['source', 'category'])->findOrFail($id);
    }

    /**
     * @param array $hashes
     * @param array $urls
     * @return Collection
     */
    public function findDuplicatesBatch(array $hashes, array $urls = []): Collection
    {
        $results = collect();

        if (!empty($hashes)) {
            $results = $results->merge(
                Article::whereIn('normalized_hash', $hashes)
                    ->get(['id', 'normalized_hash', 'canonical_url'])
            );
        }

        if (!empty($urls)) {
            $results = $results->merge(
                Article::whereIn('canonical_url', $urls)
                    ->get(['id', 'normalized_hash', 'canonical_url'])
            );
        }

        return $results->unique('id');
    }

    /**
     * @param Builder $query
     * @param array $filters
     * @param bool $usePreferencesKeys
     * @return void
     */
    private function applyFilters(Builder $query, array $filters, bool $usePreferencesKeys = false): void
    {
        $filterMap = $usePreferencesKeys
            ? [
                'sources' => fn($q, $value) => $q->whereIn('source_id', $value),
                'categories' => fn($q, $value) => $q->whereIn('category_id', $value),
                'authors' => fn($q, $value) => $q->whereIn('author', $value),
            ]
            : [
                'source_id' => fn($q, $value) => $q->where('source_id', $value),
                'category_id' => fn($q, $value) => $q->where('category_id', $value),
                'author' => fn($q, $value) => $q->whereRaw('LOWER(author) LIKE LOWER(?)', ["%{$value}%"]),
                'from_date' => fn($q, $value) => $q->where('published_at', '>=', $value),
                'to_date' => fn($q, $value) => $q->where('published_at', '<=', $value),
            ];

        foreach ($filterMap as $key => $callback) {
            if (!empty($filters[$key])) {
                $callback($query, $filters[$key]);
            }
        }
    }

    /**
     * @param array $preferences
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getArticlesByPreferences(array $preferences, int $perPage = 15): LengthAwarePaginator
    {
        return $this->getArticles($preferences, $perPage, true);
    }
}
