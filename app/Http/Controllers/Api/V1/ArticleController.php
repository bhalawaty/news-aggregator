<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ArticleIndexRequest;
use App\Http\Requests\Api\V1\ArticlePreferencesRequest;
use App\Http\Resources\ArticleResource;
use App\Services\ArticleService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService
    ) {}

    /**
     * @param ArticleIndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(ArticleIndexRequest $request): AnonymousResourceCollection
    {
        $articles = $this->articleService->getArticles(
            $request->filters(),
            $request->perPage()
        );

        return ArticleResource::collection($articles);
    }

    /**
     * @param string $id
     * @return ArticleResource
     */
    public function show(string $id): ArticleResource
    {
        $article = $this->articleService->getArticleById((int) $id);
        return new ArticleResource($article);
    }

    /**
     * Get articles by user preferences
     *
     * POST /api/v1/articles/preferences
     * Body: {
     *   "sources": [1, 2],
     *   "categories": [1, 3],
     *   "authors": ["John Doe", "Jane Smith"]
     * }
     * @param ArticlePreferencesRequest $request
     * @return AnonymousResourceCollection
     */
    public function preferences(ArticlePreferencesRequest $request): AnonymousResourceCollection
    {
        return ArticleResource::collection(
            $this->articleService->getArticlesByPreferences(
                $request->preferences(),
                $request->perPage()
            )
        );
    }
}
