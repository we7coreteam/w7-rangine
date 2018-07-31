<?php
/**
 * @author donknap
 * @date 18-7-25 上午10:48
 */

namespace W7\Core\Base\Dispatcher;

abstract class DispatcherAbstract implements DispatcherInterface
{
    /**
     * 前置的中间件，用于定义一些系统的操作
     */
    public $beforeMiddleware = [];
    /**
     * 后置的中间件，用于定义一些系统的操作
     */
    public $afterMiddleware = [];
    /**
     * 最后的中间件，用于处理控制器请求等操作
     */
    public $lastMiddleware = null;
}
