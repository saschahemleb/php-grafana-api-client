# PHP Grafana API Client

A client for the Grafana API, written in PHP.

## Project Status

This project is still in an alpha state; not all API endpoints are implemented yet,
and the public package API is subject to major changes.



## Installation

Via Composer

```bash
$ composer require saschahemleb/php-grafana-api-client
```

### Framework Integration

I've written a bridge package for Laravel, providing a facade and a connection manager for controlling multiple grafana instances.
You can find it [here](//github.com/saschahemleb/laravel-grafana).

## Example

```php
use Saschahemleb\PhpGrafanaApiClient\Client;
use Saschahemleb\PhpGrafanaApiClient\Authentication;

$client = Client::create(
    new Uri('http://localhost:3000/'), // url to grafana
    Authentication::basicAuth('admin', 'admin')
);

echo $client->other()->health()->getVersion();
// 8.3.6
```