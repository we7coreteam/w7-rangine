<?php

namespace W7\Console\Command\Vendor;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Provider\ProviderMapping;

class PublishCommand extends CommandAbstract {
	protected function configure() {
		$this->addOption('--provider', '-p', InputOption::VALUE_REQUIRED);
		$this->addOption('--tag', '-t', InputOption::VALUE_REQUIRED);
		$this->addOption('--force', '-f');
	}

	protected function handle($options) {
		if (empty($options['provider']) && empty($options['tag'])) {
			throw new CommandException('option provider or tag not be empty');
		}

		(new ProviderMapping())->publish();

		$this->publishTag($options['provider'] ?? '', $options['tag'] ?? '');

		$this->output->info('Publishing complete.');
	}

	/**
	 * Publishes the assets for a tag.
	 *
	 * @param  string  $tag
	 * @return mixed
	 */
	private function publishTag($provider, $tag) {
		foreach ($this->pathsToPublish($provider, $tag) as $from => $to) {
			$this->publishItem($from, $to);
		}
	}

	/**
	 * Get all of the paths to publish.
	 *
	 * @param  string  $tag
	 * @return array
	 */
	private function pathsToPublish($provider, $tag) {
		return ProviderAbstract::pathsToPublish($provider, $tag);
	}

	/**
	 * Publish the given item from and to the given location.
	 *
	 * @param  string  $from
	 * @param  string  $to
	 * @return void
	 */
	private function publishItem($from, $to) {
		if (is_file($from)) {
			return $this->publishFile($from, $to);
		} else if (is_dir($from)) {
			return $this->publishDirectory($from, $to);
		}

		$this->output->error("Can't locate path: <{$from}>");
	}

	/**
	 * Publish the file to the given path.
	 *
	 * @param  string  $from
	 * @param  string  $to
	 * @return void
	 */
	private function publishFile($from, $to) {
		if (!file_exists($to) || $this->input->getOption('force')) {
			$this->createParentDirectory(dirname($to));
			copy($from, $to);
			$this->status($from, $to, 'File');
		}
	}

	/**
	 * 待开发
	 * Publish the directory to the given directory.
	 *
	 * @param  string  $from
	 * @param  string  $to
	 * @return void
	 */
	private function publishDirectory($from, $to) {
//		$this->moveManagedFiles(new MountManager([
//			'from' => new Flysystem(new LocalAdapter($from)),
//			'to' => new Flysystem(new LocalAdapter($to)),
//		]));

		$this->status($from, $to, 'Directory');
	}

	/**
	 * Create the directory to house the published files if needed.
	 *
	 * @param  string  $directory
	 * @return void
	 */
	private function createParentDirectory($directory) {
		if (!is_dir($directory)) {
			mkdir($directory, 0755, true);
		}
	}

	/**
	 * Write a status message to the console.
	 *
	 * @param  string  $from
	 * @param  string  $to
	 * @param  string  $type
	 * @return void
	 */
	private function status($from, $to, $type) {
		$from = str_replace(BASE_PATH, '', realpath($from));
		$to = str_replace(BASE_PATH, '', realpath($to));
		$this->output->writeln('<info>Copied '.$type.'</info> <comment>['.$from.']</comment> <info>To</info> <comment>['.$to.']</comment>');
	}
}