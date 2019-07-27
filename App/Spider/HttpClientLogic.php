<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-16
 * Time: 下午4:55
 */

namespace App\Spider;

use EasySwoole\EasySwoole\Logger;
use EasySwoole\HttpClient\Bean\Response;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\RedisPool\Redis;

class HttpClientLogic
{
    static function getHtmlContent($url, $i = 1): ?Response
    {
        if ($i >= 10) {
            Logger::getInstance()->console('302跳转次数太多,已终止', Logger::LOG_LEVEL_WARNING);
            return false;
        }
        $httpClient = new HttpClient($url);
        $cookie = self::getDomainCookie(self::getUrlDomain($url));
        if ($cookie) {
            $httpClient->getClient()->setCookies($cookie);
        }
        $response = $httpClient->get([
            'referer' => $url
        ]);
//        if ($response->getStatusCode() == '302') {
//            return self::getHtmlContent(self::getTrueUrl($url, $response->getHeaders()['location']), $i + 1);
//        }
        self::setDomainCookie(self::getUrlDomain($url), $response->getCookies());
        return $response;
    }

    static function getUrlDomain($url)
    {
        $urlInfo = parse_url($url);
        return $urlInfo['host'];
    }

    static function getDomainCookie($domain)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($domain) {
            $cookeListKey = 'cookieList';
            $cookie = $redis->hget($cookeListKey, $domain);
            return $cookie;
        });
    }

    static function setDomainCookie($domain, $cookie)
    {
        return Redis::invoker('redis', function (\Swoole\Coroutine\Redis $redis) use ($domain, $cookie) {
            $cookeListKey = 'cookieList';
            $result = $redis->hSet($cookeListKey, $domain, $cookie);
            return $result;
        });
    }

    static function getTrueUrl($preUrl, $link)
    {
        //分析来源网站 path
        $urlInfo = parse_url($preUrl);
        $linkUrlInfo = parse_url($link);
        //目标url没有scheme的情况
        if (!isset($linkUrlInfo['scheme'])) {
            //没有域名,使用的绝对路径和相对路径
            //绝对路径
            if (substr($link, 0, 1) == '/') {
                $linkUrlInfo['scheme'] = $urlInfo['scheme'];
                $linkUrlInfo['host'] = $urlInfo['host'];
            } elseif (substr($link, 0, 2) == './') {
                $linkUrlInfo['scheme'] = $urlInfo['scheme'];
                $linkUrlInfo['host'] = $urlInfo['host'];
                if (substr($urlInfo['path'],-1,1)=='/'){
                    $urlInfo['path'] = substr($urlInfo['path'],0,strlen($urlInfo['path'])-1);
                }
                $linkUrlInfo['path'] = $urlInfo['path'] . str_replace('./','/',$linkUrlInfo['path']);
            }
            //有域名,没有http://,会显示在path中
            $linkUrlPathArr = explode('/', $linkUrlInfo['path']);
            //域名显示在path中的情况
            if (strpos($linkUrlPathArr[0], '.') != -1) {
                $linkUrlInfo['scheme'] = $urlInfo['scheme'];
            }else{
                //没有域名的情况
                $linkUrlInfo['scheme'] = $urlInfo['scheme'];
                $linkUrlInfo['host'] = $urlInfo['host'];
            }
        }
        return self::unParseUrl($linkUrlInfo);
    }

   static function unParseUrl($parsedUrl)
    {
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}