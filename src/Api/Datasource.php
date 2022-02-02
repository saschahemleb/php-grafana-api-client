<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Api;

use Saschahemleb\PhpGrafanaApiClient\Resource\Datasource as DatasourceResource;

class Datasource extends Api
{
    /**
     * Creates a datasource for the organizer
     * 
     * If the user is in multiple organizers, use the `Client::inOrganization` to control
     * in which organizer the datasource is created
     */
    public function createDatasource(DatasourceResource $datasource): DatasourceResource
    {
        $data = $this->extract($datasource);
        $response = json_decode($this->post('/datasources', $data)->getBody()->getContents(), true);

        return $this->hydrateRaw($response['datasource'], clone $datasource);
    }
}