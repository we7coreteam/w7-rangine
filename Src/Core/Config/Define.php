<?php

/**
 * WeEngine Api System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

!defined('RELEASE') && define('RELEASE', 8);
!defined('DEBUG') && define('DEBUG', 1);
!defined('CLEAR_LOG') && define('CLEAR_LOG', 2);
!defined('BACKTRACE') && define('BACKTRACE', 4);
!defined('DEVELOPMENT') && define('DEVELOPMENT', DEBUG | CLEAR_LOG | BACKTRACE);
!defined('RANGINE_FRAMEWORK_PATH') && define('RANGINE_FRAMEWORK_PATH', dirname(__FILE__, 3));

//在加载配置前定义需要的常量
!defined('HTTP') && define('HTTP', 1);
!defined('TCP') && define('TCP', 2);
!defined('PROCESS') && define('PROCESS', 4);
!defined('CRONTAB') && define('CRONTAB', 8);
!defined('RELOAD') && define('RELOAD', 16);
