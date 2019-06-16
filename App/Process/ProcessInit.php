<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/6/13 0013
 * Time: 9:54
 */

namespace App\Process;


use App\Model\WebPage\WebPageModel;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\MysqliPool\Mysql;
use EasySwoole\RedisPool\Redis;

class ProcessInit extends AbstractProcess
{
    protected function run($arg)
    {
        $redis = Redis::defer('redis', 0);
        $urlInfo = $this->handelUrl($arg['webUrl']);
        $query = !empty($urlInfo['query']) ? ('?' . $urlInfo['query']) : '';
        $webUrl = $urlInfo['scheme'] . "://" . ($urlInfo['host'] ?? '') . ($urlInfo['path'] ?? '') . $query;
        $redisListKey = "web_page_list";
        $redisMapKey = "web_page_map";
        if ($arg['isClear'] == true) {
            $redis->delete($redisListKey);
            $redis->delete($redisMapKey);
        }
        $redis->rPush($redisListKey,json_encode([
            'url'    => $webUrl,
            'depth'  => 1,
            'matchValue' => $arg['matchValue'],
        ]));

        Logger::getInstance()->console("$webUrl 任务正在处理...");

    }

    protected function handelUrl($url)
    {
        $info = parse_url($url);
        if (empty($info['scheme'])) {
            $info['scheme'] = 'http';
        }
        return $info;
    }
}