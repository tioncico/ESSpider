<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-16
 * Time: 上午9:44
 */

namespace App\Process;


use App\Spider\HttpClientLogic;
use App\Spider\RedisLogic;
use EasySwoole\EasySwoole\Logger;
use QL\QueryList;

class ImgItem extends SpiderBase
{
    function handelProcess($data)
    {
        //这里执行任务，根据$data的数据，进行爬取，分析数据
        $response = HttpClientLogic::getHtmlContent($data['url']);
        return $response;
    }

    function beforeHandel($data)
    {

    }

    /**
     * afterHandel
     * @param $response \EasySwoole\HttpClient\Bean\Response
     * @param $data
     * @author tioncico
     * Time: 下午9:59
     */
    function afterHandel($response, $data)
    {
        //根据任务处理的情况，进行判断时候完成该任务，任务出错的话，则重新入列
        if ($response->getStatusCode() != 200) {
            $this->console("消费发生错误：{$data['url']}  code:" . $response->getStatusCode() . "  msg:" . $response->getErrMsg(), Logger::LOG_LEVEL_WARNING);
            $this->tryAgain($data);
            return;
        }
        $this->handelData($response->getBody(),$data);
    }

    /**
     * 当数据请求完毕之后，就可以进行处理了
     * handelData
     * @param $result
     * @param $data
     * @author tioncico
     * Time: 下午10:05
     */
    function handelData($html, $data)
    {
        //获得一个queryList对象，并且防止报错
        libxml_use_internal_errors(true);
        @$ql = QueryList::html($html);
        //获得本页的所有图片
        $imgList = $ql->find('.pic-content img')->attrs('src,alt')->all();
        var_dump($imgList);
        //将图片主题的链接入列到新的队列
//        foreach ($imgList as $value) {
//            RedisLogic::add('imgItem',$value);
//        }
        $ql->destruct();
    }


}