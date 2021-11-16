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

/**
 * Class ModelAbstract
 * @package W7\Core\Database
 *
 * @method make(array $attributes = [])
 * @method withGlobalScope($identifier, $scope)
 * @method withoutGlobalScope($scope)
 * @method withoutGlobalScopes(array $scopes = null)
 * @method removedScopes()
 * @method whereKey($id)
 * @method whereKeyNot($id)
 * @method where($column, $operator = null, $value = null, $boolean = 'and')
 * @method orWhere($column, $operator = null, $value = null)
 * @method hydrate(array $items)
 * @method fromQuery($query, $bindings = [])
 * @method find($id, $columns = ['*'])
 * @method findMany($ids, $columns = ['*'])
 * @method findOrFail($id, $columns = ['*'])
 * @method findOrNew($id, $columns = ['*'])
 * @method firstOrNew(array $attributes, array $values = [])
 * @method firstOrCreate(array $attributes, array $values = [])
 * @method updateOrCreate(array $attributes, array $values = [])
 * @method firstOrFail($columns = ['*'])
 * @method firstOr($columns = ['*'], Closure $callback = null)
 * @method value($column)
 * @method get($columns = ['*'])
 * @method getModels($columns = ['*'])
 * @method eagerLoadRelations(array $models)
 * @method getRelation($name)
 * @method cursor()
 * @method chunkById($count, callable $callback, $column = null, $alias = null)
 * @method pluck($column, $key = null)
 * @method paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
 * @method simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
 * @method create(array $attributes = [])
 * @method forceCreate(array $attributes)
 * @method update(array $values)
 * @method increment($column, $amount = 1, array $extra = [])
 * @method decrement($column, $amount = 1, array $extra = [])
 * @method delete()
 * @method forceDelete()
 * @method onDelete(Closure $callback)
 * @method scopes(array $scopes)
 * @method applyScopes()
 * @method with($relations)
 * @method without($relations)
 * @method newModelInstance($attributes = [])
 * @method getQuery()
 * @method setQuery($query)
 * @method toBase()
 * @method getEagerLoads()
 * @method setEagerLoads(array $eagerLoad)
 * @method getModel()
 * @method setModel(Model $model)
 * @method qualifyColumn($column)
 * @method getMacro($name)
 */
abstract class ModelAbstract extends \Illuminate\Database\Eloquent\Model {
	use InstanceTrait;

	public function createOrUpdate($condition): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder {
		return static::query()->updateOrCreate($condition, $this->getAttributes());
	}

	/**
	 * Adds the field table prefix of the current table
	 * @param array $columns
	 * @return array
	 */
	public function qualifyColumns($columns = []): array {
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
