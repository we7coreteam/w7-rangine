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

namespace W7\Core\Facades;

use Symfony\Component\Console\Question\Question;

/**
 * Class Output
 * @package W7\Core\Facades
 *
 * @method static void block($messages, $type = null, $style = null, $prefix = ' ', $padding = false, $escape = true)
 * @method static void title($message)
 * @method static void section($message)
 * @method static void listing(array $elements)
 * @method static void text($message)
 * @method static void comment($message)
 * @method static void success($message)
 * @method static void error($message)
 * @method static void warning($message)
 * @method static void note($message)
 * @method static void caution($message)
 * @method static void ask($question, $default = null, $validator = null)
 * @method static void askHidden($question, $validator = null)
 * @method static void confirm($question, $default = true)
 * @method static void choice($question, array $choices, $default = null)
 * @method static void progressStart($max = 0)
 * @method static void progressAdvance($step = 1)
 * @method static void progressFinish()
 * @method static void createProgressBar($max = 0)
 * @method static void askQuestion(Question $question)
 * @method static void table(array $headers, array $rows)
 * @method static void writeList($list)
 * @method static void writeln($messages, $type = self::OUTPUT_NORMAL)
 * @method static void info($content)
 */
class Output extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return \W7\Console\Io\Output::class;
	}
}
