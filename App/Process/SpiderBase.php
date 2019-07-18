<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-7-18
 * Time: 下午9:08
 */

namespace App\Process;


use App\Spider\RedisLogic;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\RedisPool\Redis;
use Swoole\Coroutine;

abstract class SpiderBase extends AbstractProcess
{
    protected $listKey;
    protected $maxCoroutineNum = 3;
    protected $isConsoleLog = true;
    protected $tryNum = 5;

    const CLEAR_NO = 0;
    const CLEAR_KEY = 1;
    const CLEAR_MAP = 2;

    protected function run($arg)
    {
        $this->listKey = $arg['listKey'];
        $this->maxCoroutineNum = $arg['maxCoroutineNum'] ?? 3;
        $this->isConsoleLog = $arg['console'] ?? true;
        $clear = $arg['clear'] ?? self::CLEAR_NO;
        $this->clear($clear);
        $this->clearMap($clear);
        for ($i = 1; $i <= $this->maxCoroutineNum; $i++) {
            go(function () {
                Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) {
                    while (1) {
                        $data = $redis->lpop($this->listKey);
                        if (empty($data)) {
                            $this->console($this->getProcessName() . "暂无队列");
                            Coroutine::sleep(5);
                            continue;
                        }
                        $this->console($this->getProcessName() . "正在处理{$data['url']}");
                        $result = $this->beforeHandel($data);
                        if ($result === false) {
                            $this->console($this->getProcessName() . "跳过该任务");
                            continue;
                        }
                        $result = $this->handelProcess($data);
                        $this->afterHandel($result, $data);
                        Coroutine::sleep(0.5);
                    }
                });
            });
        }
    }

    protected function console($msg, $level = Logger::LOG_LEVEL_INFO)
    {
        if ($this->isConsoleLog) {
            Logger::getInstance()->console($msg, $level, $this->getProcessName());
        } else {
            Logger::getInstance()->Log($msg, $level, $this->getProcessName());
        }
    }

    /**
     * 处理任务的逻辑
     * handelProcess
     * @return mixed
     * @author tioncico
     * Time: 下午9:18
     */
    abstract function handelProcess($data);

    abstract function beforeHandel($data);

    abstract function afterHandel($result, $data);

    protected function tryAgain($data)
    {
        if (isset($data['errorNum'])) {
            $data['errorNum'] = 1;
        } else {
            $data['errorNum'] = 1;
        }
        if ($data['errorNum'] <= $this->tryNum) {
            RedisLogic::addMap($this->listKey, $data['url'], 0);
            RedisLogic::add($this->listKey, $data);
        }
    }

    function clear($clear)
    {
        if ($clear == ($clear | self::CLEAR_KEY)) {
            RedisLogic::clear($this->listKey);
        }
        return $this;
    }

    function clearMap($clear)
    {
        if ($clear == ($clear | self::CLEAR_MAP)) {
            RedisLogic::clearMap($this->listKey);
        }
        return $this;
    }


}