<?php
/**
 * 保存中间件数据
 * @author alex
 * @date 18-7-24 下午5:31
 */

namespace W7\Core\Helper;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Table;
use W7\Http\Middleware\RequestMiddleware;

class Middleware
{


    const FILE_CACHE_TYPE = 1;

    const MEMORY_CACHE_TYPE = 2;

    const LAST_MIDDLER_WARE = RequestMiddleware::class;
    //1191014510910510010010810111997114101 转化32进制去掉0000000000000
    const MEMORY_CACHE_KEY = 'slgop41mg0c';


    public $cacheType = self::MEMORY_CACHE_TYPE;



    /**
     * @param int $cacheType
     * @param string|null $filePath
     * @param Table|null $tableObj
     */
    public function getMiddlewares(Table $tableObj = null)
    {

        if (empty($filePath) && empty($tableObj)) {
            throw new \RuntimeException("fun args is not be empty all");
        }
        $data = $this->getMemoryCached($tableObj);
        $server = iconfig()->getServer();
        $beforeMiddlerwares = $server['befor_middlerware'];
        $afterMiddlerwares  = $server['after_middlerware'];
        $lastMiddlerware    = static::LAST_MIDDLER_WARE;
        $data = array_merge($beforeMiddlerwares, $data, $afterMiddlerwares);
        array_unshift($data, $lastMiddlerware);
        return $data;
    }

    protected function formatData(array $commonMiddlewares, array $methodMiddlewares)
    {
        $middlewares = [];
        foreach($commonMiddlewares as $controller=>$middleware)
        {
            $middlewares[$controller] = $middleware;
        }
        foreach($methodMiddlewares as $method=>$middleware)
        {
            $middlewares[$method] = $middleware;
        }
        return $middlewares;
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
     * @param array $middlewares
     * @param int $cacheType
     * @param string $filePath
     * @return bool|Table
     */
    public function insertMiddlewareCached()
    {
        $dataHepler  = new RouteData();
        $middlewares = $dataHepler->middlerWareData();
        $middlewares = $this->formatData($middlewares['controller_midllerware'], $middlewares['method_middlerware']);
        $table = $this->memoryCached($middlewares);
        return $table;
    }

    /**
     * @param array $middlewares
     * @return MemoryCache
     */
    protected function memoryCached(array $middlewares)
    {
        $cacheObj = new Cache();
        /**
         * @var MemoryCache $table
         */
        $table = $cacheObj->getDriver('memory');
        $middlewaresJson = json_encode($middlewares);
        $table->create();
        $table->set(self::MEMORY_CACHE_KEY, ["values"=>$middlewaresJson]);
        return $table;
    }

}