<?php

namespace App\Spider;

use App\Model\img\CosplayImgBean;
use App\Model\img\CosplayImgModel;
use App\Model\img\ImgBean;
use App\Model\img\ImgModel;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\MysqliPool\Connection;
use EasySwoole\MysqliPool\Mysql;
use EasySwoole\Utility\File;
use QL\QueryList;

/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-16
 * Time: 上午9:53
 */
class Event
{

    static function consume($data, $html)
    {
        //获得一个queryList对象，并且防止报错
        libxml_use_internal_errors(true);
        if ($data['type'] == 1) {
            //消费类型为1，则代表还不是下载图片，需要进行二次消费
            //查询下一页链接，用于继续爬取数据
            @$ql = QueryList::html($html);
            $nextLink = $ql->find('.pagelist .n')->attr('href');
            $nextLink = HttpClientLogic::getTrueUrl($data['url'], $nextLink);
            if ($nextLink) {
                RedisLogic::addConsume([
                    'type' => 1,//消费标识
                    'url'  => $nextLink,
                ]);
            }
            $imgList = $ql->find('.content_nr img')->attrs('src')->all();
//            var_dump($img);
            $title = $ql->find('.content_w_box h1')->html();
            foreach ($imgList as $img) {
                Mysql::invoker('mysql', function (Connection $db) use ($img, $title) {
                    $urlInfo = parse_url($img);
                    $filePath = '/media/tioncico/2T硬盘资源文件/网站爬虫相关/cosplay/' . $urlInfo['host'] . $urlInfo['path'];
                    $model = new CosplayImgModel($db);
                    $bean = new CosplayImgBean();
                    $bean->setSourceUrl('http://moe.005.tv/');
                    $bean->setFilePath($urlInfo['host'] . $urlInfo['path']);
                    $bean->setImgName($title);
                    $bean->setImgTitle($title);
                    $result = $model->add($bean);
                    if ($result === false) {
                        Logger::getInstance()->console('插入失败', Logger::LOG_LEVEL_WARNING);
                    }

                    RedisLogic::addConsume([
                        'type' => 2,//消费标识
                        'url'  => $img,
                    ]);
                });

            }
        } elseif ($data['type'] == 2) {
            $urlInfo = parse_url($data['url']);
            $filePath = '/media/tioncico/2T硬盘资源文件/网站爬虫相关/cosplay/' . $urlInfo['host'] . $urlInfo['path'];
//            var_dump($filePath);
            File::createFile($filePath, $html);

//            var_dump($data['url']);
        }

    }

    static function produce($data, $html)
    {
        //获得一个queryList对象，并且防止报错
        libxml_use_internal_errors(true);
        @$ql = QueryList::html($html);

        //查询下一页链接，用于继续爬取数据
        $nextLink = $ql->find('.pagelist .n')->attr('href');
        //由于爬取到的是相对路径，我们需要增加完整的路径
        $nextLink = HttpClientLogic::getTrueUrl($data['url'], $nextLink);

        RedisLogic::addProduce($nextLink);

        //查询所有需要爬取的数据，用于爬取里面的图片
        $imgConsumeList = $ql->find('.zhuti_w_list li a')->attrs('href')->all();
        foreach ($imgConsumeList as $value) {
            RedisLogic::addConsume([
                'type' => 1,//消费标识
                'url'  => $value,
            ]);
        }
        return true;
    }
}