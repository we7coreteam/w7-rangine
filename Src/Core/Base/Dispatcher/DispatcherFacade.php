<?php
/**
 * author: alex
 * date: 18-8-3 上午9:37
 */

namespace W7\Core\Base\Dispatcher;

abstract class DispatcherFacade extends DispatcherAbstract
{
	protected $resolvedInstance;





	public function dispatch(...$params)
	{
		// TODO: Implement dispatch() method.
	}

	/**
	 * @param mixed ...$param
	 * @throws \Exception
	 */
	public function build(...$param)
	{
		throw new \Exception(__CLASS__ . "not be " . __METHOD__);
	}

	/**
	 * @param mixed ...$param
	 * @throws \Exception
	 */
	public function register(...$param)
	{
		throw new \Exception(__CLASS__ . "not be " . __METHOD__);
	}


	/**
	 * @param mixed ...$param
	 * @throws \Exception
	 */
	public function run(...$param)
	{
		throw new \Exception(__CLASS__ . "not be " . __METHOD__);
	}


	/**
	 * @param mixed ...$param
	 * @throws \Exception
	 */
	public function trigger(...$param)
	{
		throw new \Exception(__CLASS__ . "not be " . __METHOD__);
	}
}
