<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient;

use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\Hydrator\Strategy\CollectionStrategy;
use Laminas\Hydrator\Strategy\DateTimeFormatterStrategy;
use Laminas\Hydrator\Strategy\DateTimeImmutableFormatterStrategy;
use Laminas\Hydrator\Strategy\HydratorStrategy;
use Laminas\Hydrator\Strategy\StrategyEnabledInterface;
use Saschahemleb\PhpGrafanaApiClient\Resource\User;

/**
 * @internal
 */
class HydratorFactory
{
    public static function create(): HydratorInterface
    {
        $hydrator = new ReflectionHydrator();

        self::addCommonStrategies($hydrator);
        self::addUserResourceStrategies($hydrator);
        self::addDatasourceResourceStrategies($hydrator);

        return $hydrator;
    }

    private static function addUserResourceStrategies(AbstractHydrator $hydrator): void
    {
        $hydrator->addStrategy(
            'users',
            new CollectionStrategy($hydrator, User::class)
        );
    }

    private static function addCommonStrategies(StrategyEnabledInterface $hydrator): void
    {
        $hydrator->addStrategy(
            'createdAt',
            new DateTimeImmutableFormatterStrategy(
                new DateTimeFormatterStrategy()
            )
        );
        $hydrator->addStrategy(
            'created',
            new DateTimeImmutableFormatterStrategy(
                new DateTimeFormatterStrategy()
            )
        );
        $hydrator->addStrategy(
            'updatedAt',
            new DateTimeImmutableFormatterStrategy(
                new DateTimeFormatterStrategy()
            )
        );
        $hydrator->addStrategy(
            'updated',
            new DateTimeImmutableFormatterStrategy(
                new DateTimeFormatterStrategy()
            )
        );
        $hydrator->addStrategy(
            'lastSeenAt',
            new DateTimeImmutableFormatterStrategy(
                new DateTimeFormatterStrategy()
            )
        );
    }

    private static function addDatasourceResourceStrategies(ReflectionHydrator $hydrator)
    {
        $hydrator->addStrategy(
            'jsonData',
            new HydratorStrategy(
                new ObjectPropertyHydrator(),
                \stdClass::class
            )
        );
    }
}