<?php

namespace W7\Core\Exception\Formatter;

interface ExceptionFormatterInterface {
	public function formatDevelopmentException(\Throwable $e) : string;
	public function formatReleaseException(\Throwable $e) : string;
}