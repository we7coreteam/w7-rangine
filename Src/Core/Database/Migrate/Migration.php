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

namespace W7\Core\Database\Migrate;

use Illuminate\Database\Schema\MySqlBuilder;

abstract class Migration {
	/**
	 * The name of the database connection to use.
	 *
	 * @var string|null
	 */
	protected $connection = 'default';

	/**
	 * Enables, if supported, wrapping the migration within a transaction.
	 *
	 * @var bool
	 */
	public $withinTransaction = true;
	/**
	 * @var MySqlBuilder
	 */
	protected $schema;

	public function __construct() {
		$this->schema = idb()->connection($this->getConnection())->getSchemaBuilder();
	}

	/**
	 * Get the migration connection name.
	 *
	 * @return string|null
	 */
	public function getConnection() {
		return $this->connection;
	}
}
