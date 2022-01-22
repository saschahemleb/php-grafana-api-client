<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Resource;

class OrganizationUser implements Resource
{
    private int $orgId;
    private int $userId;
    private string $email;
    private string $avatarUrl;
    private string $login;
    private string $role;
    private \DateTimeImmutable $lastSeenAt;
    private string $lastSeenAtAge;

    public function getOrgId(): int
    {
        return $this->orgId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAvatarUrl(): string
    {
        return $this->avatarUrl;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getLastSeenAt(): \DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function getLastSeenAtAge(): string
    {
        return $this->lastSeenAtAge;
    }
}