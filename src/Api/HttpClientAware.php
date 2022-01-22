<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

trait HttpClientAware
{
    protected function get(string $uri, array $parameters = []): ResponseInterface
    {
        return $this->httpClient->sendRequest(
            $this->requestFactory->createRequest(
                'GET',
                $this->createUri($uri, $parameters)
            )
        );
    }

    protected function put(string $uri, mixed $content, array $parameters = []): ResponseInterface
    {
        return $this->httpClient->sendRequest(
            $this->requestFactory->createRequest(
                'PUT',
                $this->createUri($uri, $parameters)
            )->withBody($this->streamFactory->createStream(json_encode($content)))
        );
    }

    protected function patch(string $uri, mixed $content, array $parameters = []): ResponseInterface
    {
        return $this->httpClient->sendRequest(
            $this->requestFactory->createRequest(
                'PATCH',
                $this->createUri($uri, $parameters)
            )->withBody($this->streamFactory->createStream(json_encode($content)))
        );
    }

    protected function post(string $uri, mixed $content, array $parameters = []): ResponseInterface
    {
        return $this->httpClient->sendRequest(
            $this->requestFactory->createRequest(
                'POST',
                $this->createUri($uri, $parameters)
            )->withBody($this->streamFactory->createStream(json_encode($content)))
        );
    }

    protected function createUri(string $uri, array $parameters): UriInterface
    {
        return $this->uriFactory->createUri($uri)->withQuery(http_build_query($parameters));
    }
}