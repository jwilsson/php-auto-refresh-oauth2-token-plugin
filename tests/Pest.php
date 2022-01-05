<?php

declare(strict_types=1);

namespace JWilsson;

use Http\Client\Common\PluginClient;
use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use OAuth2\Grant\RefreshToken;
use OAuth2\Token;
use Psr\Http\Message\RequestInterface;

function create_token(array $values = []): Token
{
    $values = array_replace(
        [
        'access_token' => '86b3901eea37a04e8547cd912225f548d2e0a92078887682ce831a433072f9d1',
        'expires' => time() + 3600, // One hour from now
        'refresh_token' => '6c8a7d4aa21708a432174e4cb5c6cfaf0218f5f3e52f9a76a7d95d2aaade2c83',
        'scope' => 'scope-1 scope-2',
        'token_type' => 'Bearer',
        'values' => [],
        ],
        $values
    );

    return new Token($values);
}

function setup_plugin(Token $token, ?RefreshToken $refreshTokenGrant = null): AutoRefreshOAuth2TokenPlugin
{
    $psr17Factory = new Psr17Factory();
    $refreshTokenGrant = $refreshTokenGrant ?? new RefreshToken(
        [],
        new Client(),
        $psr17Factory,
        $psr17Factory
    );

    return new AutoRefreshOAuth2TokenPlugin($token, $refreshTokenGrant);
}

function setup_client(
    Client $mockClient,
    AutoRefreshOAuth2TokenPlugin $plugin
): PluginClient {
    return new PluginClient(
        $mockClient,
        [$plugin]
    );
}

function create_request(): RequestInterface
{
    $psr17Factory = new Psr17Factory();

    return $psr17Factory->createRequest('GET', 'https://api.example.com/endpoint');
}
