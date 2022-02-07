<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Resource;

class User implements Resource
{
    private ?int $id = null;
    private string $email;
    private string $name;
    private string $login;
    private ?string $theme = null;
    private int $orgId = 1;
    private ?bool $isGrafanaAdmin = null;
    private ?bool $isDisabled = null;
    private ?bool $isExternal = null;
    private ?array $authLabels = null;
    private \DateTimeImmutable $updatedAt;
    private \DateTimeImmutable $createdAt;
    private ?string $avatarUrl = null;

    public static function create(string $email, string $name, string $login): self
    {
        $user = new self;
        $user->id = 0;

        $user->email = $email;
        $user->name = $name;
        $user->login = $login;

        $user->createdAt = new \DateTimeImmutable;
        $user->updatedAt = new \DateTimeImmutable;

        return $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getOrgId(): int
    {
        return $this->orgId;
    }

    public function isGrafanaAdmin(): bool
    {
        return $this->isGrafanaAdmin;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    public function isExternal(): bool
    {
        return $this->isExternal;
    }

    public function getAuthLabels(): ?array
    {
        return $this->authLabels;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getAvatarUrl(): string
    {
        return $this->avatarUrl;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $login
     * @return User
     */
    public function setLogin(string $login): User
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @param string $theme
     * @return User
     */
    public function setTheme(string $theme): User
    {
        $this->theme = $theme;
        return $this;
    }

    public function setOrgId(int $orgId): User
    {
        $this->orgId = $orgId;
        return $this;
    }
}