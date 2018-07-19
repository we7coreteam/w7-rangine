<?php
/**
 * @author donknap
 * @date 18-7-19 下午3:58
 */

namespace W7\Core\Base;

interface CommendInterface {
	public function start();
	public function stop();
	public function reload();
}