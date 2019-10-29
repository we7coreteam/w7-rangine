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

interface MigrationRepositoryInterface {
	/**
	 * Get the completed migrations.
	 *
	 * @return array
	 */
	public function getRan();

	/**
	 * Get list of migrations.
	 *
	 * @param  int  $steps
	 * @return array
	 */
	public function getMigrations($steps);

	/**
	 * Get the last migration batch.
	 *
	 * @return array
	 */
	public function getLast();

	/**
	 * Get the completed migrations with their batch numbers.
	 *
	 * @return array
	 */
	public function getMigrationBatches();

	/**
	 * Log that a migration was run.
	 *
	 * @param  string  $file
	 * @param  int  $batch
	 * @return void
	 */
	public function log($file, $batch);

	/**
	 * Remove a migration from the log.
	 *
	 * @param  object  $migration
	 * @return void
	 */
	public function delete($migration);

	/**
	 * Get the next migration batch number.
	 *
	 * @return int
	 */
	public function getNextBatchNumber();

	/**
	 * Create the migration repository data store.
	 *
	 * @return void
	 */
	public function createRepository();

	/**
	 * Determine if the migration repository exists.
	 *
	 * @return bool
	 */
	public function repositoryExists();

	/**
	 * Set the information source to gather data.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setSource($name);
}
