<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

include_once 'vendor/autoload.php';

const SOFT_NAME = 'Rangine';

$finder = PhpCsFixer\Finder::create()
	->files()
	->name('*.php')
	->exclude('vendor')
	->in(__DIR__)
	->ignoreDotFiles(true)
	->ignoreVCS(true);

$fixers = [
	'@PSR2'                                      => true,
	'single_quote'                               => true, //简单字符串应该使用单引号代替双引号；
	'no_unused_imports'                          => true, //删除没用到的use
	'no_singleline_whitespace_before_semicolons' => true, //禁止只有单行空格和分号的写法；
	'no_empty_statement'                         => true, //多余的分号
	'no_extra_blank_lines'                       => true, //多余空白行
	'no_blank_lines_after_phpdoc'                => true, //注释和代码中间不能有空行
	'no_empty_phpdoc'                            => true, //禁止空注释
	'phpdoc_indent'                              => true, //注释和代码的缩进相同
	'no_blank_lines_after_class_opening'         => true, //类开始标签后不应该有空白行；
	'include'                                    => true, //include 和文件路径之间需要有一个空格，文件路径不需要用括号括起来；
	'no_trailing_comma_in_list_call'             => true, //删除 list 语句中多余的逗号；
	'no_leading_namespace_whitespace'            => true, //命名空间前面不应该有空格；
	'standardize_not_equals'                     => true, //使用 <> 代替 !=；
	'blank_line_after_opening_tag'               => true, //PHP开始标记后换行
	'indentation_type'                           => true,
	'concat_space'                               => [
		'spacing' => 'one',
	],
	'space_after_semicolon' => [
		'remove_in_empty_for_expressions' => true,
	],
	'header_comment' => [
		'comment_type' => 'PHPDoc',
		'header' => "This file is part of Rangine \r\n\r\n(c) We7Team 2019 <https://www.rangine.com/> \r\n\r\ndocument http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284\r\n\r\nvisited https://www.rangine.com/ for more details",
	],
	'braces'                                     => ['position_after_functions_and_oop_constructs' => 'same']

];
$config = new \PhpCsFixer\Config();
return $config
	->setRules($fixers)
	->setFinder($finder)
	->setIndent("\t");
