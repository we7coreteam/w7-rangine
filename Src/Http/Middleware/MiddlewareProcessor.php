<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18-7-20
 * Time: 上午10:33
 */

namespace W7\Http\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Table;

class MiddlewareProcessor
{


    const FILE_CACHE_TYPE = 1;

    const MEMORY_CACHE_TYPE = 2;

    //1191014510910510010010810111997114101 转化32进制去掉0000000000000
    const MEMORY_CACHE_KEY = 'slgop41mg0c';


    public $cacheType = self::MEMORY_CACHE_TYPE;


    /**
     * @param int $cacheType
     * @param string|null $filePath
     * @param Table|null $tableObj
     */
    public function getMiddlewares(int $cacheType, string $filePath = null, Table $tableObj = null)
    {

        if (empty($filePath) && empty($tableObj)) {
            throw new \RuntimeException("fun args is not be empty all");
        }
        switch ($cacheType)
        {
            case self::MEMORY_CACHE_TYPE:
                $data = $this->getMemoryCached($tableObj);
                break;
            case self::FILE_CACHE_TYPE:
                $data = $this->getfileCached($filePath);
                break;
        }
        return $data;
    }


    /**
     * @param Table $tableObj
     * @return array|mixed
     */
    protected function getMemoryCached(Table $tableObj)
    {
        if (!($tableObj instanceof Table) || empty($tableObj)){
            throw new \RuntimeException("tableObj is not isset");
        }
        $data = $tableObj->get(self::MEMORY_CACHE_KEY);
        if (empty($data)){
            return [];
        }
        return json_decode($data['values'], true);

    }

    /**
     * @param string $filePath
     * @return mixed
     */
    protected function getfileCached(string $filePath)
    {
        if (!is_file($filePath)){
            throw new \RuntimeException("file is not isset");
        }
        return require_once $filePath;
    }
    /**
     * @param array $middlewares
     * @param int $cacheType
     * @param string $filePath
     * @return bool|Table
     */
    public function insertMiddlewareCached(array $middlewares, int $cacheType, string $filePath = null)
    {
        switch ($cacheType)
        {
            case self::FILE_CACHE_TYPE:
                $this->fileCached($middlewares, $filePath);
                break;
            case self::MEMORY_CACHE_TYPE:
                $table = $this->memoryCached($middlewares);
                return $table;
                break;
        }
        return true;
    }

    /**
     * @param array $middlewares
     * @return Table
     */
    public function memoryCached(array $middlewares)
    {
        $table = new Table(102400);
        $middlewaresJson = json_encode($middlewares);
        $table->column('values', Table::TYPE_STRING, 102000);
        $table->set(self::MEMORY_CACHE_KEY, ["values"=>$middlewaresJson]);
        return $table;
    }

    /**
     * @param array $middlewares
     * @param string $filePath
     */
    public function fileCached(array $middlewares, string $filePath)
    {
        if (!is_file($filePath)){
            throw new \RuntimeException("file is not isset");
        }
        file_put_contents(
            $filePath,
            '<?php return '. var_export($middlewares, true). ';'
        );
    }
}