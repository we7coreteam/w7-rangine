<?php
/**
 * @author donknap
 * @date 18-7-30 下午3:30
 */

namespace W7\Core\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelAbstract
 * @package W7\Core\Database
 *
 * @method static find($id, $columns = ['*'])
 */
class ModelAbstract extends Model {
	protected function insertAndSetId(Builder $query, $attributes) {
		$id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

		$this->setAttribute($keyName, $id);
	}

	public function createOrUpdate($condition) {
		return static::query()->updateOrCreate($condition, $this->getAttributes());
	}

}
