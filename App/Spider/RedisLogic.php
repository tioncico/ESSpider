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

    static function addProduce($data)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($data) {
            $produceListKey = 'easyswooleProduceList';
            if (is_string($data)) {
                $data = [
                    'url' => $data,
                ];
            }
            if (self::checkProduceMap($data['url'])) {
                return true;
            }
            self::addProduceMap($data['url']);
            $result = $redis->rPush($produceListKey, $data);
            return $result;
        },0);
    }

    static function checkProduceMap($url)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($url) {
            $produceUrlMapKey = 'produceUrlMapKey';
            $result = $redis->hGet($produceUrlMapKey, $url);
            return (bool)$result;
        },0);
    }

    static function addProduceMap($url,$state=1)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($url,$state) {
            $produceUrlMapKey = 'produceUrlMapKey';
            $result = $redis->hset($produceUrlMapKey, $url, $state);
            return (bool)$result;
        },0);
    }

    static function addConsume($data)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($data) {
            $consumeListKey = 'easyswooleConsumeList';
            if (is_string($data)) {
                $data = [
                    'url' => $data,
                ];
            }
            if (self::checkConsumeMap($data['url'])) {
                return true;
            }
            self::addConsumeMap($data['url']);
            $result = $redis->rPush($consumeListKey, $data);
            return $result;
        },0);
    }

    static function checkConsumeMap($url)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($url) {
            $consumeUrlMapKey = 'consumeUrlMapKey';
            $result = $redis->hGet($consumeUrlMapKey, $url);
            return (bool)$result;
        },0);
    }

    static function addConsumeMap($url,$state=1)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($url,$state) {
            $consumeUrlMapKey = 'consumeUrlMapKey';
            $result = $redis->hset($consumeUrlMapKey, $url, $state);
            return (bool)$result;
        },0);
    }

    static function clearConsumeMap()
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) {
            $consumeUrlMapKey = 'consumeUrlMapKey';
            $result = $redis->delete($consumeUrlMapKey);
            return (bool)$result;
        },0);
    }

    static function clearProduceMap()
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) {
            $consumeUrlMapKey = 'produceUrlMapKey';
            $result = $redis->delete($consumeUrlMapKey);
            return (bool)$result;
        },0);
    }

    static function clearConsumeList()
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) {
            $consumeUrlMapKey = 'easyswooleConsumeList';
            $result = $redis->delete($consumeUrlMapKey);
            return (bool)$result;
        },0);
    }

    static function clearProduceList()
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) {
            $consumeUrlMapKey = 'easyswooleProduceList';
            $result = $redis->delete($consumeUrlMapKey);
            return (bool)$result;
        },0);
    }

}