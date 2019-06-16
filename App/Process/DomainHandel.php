<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/6/14 0014
 * Time: 15:34
 */

namespace App\Process;


use App\Model\Domain\DomainBean;
use App\Model\Domain\DomainModel;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\Mysqli\Mysqli;
use EasySwoole\MysqliPool\Mysql;
use EasySwoole\RedisPool\Redis;

class DomainHandel extends AbstractProcess
{
    protected function run($arg)
    {
        // TODO: Implement run() method.

        Timer::getInstance()->loop(1 * 1000, function () {
            Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) {
                while (1) {
                    $domainListKey = 'domainList';
                    $data = $redis->lPop($domainListKey);
                    if (empty($data)) {
                        Logger::getInstance()->console('暂无域名队列');
                        sleep(1);
                        break;
                    }
                    $urlInfo = parse_url($data);
                    $hostArr = explode('.', $urlInfo['host']);
                    $domain = ($hostArr[count($hostArr) - 2]) . ($hostArr[count($hostArr) - 1]);
                    $this->handelUrl($domain);
                }
            });
        });
    }

    protected function handelUrl($url)
    {
        $domain = 'www' . $url;
        $this->addDomain($url, $domain);

        $domain = 'ww' . $url;
        $this->addDomain($url, $domain);

        $domain = 'a' . $url;
        $this->addDomain($url, $domain);

    }

    protected function checkDomain($domain)
    {
        $httpClient = new HttpClient("http://apidata.chinaz.com/CallAPI/Whois?key=93b828b408db4f6db8972c80d0934cf4&domainName={$domain}");
        $response = $httpClient->getRequest();
        $response = json_decode($response->getBody(), 1);
        Logger::getInstance()->log($domain . ":" . json_encode($response), Logger::LOG_LEVEL_INFO, 'domain');
        if ($response['StateCode'] != 1) {
            return false;
        } else {
            return true;
        }
    }

    protected function addDomain($url, $domain)
    {
        return Mysql::invoker('mysql', function (Mysqli $db) use ($url, $domain) {
            $model = new DomainModel($db);
            if ($model->getOneByDomain($domain)) {
                Logger::getInstance()->console("域名已存在");
                return true;
            }
            $bean = new DomainBean();
            $bean->setUrl($url);
            $bean->setDomain($domain);
            $bean->setState((int)$this->checkDomain($domain));
            $result  =  $model->add($bean);
            return $result;
        });
    }


}