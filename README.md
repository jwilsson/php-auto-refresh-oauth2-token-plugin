# PHP AutoRefreshOAuth2TokenPlugin
[![Packagist](https://img.shields.io/packagist/v/jwilsson/auto-refresh-oauth2-token-plugin.svg)](https://packagist.org/packages/jwilsson/auto-refresh-oauth2-token-plugin)
![build](https://github.com/jwilsson/php-auto-refresh-oauth2-token-plugin/workflows/build/badge.svg)
[![Coverage Status](https://coveralls.io/repos/jwilsson/php-auto-refresh-oauth2-token-plugin/badge.svg?branch=main)](https://coveralls.io/r/jwilsson/php-auto-refresh-oauth2-token-plugin?branch=main)

A [HTTPlug plugin](https://docs.php-http.org/en/latest/plugins/introduction.html) to automatically refresh expired OAuth2 access tokens.

## Requirements
* PHP 8.1 or later.
* [jwilsson/oauth2-client](https://github.com/jwilsson/php-oauth2-client) library.

## Installation
Via Composer:

```sh
composer require jwilsson/auto-refresh-oauth2-token-plugin
```

## Usage
This assumes you have an instantiated Refresh Token grant and Token object from the [jwilsson/oauth2-client](https://github.com/jwilsson/php-oauth2-client) library. A full Token object complete with access token, refresh token, and expiry information is expected.

```php
use Http\Client\Common\PluginClient;
use JWilsson\AutoRefreshOAuth2TokenPlugin;

$autoRefreshOAuth2TokenPlugin = new AutoRefreshOAuth2TokenPlugin(
    $token,
    $refreshTokenGrant,
    $options, // Options for the plugin, see below
    $refreshTokenOptions // Additional options to pass to RefreshToken::requestAccessToken()
);

$pluginClient = new PluginClient(
    $myHttpClient,
    [$autoRefreshOAuth2TokenPlugin]
);

$response = $pluginClient->sendRequest($myRequest);

// Remember to grab the token object after each call, it might have been updated with new information
$refreshedToken = $autoRefreshOAuth2TokenPlugin->getToken();
```

### Options
* `threshold` - Threshold in seconds for how close to the token's expiry time it should be considered expired. Default is 300 (5 minutes).
