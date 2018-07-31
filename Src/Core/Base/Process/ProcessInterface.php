<?php
/**
 * @author donknap
 * @date 18-7-25 下午3:04
 */

namespace W7\Core\Base\Process;

interface ProcessInterface
{
    /**
     * 线程具体执行内容
     * @return mixed
     */
    public function run();

    /**
     * 检查线程是否可以执行，TRUE执行，FALSE不执行
     * @return mixed
     */
    public function check();
}
