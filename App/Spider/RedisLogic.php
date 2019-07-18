<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-16
 * Time: 下午4:51
 */

namespace App\Spider;


use EasySwoole\RedisPool\Redis;

class RedisLogic
{
    static $redisName = 'redis';

    static function add($key, $data)
    {
        return Redis::invoker(self::$redisName, function (\Swoole\Coroutine\Redis $redis) use ($key, $data) {
            ;
            if (is_string($data)) {
                $data = [
                    'url' => $data,
                ];
            }
            if (self::checkMap($key, $data['url'])) {
                return true;
            }
            self::addMap($key, $data['url']);
            $result = $redis->rPush($key, $data);
            return $result;
        }, 0);
    }

    static function checkMap($key, $url)
    {
        return Redis::invoker(self::$redisName, function (\Swoole\Coroutine\Redis $redis) use ($key, $url) {
            $key = $key . 'Map';
            $result = $redis->hGet($key, $url);
            return (bool)$result;
        }, 0);
    }

    static function addMap($key, $url, $state = 1)
    {
        return Redis::invoker(self::$redisName, function (\Swoole\Coroutine\Redis $redis) use ($key, $url, $state) {
            $key = $key . 'Map';
            $result = $redis->hset($key, $url, $state);
            return (bool)$result;
        }, 0);
    }

    static function clear($key){
        return Redis::invoker(self::$redisName, function (\Swoole\Coroutine\Redis $redis) use ($key) {
            $result = $redis->delete($key);
            return (bool)$result;
        }, 0);
    }
    static function clearMap($key){
        return Redis::invoker(self::$redisName, function (\Swoole\Coroutine\Redis $redis) use ($key) {
            $key = $key . 'Map';
            $result = $redis->delete($key);
            return (bool)$result;
        }, 0);
    }
}