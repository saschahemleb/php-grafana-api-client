<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Api;

use Laminas\Hydrator\Strategy\CollectionStrategy;
use Psr\Http\Message\ResponseInterface;
use Saschahemleb\PhpGrafanaApiClient\Resource\Resource;

trait HydratorAware
{
    protected function hydrate(ResponseInterface $response, Resource $target, bool $collection = false)
    {
        $value = match (true) {
            str_starts_with($response->getHeaderLine('Content-Type'), 'application/json') => json_decode($response->getBody()->getContents(), true)
        };

        return $this->hydrateRaw($value, $target, $collection);
    }

    protected function hydrateRaw(mixed $value, Resource $target, bool $collection = false)
    {
        if ($collection) {
            return (new CollectionStrategy(
                $this->hydrator,
                $target::class
            ))->hydrate($value);
        }

        return $this->hydrator
            ->hydrate(
                $value,
                $target,
            );
    }

    protected function extract(Resource $source): array
    {
        return $this->hydrator->extract($source);
    }
}