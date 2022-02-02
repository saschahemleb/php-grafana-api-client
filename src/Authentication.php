<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient;

use Http\Message\Authentication\BasicAuth;
use Http\Message\Authentication\Bearer;
use Http\Message\Authentication as HttpAuthentication;

class Authentication
{
    public static function token(string $token): HttpAuthentication
    {
        return new Bearer($token);
    }

    public static function basicAuth(string $username, string $password): HttpAuthentication
    {
        return new BasicAuth($username, $password);
    }
}