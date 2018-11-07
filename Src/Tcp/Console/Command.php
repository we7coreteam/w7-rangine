<?php
/**
 * @author donknap
 * @date 18-11-6 下午2:31
 */

namespace W7\Tcp\Console;


use W7\Core\Command\CommandAbstract;
use W7\Tcp\Server\Server;

class Command extends CommandAbstract {

	/**
	 * @return Server
	 */
	public function createServer() {
		$server = new Server();
		return $server;
	}
}