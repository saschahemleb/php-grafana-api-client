<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Api;

use Saschahemleb\PhpGrafanaApiClient\Resource\PagedUsers;
use Saschahemleb\PhpGrafanaApiClient\Resource\User as UserResource;
use Saschahemleb\PhpGrafanaApiClient\Resource\UserOrganization;
use Saschahemleb\PhpGrafanaApiClient\Resource\UserTeam;

class User extends Api
{
    /**
     * @return UserResource[]
     */
    public function searchUsers(int $perPage, int $page): array
    {
        return $this->hydrate(
            $this->get('/users', ['perpage' => $perPage, 'page' => $page]),
            new UserResource(),
            true
        );
    }

    public function searchUsersWithPaging(int $perPage, int $page, string $query = null): PagedUsers
    {
        $parameters = ['perpage' => $perPage, 'page' => $page];
        if ($query) {
            $parameters['query'] = $query;
        }

        return $this->hydrate(
            $this->get('/users/search', $parameters),
            new PagedUsers()
        );
    }

    public function getSingleUserById(int $id): UserResource
    {
        return $this->hydrate(
            $this->get("/users/$id"),
            new UserResource()
        );
    }

    public function getSingleUserByLoginOrEmail(string $loginOrEmail): UserResource
    {
        return $this->hydrate(
            $this->get('/users/lookup', ['loginOrEmail' => $loginOrEmail]),
            new UserResource()
        );
    }

    public function updateUser(UserResource $user): void
    {
        $this->put("/api/users/{$user->getId()}", $this->extract($user));
    }

    /**
     * @return UserOrganization[]
     */
    public function getOrganizationsForUser(int $id): array
    {
        return $this->hydrate(
            $this->get("/users/$id/orgs"),
            new UserOrganization(),
            true
        );
    }

    /**
     * @return UserTeam[]
     */
    public function getTeamsForUser(int $id): array
    {
        return $this->hydrate(
            $this->get("/users/$id/teams"),
            new UserTeam(),
            true
        );
    }

    public function actualUser(): UserResource
    {
        return $this->hydrate(
            $this->get('/user'),
            new UserResource()
        );
    }

    public function switchContextForActualUser(int $organizationId): void
    {
        $this->post("/user/using/{$organizationId}", []);
    }
}