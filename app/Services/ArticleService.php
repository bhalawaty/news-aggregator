<?php

namespace App\Services;

use App\Dto\NewsArticleDto;
use App\Models\Category;
use App\Repositories\ArticleRepositoryInterface;
use App\Repositories\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Article;

class ArticleService
{
    protected ArticleRepositoryInterface $articleRepository;
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @param ArticleRepositoryInterface $articleRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(ArticleRepositoryInterface $articleRepository,CategoryRepositoryInterface $categoryRepository)
    {
        $this->articleRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
    }


    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getArticles(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->articleRepository->getArticles($filters, $perPage);
    }

    /**
     * @param int $id
     * @return Article
     */
    public function getArticleById(int $id): Article
    {
        return $this->articleRepository->getArticleById($id);
    }

    /**
     * @param array $articlesDto
     * @param int $sourceId
     * @return void
     */
    public function saveBulkNormalizedArticles(array $articlesDto, int $sourceId): void
    {
        if (empty($articlesDto)) {
            return;
        }

        $categories = [];
        foreach ($articlesDto as $dto) {
            if (!empty($dto->category)) {
                $categories[] = $dto->category;
            }
        }
        $categories = array_unique($categories);
        $categoryMap = $this->categoryRepository->bulkFindOrCreate($categories);

        $rows = [];

        foreach ($articlesDto as $dto) {

            $canonicalUrl = Normalizer::canonicalizeUrl($dto->url);

            $hash = Normalizer::normalizedHash(
                $canonicalUrl,
                $dto->title,
                $dto->content
            );

            $rows[] = [
                'source_id' => $sourceId,
                'external_id' => $dto->externalId,
                'category_id' => $dto->category ? ($categoryMap[$dto->category] ?? null) : null,
                'author' => $dto->author,
                'url' => $dto->url,
                'canonical_url' => $canonicalUrl,
                'title' => $dto->title,
                'summary' => $dto->summary,
                'content' => $dto->content,
                'published_at' => $dto->publishedAt,
                'language' => $dto->language,
                'image_url' => $dto->imageUrl,
                'normalized_hash' => $hash,
                'ingestion_metadata' => json_encode([$dto->raw]),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        $this->articleRepository->bulkUpsert($rows);
    }


    /**
     * @param array $hashes
     * @param array $urls
     * @return Collection
     */
    public function findDuplicatesBatch(array $hashes, array $urls = []): Collection
    {
        return $this->articleRepository->findDuplicatesBatch($hashes, $urls);
    }

    /**
     * @param array $preferences
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getArticlesByPreferences(array $preferences, int $perPage = 15): LengthAwarePaginator
    {
        return $this->articleRepository->getArticlesByPreferences($preferences, $perPage);
    }

}
