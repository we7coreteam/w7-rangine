<?php

namespace W7\Core\Container\ProxyManager;

use Closure;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use W7\Core\Container\ProxyManager\Generator\LazyLoadingValueHolderGenerator;

class ProxyFactory extends LazyLoadingValueHolderFactory {
	protected \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator $generator;

	public function __construct(?Configuration $configuration = null) {
		$this->configuration = $configuration ?? new Configuration();
		$this->generator = new LazyLoadingValueHolderGenerator();
	}

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

	protected function getGenerator(): ProxyGeneratorInterface {
		return $this->generator;
	}
}