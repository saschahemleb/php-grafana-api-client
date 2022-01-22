<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Resource;

class HealthResource implements \Saschahemleb\PhpGrafanaApiClient\Resource\Resource
{
    private string $commit;
    private string $database;
    private string $version;

    public function getCommit(): string
    {
        return $this->commit;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getVersion(): string
    {
        return $this->version;
    }


}