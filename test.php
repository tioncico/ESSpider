<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/6/13 0013
 * Time: 9:35
 */

include "./vendor/autoload.php";
\EasySwoole\EasySwoole\Core::getInstance()->initialize();
go(function () {
    $domain = 'www.php20.cn';
    $httpClient = new \EasySwoole\HttpClient\HttpClient("http://apidata.chinaz.com/CallAPI/Whois?key=93b828b408db4f6db8972c80d0934cf4&domainName=www.php20.cn");
    $response = $httpClient->postRequest($data);
    var_dump($response->getBody());
});