<?php


namespace App\Factories;

use App\Services\NewsProviders\ProviderInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;

class ProviderFactory
{
    protected array $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @throws BindingResolutionException
     */
    public function make(string $providerKey): ProviderInterface
    {
        if (!isset($this->providers[$providerKey])) {
            throw new InvalidArgumentException("Unknown provider: {$providerKey}");
        }

        $config = $this->providers[$providerKey]['class'];

        return app()->make($config);

    }
}
