<?php

namespace Swoft\Process\Bootstrap;
use W7\App;
use W7\Core\Helper\FileHelper;


class Reload
{
    /**
     * 监听文件变化的路径
     *
     * @var string
     */
    private $watchDir;

    /**
     * the lasted md5 of dir
     *
     * @var string
     */
    private $md5File = '';

    /**
     * the interval of scan
     *
     * @var int
     */
    private $interval = 3;

    /**
     * 初始化方法
     */
    public function __construct()
    {
        $pathConfig = iconfig()->getUserConfig('define');
        $this->watchDir = $pathConfig['app'];
        $this->md5File = FileHelper::md5File($this->watchDir);
    }


    /**
     * 启动监听
     */
    public function run()
    {
        $server = App::$server;
        while (true) {
            sleep($this->interval);
            $md5File = FileHelper::md5File($this->watchDir);
            if (strcmp($this->md5File, $md5File) !== 0) {
                echo "Start reloading...\n";
                $server->isRun();
                $server->getServer()->reload();
                echo "Reloaded\n";
            }
            $this->md5File = $md5File;
        }
    }
}
