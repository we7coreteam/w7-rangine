<?php


namespace W7\Console\Command\Server;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use W7\Core\Exception\CommandException;
use W7\Tcp\Console\Command as TcpServerCommand;

class TcpCommand extends Command {
	protected function configure() {
		$this->addArgument('operate', null, 'start|stop|restart');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$operate = $input->getArgument('operate');
		if (!$operate) {
			$this->getApplication()->setDefaultCommand($this->getName());
			$input = new ArrayInput(['--help' => true]);
			$this->getApplication()->run($input);
			return false;
		}

		$options = $input->getOptions();
		try{
			$server = new TcpServerCommand();
			if (!method_exists($server, $operate)) {
				throw new CommandException(sprintf('Not support arguments of  %s', $operate));
			}

			call_user_func_array(array($server, $operate), [$options]);
		} catch (\Throwable $e) {
			$output->writeln($e->getMessage());
		}
	}
}