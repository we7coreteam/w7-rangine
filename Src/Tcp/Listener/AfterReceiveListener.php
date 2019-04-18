<?php

namespace W7\Tcp\Listener;

use W7\App;
use W7\Core\Listener\ListenerAbstract;

class AfterReceiveListener extends ListenerAbstract {
    public function run(...$params) {
        $context = App::getApp()->getContext();
        $context->destroy();
    }
}