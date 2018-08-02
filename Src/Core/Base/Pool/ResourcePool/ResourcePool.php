<?php
/**
 * author: alex
 * date: 18-8-1 下午4:05
 */

namespace W7\Core\Base\Pool\ResourcePool;


class ResourcePool extends AbstractPool
{
    /**
     * 资源创建者
     * @var \Closure
     */
    private $creator;

    /**
     * 资源销毁者
     * @var \Closure
     */
    private $destroyer;

    protected function init()
    {
        parent::init();

    }

    /**
     * 等待并返回可用资源
     * @return bool|mixed
     */
    protected function wait()
    {

        $timer = 0;
        $timeout = $this->getMaxWait();

        $interval = 50;
        $uSleep = $interval * 1000;

        while ($timer <= $timeout) {
            // 等到了可用的空闲资源
            if ($res = $this->queue->valid()) {
                return $res;
            }

            $timer += $interval;
            usleep($uSleep);
        }

        return false;
    }

    /**
     * release pool
     */
    public function clear()
    {
        $this->destroyer = $this->creator = null;

        parent::clear();
    }

    public function isEmpty()
    {
        return $this->queue->count()<=0;
    }

    public function create()
    {
        $cb = $this->creator;

        return $cb();
    }

    public function destroy($resource)
    {
        $cb = $this->destroyer;
        $cb($resource);
    }

    /**
     * @return \Closure
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param \Closure $creator
     * @return $this
     */
    public function setCreator(\Closure $creator)
    {
        $this->creator = $creator;


        return $this;
    }


    /**
     * @return \Closure
     */
    public function getDestroyer()
    {
        return $this->destroyer;
    }

    /**
     * @param \Closure $destroyer
     * @return $this
     */
    public function setDestroyer(\Closure $destroyer)
    {
        $this->destroyer = $destroyer;

        return $this;
    }

    /**
     * 验证资源(eg. db connection)有效性
     * @param mixed $obj
     * @return bool
     */
    protected function validate($obj): bool
    {
        // TODO: Implement validate() method.
    }
}