<?php

namespace W7\Http\Listener;

use W7\App;
use W7\Core\Listener\ListenerAbstract;

class AfterRequestListener extends ListenerAbstract {
    public function run(...$params) {
        $context = App::getApp()->getContext();
        $context->destroy();
    }
}