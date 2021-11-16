<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Helper\Traiter;

use Exception;
use W7\App;
use W7\Contract\Config\RepositoryInterface;
use W7\Contract\Event\EventDispatcherInterface;
use W7\Contract\Logger\LoggerFactoryInterface;
use W7\Core\Container\Container;
use W7\Core\Helper\Storage\Context;

trait AppCommonTrait {
	/**
	 * @var EventDispatcherInterface
	 */
	protected EventDispatcherInterface $eventDispatcher;
	/**
	 * @var LoggerFactoryInterface
	 */
	protected LoggerFactoryInterface $loggerFactory;

	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void {
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @throws Exception
	 */
	public function getEventDispatcher() {
		if (!$this->eventDispatcher) {
			if (!App::getApp()->getContainer()->has(EventDispatcherInterface::class)) {
				throw new Exception('the target instance ' . EventDispatcherInterface::class . ' does not exist');
			}
			$this->eventDispatcher = App::getApp()->getContainer()->get(EventDispatcherInterface::class);
		}

		return $this->eventDispatcher;
	}

	public function setLogger(LoggerFactoryInterface $loggerFactory): void {
		$this->loggerFactory = $loggerFactory;
	}

	public function getLogger() {
		if (!$this->loggerFactory) {
			if (!App::getApp()->getContainer()->has(LoggerFactoryInterface::class)) {
				throw new Exception('the target instance ' . LoggerFactoryInterface::class . ' does not exist');
			}
			$this->loggerFactory = App::getApp()->getContainer()->get(LoggerFactoryInterface::class);
		}

		return $this->loggerFactory;
	}

	public function getContainer(): Container {
		return App::getApp()->getContainer();
	}

	public function getConfig(): RepositoryInterface {
		return App::getApp()->getConfigger();
	}

	/**
	 * @throws \ReflectionException
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function getContext() : Context {
		return $this->getContainer()->get(Context::class);
	}
}
