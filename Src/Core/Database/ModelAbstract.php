<?php
/**
 * @author donknap
 * @date 18-7-30 下午3:30
 */

namespace W7\Core\Database;

use Illuminate\Database\Eloquent\Model;
use W7\App;
use W7\Core\Database\Pool\MasterPool;
use W7\Core\Process\MysqlPoolprocess;

class ModelAbstract extends Model
{
    /**
     * 重写方法，获取connection结合到连接池中
     * 根据传入的Connection值来实例化特定的连接池，默认是master
     * @return \Illuminate\Database\Query\Builder|QueryBuilder
     */
    public static function resolveConnection($connection = null)
    {
		/**
		 * @var \W7\Core\Database\Pool\MasterPool $dbPool
		 */
		$dbPool = App::$dbPool;
		$dbPool->setConnectionName($connection);
		return $dbPool->getConnection($connection);
    }
}
