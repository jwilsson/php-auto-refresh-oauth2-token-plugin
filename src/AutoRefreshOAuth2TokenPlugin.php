<?php

declare(strict_types=1);

namespace JWilsson;

use Http\Client\Common\Plugin;
use Http\Message\Authentication\Bearer;
use Http\Promise\Promise;
use OAuth2\Grant\RefreshToken;
use OAuth2\Token;
use Psr\Http\Message\RequestInterface;

class AutoRefreshOAuth2TokenPlugin implements Plugin
{
    /**
     * @var Token
     */
    protected Token $token;

    /**
     * @var RefreshToken
     */
    protected RefreshToken $refreshTokenGrant;

    /**
     * @var array<string, mixed>
     */
    protected array $options;

    /**
     * @var array<string, mixed>
     */
    protected array $refreshTokenOptions = [];

    /**
     * Constructor.
     *
     * @param Token $token The token to be refreshed.
     * @param RefreshToken $refreshTokenGrant The refresh token grant.
     * @param array<string, mixed> $options Optional. Options for the plugin.
     * @param array<string, mixed> $refreshTokenOptions Optional. Options for the refresh token grant.
     */
    public function __construct(
        Token $token,
        RefreshToken $refreshTokenGrant,
        array $options = [],
        array $refreshTokenOptions = []
    ) {
        $defaults = [
            'threshold' => 300, // 5 minutes
        ];

        $this->token = $token;
        $this->refreshTokenGrant = $refreshTokenGrant;
        $this->options = array_replace_recursive($defaults, $options);
        $this->refreshTokenOptions = $refreshTokenOptions;
    }

    /**
     * Refresh the access token if it's close to expiring based on current time.
     */
    protected function maybeRefreshToken(): void
    {
        $expires = $this->token->getExpires() - (int) $this->options['threshold'];

        if (time() >= $expires) {
            $this->token = $this->refreshTokenGrant->requestAccessToken(
                $this->token->getRefreshToken(),
                $this->refreshTokenOptions
            );
        }
    }

    /**
     * Retrieve the Token object, possibly updated with new values.
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $this->maybeRefreshToken();

        $bearer = new Bearer(
            $this->token->getAccessToken()
        );

        return $next(
            $bearer->authenticate($request)
        );
    }
}
