<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:49
 */

if (!function_exists('getApp')) {
	/**
	 * 获取加载器
	 * @return \W7\Core\Helper\Loader
	 */
	function iloader() {
		return \W7\App::getLoader();
	}
}

if (!function_exists('istudly')) {
	function istudly($value) {
		$value = ucwords(str_replace(['-', '_'], ' ', $value));
		return str_replace(' ', '', $value);
	}
}