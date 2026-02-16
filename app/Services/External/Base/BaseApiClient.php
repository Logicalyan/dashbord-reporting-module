<?php

namespace App\Services\External\Base;

use Illuminate\Http\Client\PendingRequest;
// use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class BaseApiClient
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $retries;
    protected ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = config('services.external_api.base_url');
        $this->timeout = config('services.external_api.timeout', 30);
        $this->retries = config('services.external_api.retries', 3);
    }

    /**
     * Set authentication token
     */
    public function withToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Build HTTP client with base configuration
     */
    protected function client(): PendingRequest
    {
        $client = Http::timeout($this->timeout)
            ->retry($this->retries, 100)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name', 'Laravel') . '/1.0',
            ]);

        if ($this->token) {
            $client = $client->withToken($this->token);
        }


        return $client;
    }

    /**
     * Make GET request
     */
    protected function get(string $endpoint, array $params = [])
    {
        $url = $this->buildUrl($endpoint);

        return $this->client()
            ->get($url, $params)
            ->throw();
    }

    /**
     * Make POST request
     */
    protected function post(string $endpoint, array $data = [])
    {
        $url = $this->buildUrl($endpoint);

        return $this->client()
            ->post($url, $data)
            ->throw();
    }

    /**
     * Make PUT request
     */
    protected function put(string $endpoint, array $data = [])
    {
        $url = $this->buildUrl($endpoint);

        return $this->client()
            ->put($url, $data);
    }

    /**
     * Make DELETE request
     */
    protected function delete(string $endpoint)
    {
        $url = $this->buildUrl($endpoint);

        return $this->client()
            ->delete($url);
    }

    /**
     * Build full URL
     */
    protected function buildUrl(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Handle API response
     */
    public function handleResponse($response)
    {
        $body = $response->json();

        if (!$response->successful()) {
            throw new \Exception($body['message'] ?? 'API request failed');
        }

        return $body;
    }
}
