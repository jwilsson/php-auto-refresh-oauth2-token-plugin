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
    public function __construct(
        protected Token $token,
        protected RefreshToken $refreshTokenGrant,
        protected array $options = [],
        protected array $refreshTokenOptions = []
    ) {
        $defaults = [
            'threshold' => 300, // 5 minutes
        ];

        $this->options = array_replace_recursive($defaults, $options);
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
