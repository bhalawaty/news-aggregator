<?php

namespace Tests\Feature\Articles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Articles\Traits\ArticleAssertions;
use Tests\Feature\Articles\Traits\ArticleTestHelpers;
use Tests\TestCase;


class ArticleControllerTest extends TestCase
{
    use RefreshDatabase, ArticleTestHelpers, ArticleAssertions;

    #[Test]
    public function it_returns_paginated_articles(): void
    {
        $this->createArticles(10);

        $response = $this->getJson('/api/v1/articles');

        $response->assertOk();
        $this->assertArticleJsonStructure($response);
        $response->assertJsonCount(10, 'data');
    }

    #[Test]
    public function it_returns_single_article_by_id(): void
    {
        $article = $this->createCompleteArticle();

        $response = $this->getJson("/api/v1/articles/{$article->id}");

        $response->assertOk();
        $this->assertArticleMatchesData($response, $article);
    }

    #[Test]
    public function it_returns_404_for_non_existent_article(): void
    {
        $response = $this->getJson('/api/v1/articles/999999');

        $response->assertNotFound();
    }

    #[Test]
    public function it_filters_articles_by_user_preferences(): void
    {
        $targetSource = $this->createSource();
        $targetCategory = $this->createCategory();

        $matchingArticle = $this->createArticlesWithRelations(
            count: 1,
            source: $targetSource,
            category: $targetCategory,
            attributes: ['author' => 'John Doe']
        );

        $this->createArticles(5);

        $response = $this->postJson('/api/v1/articles/preferences', [
            'sources' => [$targetSource->id],
            'categories' => [$targetCategory->id],
            'authors' => ['John Doe'],
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingArticle->id);
    }

    #[Test]
    public function it_filters_articles_by_source_category_and_author(): void
    {
        $source = $this->createSource();
        $category = $this->createCategory();

        $matchingArticle = $this->createArticlesWithRelations(
            count: 1,
            source: $source,
            category: $category,
            attributes: ['author' => 'Alice']
        );

        $this->createArticles(5);

        $response = $this->getJson('/api/v1/articles?' . http_build_query([
                'source' => $source->id,
                'category' => $category->id,
                'author' => 'Alice'
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingArticle->id);
    }

    #[Test]
    public function it_searches_articles_by_keyword(): void
    {
        $source = $this->createSource();
        $category = $this->createCategory();

        $laravelArticle1 = $this->createArticlesWithRelations(
            count: 1,
            source: $source,
            category: $category,
            attributes: [
                'title' => 'Laravel 11 Released',
                'content' => 'Amazing new features'
            ]
        );

        $laravelArticle2 = $this->createArticlesWithRelations(
            count: 1,
            source: $source,
            category: $category,
            attributes: [
                'title' => 'Laravel Tips',
                'content' => 'Pro tips for Laravel'
            ]
        );

        $vueArticle = $this->createArticlesWithRelations(
            count: 1,
            source: $source,
            category: $category,
            attributes: [
                'title' => 'Vue 5 Released',
                'content' => 'New Vue features'
            ]
        );

        $response = $this->getJson('/api/v1/articles?search=Laravel');

        $response->assertOk()->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($laravelArticle1->id));
        $this->assertTrue($ids->contains($laravelArticle2->id));
        $this->assertFalse($ids->contains($vueArticle->id));
    }

    #[Test]
    public function it_searches_articles_by_author(): void
    {
        $article = $this->createCompleteArticle(['author' => 'Alice Johnson']);
        $this->createArticles(5); // Noise

        $response = $this->getJson('/api/v1/articles?author=Alice');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $article->id)
            ->assertJsonPath('data.0.author', 'Alice Johnson');
    }

    #[Test]
    public function it_returns_empty_when_search_has_no_matches(): void
    {
        $this->createArticles(5);

        $response = $this->getJson('/api/v1/articles?search=NonExistentKeyword');

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJson(['data' => []]);
    }

    #[Test]
    public function it_filters_articles_by_date_range(): void
    {
        $source = $this->createSource();
        $category = $this->createCategory();

        $oldArticle = $this->createArticlesWithRelations(
            count: 1,
            source: $source,
            category: $category,
            attributes: ['published_at' => '2024-01-01']
        );

        $newArticle = $this->createArticlesWithRelations(
            count: 1,
            source: $source,
            category: $category,
            attributes: ['published_at' => '2024-12-01']
        );

        $response = $this->getJson('/api/v1/articles?from=2024-11-01&to=2024-12-31');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $newArticle->id);
    }

    #[Test]
    #[DataProvider('invalidFilterProvider')]
    public function it_validates_invalid_filters(array $filters, string $expectedError): void
    {
        $response = $this->getJson('/api/v1/articles?' . http_build_query($filters));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors($expectedError);
    }

    public static function invalidFilterProvider(): array
    {
        return [
            'invalid source' => [['source' => 999999], 'source'],
            'invalid category' => [['category' => 999999], 'category'],
            'invalid date format' => [['from' => 'invalid-date'], 'from'],
            'end date before start' => [['from' => '2024-12-09', 'to' => '2024-12-01'], 'to'],
        ];
    }

    #[Test]
    public function it_paginates_articles_correctly(): void
    {
        $this->createArticles(25);

        $response = $this->getJson('/api/v1/articles?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1);
    }
}
