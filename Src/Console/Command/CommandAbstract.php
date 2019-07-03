<?php

namespace W7\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use W7\App;
use W7\Console\Io\Output;

abstract class CommandAbstract extends Command {
	protected $description;
	/**
	 * @var InputInterface
	 */
	protected $input;
	/**
	 * @var Output
	 */
	protected $output;
	static $isRegister;

	public function __construct(string $name = null) {
		parent::__construct($name);
		$this->setDescription($this->description);
		$this->registerDb();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->getApplication()->setDefaultCommand($this->getName());
		$this->input = $input;
		$this->output = $output;

		$this->handle($this->input->getOptions());
	}

	abstract protected function handle($options);

	protected function call($command, $arguments = []) {
		$arguments['command'] = $command;
		$input = new ArrayInput($arguments);
		return $this->getApplication()->find($command)->run(
			$input, ioutputer()
		);
	}
}