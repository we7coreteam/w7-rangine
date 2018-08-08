<?php
/**
 * author: alex
 * date: 18-8-2 下午8:01
 */

namespace W7\Core\Helper;

abstract class DispatcherFacade
{
	protected $resolvedInstance;


	abstract public function getFacadeAccessor();


	public function __call($method, $args)
	{
		$instance = $this->getFacadeRoot();
		$instance->$method(...$args);
	}
}
