<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;


use App\Process\Consume;
use App\Process\DomainHandel;
use App\Process\ParsePage;
use App\Process\ProcessInit;
use App\Process\Produce;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\RedisPool\Redis;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');

        $redisConfig = new \EasySwoole\RedisPool\Config(Config::getInstance()->getConf('REDIS'));
        $a = Redis::getInstance()->register('redis', $redisConfig);
        $a->setMaxObjectNum(100);
        $mysqlConfig = new \EasySwoole\Mysqli\Config(Config::getInstance()->getConf('MYSQL'));
        \EasySwoole\MysqliPool\Mysql::getInstance()->register('mysql', $mysqlConfig);

    }

    public static function mainServerCreate(EventRegister $register)
    {

        $maxDepth = 3;
        $produce = new Produce('produce', ['maxDepth' => $maxDepth, 'maxCoroutineNum' => 1], false, \EasySwoole\Component\Process\Config::PIPE_TYPE_SOCK_DGRAM, true);
        ServerManager::getInstance()->getSwooleServer()->addProcess($produce->getProcess());
        ServerManager::getInstance()->getSwooleServer()->addProcess($produce->getProcess());

        $consume = new Consume('consume', ['maxDepth' => $maxDepth, 'maxCoroutineNum' => 1], false, \EasySwoole\Component\Process\Config::PIPE_TYPE_SOCK_DGRAM, true);
        ServerManager::getInstance()->getSwooleServer()->addProcess($consume->getProcess());
        ServerManager::getInstance()->getSwooleServer()->addProcess($consume->getProcess());
        ServerManager::getInstance()->getSwooleServer()->addProcess($consume->getProcess());
        ServerManager::getInstance()->getSwooleServer()->addProcess($consume->getProcess());
        ServerManager::getInstance()->getSwooleServer()->addProcess($consume->getProcess());
        // TODO: Implement mainServerCreate() method.
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}