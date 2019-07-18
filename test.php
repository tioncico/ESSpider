<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-16
 * Time: 下午5:35
 */
include "./vendor/autoload.php";
\EasySwoole\EasySwoole\Core::getInstance()->initialize();
go(function (){
    //新增默认队列
    \App\Spider\RedisLogic::clear('page');
    \App\Spider\RedisLogic::clearMap('page');
    \App\Spider\RedisLogic::add('page','http://www.doutula.com/article/list/?page=1');
    exit;
});
