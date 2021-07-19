<?php

namespace W7\Core\Container\ProxyManager\Generator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use ReflectionClass;
use ReflectionMethod;

use function array_map;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\VirtualProxyInterface}
 *
 * {@inheritDoc}
 */
class LazyLoadingValueHolderGenerator extends \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator
{
    /**
     * {@inheritDoc}
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = [])
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces       = [];
        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addTrait('W7\Core\Container\ProxyManager');

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
			array_map(
				$this->buildLazyLoadingMethodInterceptor(),
				ProxiedMethodsFilter::getProxiedMethods($originalClass, $proxyOptions['proxy_methods'] ?? [])
			)
        );
    }

    private function buildLazyLoadingMethodInterceptor(): callable {
        return static function (ReflectionMethod $method) : LazyLoadingMethodInterceptor {
			return self::generateMethod(
				new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
			);
        };
    }

	private static function generateMethod(
		MethodReflection $originalMethod
	): LazyLoadingMethodInterceptor {
		$method            = LazyLoadingMethodInterceptor::fromReflectionWithoutBodyAndDocBlock($originalMethod);
		$parameters        = $originalMethod->getParameters();
		$methodName        = $originalMethod->getName();
		$forwardedParams   = [];
		$initializerParams = [];

		foreach ($parameters as $parameter) {
			$parameterName       = $parameter->getName();
			$variadicPrefix      = $parameter->isVariadic() ? '...' : '';
			$forwardedParams[]   = $variadicPrefix . '$' . $parameterName;
			$initializerParams[] = var_export($parameterName, true) . ' => $' . $parameterName;
		}

		$inlineFunction = 'function()';
		if ($forwardedParams) {
			$inlineFunction .= ' use (' . implode(', ', $forwardedParams) . ')';
		}

		$method->setBody(
			ProxiedMethodReturnExpression::generate('self::__proxyCall($this, ' . var_export($methodName, true) . ', array(' . implode(', ', $initializerParams) . '), '  . $inlineFunction . ' {'
				. ProxiedMethodReturnExpression::generate(
					'parent::' . $methodName . '(' . implode(', ', $forwardedParams) . ')',
					$originalMethod
				) . '})', $originalMethod)
		);

		return $method;
	}

	private static function doFilter(ReflectionClass $class, array $include, bool $requireAbstract = false): array {
		$ignored = [
			'__get',
			'__set',
			'__isset',
			'__unset',
			'__clone',
			'__sleep',
			'__wakeup'
		];

		return array_values(array_filter(
			$class->getMethods(ReflectionMethod::IS_PUBLIC),
			static function (ReflectionMethod $method) use ($ignored, $requireAbstract): bool {
				return (! $requireAbstract || $method->isAbstract()) && ! (
						array_key_exists(strtolower($method->getName()), $ignored)
						|| self::methodCannotBeProxied($method)
					);
			}
		));
	}
}
