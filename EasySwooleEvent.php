<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;


use App\Process\ImgDown;
use App\Process\ImgItem;
use App\Process\Page;
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
        $a->setMaxObjectNum(50);
        $mysqlConfig = new \EasySwoole\Mysqli\Config(Config::getInstance()->getConf('MYSQL'));
        \EasySwoole\MysqliPool\Mysql::getInstance()->register('mysql', $mysqlConfig);
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $arg = [
            'listKey'         => 'page',
            'maxCoroutineNum' => 3,
            'clear'           => Page::CLEAR_NO
        ];

        $page = new Page('page', $arg, false, \EasySwoole\Component\Process\Config::PIPE_TYPE_SOCK_DGRAM, true);
        ServerManager::getInstance()->getSwooleServer()->addProcess($page->getProcess());


        $arg = [
            'listKey'         => 'imgItem',
            'maxCoroutineNum' => 3,
            'clear'           => Page::CLEAR_NO
        ];
        $imgItem = new ImgItem('imgItem', $arg, false, \EasySwoole\Component\Process\Config::PIPE_TYPE_SOCK_DGRAM, true);
        ServerManager::getInstance()->getSwooleServer()->addProcess($imgItem->getProcess());

        $arg = [
            'listKey'         => 'imgDown',
            'maxCoroutineNum' => 3,
            'clear'           => Page::CLEAR_NO
        ];
        $imgItem = new ImgDown('imgDown', $arg, false, \EasySwoole\Component\Process\Config::PIPE_TYPE_SOCK_DGRAM, true);
        ServerManager::getInstance()->getSwooleServer()->addProcess($imgItem->getProcess());

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