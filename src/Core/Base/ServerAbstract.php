<?php
/**
 * 服务父类，实现一些公共操作
 * @author donknap
 * @date 18-7-20 上午9:32
 */

namespace W7\Core\Base;

use W7\Core\Exception\CommendException;

abstract class ServerAbstract implements ServerInterface {
	public $server;

	/**
	 * 服务类型
	 * @var
	 */
	public $type;

	/**
	 * 配置
	 * @var
	 */
	public $setting;

	public function __construct() {
		$setting = \W7\App::getConfig('server');
		print_r($setting);
		if (empty($setting[$this->type])) {
			throw new CommendException(sprintf('缺少服务配置 %s', $this->type));
		}
		$this->setting = array_merge([], $setting[$this->type], $setting['common']);
	}

	public function getStatus() {

	}
}