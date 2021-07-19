<?php

namespace W7\Core\Container\ProxyManager\Generator;

use ReflectionClass;
use ReflectionMethod;

use function array_filter;
use function array_flip;
use function array_key_exists;
use function array_map;
use function array_values;
use function strtolower;

/**
 * Utility class used to filter methods that can be proxied
 */
final class ProxiedMethodsFilter
{
    /** @var array<int, string> */
    private static array $defaultExcluded = [
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__clone',
        '__sleep',
        '__wakeup',
    ];

    /**
     * @param ReflectionClass    $class    reflection class from which methods should be extracted
     * @param array<int, string> $excluded methods to be ignored
     *
     * @return ReflectionMethod[]
     */
    public static function getProxiedMethods(ReflectionClass $class, array $include = [], ?array $excluded = null): array
    {
        return self::doFilter($class, $include, $excluded ?? self::$defaultExcluded);
    }

    /**
     * @param array<int, string> $excluded
     *
     * @return array<int, ReflectionMethod>
     */
    private static function doFilter(ReflectionClass $class, array $include = [], array $excluded = [], bool $requireAbstract = false): array
    {
        $ignored = array_flip(array_map('strtolower', $excluded));

        return array_values(array_filter(
            $class->getMethods(ReflectionMethod::IS_PUBLIC),
            static function (ReflectionMethod $method) use ($include, $ignored, $requireAbstract): bool {
                return (! $requireAbstract || $method->isAbstract()) && (!(
                    array_key_exists(strtolower($method->getName()), $ignored)
                    || self::methodCannotBeProxied($method)
                )) && ($include && in_array($method->getName(), $include));
            }
        ));
    }

    /**
     * Checks whether the method cannot be proxied
     */
    private static function methodCannotBeProxied(ReflectionMethod $method): bool
    {
        return $method->isConstructor() || $method->isFinal() || $method->isStatic();
    }
}
