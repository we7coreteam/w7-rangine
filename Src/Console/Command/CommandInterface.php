<?php

namespace W7\Console\Command;

interface CommandInterface {
	public function run($action, $options = []);
	public function help();
}