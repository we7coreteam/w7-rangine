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
abstract class ModelAbstract extends Model {
	protected function insertAndSetId(Builder $query, $attributes) {
		$id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

		$this->setAttribute($keyName, $id);
	}

	public function createOrUpdate($condition) {
		return static::query()->updateOrCreate($condition, $this->getAttributes());
	}

	/**
	 * <没有重写功能，只是增加一下注释>
	 * 处理三张表关联的情况，使用此方法
	 *
	 * @param string $related 最终要关联的的表
	 * @param string $through 关联最终表时，需要关联的中间表
	 * @param null $firstKey 中间表关联主表的字段
	 * @param null $secondKey 中间表关联最终表的字段
	 * @param null $localKey 主表中对应中间表的字段
	 * @param null $secondLocalKey 最终表中对应中间表的字段
	 * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
	 */
	public function hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null) {
		return parent::hasManyThrough($related, $through, $firstKey, $secondKey, $localKey, $secondLocalKey);
	}

	/**
	 * 获取当前模型的实例化对象
	 */
	static public function getModel() {
		return iloader()->singleton(static::class);
	}

	/**
	 * 增加当前表的字段表前缀
	 * @param array $columns
	 */
	static public function qualifyColumns($columns = []) {
		if (empty($columns)) {
			return [];
		}
		$model = static::getModel();
		$result = [];
		foreach ($columns as $field) {
			$result[] = $model->qualifyColumn($field);
		}
		return $result;
	}
}
