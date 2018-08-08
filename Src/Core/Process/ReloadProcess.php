<?php
/**
 * @author donknap
 * @date 18-7-25 下午3:03
 */

namespace W7\Core\Process;

use Swoole\Process;
use W7\App;
use W7\Core\Base\Process\ProcessInterface;
use W7\Core\Helper\FileHelper;

class ReloadProcess implements ProcessInterface
{

	/**
	 * 监听文件变化的路径
	 *
	 * @var string
	 */
	private $watchDir;

	/**
	 * the lasted md5 of dir
	 *
	 * @var string
	 */
	private $md5File = '';

	/**
	 * the interval of scan
	 *
	 * @var int
	 */
	private $interval = 5;

	/**
	 * 初始化方法
	 */
	public function __construct()
	{
		$this->watchDir = APP_PATH;
		$this->md5File = FileHelper::md5File($this->watchDir);
	}
	public function check()
	{
		$serverConfig = iconfig()->getServer();
		if (!$serverConfig['common']['autoReload']) {
			return true;
		}
		return true;
	}

	public function run(Process $process)
	{
		$server = App::$server;
		while (true) {
			sleep($this->interval);
			$md5File = FileHelper::md5File($this->watchDir);
			if (strcmp($this->md5File, $md5File) !== 0) {
				ioutputer()->writeln("Start reloading in " . date('m-d H:i:s') . "...");
				$server->isRun();
				$server->getServer()->reload();
				ioutputer()->writeln("Reloaded");
			}
			$this->md5File = $md5File;
		}
	}
}
