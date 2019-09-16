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

namespace W7\Core\Middleware;

class TrimStringMiddleware extends TransformsRequestMiddleware {
	/**
	 * The attributes that should not be trimmed.
	 *
	 * @var array
	 */
	protected $except = [
		//
	];

	/**
	 * Transform the given value.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function transform($key, $value) {
		if (in_array($key, $this->except, true)) {
			return $value;
		}

		return is_string($value) ? trim($value) : $value;
	}
}
