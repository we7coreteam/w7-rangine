<?php
/**
 * author: alex
 * date: 18-8-1 下午3:59
 */

namespace W7\Core\Base\Pool\ResourcePool;


interface PoolInterface
{
    /**
     * Access to resource
     * @return mixed
     */
    public function get();

    /**
     * Return resource to the pool
     * @param mixed $resource
     */
    public function put($resource);

    /**
     * Empty the resource pool - Release all connections
     */
    public function clear();

}