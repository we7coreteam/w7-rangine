<?php
/**
 * author: alex
 * date: 18-7-27 下午6:02
 */

namespace W7\Core\Helper;

/**
 * Class FileHelper
 * @package W7\Core\Helper
 */
class FileHelper
{
	/**
	 * 获得文件扩展名、后缀名
	 * @param $filename
	 * @param bool $clearPoint 是否带点
	 * @return string
	 */
	public static function getSuffix($filename, $clearPoint = false): string
	{
		$suffix = strrchr($filename, '.');

		return (bool)$clearPoint ? trim($suffix, '.') : $suffix;
	}

	/**
	 * @param $path
	 * @return bool
	 */
	public static function isAbsPath($path): bool
	{
		if (!$path || !\is_string($path)) {
			return false;
		}

		if (
			$path{0} === '/' ||  // linux/mac
			1 === \preg_match('#^[a-z]:[\/|\\\]{1}.+#i', $path) // windows
		) {
			return true;
		}

		return false;
	}

	/**
	 * md5 of dir
	 *
	 * @param string $dir
	 *
	 * @return bool|string
	 */
	public static function md5File($dir)
	{
		if (!is_dir($dir)) {
			return '';
		}

		$md5File = array();
		$d	   = dir($dir);
		while (false !== ($entry = $d->read())) {
			if ($entry !== '.' && $entry !== '..') {
				if (is_dir($dir . '/' . $entry)) {
					$md5File[] = self::md5File($dir . '/' . $entry);
				} elseif (substr($entry, -4) === '.php') {
					$md5File[] = md5_file($dir . '/' . $entry);
				}
				$md5File[] = $entry;
			}
		}
		$d->close();

		return md5(implode('', $md5File));
	}

	public static function cached(string $filePath, array $data)
	{
		$data = "<?php retrun " .var_export($data) . ";";
		file_put_contents($filePath, $data);
	}

	/**
	 * 写入文件
	 * @param array $records
	 */
	public static function write(string $file, string $messageText)
	{

		// 同步写
		if (isWorkerStatus() === false) {
			static::syncWrite($file, $messageText);
			return;
		}
		// 异步写
		static::aysncWrite($file, $messageText);
	}

	/**
	 * 同步写文件
	 *
	 * @param string $logFile	 日志路径
	 * @param string $messageText 文本信息
	 */
	private static function syncWrite(string $logFile, string $messageText)
	{
		$fp = fopen($logFile, 'a');
		if ($fp === false) {
			throw new \InvalidArgumentException("Unable to append to log file: {$logFile}");
		}
		flock($fp, LOCK_EX);
		fwrite($fp, $messageText);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/**
	 * 异步写文件
	 *
	 * @param string $logFile	 日志路径
	 * @param string $messageText 文本信息
	 */
	private static function aysncWrite(string $logFile, string $messageText)
	{
		while (true) {
			$result = \Swoole\Async::writeFile($logFile, $messageText, null, FILE_APPEND);
			if ($result == true) {
				break;
			}
		}
	}
}
