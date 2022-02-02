<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

class OrganizationHeaderPlugin implements Plugin
{
    private ?int $organizationId = null;

    public function set(int $organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function reset(): void
    {
        $this->organizationId = null;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        if ($this->organizationId !== null) {
            $request = $request->withHeader('X-Grafana-Org-Id', $this->organizationId);
        }

        return $next($request);
    }
}