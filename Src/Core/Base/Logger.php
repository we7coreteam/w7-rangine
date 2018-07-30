<?php
namespace W7\Core\Base;
use W7\Core\Helper\FileHelper;


/**
 * author: yangshen
 * date: 12-7-12 上午11:48
 */
class Logger
{

	const L_ALL = 0;

	const L_DEBUG = 1;

	const L_TRACE = 2;

	const L_INFO = 3;

	const L_NOTICE = 4;

	const L_WARNING = 5;

	const L_FATAL = 6;


	private static $ARR_DESC = array (0 => 'ALL', 1 => 'DEBUG', 2 => 'TRACE', 3 => 'INFO',
			4 => 'NOTICE', 5 => 'WARNING', 6 => 'FATAL' );

	private static $LOG_LEVEL = self::L_DEBUG;

	private static $ARR_BASIC = [];

	private static $FILE = [];

	private static $FORCE_FLUSH = false;

	private static $MESSAGES = [];

	private static $FIUSH_INTERVAL = 1;

	public static function flush()
	{

		foreach ( self::$FILE as $file )
		{
			fflush ( $file );
		}
	}

	public static function addBasic($key, $value)
	{

		self::$ARR_BASIC [$key] = $value;
	}

	public static function init($filename, $level, $flushInterval = 1,$arrBasic = null, $forceFlush = false)
	{

		if (! isset ( self::$ARR_DESC [$level] ))
		{
			trigger_error ( "invalid level:$level" );
			return;
		}
		self::$LOG_LEVEL = $level;
		$dir = dirname ( $filename );
		if (! file_exists ( $dir ))
		{
			if (! mkdir ( $dir, 0755, true ))
			{
				trigger_error ( "create log file $filename failed, no permmission" );
				return;
			}
		}
		self::$FILE [0] = fopen ( $filename, 'a+' );
		if (empty ( self::$FILE [0] ))
		{
			trigger_error ( "create log file $filename failed, no disk space for permission" );
			self::$FILE = array ();
			return;
		}

		self::$FILE [1] = fopen ( $filename . '.wf', 'a+' );
		if (empty ( self::$FILE [1] ))
		{
			trigger_error ( "create log file $filename.wf failed, no disk space for permission" );
			self::$FILE = array ();
			return;
		}

		if (! empty ( $arrBasic ))
		{
			self::$ARR_BASIC = $arrBasic;
		}

		if (!empty($flushInterval))
		{
		    self::$FIUSH_INTERVAL = $flushInterval;
        }

		self::$FORCE_FLUSH = $forceFlush;
	}

	private static function checkPrintable($fileIndex)
	{
	    if (count(static::$MESSAGES[$fileIndex]) >= static::$FIUSH_INTERVAL)
	    {
	        return true;
        }
        return false;
	}

	private static function log($level, $arrArg)
	{

		if ($level < self::$LOG_LEVEL || empty ( self::$FILE ) || empty ( $arrArg ))
		{
			return;
		}

		$arrMicro = explode ( " ", microtime () );
		$content = '[' . date ( 'Ymd H:i:s ' );
		$content .= sprintf ( "%06d", intval ( 1000000 * $arrMicro [0] ) );
		$content .= '][';
		$content .= self::$ARR_DESC [$level];
		$content .= "]";
		foreach ( self::$ARR_BASIC as $key => $value )
		{
			$content .= "[$key:$value]";
		}

		$arrTrace = debug_backtrace ();
		if (isset ( $arrTrace [1] ))
		{
			$line = $arrTrace [1] ['line'];
			$file = $arrTrace [1] ['file'];
			$file = substr ( $file, strlen ( ROOT ) + 1 );
			$content .= "[$file:$line]";
		}
		$define = iconfig()->getUserConfig('define');
		$logLevel = $define['log']['level'];

		foreach ( $arrArg as $idx => $arg )
		{

			if (is_array ( $arg ))
			{
				array_walk_recursive ( $arg, array ('Logger', 'checkPrintable' ) );

				if ($logLevel)
				{
					$data = var_export ( $arg, true );
				}
				else
				{
					$data = serialize ( $arg );
				}

				$arrArg [$idx] = $data;
			}
		}
		$content .= call_user_func_array ( 'sprintf', $arrArg );
		$content .= "\n";

		$file = self::$FILE [0];
		self::$MESSAGES[0][] = $content;
		if (self::checkPrintable(0))
		{
			static::flushLog(0);
		}

		if ($level <= self::L_NOTICE)
		{
			return;
		}

		$file = self::$FILE [1];
        self::$MESSAGES[1][] = $content;
		if (self::checkPrintable(1))
		{
            static::flushLog(1);
		}
	}

	private static function flushLog( $fileIndex)
    {
        if (empty(static::$MESSAGES[$fileIndex])){
            return;
        }
        $messageText = implode("\n", static::$MESSAGES[$fileIndex]) . "\n";
        FileHelper::write(self::$FILE[$fileIndex], $messageText);
        static::$MESSAGES[$fileIndex] = [];
    }

	public static function debug()
	{

		$arrArg = func_get_args ();
		self::log ( self::L_DEBUG, $arrArg );
	}

	public static function trace()
	{

		$arrArg = func_get_args ();
		self::log ( self::L_TRACE, $arrArg );
	}

	public static function info()
	{

		$arrArg = func_get_args ();
		self::log ( self::L_INFO, $arrArg );
	}

	public static function notice()
	{

		$arrArg = func_get_args ();
		self::log ( self::L_NOTICE, $arrArg );
	}

	public static function warning()
	{

		$arrArg = func_get_args ();
		self::log ( self::L_WARNING, $arrArg );
	}

	public static function fatal()
	{

		$arrArg = func_get_args ();
		self::log ( self::L_FATAL, $arrArg );
	}
}
/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */