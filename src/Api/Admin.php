<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Api;

use Saschahemleb\PhpGrafanaApiClient\Resource\User as UserResource;

class Admin extends Api
{
    public function createNewUser(UserResource $user, string $password): UserResource
    {
        $data = [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'login' => $user->getLogin(),
            'password' => $password,
            'OrgId' => $user->getOrgId()
        ];
        $response = json_decode($this->post('/admin/users', $data)->getBody()->getContents());

        return $this->hydrateRaw(
            ['id' => $response->id] + $data,
            new UserResource()
        );
    }

    public function deleteUser(int $userId)
    {
        $this->delete("/admin/users/$userId");
    }
}