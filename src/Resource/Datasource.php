<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Resource;

use stdClass;

class Datasource implements Resource
{
    private string $name;
    private string $type;
    private string $url;

    private ?int $id = null;
    private ?int $orgId = null;
    private string $access = 'proxy';
    private bool $basicAuth = false;
    private string $basicAuthUser = '';
    private string $basicAuthPassword = '';
    private string $typeLogoUrl = '';
    private string $password = '';
    private string $user = '';
    private string $database = '';
    private bool $withCredentials = false;
    private bool $isDefault = false;
    private \stdClass $jsonData;
    private \stdClass $secureJsonData;
    private int $version = 1;
    private bool $readOnly = true;

    public function __construct(string $name, string $type, string $url)
    {
        $this->name = $name;
        $this->type = $type;
        $this->url = $url;

        $this->jsonData = new stdClass();
        $this->secureJsonData = new stdClass();
    }

    public function setJsonData(array $jsonData): Datasource
    {
        $this->jsonData = (object)$jsonData;
        return $this;
    }

    public function setSecureJsonData(array $secureJsonData): Datasource
    {
        $this->secureJsonData = (object)$secureJsonData;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganizationId(): int
    {
        return $this->orgId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getJsonData(): array
    {
        return (array)$this->jsonData;
    }
}