<?php

namespace W7\Core\Container\ProxyManager;

use Closure;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\ValueHolderInterface;

class ProxyFactory extends LazyLoadingValueHolderFactory {
	/**
	 * @param array<string, mixed> $proxyOptions
	 *
	 * @psalm-template RealObjectType of object
	 *
	 * @psalm-param class-string<RealObjectType> $className
	 * @psalm-param Closure(
	 *   RealObjectType|null=,
	 *   RealObjectType&ValueHolderInterface<RealObjectType>&VirtualProxyInterface=,
	 *   string=,
	 *   array<string, mixed>=,
	 *   ?Closure=
	 * ) : bool $initializer
	 *
	 * @psalm-return RealObjectType&ValueHolderInterface<RealObjectType>
	 *
	 * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
	 *                                         interfaced (by design)
	 */
	public function createDelegationProxy(string $className, array $proxyOptions = []) {
		return $this->generateProxy($className, $proxyOptions);
	}
}