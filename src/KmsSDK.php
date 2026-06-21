<?php

declare(strict_types=1);

namespace HanzoKms\SDK;

use HanzoKms\SDK\Services\SecretsService;
use HanzoKms\SDK\Services\AuthService;
use HanzoKms\SDK\Http\HttpClient;

/**
 * Main SDK class for interacting with the API
 */
class KmsSDK
{
    private string $host;
    private HttpClient $httpClient;
    private SecretsService $secretsService;
    private AuthService $authService;

    /**
     * Initialize the SDK with configuration
     *
     * @param string $host The host URL for the API
     */
    public function __construct(string $host)
    {
        $this->host = $host;
        $this->httpClient = new HttpClient($host);

        $this->secretsService = new SecretsService($this->httpClient);
        $this->authService = new AuthService($this->httpClient, fn(string $token) => $this->onAuthenticate($token));
    }

    /**
     * Get the secrets service
     *
     * @return SecretsService
     */
    public function secrets(): SecretsService
    {
        return $this->secretsService;
    }

    /**
     * Get the auth service
     *
     * @return AuthService
     */
    public function auth(): AuthService
    {
        return $this->authService;
    }

    /**
     * Handle authentication callback
     */
    private function onAuthenticate(string $accessToken): void
    {
        $this->httpClient = new HttpClient(
            $this->host, [
            'Authorization' => 'Bearer ' . $accessToken,
            ]
        );

        $this->secretsService = new SecretsService($this->httpClient);
        $this->authService = new AuthService($this->httpClient, fn(string $token) => $this->onAuthenticate($token));
    }
}
