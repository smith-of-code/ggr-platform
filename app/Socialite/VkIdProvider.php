<?php

namespace App\Socialite;

use GuzzleHttp\RequestOptions;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User;

/**
 * VK ID OAuth 2.1 provider.
 *
 * Uses id.vk.ru endpoints (not legacy oauth.vk.ru).
 * PKCE (S256) is mandatory. client_secret is not used.
 * Callback data arrives in a JSON-encoded `payload` query param.
 *
 * @see https://id.vk.com/about/business/go/docs/ru/vkid/latest/vk-id/connection/start-integration/auth-without-sdk/auth-without-sdk-web
 */
class VkIdProvider extends AbstractProvider
{
    public const IDENTIFIER = 'VKONTAKTE';

    protected $usesPKCE = true;

    protected $scopes = ['email'];

    protected $scopeSeparator = ' ';

    private ?array $parsedPayload = null;

    private ?string $deviceId = null;

    private ?string $returnedState = null;

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://id.vk.ru/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://id.vk.ru/oauth2/auth';
    }

    /**
     * VK ID returns callback data inside a JSON `payload` query param.
     * Fall back to standard query params for forward-compat.
     */
    private function payload(): array
    {
        if ($this->parsedPayload === null) {
            $raw = $this->request->input('payload');
            $this->parsedPayload = $raw ? (json_decode($raw, true) ?: []) : [];
        }

        return $this->parsedPayload;
    }

    protected function getCode()
    {
        return $this->payload()['code']
            ?? $this->request->input('code');
    }

    protected function hasInvalidState(): bool
    {
        if ($this->isStateless()) {
            return false;
        }

        $sessionState = $this->request->session()->pull('state');

        $this->returnedState = $this->payload()['state']
            ?? $this->request->input('state');

        return empty($sessionState) || $sessionState !== $this->returnedState;
    }

    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $this->deviceId = $this->payload()['device_id']
            ?? $this->request->input('device_id');

        $response = $this->getAccessTokenResponse($this->getCode());

        $user = $this->getUserByToken($response['access_token']);

        return $this->mapUserToObject($user)
            ->setToken($response['access_token'])
            ->setRefreshToken($response['refresh_token'] ?? null)
            ->setExpiresIn($response['expires_in'] ?? null);
    }

    /**
     * VK ID token exchange: no client_secret, requires device_id + state.
     */
    protected function getTokenFields($code): array
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'code' => $code,
            'code_verifier' => $this->request->session()->pull('code_verifier'),
            'redirect_uri' => $this->redirectUrl,
            'device_id' => $this->deviceId ?? '',
            'state' => $this->returnedState ?? '',
        ];
    }

    /**
     * VK ID user info: POST to /oauth2/user_info.
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->post('https://id.vk.ru/oauth2/user_info', [
            RequestOptions::FORM_PARAMS => [
                'access_token' => $token,
                'client_id' => $this->clientId,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['user'] ?? [];
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['user_id'] ?? null,
            'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email' => $user['email'] ?? null,
            'avatar' => $user['avatar'] ?? null,
        ]);
    }
}
