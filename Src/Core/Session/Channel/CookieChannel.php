<?php


namespace W7\Core\Session\Channel;


class CookieChannel extends ChannelAbstract {
	public function getId() {
		$cookies = $this->request->getCookieParams();
		if (empty($cookies[$this->getSessionName()])) {
			$cookies[$this->getSessionName()] = $this->generateId();
		}

		return $cookies[$this->getSessionName()];
	}
}