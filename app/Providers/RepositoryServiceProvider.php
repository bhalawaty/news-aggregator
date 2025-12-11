<?php


namespace App\Providers;

use App\Repositories\CategoryRepositoryInterface;
use App\Repositories\EloquentCategoryRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ArticleRepositoryInterface;
use App\Repositories\EloquentArticleRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArticleRepositoryInterface::class, EloquentArticleRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);

    }

    public function boot()
    {
        //
    }
}
