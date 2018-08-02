<?php
/**
 * author: alex
 * date: 18-8-1 下午4:00
 */

namespace W7\Core\Base\Pool\ResourcePool;



abstract class AbstractPool implements PoolInterface
{
    use ManyPoolTrait;


    protected $key = 0;
    /**
     * @var string The pool name
     */
    protected $name = 'default';

    /**
     * (Busy) in use resource
     * @var \SplObjectStorage
     */
    protected $queue;

    /**
     * default 30 seconds
     * @var int
     */
    protected $expireTime = 30;

    /**
     * 自定义的资源配置(创建资源对象时可能会用到 e.g mysql 连接配置)
     * @var array
     */
    protected $options = [];

    /**
     * StdObject constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $property => $value) {
            $setter = 'set' . ucfirst($property);

            if (\method_exists($this, $setter)) {
                $this->$setter($value);
                continue;
            }
            if (\property_exists($this, $property)) {
                $this->$property = $value;
            }
        }

        $this->init();
        $this->queue = new \SplObjectStorage();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->queue->count();
    }

    /**
     * 验证资源(eg. db connection)有效性
     * @param mixed $obj
     * @return bool
     */
    abstract protected function validate($obj): bool;
    /**
     * 销毁资源实例
     * @param $obj
     * @return void
     */
    abstract public function destroy($obj);

    /**
     * 处理已过期的对象
     * @param $obj
     */
    protected function expire($obj)
    {
    }
    /**
     * 等待并返回可用资源
     * @return bool|mixed
     */
    abstract protected function wait();

    /**
     * {@inheritdoc}
     */
    public function put($resource)
    {
        // remove from busy queue
        $this->queue->attach($resource);
        var_dump($this->queue->count());
    }

    /**
     * @param mixed $obj
     * @return string
     */
    protected function genID($obj): string
    {
        if (\is_resource($obj)) {
            return (string)$obj;
        }

        if (\is_object($obj)) {
            return \spl_object_hash($obj);
        }

        return \md5(\json_encode($obj));
    }

    /**
     * release pool
     */
    public function clear()
    {


        $this->queue->removeAll($this->queue);
    }

    /**
     * release pool
     */
    public function __destruct()
    {
        $this->clear();
    }


    /**
     * init
     */
    protected function init()
    {
        $this->queue = new \SplObjectStorage();

        // fix mixSize
        if ($this->initSize > $this->maxSize) {
            $this->maxSize = $this->initSize;
        }
    }

    public function initPool()
    {
        // some works ...
    }

    /**
     * {@inheritdoc}
     * @return mixed
     * @throws \RuntimeException
     */
    public function get()
    {
        $res = null;
        // There are also resources available
        if (!$this->isEmpty()) {
            $this->queue->rewind();
            $res = $this->queue->current();
        }
        return $res;
    }

    public function isEmpty()
    {
        return $this->queue->count() <=0;
    }

}