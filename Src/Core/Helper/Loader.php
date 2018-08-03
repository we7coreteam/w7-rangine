<?php
/**
 * 加载助手
 * @author donknap
 * @date 18-7-19 上午10:24
 */

namespace W7\Core\Helper;

class Loader
{
    //存储加载过的类
    private $cache = array();
    private $loadTypeMap = array();

    /**
     * 实例化一个单例
     * @param $name
     * @return mixed
     */
    public function singleton($name)
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        $this->cache[$name] = $this->object($name);
        return $this->cache[$name];
    }

    /**
     * 实例化一个对
     * @param $name
     * @return bool
     */
    public function object($name)
    {
        if (class_exists($name)) {
            return new $name();
        } else {
            var_dump($name);
            throw new \Exception('类不存在');
        }
    }
}
