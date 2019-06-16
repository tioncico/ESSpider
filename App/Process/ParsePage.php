<?php

namespace App\Process;

use App\Model\WebPage\WebPageBean;
use App\Model\WebPage\WebPageModel;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\Mysqli\Mysqli;
use EasySwoole\MysqliPool\Mysql;
use EasySwoole\RedisPool\Redis;
use EasySwoole\Utility\File;
use QL\QueryList;

/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/6/13 0013
 * Time: 9:48
 */
class ParsePage extends AbstractProcess
{
    protected function run($arg)
    {
        $maxDepth = $arg['maxDepth'];
        Timer::getInstance()->loop(1 * 1000, function () use ($maxDepth) {
            Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($maxDepth) {
                while (1) {
                    $redisListKey = "web_page_list";
                    $redisMapKey = "web_page_map";
                    $data = $redis->lPop($redisListKey);
                    $data = json_decode($data, 1);
                    if (empty($data)) {
                        Logger::getInstance()->console('暂无队列');
                        sleep(1);
                        break;
                    }
                    $urlInfo = $this->handelUrl($data['url']);
                    $query = !empty($urlInfo['query']) ? ('?' . $urlInfo['query']) : '';
                    $webUrl = $urlInfo['scheme'] . "://" . ($urlInfo['host'] ?? '') . ($urlInfo['path'] ?? '') . $query;
                    $matchValue = $data['matchValue'];

                    try {
                        if ($redis->hExists($redisMapKey, $data['url'])) {
                            continue;
                        }
                        $redis->hSet($redisMapKey, $data['url'], 0);
                        if (empty($data['url'])) {
                            continue;
                        }
//                if ($this->checkUrlExist($data['url'])==true){
//                    continue;
//                }
                        Logger::getInstance()->console("{$data['url']} 页面正在处理.");
                        //获取内容

                        $response = $this->handelHtmlContent($data['url']);
                        var_dump($response);
                        $flag = 0;

                        if ($response->getStatusCode() == '200') {
                            //写入文件
//                            $this->writeFile($webUrl, $data['url'], $maxDepth, $response->getBody());
                            //查询匹配
                            $flag = $this->matchHtml($matchValue, $response->getBody());
                            if ($flag == true) {
                                $domainListKey = 'domainList';
                                $redis->rPush($domainListKey, $data['url']);
                            }

                        } elseif ($response->getStatusCode() == '-1') {
                            $redis->rPush($redisListKey, $data['url']);
                        }
                        $redis->hSet($redisMapKey, $data['url'], 1);

                        //插入数据
                        $this->saveMysql(null, $webUrl, $data['url'], $data['depth'], $matchValue, $flag, 1, $response->getStatusCode());

                        //分析链接
                        if ($response->getStatusCode() == '200' && $data['depth'] < $maxDepth) {
                            $linkList = $this->handelHtmlLink($response->getBody());
                            $this->addQueue($webUrl, $linkList, $data['preUrl'] ?? $webUrl, $data['depth'], $matchValue, $redisListKey);
                        }
                    } catch (\Throwable $throwable) {
                        $redis->rPush($redisListKey, $data['url']);
                        Logger::getInstance()->console($throwable->getMessage(), Logger::LOG_LEVEL_WARNING);
                    }
                }
            }, 0);
        });
        // TODO: Implement run() method.
    }

    protected function addQueue($webUrl, $linkList, $preUrl, $depth, $matchValue, $redisListKey)
    {
        Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($webUrl, $linkList, $preUrl, $depth, $matchValue, $redisListKey) {
            $redisMapKey = "{$webUrl}_map";
            $webUrlInfo = $this->handelUrl($webUrl);
            $preUrlInfo = parse_url($preUrl);
            $i = 0;
            foreach ($linkList as $link) {
                $url = urldecode($link);
                if (substr($link, 0, 1) == '/') {
                    $url = $preUrlInfo['scheme'] . '://' . $preUrlInfo['host'] . $link;
                } elseif (substr($link, 0, 2) == './') {
                    $url = $preUrlInfo['scheme'] . '://' . $preUrlInfo['host'] . $link;
                } elseif (substr($link, 0, 4) != 'http') {
                    $url = "http://" . $link;
                }
//                if (strpos($url, $webUrlInfo['host']) === false) {
//                    continue;
//                }
//                if ($this->checkUrl($url) === false) {
//                    continue;
//                }
                if ($redis->hExists($redisMapKey, $url) == 1) {
                    continue;
                }
                $redis->rPush($redisListKey, json_encode([
                    'url'        => $url,
                    'depth'      => $depth + 1,
                    'preUrl'     => $preUrl,
                    'matchValue' => $matchValue,
                ]));
                $i++;
            }
            Logger::getInstance()->console($i . "条链接入列成功.");
        }, 0);
    }

    protected function checkUrlExist($url)
    {
        return Mysql::invoker('mysql', function (Mysqli $db) use ($url) {
            $model = new WebPageModel($db);
            $data = $model->getOneByPageUrl($url);
            if ($data) {
                return true;
            } else {
                return false;
            }
        }, 0);
    }

    protected function handelHtmlContent($url, $i = 1)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($url, $i) {
            if ($i >= 10) {
                Logger::getInstance()->console('302跳转次数太多,已终止', Logger::LOG_LEVEL_WARNING);
                return false;
            }
            $httpClient = new HttpClient($url);
            $urlInfo = parse_url($url);
            $cookeListKey = 'cookieList';
            $cookeKey = $urlInfo['host'];
            $cookie = json_decode($redis->hget($cookeListKey, $cookeKey), 1);
            if ($cookie) {
                $httpClient->getClient()->setCookies($cookie);
            }
            $response = $httpClient->getRequest([

            ]);
            if ($response->getStatusCode() == '302') {
                $urlInfo = parse_url($url);
                $link = $response->getHeaders()['location'];
                if (substr($link, 0, 1) == '/') {
                    $url = $urlInfo['scheme'] . '://' . $urlInfo['host'] . $link;
                } elseif (substr($link, 0, 2) == './') {
                    $url = $urlInfo['scheme'] . '://' . $urlInfo['host'] . $link;
                }
                return $this->handelHtmlContent($url, $i + 1);
            }


            $urlInfo = parse_url($url);
            $cookeListKey = 'cookieList';
            $cookeKey = $urlInfo['host'];

            $redis->hset($cookeListKey, $cookeKey, json_encode($response->getCookies()));
            return $response;
        }, 0);

    }

    protected function handelHtmlLink($html)
    {
        libxml_use_internal_errors(true);
        @$ql = QueryList::html($html);
        $linkList = $ql->find('a')->attrs('href')->all();
//        var_dump($linkList);
        return $linkList;
    }

    protected function writeFile($webUrl, $pageUrl, $maxDepth, $html)
    {
        //分析主任务host,用于建立父文件夹
        $webUrlInfo = $this->handelUrl($webUrl);
        //分析本次链接,用于建立子文件夹以及处理文件
        $pageUrlInfo = $this->handelUrl($pageUrl);
        empty($pageUrlInfo['path']) && ($pageUrlInfo['path'] = '');
        if (strpos($pageUrlInfo['path'], '.htm') === false) {
            $fileName = '/index.html';
        } else {
            $fileName = substr($pageUrlInfo['path'], strrpos($pageUrlInfo['path'], '/'));
            $pageUrlInfo['path'] = substr($pageUrlInfo['path'], 0, strrpos($pageUrlInfo['path'], '/'));
        }
        $path = EASYSWOOLE_ROOT . "/WebPage/{$webUrlInfo['host']}_{$maxDepth}/{$pageUrlInfo['host']}" . (!empty($pageUrlInfo['path']) ? $pageUrlInfo['path'] : '');
        File::createDirectory($path);
        File::createFile($path . $fileName, $html);
        return $path;
    }

    protected function matchHtml($matchValue, $html)
    {
        if (empty($matchValue)) {
            return false;
        }
        return preg_match($matchValue, $html);
    }

    protected function saveMysql(?WebPageBean $bean, $webUrl, $pagePath, $depth = 1, $matchValue = '', $isFlag = 0, $status = 0, $responseCode = '200')
    {
        return Mysql::invoker('mysql', function (Mysqli $db) use ($bean, $webUrl, $pagePath, $depth, $matchValue, $isFlag, $status, $responseCode) {
            $model = new WebPageModel($db);
            $webPageBean = new WebPageBean();
            $webPageBean->setWebUrl($webUrl);
            $webPageBean->setDepth($depth);
            $webPageBean->setIsFlag((int)$isFlag);
            $webPageBean->setMatchValue($matchValue);
            $webPageBean->setAddTime(time());
            $webPageBean->setPagePath($pagePath);
            $webPageBean->setResponseCode($responseCode);
            $webPageBean->setStatus($status);
            if ($bean === null) {
                $result = $model->add($webPageBean);
            } else {
                $result = $model->update($bean, $webPageBean->toArray(null, $webPageBean::FILTER_NOT_NULL));
            }
            if ($result === false) {
                Logger::getInstance()->console("$webUrl,$pagePath");
            }
            $webPageBean->setId($db->getInsertId());
            return $webPageBean;
        }, 0);
    }


    protected function handelUrl($url)
    {
        $info = parse_url($url);
        if (empty($info['scheme'])) {
            $info['scheme'] = 'http';
        }
        return $info;
    }

    protected function checkUrl($url)
    {
        $str = "/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
        if (!preg_match($str, $url)) {
            return false;
        } else {
            return true;
        }
    }
}