<?php


namespace W7\Core\Container;


class ContainerManager {
	private $current = 'APP';
	private $containers;
	private $shares;
	
	
	public function __construct() {
		$this->push($this->current);
	}
	
	public function push($name) {
		if (!empty($this->containers[$name])) {
			return true;
		}
		$this->containers[$name] = new Container();
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
		if (!$this->getContainer()->has($name) && !empty($this->shares[$name])) {
			$this->getContainer()->set($name, $this->shares[$name]);
		}
		
		return $this->getContainer()->get($name);
	}
	
	public function singleton($name) {
		return $this->get($name);
	}
}