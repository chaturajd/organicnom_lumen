<?php

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class JwtTokenHelper
{
    private $accessSecretKey;
    private $refreshSecretKey;

    private $configuration;

    private $userId;
    private $appId;

    function __construct($userId = "", $appId = "")
    {
        $this->accessSecretKey = InMemory::plainText('accesskey');
        $this->refreshSecretKey = InMemory::plainText('refreshSecret');

        $this->userId = $userId;
        $this->appId = $appId;
    }



    public function getAccessToken()
    {
        return $this->getToken($this->accessSecretKey, '+1 hour');
    }

    public function getRefreshToken()
    {
        return $this->getToken($this->refreshSecretKey, '+15 days');
    }

    private function getToken($key, $expireIn)
    {
        $this->configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            $key
        );

        $now = new DateTimeImmutable();

        $token = $this->configuration->builder()
            ->issuedBy('api.web')
            ->identifiedBy($this->userId)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify($expireIn))
            ->withClaim('appId', $this->appId)
            ->getToken($this->configuration->signer(), $this->configuration->signingKey());

        return $token;
    }

    public function parse($token)
    {
        $this->configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            $this->accessSecretKey
        );

        return $this->configuration->parser()->parse($token);
    }
}
