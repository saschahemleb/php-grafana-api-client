<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Resource;

class PagedUsers implements Resource, \ArrayAccess
{
    private int $totalCount;
    /** @var User[] */
    private array $users;
    private int $page;
    private int $perPage;

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }


    public function offsetExists($offset)
    {
        return isset($this->users[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->users[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Modifying the list of users does not make sense');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Modifying the list of users does not make sense');
    }
}