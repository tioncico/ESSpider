<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-16
 * Time: 下午4:11
 */

namespace App\Process;


use App\Spider\Event;
use App\Spider\HttpClientLogic;
use App\Spider\RedisLogic;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\RedisPool\Redis;
use Swoole\Coroutine;

class Produce extends AbstractProcess
{
    protected function run($arg)
    {
//        RedisLogic::clearConsumeMap();
//        RedisLogic::clearProduceMap();
//        RedisLogic::clearConsumeList();
//        RedisLogic::clearProduceList();
//        RedisLogic::addProduce('https://www.mzitu.com/');
        // TODO: Implement run() method.
        $produceListKey = 'easyswooleProduceList';
        $maxDepth = $arg['maxDepth'];
        $maxCoroutineNum = $arg['maxCoroutineNum'] ?? 3;
        for ($i = 0; $i <= $maxCoroutineNum; $i++) {
            go(function () use ($produceListKey, $maxDepth) {
                Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($produceListKey, $maxDepth) {
                    while (1) {
                        $data = $redis->lpop($produceListKey);
                        if (empty($data)) {
                            Logger::getInstance()->console('暂无生产队列');
                            Coroutine::sleep(5);
                            continue;
                        }
                        //获取网页内容
                        $response = HttpClientLogic::getHtmlContent($data['url']);
                        if ($response->getStatusCode() == '200') {
                            $result = Event::produce($data, $response->getBody());
                            Logger::getInstance()->console("{$data['url']} 已完成");
                        } else {
                            Logger::getInstance()->console("生产发生错误：{$data['url']}  code:" . $response->getStatusCode(), Logger::LOG_LEVEL_WARNING);
                            if (isset($data['errorNum'])) {
                                $data['errorNum'] += 1;
                            } else {
                                $data['errorNum'] = 1;
                            }
                            if ($data['errorNum'] <= 5) {
                                RedisLogic::addProduceMap($data['url'], 0);
                                RedisLogic::addProduce($data);
                            }
                        }
                        Coroutine::sleep(0.5);
                    }
                });
            });
        }
    }

}