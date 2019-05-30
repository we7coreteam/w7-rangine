<?php


namespace W7\Core\Container;


class ContainerManager {
	private $current = 'APP';
	/**
	 *最少有两个container 配置由container分发，这样的话框架内部需要维持一份内部container的config（tp）
	 * system [dispatcher route exception log config]
	 * app [db cache]
	 * @var
	 */
	private $containers;
	private $shares;
	
	
	public function push($name, Container $container) {
		if (!empty($this->containers[$name])) {
			return true;
		}
		$this->containers[$name] = $container;
	}
	
	private function getContainer() : Container {
		return $this->containers[$this->current];
	}
	
	public function select($name = null) {
		$this->current = $name == null ? 'APP' : $name;
		return $this;
	}
	
	public function set($name, $handle, $isShare = true) {
		if ($isShare) {
			$this->shares[$name] = $handle;
		}
		$this->getContainer()->set($name, $handle);
	}
	
	public function get($name) {
		if (!$this->getContainer()->has($name)) {
			if (!empty($this->shares[$name])) {
				$this->getContainer()->set($name, $this->shares[$name]);
			} else {
				return $this->containers['APP']->get($name);
			}
		}
		
		return $this->getContainer()->get($name);
	}
	
	public function singleton($name) {
		return $this->get($name);
	}
}