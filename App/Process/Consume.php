<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-16
 * Time: 上午9:44
 */

namespace App\Process;


use App\Spider\Event;
use App\Spider\HttpClientLogic;
use App\Spider\RedisLogic;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\RedisPool\Redis;
use Swoole\Coroutine;

class Consume extends AbstractProcess
{
    protected function run($arg)
    {
        $consumeListKey = 'easyswooleConsumeList';
        $maxDepth = $arg['maxDepth'];
        $maxCoroutineNum = $arg['maxCoroutineNum'] ?? 3;
        for ($i = 0; $i <= $maxCoroutineNum; $i++) {
            go(function () use ($consumeListKey) {
                Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($consumeListKey) {
                    while (1) {
                        $data = $redis->lpop($consumeListKey);
                        if (empty($data)) {
                            Logger::getInstance()->console('暂无消费队列');
                            Coroutine::sleep(5);
                            continue;
                        }
                        $response = HttpClientLogic::getHtmlContent($data['url']);
                        if ($response->getStatusCode() == '200') {
                            Event::consume($data, $response->getBody());
                            Logger::getInstance()->console("{$data['url']} 消费已完成");
                        } else {
//                        var_dump($response);
                            Logger::getInstance()->console("消费发生错误：{$data['url']}  code:" . $response->getStatusCode(), Logger::LOG_LEVEL_WARNING);
                            RedisLogic::addConsumeMap($data['url'],0);
                            RedisLogic::addConsume($data);
                        }
//                Coroutine::sleep(0.1);
                    }
                });
            });
        }
        // TODO: Implement run() method.
    }
}