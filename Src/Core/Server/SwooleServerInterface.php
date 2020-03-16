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

namespace W7\Core\Server;

interface SwooleServerInterface extends ServerInterface {
	/**
	 * 服务停止
	 * @return mixed
	 */
	public function stop();

	/**
	 * 服务是否运行
	 * @return mixed
	 */
	public function isRun();

	/**
	 * 获取服务状态
	 * @return mixed
	 */
	public function getStatus();
}
