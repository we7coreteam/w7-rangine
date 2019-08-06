<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

!defined('RELEASE') && define('RELEASE', 8);
!defined('DEBUG') && define('DEBUG', 1);
!defined('CLEAR_LOG') && define('CLEAR_LOG', 2);
!defined('BACKTRACE') && define('BACKTRACE', 4);
!defined('DEVELOPMENT') && define('DEVELOPMENT', DEBUG | CLEAR_LOG | BACKTRACE);
!defined('RANGINE_FRAMEWORK_PATH') && define('RANGINE_FRAMEWORK_PATH', dirname(__FILE__, 3));
