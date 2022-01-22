<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Resource;

class Organization implements Resource
{
    private int $id;
    private string $name;

    public static function create(string $name)
    {
        $org = new self;
        $org->id = 0;
        $org->name = $name;

        return $org;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}