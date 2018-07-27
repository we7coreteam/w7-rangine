<?php
/**
 * 处理控制台的输入，分隔命令
 * @author donknap
 * @date 18-7-19 上午10:17
 */

namespace W7\Console\Io;

class Input
{
    private $command;
    private $action;
    private $option;

    public function getCommand($argv = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }
        /**
         * @var \W7\Console\Io\Parser $parser
         */
        $parser = iloader()->singleton(\W7\Console\Io\Parser::class);
        $command = $parser->parse($argv);
        list($temp, $this->command, $this->action) = $command[0];
        $this->option = array_merge([], $command[1], $command[2]);
        return [
            'command' => $this->command,
            'action' => $this->action,
            'option' => $this->option,
        ];
    }

    public function isVersionCommand()
    {
        $command = $this->getCommand();

        if (isset($command['option']['v']) || isset($command['option']['version'])) {
            return true;
        } else {
            return false;
        }
    }
}
