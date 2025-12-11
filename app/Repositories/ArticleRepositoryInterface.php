<?php


namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Article;
use Illuminate\Support\Collection;

interface ArticleRepositoryInterface
{
    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getArticles(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * @param int $id
     * @return Article
     */
    public function getArticleById(int $id): Article;

    /**
     * @param array $hashes
     * @param array $urls
     * @return Collection
     */
    public function findDuplicatesBatch(array $hashes, array $urls = []): Collection;

    /**
     * @param array $rows
     * @return void
     */
    public function bulkUpsert(array $rows): void;

    /**
     * @param array $preferences
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getArticlesByPreferences(array $preferences, int $perPage = 15): LengthAwarePaginator;

}
