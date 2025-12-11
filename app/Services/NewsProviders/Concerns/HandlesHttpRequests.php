<?php


namespace App\Services\NewsProviders\Concerns;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;


trait HandlesHttpRequests
{
    protected Client $http;

    /**
     * @return void
     */
    protected function initializeHttpClient(): void
    {
        $this->http = new Client([
            'base_uri' => $this->config['base_url'] ?? $this->config['base'] ?? null,
            'timeout' => $this->config['timeout'] ?? 30,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'NewsAggregator/1.0',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * @param string $endpoint
     * @param array $query
     * @return array
     */
    protected function makeRequest(string $endpoint, array $query): array
    {
        $this->enforceRateLimit();

        try {
            $response = $this->http->get($endpoint, ['query' => $query]);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            if ($statusCode !== 200) {
                $this->handleHttpError($statusCode, $body);
            }

            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException(
                    "Invalid JSON from {$this->providerKey()}: " . json_last_error_msg()
                );
            }

            return $data;

        } catch (GuzzleException $e) {
            Log::error("HTTP request failed for {$this->providerKey()}", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                "Network error from {$this->providerKey()}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * @return void
     */
    protected function enforceRateLimit(): void
    {
        $key = "provider_rate:{$this->providerKey()}";
        $maxAttempts = $this->config['rate_limit']['requests'] ?? 100;
        $decaySeconds = $this->config['rate_limit']['per_seconds'] ?? 3600;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $availableIn = RateLimiter::availableIn($key);

            throw new \RuntimeException(
                "Rate limit exceeded for {$this->providerKey()}. Retry in {$availableIn}s"
            );
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * @param int $statusCode
     * @param string $body
     * @return void
     */
    protected function handleHttpError(int $statusCode, string $body): void
    {
        $provider = $this->providerKey();

        match (true) {
            $statusCode === 401 => throw new \RuntimeException("Invalid API key for {$provider}"),
            $statusCode === 403 => throw new \RuntimeException("Access forbidden for {$provider}"),
            $statusCode === 429 => throw new \RuntimeException("Rate limit exceeded for {$provider}"),
            $statusCode >= 500 => throw new \RuntimeException("{$provider} server error: {$statusCode}"),
            default => throw new \RuntimeException("HTTP {$statusCode} from {$provider}: " . substr($body, 0, 200))
        };
    }
}
