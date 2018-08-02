<?php
/**
 * @author donknap
 * @date 18-8-1 下午5:44
 */

namespace W7\Core\Database\Connection;

use Illuminate\Database\MySqlConnection;

class SwooleMySqlConnection extends MySqlConnection
{
	public function select($query, $bindings = [], $useReadPdo = true)
	{
		return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
			if ($this->pretending()) {
				return [];
			}
			$statement = $this->getPdoForSelect($useReadPdo)->prepare($query);
			$statement->execute($this->prepareBindings($bindings));
			return $statement->fetchAll();
		});
	}

	public function statement($query, $bindings = [])
	{
		return $this->run($query, $bindings, function ($query, $bindings) {
			if ($this->pretending()) {
				return true;
			}
			$statement = $this->getPdo()->prepare($query);
			$this->recordsHaveBeenModified();
			return $statement->execute($this->prepareBindings($bindings));
		});
	}

	public function affectingStatement($query, $bindings = [])
	{
		return $this->run($query, $bindings, function ($query, $bindings) {
			if ($this->pretending()) {
				return 0;
			}
			$statement = $this->getPdo()->prepare($query);
			$statement->execute($this->prepareBindings($bindings));
			ilogger()->info('execute ' . $this->getPdo()->error);
			$this->recordsHaveBeenModified(
				($count = $statement->affected_rows) > 0
			);
			return $count;
		});
	}
}