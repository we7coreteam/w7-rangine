<?php
namespace W7\Core\Helper;





/**
 * author: yangshen
 * date: 12-7-12 上午11:48
 */
class Logger
{

    //所有错误
    const L_ALL = 0;
    //开启DEBUG
    const L_DEBUG = 1;

    //debug之上的级别基本用于打印变量数据，类属性数据等
    const L_TRACE = 2;

    //打印返回数据，
    const L_INFO = 3;

    //打印执行结果或者执行结果效率时候
    const L_NOTICE = 4;

    //逻辑错误时会用到
    const L_WARNING = 5;

    //系统级别的错误，一般不建议自己使用
    const L_FATAL = 6;


    private  $arr_desc = array (0 => 'ALL', 1 => 'DEBUG', 2 => 'TRACE', 3 => 'INFO',
        4 => 'NOTICE', 5 => 'WARNING', 6 => 'FATAL' );

    private  $log_level = self::L_DEBUG;

    private  $arr_basic = [];

    private  $file = [];

    private  $force_flush = false;

    private  $messgaes = [];

    private  $flush_interval = 1;

    public function flush()
    {

        foreach ( $this->file as $fileIndex=>$file )
        {
            $this->flushLog($fileIndex);
        }
    }

    public function addBasic($key, $value)
    {

        $this->arr_basic[$key] = $value;
    }

    public function init($filename, $level, $flushInterval = 1,$arrBasic = null, $forceFlush = false)
    {

        if (! isset ( $this->arr_desc [$level] ))
        {
            trigger_error ( "invalid level:$level" );
            return;
        }
        $this->log_level = $level;
        $dir = dirname ( $filename );
        if (! file_exists ( $dir ))
        {
            if (! mkdir ( $dir, 0755, true ))
            {
                trigger_error ( "create log file $filename failed, no permmission" );
                return;
            }
        }
        $accessFile = fopen ( $filename, 'a+' );
        if (empty ( $accessFile ))
        {
            trigger_error ( "create log file $filename failed, no disk space for permission" );
            $this->file = array ();
            return;
        }

       $this->file['access'] = $filename;

        $errorFile = fopen ( $filename . '.wf', 'a+' );
        if (empty ( $errorFile ))
        {
            trigger_error ( "create log file $filename.wf failed, no disk space for permission" );
            $this->file = array ();
            return;
        }
        $this->file['error'] = $filename . ".wf";
        if (! empty ( $arrBasic ))
        {
           $this->arr_basic = $arrBasic;
        }

        if (!empty($flushInterval))
        {
            $this->flush_interval = $flushInterval;
        }

        $this->force_flush = $forceFlush;
    }

    private static function checkPrintable(&$data, $key)
    {

        if (! is_string ( $data ))
        {
            return;
        }

        if (preg_match ( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\xFF]/', $data ))
        {
            $data = base64_encode ( $data );
        }
    }
    private function checkFlushable($fileIndex)
    {
        if (count($this->messgaes[$fileIndex]) >= $this->flush_interval)
        {
            return true;
        }
        return false;
    }

    private function log($level, $arrArg)
    {

        if ($level < $this->log_level || empty ( $this->file ) || empty ( $arrArg ))
        {
            return;
        }

        $arrMicro = explode ( " ", microtime () );
        $content = '[' . date ( 'Ymd H:i:s ' );
        $content .= sprintf ( "%06d", intval ( 1000000 * $arrMicro [0] ) );
        $content .= '][';
        $content .= $this->arr_desc [$level];
        $content .= "]";
        foreach ( $this->arr_basic as $key => $value )
        {
            $content .= "[$key:$value]";
        }

        $arrTrace = debug_backtrace ();
        if (isset ( $arrTrace [1] ))
        {
            $line = $arrTrace [1] ['line'];
            $file = $arrTrace [1] ['file'];
            $file = substr ( $file, strlen ( IA_ROOT ) + 1 );
            $content .= "[$file:$line]";
        }

        foreach ( $arrArg as $idx => $arg )
        {

            if (is_object($arg))
            {
                $arg = (array)$arg;
            }
            if (is_array ( $arg ))
            {
                array_walk_recursive ( $arg, array (Logger::class, 'checkPrintable' ) );

                if ($this->log_level)
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

        $this->messgaes['access'][] = $content;
        if (self::checkFlushable('access'))
        {
            $this->flushLog('access');
        }

        if ($level <= self::L_NOTICE)
        {
            return;
        }

        $this->messgaes["error"][] = $content;
        if (self::checkFlushable('error'))
        {
            $this->flushLog('error');
        }
    }

    private  function flushLog( $fileIndex)
    {
        if (empty($this->messgaes[$fileIndex])){
            return;
        }
        $messageText = implode("\n", $this->messgaes[$fileIndex]) . "\n";
        FileHelper::write($this->file[$fileIndex], $messageText);
        $this->messgaes[$fileIndex] = [];
    }

    public function debug()
    {

        $arrArg = func_get_args ();
        self::log ( self::L_DEBUG, $arrArg );
    }

    public function trace()
    {

        $arrArg = func_get_args ();
        self::log ( self::L_TRACE, $arrArg );
    }

    public function info()
    {

        $arrArg = func_get_args ();
        self::log ( self::L_INFO, $arrArg );
    }

    public function notice()
    {

        $arrArg = func_get_args ();
        self::log ( self::L_NOTICE, $arrArg );
    }

    public function warning()
    {

        $arrArg = func_get_args ();
        self::log ( self::L_WARNING, $arrArg );
    }

    public function fatal()
    {

        $arrArg = func_get_args ();
        self::log ( self::L_FATAL, $arrArg );
    }
}
/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */