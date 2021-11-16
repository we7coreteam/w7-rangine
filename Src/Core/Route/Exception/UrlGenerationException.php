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

namespace W7\Core\Route\Exception;

use Exception;
use Illuminate\Support\Str;
use W7\Core\Route\Route;

class UrlGenerationException extends Exception {
	/**
	 * Create a new exception for missing route parameters.
	 *
	 * @param  Route  $route
	 * @param  array  $parameters
	 * @return static
	 */
	public static function forMissingParameters(Route $route, array $parameters = []): static {
		$parameterLabel = Str::plural('parameter', count($parameters));

		$message = sprintf(
			'Missing required %s for [Route: %s] [URI: %s]',
			$parameterLabel,
			$route->getName(),
			$route->getUri()
		);

		if (count($parameters) > 0) {
			$message .= sprintf(' [Missing %s: %s]', $parameterLabel, implode(', ', $parameters));
		}

		$message .= '.';

		return new static($message);
	}
}
