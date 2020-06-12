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

namespace W7\Core\Facades;

use Psr\SimpleCache\CacheInterface;

/**
 * Class Cache
 * @package W7\Core\Facades
 *
 * @method static connect( $host, $port = 6379, $timeout = 0.0, $reserved = null, $retry_interval = 0 ) {}
 * @method static psetex($key, $ttl, $value) {}
 * @method static sScan($key, $iterator, $pattern = '', $count = 0) {}
 * @method static scan( &$iterator, $pattern = null, $count = 0 ) {}
 * @method static zScan($key, $iterator, $pattern = '', $count = 0) {}
 * @method static hScan($key, $iterator, $pattern = '', $count = 0) {}
 * @method static client($command, $arg = '') {}
 * @method static slowlog($command) {}
 * @method static open( $host, $port = 6379, $timeout = 0.0, $retry_interval = 0 ) {}
 * @method static pconnect( $host, $port = 6379, $timeout = 0.0, $persistent_id = null ) {}
 * @method static popen( $host, $port = 6379, $timeout = 0.0, $persistent_id = null ) {}
 * @method static close( ) {}
 * @method static setOption( $name, $value ) {}
 * @method static getOption( $name ) {}
 * @method static ping( ) {}
 * @method static setex( $key, $ttl, $value ) {}
 * @method static setnx( $key, $value ) {}
 * @method static del( $key1, $key2 = null, $key3 = null ) {}
 * @method static multi( $mode = \Redis::MULTI ) {}
 * @method static exec( ) {}
 * @method static discard( ) {}
 * @method static watch( $key ) {}
 * @method static unwatch( ) {}
 * @method static subscribe( $channels, $callback ) {}
 * @method static psubscribe( $patterns, $callback ) {}
 * @method static publish( $channel, $message ) {}
 * @method static pubsub( $keyword, $argument ) {}
 * @method static exists( $key ) {}
 * @method static incr( $key ) {}
 * @method static incrByFloat( $key, $increment ) {}
 * @method static incrBy( $key, $value ) {}
 * @method static decr( $key ) {}
 * @method static decrBy( $key, $value ) {}
 * @method static lPush( $key, $value1, $value2 = null, $valueN = null ) {}
 * @method static rPush( $key, $value1, $value2 = null, $valueN = null ) {}
 * @method static lPushx( $key, $value ) {}
 * @method static rPushx( $key, $value ) {}
 * @method static lPop( $key ) {}
 * @method static rPop( $key ) {}
 * @method static blPop( array $keys, $timeout) {}
 * @method static brPop( array $keys, $timeout ) {}
 * @method static lLen( $key ) {}
 * @method static lSize( $key ) {}
 * @method static lIndex( $key, $index ) {}
 * @method static lGet( $key, $index ) {}
 * @method static lSet( $key, $index, $value ) {}
 * @method static lRange( $key, $start, $end ) {}
 * @method static lGetRange( $key, $start, $end ) {}
 * @method static lTrim( $key, $start, $stop ) {}
 * @method static listTrim( $key, $start, $stop ) {}
 * @method static lRem( $key, $value, $count ) {}
 * @method static lRemove( $key, $value, $count ) {}
 * @method static lInsert( $key, $position, $pivot, $value ) {}
 * @method static sAdd( $key, $value1, $value2 = null, $valueN = null ) {}
 * @method static sAddArray( $key, array $values) {}
 * @method static sRem( $key, $member1, $member2 = null, $memberN = null ) {}
 * @method static sRemove( $key, $member1, $member2 = null, $memberN = null ) {}
 * @method static sMove( $srcKey, $dstKey, $member ) {}
 * @method static sIsMember( $key, $value ) {}
 * @method static sContains( $key, $value ) {}
 * @method static sCard( $key ) {}
 * @method static sPop( $key ) {}
 * @method static sRandMember( $key, $count = null ) {}
 * @method static sInter( $key1, $key2, $keyN = null ) {}
 * @method static sInterStore( $dstKey, $key1, $key2, $keyN = null ) {}
 * @method static sUnion( $key1, $key2, $keyN = null ) {}
 * @method static sUnionStore( $dstKey, $key1, $key2, $keyN = null ) {}
 * @method static sDiff( $key1, $key2, $keyN = null ) {}
 * @method static sDiffStore( $dstKey, $key1, $key2, $keyN = null ) {}
 * @method static sMembers( $key ) {}
 * @method static sGetMembers( $key ) {}
 * @method static getSet( $key, $value ) {}
 * @method static randomKey( ) {}
 * @method static select( $dbindex ) {}
 * @method static move( $key, $dbindex ) {}
 * @method static rename( $srcKey, $dstKey ) {}
 * @method static renameKey( $srcKey, $dstKey ) {}
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
 *
 * @see \W7\Core\Cache\Cache
 */
class Cache extends FacadeAbstract {
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() {
		return '';
	}

	public static function getFacadeRoot() {
		return self::channel();
	}

	public static function channel($name = 'default') : CacheInterface {
		if (!self::getContainer()->has('cache-' . $name)) {
			$name = 'default';
		}

		return self::getContainer()->get('cache-' . $name);
	}
}
