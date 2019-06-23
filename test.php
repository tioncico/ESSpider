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
    \App\Spider\RedisLogic::clearConsumeMap();//清除消费map
    \App\Spider\RedisLogic::clearProduceMap();//清除生产map
    \App\Spider\RedisLogic::clearConsumeList();//清除消费队列
    \App\Spider\RedisLogic::clearProduceList();//清除生产队列
    //新增默认队列
    \App\Spider\RedisLogic::addProduce('http://moe.005.tv/cosplay/');
});
//go(function (){
//    $init = new \AutomaticGeneration\Init();
//    $init->initBaseModel();
//    $init->initBaseController();
//    $db = \EasySwoole\MysqliPool\Mysql::defer('mysql');
//
//    $mysqlTable = new \AutomaticGeneration\MysqlTable($db, \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.database'));
//    $tableName = 'cosplay_img_list';
//    $tableColumns = $mysqlTable->getColumnList($tableName);
//    $tableComment = $mysqlTable->getComment($tableName);
//    $path = 'img';
//
//    $beanConfig = new \AutomaticGeneration\Config\BeanConfig();
//    $beanConfig->setBaseNamespace("App\\Model\\".$path);
////    $beanConfig->setBaseDirectory(EASYSWOOLE_ROOT . '/' .\AutomaticGeneration\AppLogic::getAppPath() . 'Model');
//    $beanConfig->setTablePre('');
//    $beanConfig->setTableName('cosplay_img_list');
//    $beanConfig->setTableComment($tableComment);
//    $beanConfig->setTableColumns($tableColumns);
//    $beanBuilder = new \AutomaticGeneration\BeanBuilder($beanConfig);
//    $result = $beanBuilder->generateBean();
//    $path = 'img';
//    $modelConfig = new \AutomaticGeneration\Config\ModelConfig();
//    $modelConfig->setBaseNamespace("App\\Model\\".$path);
////    $modelConfig->setBaseDirectory(EASYSWOOLE_ROOT . '/' .\AutomaticGeneration\AppLogic::getAppPath() . 'Model');
//    $modelConfig->setTablePre("");
//    $modelConfig->setExtendClass(\App\Model\BaseModel::class);
//    $modelConfig->setTableName("cosplay_img_list");
//    $modelConfig->setTableComment($tableComment);
//    $modelConfig->setTableColumns($tableColumns);
//    $modelBuilder = new \AutomaticGeneration\ModelBuilder($modelConfig);
//    $result = $modelBuilder->generateModel();
//    var_dump($result);
//
//});