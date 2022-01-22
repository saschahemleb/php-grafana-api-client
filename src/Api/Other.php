<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Api;

use Saschahemleb\PhpGrafanaApiClient\Resource\HealthResource;

class Other extends Api
{
    public function health(): HealthResource
    {
        return $this->hydrate(
            $this->get('/health'),
            new HealthResource()
        );
    }
}