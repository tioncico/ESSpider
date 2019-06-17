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
        $response = $httpClient->getRequest([
            'referer'=>$url
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
        $urlInfo = parse_url($preUrl);
        if (substr($link, 0, 1) == '/') {
            $url = $urlInfo['scheme'] . '://' . $urlInfo['host'] . $link;
        } elseif (substr($link, 0, 2) == './') {
            $url = $urlInfo['scheme'] . '://' . $urlInfo['host'] . $link;
        } else {
            $url = $link;
        }

        return $url;
    }
}