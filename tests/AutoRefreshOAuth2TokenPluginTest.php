<?php

declare(strict_types=1);

namespace JWilsson;

use Http\Mock\Client;
use Mockery;
use OAuth2\Grant\RefreshToken;

beforeEach(function () {
    $this->mockClient = new Client();
});

it('should add an access token to requests', function () {
    $token = create_token();
    $plugin = setup_plugin($token);
    $client = setup_client($this->mockClient, $plugin);

    $client->sendRequest(
        create_request()
    );

    expect(
        $this->mockClient->getLastRequest()->getHeaderLine('Authorization')
    )->toContain($token->getAccessToken());
});

it('should refresh the access token when expired', function () {
    $expiredToken = create_token([
        'expires' => strtotime('2021-01-01 12:00:00'),
    ]);

    $refreshedToken = create_token([
        'access_token' => 'c572c16299f42e07b03540d1d4410604f7e4471c7a30beeeaefa81972bc1c4ed',
    ]);

    $refreshTokenGrant = Mockery::mock(RefreshToken::class);
    $refreshTokenGrant->shouldReceive('requestAccessToken')
        ->andReturn($refreshedToken);

    $plugin = setup_plugin($expiredToken, $refreshTokenGrant);
    $client = setup_client($this->mockClient, $plugin);

    $client->sendRequest(
        create_request()
    );

    expect($plugin->getToken())->toBe($refreshedToken);
    expect(
        $this->mockClient->getLastRequest()->getHeaderLine('Authorization')
    )->toContain($refreshedToken->getAccessToken());
});

it('should refresh the access token when close to expiring', function () {
    $expiredToken = create_token([
        'expires' => strtotime('+2 min'),
    ]);

    $refreshedToken = create_token([
        'access_token' => 'c572c16299f42e07b03540d1d4410604f7e4471c7a30beeeaefa81972bc1c4ed',
    ]);

    $refreshTokenGrant = Mockery::mock(RefreshToken::class);
    $refreshTokenGrant->shouldReceive('requestAccessToken')
        ->andReturn($refreshedToken);

    $plugin = setup_plugin($expiredToken, $refreshTokenGrant);
    $client = setup_client($this->mockClient, $plugin);

    $client->sendRequest(
        create_request()
    );

    expect($plugin->getToken())->toBe($refreshedToken);
    expect(
        $this->mockClient->getLastRequest()->getHeaderLine('Authorization')
    )->toContain($refreshedToken->getAccessToken());
});

it('should not refresh the access token when not expired', function () {
    $refreshTokenGrant = Mockery::spy(RefreshToken::class);

    $token = create_token();
    $plugin = setup_plugin($token, $refreshTokenGrant);
    $client = setup_client($this->mockClient, $plugin);

    $client->sendRequest(
        create_request()
    );

    $refreshTokenGrant->shouldNotHaveReceived('requestAccessToken');
    expect($plugin->getToken())->toBe($token);
});
