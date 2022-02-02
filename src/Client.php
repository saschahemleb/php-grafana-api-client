<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient;

use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\Authentication as HttpAuthentication;
use Laminas\Hydrator\HydratorInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Saschahemleb\PhpGrafanaApiClient\Api\Admin;
use Saschahemleb\PhpGrafanaApiClient\Api\Datasource;
use Saschahemleb\PhpGrafanaApiClient\Api\Organization;
use Saschahemleb\PhpGrafanaApiClient\Api\Other;
use Saschahemleb\PhpGrafanaApiClient\Api\Team;
use Saschahemleb\PhpGrafanaApiClient\Api\User;

class Client
{
    private HydratorInterface $hydrator;
    private OrganizationHeaderPlugin $organizationPlugin;

    /**
     * @param UriInterface $baseUri The uri to the grafana endpoint (for example http://localhost:3000)
     * @param Plugin[] $plugins Additional plugins to use
     * @param ClientInterface|null $client The http client to use instead of auto-discovering one
     * @param RequestFactoryInterface|null $requestFactory Factory to create Request objects with, auto-discovered if left null
     * @param UriFactoryInterface|null $uriFactory Factory to create Uri objects with, auto-discovered if left null
     */
    public static function create(
        UriInterface $baseUri,
        HttpAuthentication $authentication,
        array $plugins = [],
        ClientInterface $client = null,
        RequestFactoryInterface $requestFactory = null,
        UriFactoryInterface $uriFactory = null,
        StreamFactoryInterface $streamFactory = null
    ): self {
        return new self(
            $baseUri,
            $authentication,
            $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory(),
            $uriFactory ?? Psr17FactoryDiscovery::findUriFactory(),
            $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory(),
            $plugins,
            $client,
        );
    }

    public function inOrganization(int $organizationId, callable $callable): mixed
    {
        $this->organizationPlugin->set($organizationId);
        $result = call_user_func($callable, $this);
        $this->organizationPlugin->reset();
        
        return $result;
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

    public function datasource(): Datasource
    {
        return $this->createApi(Datasource::class);
    }

    protected function __construct(
        UriInterface $baseUri,
        HttpAuthentication $authentication,
        private RequestFactoryInterface $requestFactory,
        private UriFactoryInterface $uriFactory,
        private StreamFactoryInterface $streamFactory,
        array $plugins = [],
        ClientInterface $client = null
    ) {
        $plugins = array_merge(
            [
                new Plugin\BaseUriPlugin(
                    $baseUri->withPath(
                        rtrim($baseUri->getPath(), '/') . '/api'
                    )
                ),
                new Plugin\HeaderSetPlugin(['Content-Type' => 'application/json']),
                new Plugin\ErrorPlugin(),
                new AuthenticationPlugin($authentication),
                $this->organizationPlugin = new OrganizationHeaderPlugin(),
            ],
            $plugins
        );

        $this->client = new PluginClient($client ?? Psr18ClientDiscovery::find(), $plugins);
        $this->hydrator = HydratorFactory::create();
    }

    protected function createApi(string $apiClass): object
    {
        return new $apiClass(
            $this->client,
            $this->requestFactory,
            $this->uriFactory,
            $this->streamFactory,
            $this->hydrator
        );
    }
}