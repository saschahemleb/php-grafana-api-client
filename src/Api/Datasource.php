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

        return $this->hydrateRaw($response['datasource'], new DatasourceResource());
    }

    /**
     * @return DatasourceResource[]
     */
    public function getAllDatasources(): array
    {
        return $this->hydrate(
            $this->get('/datasources'),
            new DatasourceResource(),
            true
        );
    }

    public function getDatasourceById(int $datasourceId): DatasourceResource
    {
        return $this->hydrate(
            $this->get("/datasources/{$datasourceId}"),
            new DatasourceResource(),
        );
    }

    public function getDatasourceByUid(string $datasourceUid): DatasourceResource
    {
        return $this->hydrate(
            $this->get("/datasources/uid/{$datasourceUid}"),
            new DatasourceResource(),
        );
    }

    public function getDatasourceByName(string $name): DatasourceResource
    {
        return $this->hydrate(
            $this->get("/datasources/name/{$name}"),
            new DatasourceResource(),
        );
    }

    public function updateDatasource(DatasourceResource $datasource): DatasourceResource
    {
        $data = $this->extract($datasource);
        $response = json_decode($this->put("/datasources/{$datasource->getId()}", $data)->getBody()->getContents(), true);

        return $this->hydrateRaw($response['datasource'], new DatasourceResource());
    }

    public function deleteDatasourceById(int $datasourceId)
    {
        $this->delete("/datasources/{$datasourceId}");
    }

    public function deleteDatasourceByUid(string $datasourceUid)
    {
        $this->delete("/datasources/uid/{$datasourceUid}");
    }

    public function deleteDatasourceByName(string $datasourceName)
    {
        $this->delete("/datasources/name/{$datasourceName}");
    }
}