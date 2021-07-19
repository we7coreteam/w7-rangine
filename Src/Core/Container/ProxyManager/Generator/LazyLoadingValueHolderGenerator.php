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
        foreach ($proxyOptions['proxy_traits'] ??[] as $item) {
			$classGenerator->addTrait($item);
		}

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
			array_map(
				$this->buildMethodInterceptor($originalClass),
				ProxiedMethodsFilter::getProxiedMethods($originalClass, $proxyOptions['proxy_methods'] ?? [])
			)
        );
    }

    private function buildMethodInterceptor(ReflectionClass $originalClass): callable {
        return static function (ReflectionMethod $method) use ($originalClass) : LazyLoadingMethodInterceptor {
			return self::generateMethod(
				$originalClass,
				new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
			);
        };
    }

	private static function generateMethod(
		ReflectionClass $originalClass,
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
			ProxiedMethodReturnExpression::generate('self::__proxyCall(\\' . $originalClass->getName() .'::class, ' . var_export($methodName, true) . ', array(' . implode(', ', $initializerParams) . '), '  . $inlineFunction . ' {'
				. ProxiedMethodReturnExpression::generate(
					'parent::' . $methodName . '(' . implode(', ', $forwardedParams) . ')',
					$originalMethod
				) . '})', $originalMethod)
		);

		return $method;
	}
}
