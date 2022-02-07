<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Resource;

class Team implements Resource
{
    private int $id;
    private int $orgId;
    private string $name;
    private string $email;
    private \DateTimeImmutable $created;
    private \DateTimeImmutable $updated;

    public static function create(string $name, int $orgId = 1): self
    {
        $team = new Team();
        $team->id = 0;
        $team->orgId = $orgId;
        $team->setName($name);
        $team->email = '';
        $team->created = new \DateTimeImmutable();
        $team->updated = new \DateTimeImmutable();

        return $team;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrgId(): int
    {
        return $this->orgId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    public function getUpdated(): \DateTimeImmutable
    {
        return $this->updated;
    }

    /**
     * @param string $name
     * @return Team
     */
    public function setName(string $name): Team
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $email
     * @return Team
     */
    public function setEmail(string $email): Team
    {
        $this->email = $email;
        return $this;
    }
}