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


    /**
     * Registers a set of PSR-4 directories for a given namespace,
     * replacing any others previously set for this namespace.
     *
     * @param string       $prefix The prefix/namespace, with trailing '\\'
     * @param array|string $paths  The PSR-4 base directories
     *
     * @throws \InvalidArgumentException
     */
    public static function setPsr4($prefix, $paths)
    {
        if (!$prefix) {
            static::$fallbackDirsPsr4 = (array) $paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            static::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            static::$prefixDirsPsr4[$prefix] = (array) $paths;
        }
    }

    private static function findFileWithExtension($class, $ext)
    {
        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];
        if (isset(static::$prefixLengthsPsr4[$first])) {
            $subPath = $class;
            while (false !== $lastPos = strrpos($subPath, '\\')) {
                $subPath = substr($subPath, 0, $lastPos);
                $search = $subPath.'\\';
                if (isset(static::$prefixDirsPsr4[$search])) {
                    $pathEnd = DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $lastPos + 1);
                    foreach (static::$prefixDirsPsr4[$search] as $dir) {
                        if (file_exists($file = $dir . $pathEnd)) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-4 fallback dirs
        foreach (static::$fallbackDirsPsr4 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
                return $file;
            }
        }
        return false;
    }
}
