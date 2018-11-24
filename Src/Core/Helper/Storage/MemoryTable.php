<?php
/**
 * @author donknap
 * @date 18-11-14 下午5:20
 */

namespace W7\Core\Helper\Storage;


class MemoryTable {
	const FIELD_TYPE_STRING = \swoole_table::TYPE_STRING;
	const FIELD_TYPE_INT = \swoole_table::TYPE_INT;
	const FIELD_TYPE_FLOAT = \swoole_table::TYPE_FLOAT;

	private $table = [];

	/**
	 * 创建时可以直接指定一个列数据组用于初始化表，结构如下：
	 * [
	 *      'fieldname' => [type, length],
	 *      'fieldname1' => [type, length],
	 * ]
	 * @param string $name
	 * @param int $size
	 * @param array $column
	 */
	public function create(string $name, int $size, array $column = []) {
		if (!empty($this->table[$name])) {
			return $this->table[$name];
		}

		$table = new \swoole_table($size);
		if (!empty($column)) {
			foreach ($column as $field => $params) {
				if (empty($params)) {
					throw new \RuntimeException($field . ' type is null');
				}
				$table->column($field, $params[0], $params[1]);
			}
		}
		if (empty($table->create())) {
			throw new \RuntimeException('Allocation table failed');
		}

		return $table;
	}

	public function get($name) {
		if (empty($this->table[$name])) {
			throw new \RuntimeException('Memory table not exists');
		}
		return $this->table[$name];
	}

	public function getAllName() {
		return array_keys($this->table);
	}
}