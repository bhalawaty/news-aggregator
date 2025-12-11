<?php
namespace Database\Factories;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SourceFactory extends Factory
{
    protected $model = Source::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'slug' => Str::slug($this->faker->unique()->company),
            'provider_key' => $this->faker->randomElement(['newsapi', 'guardian', 'nytimes']),
            'enabled' => true,
            'last_success_at' => now(),
        ];
    }
}
