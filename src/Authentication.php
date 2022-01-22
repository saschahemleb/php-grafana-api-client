<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient;

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\HeaderSetPlugin;
use Http\Message\Authentication\BasicAuth;
use Http\Message\Authentication\Bearer;

class Authentication
{
    private ?int $organizerId;

    public static function token(string $token): self
    {
        return new self(new Bearer($token));
    }

    public static function basicAuth(string $username, string $password): self
    {
        return new self(new BasicAuth($username, $password));
    }

    public function __construct(private \Http\Message\Authentication $authentication)
    {
    }

    /**
     * @param int|null $organizerId The id used in the request header 'X-Grafana-Org-Id'
     */
    public function withOrganizer(?int $organizerId): self
    {
        $this->organizerId = $organizerId;

        return $this;
    }

    public function createHttpClientPlugins(): array
    {
        $plugins = [
            new AuthenticationPlugin($this->authentication)
        ];

        if (isset($this->organizerId)) {
            $plugins[] = new HeaderSetPlugin(['X-Grafana-Org-Id' => $this->organizerId]);
        }

        return $plugins;
    }
}