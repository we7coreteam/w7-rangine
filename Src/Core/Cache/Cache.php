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

namespace W7\Core\Cache;

use W7\Core\Cache\Handler\HandlerAbstract;

/**
 * @method connect( $host, $port = 6379, $timeout = 0.0, $reserved = null, $retry_interval = 0 ) {}
 * @method psetex($key, $ttl, $value) {}
 * @method sScan($key, $iterator, $pattern = '', $count = 0) {}
 * @method scan( &$iterator, $pattern = null, $count = 0 ) {}
 * @method zScan($key, $iterator, $pattern = '', $count = 0) {}
 * @method hScan($key, $iterator, $pattern = '', $count = 0) {}
 * @method client($command, $arg = '') {}
 * @method slowlog($command) {}
 * @method open( $host, $port = 6379, $timeout = 0.0, $retry_interval = 0 ) {}
 * @method pconnect( $host, $port = 6379, $timeout = 0.0, $persistent_id = null ) {}
 * @method popen( $host, $port = 6379, $timeout = 0.0, $persistent_id = null ) {}
 * @method close( ) {}
 * @method setOption( $name, $value ) {}
 * @method getOption( $name ) {}
 * @method ping( ) {}
 * @method setex( $key, $ttl, $value ) {}
 * @method setnx( $key, $value ) {}
 * @method del( $key1, $key2 = null, $key3 = null ) {}
 * @method multi( $mode = \Redis::MULTI ) {}
 * @method exec( ) {}
 * @method discard( ) {}
 * @method watch( $key ) {}
 * @method unwatch( ) {}
 * @method subscribe( $channels, $callback ) {}
 * @method psubscribe( $patterns, $callback ) {}
 * @method publish( $channel, $message ) {}
 * @method pubsub( $keyword, $argument ) {}
 * @method exists( $key ) {}
 * @method incr( $key ) {}
 * @method incrByFloat( $key, $increment ) {}
 * @method incrBy( $key, $value ) {}
 * @method decr( $key ) {}
 * @method decrBy( $key, $value ) {}
 * @method lPush( $key, $value1, $value2 = null, $valueN = null ) {}
 * @method rPush( $key, $value1, $value2 = null, $valueN = null ) {}
 * @method lPushx( $key, $value ) {}
 * @method rPushx( $key, $value ) {}
 * @method lPop( $key ) {}
 * @method rPop( $key ) {}
 * @method blPop( array $keys, $timeout) {}
 * @method brPop( array $keys, $timeout ) {}
 * @method lLen( $key ) {}
 * @method lSize( $key ) {}
 * @method lIndex( $key, $index ) {}
 * @method lGet( $key, $index ) {}
 * @method lSet( $key, $index, $value ) {}
 * @method lRange( $key, $start, $end ) {}
 * @method lGetRange( $key, $start, $end ) {}
 * @method lTrim( $key, $start, $stop ) {}
 * @method listTrim( $key, $start, $stop ) {}
 * @method lRem( $key, $value, $count ) {}
 * @method lRemove( $key, $value, $count ) {}
 * @method lInsert( $key, $position, $pivot, $value ) {}
 * @method sAdd( $key, $value1, $value2 = null, $valueN = null ) {}
 * @method sAddArray( $key, array $values) {}
 * @method sRem( $key, $member1, $member2 = null, $memberN = null ) {}
 * @method sRemove( $key, $member1, $member2 = null, $memberN = null ) {}
 * @method sMove( $srcKey, $dstKey, $member ) {}
 * @method sIsMember( $key, $value ) {}
 * @method sContains( $key, $value ) {}
 * @method sCard( $key ) {}
 * @method sPop( $key ) {}
 * @method sRandMember( $key, $count = null ) {}
 * @method sInter( $key1, $key2, $keyN = null ) {}
 * @method sInterStore( $dstKey, $key1, $key2, $keyN = null ) {}
 * @method sUnion( $key1, $key2, $keyN = null ) {}
 * @method sUnionStore( $dstKey, $key1, $key2, $keyN = null ) {}
 * @method sDiff( $key1, $key2, $keyN = null ) {}
 * @method sDiffStore( $dstKey, $key1, $key2, $keyN = null ) {}
 * @method sMembers( $key ) {}
 * @method sGetMembers( $key ) {}
 * @method getSet( $key, $value ) {}
 * @method randomKey( ) {}
 * @method select( $dbindex ) {}
 * @method move( $key, $dbindex ) {}
 * @method rename( $srcKey, $dstKey ) {}
 * @method renameKey( $srcKey, $dstKey ) {}
 * @method renameNx( $srcKey, $dstKey ) {}
 * @method expire( $key, $ttl ) {}
 * @method pExpire( $key, $ttl ) {}
 * @method setTimeout( $key, $ttl ) {}
 * @method expireAt( $key, $timestamp ) {}
 * @method pExpireAt( $key, $timestamp ) {}
 * @method keys( $pattern ) {}
 * @method getKeys( $pattern ) {}
 * @method dbSize( ) {}
 * @method auth( $password ) {}
 * @method bgrewriteaof( ) {}
 * @method slaveof( $host = '127.0.0.1', $port = 6379 ) {}
 * @method object( $string = '', $key = '' ) {}
 * @method save( ) {}
 * @method bgsave( ) {}
 * @method lastSave( ) {}
 * @method wait( $numSlaves, $timeout ) {}
 * @method type( $key ) {}
 * @method append( $key, $value ) {}
 * @method getRange( $key, $start, $end ) {}
 * @method substr( $key, $start, $end ) {}
 * @method setRange( $key, $offset, $value ) {}
 * @method strlen( $key ) {}
 * @method bitpos( $key, $bit, $start = 0, $end = null) {}
 * @method getBit( $key, $offset ) {}
 * @method setBit( $key, $offset, $value ) {}
 * @method bitCount( $key ) {}
 * @method bitOp( $operation, $retKey, ...$keys) {}
 * @method flushDB( ) {}
 * @method flushAll( ) {}
 * @method sort( $key, $option = null ) {}
 * @method info( $option = null ) {}
 * @method resetStat( ) {}
 * @method ttl( $key ) {}
 * @method pttl( $key ) {}
 * @method persist( $key ) {}
 * @method mset( array $array ) {}
 * @method mget( array $array ) {}
 * @method msetnx( array $array ) {}
 * @method rpoplpush( $srcKey, $dstKey ) {}
 * @method brpoplpush( $srcKey, $dstKey, $timeout ) {}
 * @method zAdd( $key, $score1, $value1, $score2 = null, $value2 = null, $scoreN = null, $valueN = null ) {}
 * @method zRange( $key, $start, $end, $withscores = null ) {}
 * @method zRem( $key, $member1, $member2 = null, $memberN = null ) {}
 * @method zDelete( $key, $member1, $member2 = null, $memberN = null ) {}
 * @method zRevRange( $key, $start, $end, $withscore = null ) {}
 * @method zRangeByScore( $key, $start, $end, array $options = array() ) {}
 * @method zRevRangeByScore( $key, $start, $end, array $options = array() ) {}
 * @method zRangeByLex( $key, $min, $max, $offset = null, $limit = null ) {}
 * @method zRevRangeByLex( $key, $min, $max, $offset = null, $limit = null ) {}
 * @method zCount( $key, $start, $end ) {}
 * @method zRemRangeByScore( $key, $start, $end ) {}
 * @method zDeleteRangeByScore( $key, $start, $end ) {}
 * @method zRemRangeByRank( $key, $start, $end ) {}
 * @method zDeleteRangeByRank( $key, $start, $end ) {}
 * @method zCard( $key ) {}
 * @method zSize( $key ) {}
 * @method zScore( $key, $member ) {}
 * @method zRank( $key, $member ) {}
 * @method zRevRank( $key, $member ) {}
 * @method zIncrBy( $key, $value, $member ) {}
 * @method zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM') {}
 * @method zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM') {}
 * @method hSet( $key, $hashKey, $value ) {}
 * @method hSetNx( $key, $hashKey, $value ) {}
 * @method hGet($key, $hashKey) {}
 * @method hLen( $key ) {}
 * @method hDel( $key, $hashKey1, $hashKey2 = null, $hashKeyN = null ) {}
 * @method hKeys( $key ) {}
 * @method hVals( $key ) {}
 * @method hGetAll( $key ) {}
 * @method hExists( $key, $hashKey ) {}
 * @method hIncrBy( $key, $hashKey, $value ) {}
 * @method hIncrByFloat( $key, $field, $increment ) {}
 * @method hMset( $key, $hashKeys ) {}
 * @method hMGet( $key, $hashKeys ) {}
 * @method config( $operation, $key, $value ) {}
 * @method evaluate( $script, $args = array(), $numKeys = 0 ) {}
 * @method evalSha( $scriptSha, $args = array(), $numKeys = 0 ) {}
 * @method evaluateSha( $scriptSha, $args = array(), $numKeys = 0 ) {}
 * @method script( $command, $script ) {}
 * @method getLastError() {}
 * @method clearLastError() {}
 * @method dump( $key ) {}
 * @method restore( $key, $ttl, $value ) {}
 * @method migrate( $host, $port, $key, $db, $timeout, $copy = false, $replace = false ) {}
 * @method time() {}
 * @method pfAdd( $key, array $elements ) {}
 * @method pfCount( $key ) {}
 * @method pfMerge( $destkey, array $sourcekeys ) {}
 * @method rawCommand( $command, $arguments ) {}
 * @method getMode() {}
 */
class Cache extends CacheAbstract {
	public function set($key, $value, $ttl = null) {
		return $this->call(function (HandlerAbstract $handler) use ($key, $value, $ttl) {
			$value = $handler->pack($value);
			return $handler->set($this->warpKey($key), $value, $ttl);
		});
	}

	public function get($key, $default = null) {
		return $this->call(function (HandlerAbstract $handler) use ($key, $default) {
			$result = $handler->get($this->warpKey($key), $default);
			if ($result === false || $result === null) {
				return $default;
			}

			return $handler->unpack($result);
		});
	}

	public function delete($key) {
		return (bool)$this->call(function (HandlerAbstract $handler) use ($key) {
			return (bool)$handler->delete($this->warpKey($key));
		});
	}

	public function setMultiple($values, $ttl = null) {
		return $this->call(function (HandlerAbstract $handler) use ($values, $ttl) {
			$values = (array)$values;
			$cacheValues = [];
			foreach ($values as $key => $value) {
				$cacheValues[$this->warpKey($key)] = $handler->pack($value);
			}
			return $handler->setMultiple($cacheValues, $ttl);
		});
	}

	public function getMultiple($keys, $default = null) {
		return $this->call(function (HandlerAbstract $handler) use ($keys, $default) {
			$keys = (array)$keys;
			$cacheKeys = [];
			foreach ($keys as $key) {
				$cacheKeys[] = $this->warpKey($key);
			}
			$mgetResult = $handler->getMultiple($cacheKeys, $default);
			if ($mgetResult === false) {
				return $default;
			}
			$result = [];
			foreach ($mgetResult ?? [] as $key => $value) {
				$result[$keys[$key]] = $handler->unpack($value);
			}

			return $result;
		});
	}

	public function deleteMultiple($keys): bool {
		return (bool)$this->call(function (HandlerAbstract $handler) use ($keys) {
			$keys = (array)$keys;
			foreach ($keys as &$key) {
				$key = $this->warpKey($key);
			}
			return (bool)$handler->deleteMultiple($keys);
		});
	}

	public function has($key) {
		return (bool)$this->call(function (HandlerAbstract $handler) use ($key) {
			return (bool)$handler->has($this->warpKey($key));
		});
	}

	public function clear() {
		return (bool)$this->call(function (HandlerAbstract $handler) {
			return (bool)$handler->clear();
		});
	}

	public function __call($method, $arguments) {
		return $this->call(function (HandlerAbstract $handler) use ($method, $arguments) {
			return $handler->$method(...$arguments);
		});
	}

	public function call(\Closure $method) {
		$connection = $this->getConnection();

		try {
			$result = $method($connection);
		} catch (\Throwable $e) {
			$result = $this->tryAgainIfCausedByLostConnection($e, $method, $connection, [$this, 'call']);
		}

		return $result;
	}
}
