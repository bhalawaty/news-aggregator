<?php
namespace Tests\Feature\Articles\Traits;

use App\Models\Article;


trait ArticleAssertions
{
    /**
     * @param $response
     * @return void
     */
    protected function assertArticleJsonStructure($response): void
    {
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'summary',
                    'content',
                    'author',
                    'published_at',
                    'source' => ['id', 'name', 'slug'],
                    'category' => ['id', 'name', 'slug'],
                ]
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'to', 'per_page', 'total', 'last_page']
        ]);
    }

    /**
     * @param $response
     * @param Article $article
     * @return void
     */
    protected function assertArticleMatchesData($response, Article $article): void
    {
        $response->assertJson([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'author' => $article->author,
                'summary' => $article->summary,
            ]
        ]);
    }
}
