<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Api;

use Saschahemleb\PhpGrafanaApiClient\Resource\GenericResponse;
use Saschahemleb\PhpGrafanaApiClient\Resource\Organization as OrganizationResource;
use Saschahemleb\PhpGrafanaApiClient\Resource\OrganizationUser;
use Saschahemleb\PhpGrafanaApiClient\Resource\OrganizationUserPatch;
use Saschahemleb\PhpGrafanaApiClient\Resource\User as UserResource;

class Organization extends Api
{
    public function getCurrentOrganization(): OrganizationResource
    {
        return $this->hydrate(
            $this->get('/org/'),
            new OrganizationResource()
        );
    }

    /** @return OrganizationUser[] */
    public function getAllUsersWithinTheCurrentOrganization(): array
    {
        return $this->hydrate(
            $this->get('/org/users'),
            new OrganizationUser(),
            true
        );
    }

    /** @return OrganizationUser[] */
    public function getUsersInOrganization(int $organizationId): array
    {
        return $this->hydrate(
            $this->get("/orgs/$organizationId/users"),
            new OrganizationUser(),
            true
        );
    }

    public function createOrganization(OrganizationResource $organization): OrganizationResource
    {
        $data = $this->extract($organization);
        $response = json_decode($this->post('/orgs', $data)->getBody()->getContents());

        return $this->hydrateRaw(['id' => $response->orgId] + $data, new OrganizationResource());
    }

    public function addUserInOrganization(int $organizationId, string $role, string $userLoginOrEmail)
    {
        $data = [
            'loginOrEmail' => $userLoginOrEmail,
            'role' => $role,
        ];

        $this->post("/orgs/$organizationId/users", $data);
    }

    public function updateUserInOrganization(int $organizationId, string $role, int $userId)
    {
        $data = [
            'Role' => $role,
        ];

        $this->patch("/orgs/$organizationId/users/$userId", $data);
    }

    public function deleteOrganization(int $organizationId)
    {
        $this->delete("/orgs/$organizationId");
    }

    public function getOrganizationById(int $organizationId): OrganizationResource
    {
        return $this->hydrate(
            $this->get("/orgs/$organizationId"),
            new OrganizationResource()
        );
    }

    public function updateOrganization(OrganizationResource $organization)
    {
        $this->put("/orgs/{$organization->getId()}", $this->extract($organization));
    }

    public function deleteUserInOrganization(int $organizationId, int $userId)
    {
        $this->delete("/orgs/{$organizationId}/users/{$userId}");
    }
}