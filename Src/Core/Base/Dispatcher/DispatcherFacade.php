<?php
/**
 * author: alex
 * date: 18-8-3 上午9:37
 */

namespace W7\Core\Base\Dispatcher;

abstract class DispatcherFacade extends DispatcherAbstract
{
    protected $resolvedInstance;


    abstract public function getFacadeAccessor();


    public function dispatch(...$args)
    {
        $instance = $this->getFacadeAccessor();
        return $instance->trigger(...$args);
    }
}
