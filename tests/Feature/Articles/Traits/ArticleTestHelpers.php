<?php

namespace Tests\Feature\Articles\Traits;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ArticleTestHelpers
{
    /**
     * @param int $count
     * @param array $attributes
     * @return Collection|Article
     */
    protected function createArticles(int $count = 1, array $attributes = []): Collection|Article
    {
        $factory = Article::factory()->count($count);

        if (!empty($attributes)) {
            $factory = $factory->state($attributes);
        }

        $result = $factory->create();

        return $count === 1 ? $result->first() : $result;
    }

    /**
     * @param array $attributes
     * @return Source
     */
    protected function createSource(array $attributes = []): Source
    {
        return Source::factory()->create($attributes);
    }

    /**
     * @param array $attributes
     * @return Category
     */
    protected function createCategory(array $attributes = []): Category
    {
        return Category::factory()->create($attributes);
    }

    /**
     * @param array $articleAttributes
     * @return Article
     */
    protected function createCompleteArticle(array $articleAttributes = []): Article
    {
        return Article::factory()
            ->for(Source::factory())
            ->for(Category::factory())
            ->create($articleAttributes);
    }

    /**
     * @param int $count
     * @param Source|null $source
     * @param Category|null $category
     * @param array $attributes
     * @return Model
     */
    protected function createArticlesWithRelations(int $count = 1, ?Source $source = null, ?Category $category = null, array $attributes = []): Model
    {
        $factory = Article::factory()->count($count);

        if ($source) {
            $factory = $factory->for($source);
        } else {
            $factory = $factory->for(Source::factory());
        }

        if ($category) {
            $factory = $factory->for($category);
        } else {
            $factory = $factory->for(Category::factory());
        }

        if (!empty($attributes)) {
            $factory = $factory->state($attributes);
        }

        $result = $factory->create();

        return $count === 1 ? $result->first() : $result;
    }
}
