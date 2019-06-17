<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-15
 * Time: 上午10:43
 */

namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\RedisPool\Redis;

class Index extends Controller
{
    function index()
    {
        $redis = Redis::defer('redis');
        $redisListKey = "web_page_list";
        $url =  $this->request()->getRequestParam('url');
        $matchValue =  $this->request()->getRequestParam('matchValue');

        $redis->rPush($redisListKey,json_encode([
            'url'    => $url,
            'depth'  => 1,
            'matchValue' => $matchValue,
        ]));
        $this->response()->write('success');
        // TODO: Implement index() method.
    }

    /**
     * clear
     * @author tioncico
     * Time: 上午10:54
     */
    function clear(){
        $redis = Redis::defer('redis');
        $redisListKey = "web_page_list";
        $redis->delete($redisListKey);
        $redisMapKey = "web_page_map";
        $redis->delete($redisMapKey);
        $this->response()->write('clear success');
    }

}