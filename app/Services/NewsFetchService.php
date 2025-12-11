<?php

namespace App\Services;

use App\Factories\ProviderFactory;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;

class NewsFetchService
{
    protected ProviderFactory $factory;

    /**
     * @param ProviderFactory $factory
     */
    public function __construct(ProviderFactory $factory)
    {
        $this->factory = $factory;
    }


    /**
     * @param Source $source
     * @param Carbon|null $since
     * @return array
     * @throws BindingResolutionException
     */
    public function fetchNewArticles(Source $source, ?Carbon $since = null): array
    {
        $provider = $this->factory->make($source->provider_key);
        return $provider->fetchNew($source->id, $since);
    }

}
