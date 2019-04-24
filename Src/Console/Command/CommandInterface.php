<?php

namespace W7\Console\Command;

use W7\Console\Io\Input;

interface CommandInterface {
	public function run(Input $input);
	public function dispatch($action, $options);
	public function help();
	public function version();
}