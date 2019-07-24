<?php


namespace W7\Console;


use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use W7\Console\Io\Output;
use W7\Core\Provider\ProviderAbstract;

class ConsoleProvider extends ProviderAbstract {
	public function register() {
		iloader()->set(Output::class, function () {
			return new Output(new ArgvInput(), new ConsoleOutput());
		});
	}
}