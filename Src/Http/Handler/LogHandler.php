<?php
/**
 * author: alex
 * date: 18-7-30 下午2:16
 */

namespace W7\Http\Handler;


use W7\Core\Base\Logger;
use W7\Core\Helper\Context;

class LogHandler
{


    /**
     * @var Context $contextObj
     */
    private $contextObj;




    public function __construct()
    {
        $this->contextObj = iloader()->singleton(Context::class);
    }

    public function beforeRequestInit($controller, $method)
    {
        $logid = uniqid();
        $spanid = rand(1000000, 9999999);
        $uri   = $controller . DIRECTORY_SEPARATOR . $method;
        Logger::addBasic("logid", uniqid());
        Logger::addBasic("client", getClientIp());
        Logger::addBasic('controller', $controller);
        Logger::addBasic('method',     $method);

        $contextData = [
            'logid'       => $logid,
            'spanid'      => $spanid,
            'uri'         => $uri,
            'requestTime' => microtime(true),
        ];
        /**
         * @var Context $contextObj
         */
        $contextObj = iloader()->singleton(Context::class);
        $contextObj->setContextData($contextData);
    }

    public function appendNoticeLog()
    {
        // php耗时单位ms毫秒
        $timeUsed = sprintf('%.2f', (microtime(true) - $this->getRequestTime()) * 1000);

        // php运行内存大小单位M
        $memUsed = sprintf('%.0f', memory_get_peak_usage() / (1024 * 1024));

        $messageAry = array(
            "[$timeUsed(ms)]",
            "[$memUsed(MB)]",
            "[{$this->getUri()}]",
        );
        Logger::notice($messageAry);
    }

    /**
     * 请求开始时间
     *
     * @return int
     */
    private function getRequestTime(): int
    {
        $contextData = $this->contextObj->getContextData();

        return $contextData['requestTime'] ?? 0;
    }

    /**
     * 请求开始时间
     *
     * @return int
     */
    private function getUri(): int
    {
        $contextData = $this->contextObj->getContextData();

        return $contextData['url'] ?? 0;
    }

    /**
     * 格式化一条日志记录
     *
     * @param string    $message   信息
     * @param array     $context    上下文信息
     * @param int       $level     级别
     * @param string    $levelName 级别名
     * @param \DateTime $ts        时间
     * @param array     $extra     附加信息
     * @return array
     */
    public function formateRecord($message, $context, $level, $levelName, $ts, $extra)
    {
        $record = array(
            'messages'   => $message,
            'context'    => $context,
            'level'      => $level,
            'level_name' => $levelName,
            'datetime'   => $ts,
            'extra'      => $extra,
        );

        return $record;
    }
}