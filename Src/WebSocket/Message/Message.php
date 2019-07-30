<?php

namespace W7\WebSocket\Message;

class Message {
    private $cmd;
    private $data;
    private $ext;


    public function __construct(string $cmd, $data, array $ext = []) {
        $this->cmd  = $cmd;
	    $this->data = $data;
	    $this->ext  = $ext;
    }

	public function setCmd(string $cmd): void {
		$this->cmd = $cmd;
	}

    public function getCmd(): string {
        return $this->cmd;
    }

    public function setData($data) {
		$this->data = $data;
    }

    public function getData() {
    	return $this->data;
    }

    public function getPackage() {
    	return [
		    'cmd'  => $this->cmd,
		    'data' => $this->data,
		    'ext'  => $this->ext,
	    ];
    }
}
