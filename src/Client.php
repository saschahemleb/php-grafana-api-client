<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Laminas\Hydrator\HydratorInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Saschahemleb\PhpGrafanaApiClient\Api\Admin;
use Saschahemleb\PhpGrafanaApiClient\Api\Organization;
use Saschahemleb\PhpGrafanaApiClient\Api\Other;
use Saschahemleb\PhpGrafanaApiClient\Api\Team;
use Saschahemleb\PhpGrafanaApiClient\Api\User;

class Client
{
    private HydratorInterface $hydrator;

    /**
     * @param UriInterface $baseUri The uri to the grafana api endpoint (for example http://localhost:3000/api)
     * @param Plugin[] $plugins Additional plugins to use
     * @param ClientInterface|null $client The http client to use instead of auto-discovering one
     * @param RequestFactoryInterface|null $requestFactory Factory to create Request objects with, auto-discovered if left null
     * @param UriFactoryInterface|null $uriFactory Factory to create Uri objects with, auto-discovered if left null
     */
    public static function create(
        UriInterface $baseUri,
        Authentication $authentication,
        array $plugins = [],
        ClientInterface $client = null,
        RequestFactoryInterface $requestFactory = null,
        UriFactoryInterface $uriFactory = null,
        StreamFactoryInterface $streamFactory = null
    ): self {
        $plugins = array_merge(
            [
                new Plugin\BaseUriPlugin($baseUri),
                new Plugin\HeaderSetPlugin(['Content-Type' => 'application/json']),
                new Plugin\ErrorPlugin()
            ],
            $authentication->createHttpClientPlugins(),
            $plugins
        );

        return new self(
            new PluginClient($client ?? Psr18ClientDiscovery::find(), $plugins),
            $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory(),
            $uriFactory ?? Psr17FactoryDiscovery::findUriFactory(),
            $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory(),
        );
    }

    public function user(): User
    {
        return $this->createApi(User::class);
    }

    public function team(): Team
    {
        return $this->createApi(Team::class);
    }

    public function other(): Other
    {
        return $this->createApi(Other::class);
    }

    public function admin(): Admin
    {
        return $this->createApi(Admin::class);
    }

    public function organization(): Organization
    {
        return $this->createApi(Organization::class);
    }

    protected function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private UriFactoryInterface $uriFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
        $this->hydrator = HydratorFactory::create();
    }

    protected function createApi(string $apiClass): object
    {
        return new $apiClass($this->client, $this->requestFactory, $this->uriFactory, $this->streamFactory, $this->hydrator);
    }
}