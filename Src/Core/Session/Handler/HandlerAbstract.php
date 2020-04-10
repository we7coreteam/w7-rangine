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

namespace W7\Core\Session\Handler;

abstract class HandlerAbstract implements \SessionHandlerInterface {
	protected $config;
	protected $expires;

	public function __construct($config) {
		$this->config = $config;
		$this->init();
	}

	protected function init() {
	}

	public function getExpires() {
		if ($this->expires === null) {
			$configExpires = (int)($this->config['expires'] ?? 0);
			$this->expires = $configExpires <= 0 ? ini_get('session.gc_maxlifetime') : $configExpires;
		}
		return $this->expires;
	}

	public function pack($data) {
		return serialize($data);
	}

	public function unpack($data) {
		return unserialize($data);
	}

	public function open($save_path, $name) {
		return true;
	}

	public function close() {
		return true;
	}

	/**
	 * @param string $session_id
	 * @param int $flag
	 * <table>
	 * destroy flag constants and bit values
	 * <tr valign="top">
	 * <td>value</td>
	 * <td>constant</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>1</td>
	 * <td>
	 * SESSION_DESTROY
	 * </td>
	 * </tr>
	 * <tr valign="top">
	 * <td>2</td>
	 * <td>
	 * SESSION_WEBSOCKET_CLOSE
	 * </td>
	 * </tr>
	 * <tr valign="top">
	 * <td>3</td>
	 * <td>
	 * SESSION_TCP_CLOSE
	 * </td>
	 * </tr>
	 * </table>
	 * @return bool
	 */
	abstract public function destroy($session_id, $flag = SESSION_DESTROY);
}
