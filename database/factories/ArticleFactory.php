<?php
namespace Database\Factories;
use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = $this->faker->sentence;
        $content = $this->faker->paragraphs(3, true);
        $canonicalUrl = $this->faker->unique()->url;

        return [
            'source_id' => Source::factory(),
            'category_id' => Category::factory(),
            'external_id' => $this->faker->uuid,
            'author' => $this->faker->name,
            'url' => $this->faker->unique()->url,
            'canonical_url' => $canonicalUrl,
            'title' => $title,
            'summary' => $this->faker->sentence,
            'content' => $content,
            'published_at' => now(),
            'language' => 'en',
            'image_url' => $this->faker->imageUrl,
            'normalized_hash' => md5($title . $content),
            'ingestion_metadata' => json_encode(['source' => 'test']),
        ];
    }
}
