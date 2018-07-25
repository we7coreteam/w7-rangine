<?php
/**
 * @author donknap
 * @date 18-7-24 下午3:09
 */

namespace W7\Core\Base;

interface DispatcherInterface {

	/**
	 * 匹配请求
	 * @param mixed ...$params
	 * @return mixed
	 */
	public function dispatch(...$params);
}