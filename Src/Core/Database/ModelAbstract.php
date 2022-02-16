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

namespace W7\Core\Database;

use W7\Core\Helper\Traiter\InstanceTrait;

abstract class ModelAbstract extends \Illuminate\Database\Eloquent\Model {
	use InstanceTrait;

	public function createOrUpdate($condition) {
		return static::query()->updateOrCreate($condition, $this->getAttributes());
	}

	/**
	 * Adds the field table prefix of the current table
	 * @param array $columns
	 * @return array
	 */
	public function qualifyColumns($columns = []) {
		if (empty($columns)) {
			return [];
		}
		$model = static::instance();
		$result = [];
		foreach ($columns as $field) {
			$result[] = $model->qualifyColumn($field);
		}
		return $result;
	}
}
