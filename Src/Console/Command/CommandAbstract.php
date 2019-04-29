<?php

namespace W7\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CommandAbstract extends Command {
	protected $input;
	protected $output;

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->getApplication()->setDefaultCommand($this->getName());
		$this->input = $input;
		$this->output = $output;

		$this->handle($this->input->getOptions());
	}

	abstract protected function handle($options);
}