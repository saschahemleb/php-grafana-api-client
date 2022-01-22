<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Api;

use Saschahemleb\PhpGrafanaApiClient\Resource\Team as TeamResource;

class Team extends Api
{
    public function addTeam(TeamResource $team): TeamResource
    {
        $data = $this->extract($team);
        $response = json_decode($this->post('/teams', $data)->getBody()->getContents());

        return $this->hydrateRaw(['id' => $response->teamId] + $data, new TeamResource());
    }

    public function getTeamById(int $id): TeamResource
    {
        return $this->hydrate(
            $this->get("/teams/$id"),
            new TeamResource()
        );
    }

    public function addTeamMember(int $id, int $userId): void
    {
        $this->post("/teams/$id/members", ['userId' => $userId]);
    }
}