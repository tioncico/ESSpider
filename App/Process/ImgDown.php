<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/7/21 0021
 * Time: 14:02
 */

namespace App\Process;


use EasySwoole\HttpClient\HttpClient;
use EasySwoole\Utility\File;

class ImgDown extends SpiderBase
{
    function handelProcess($data)
    {
        //获取url最后的后缀
        $pathInfo = pathinfo($data['url']);
        $path = EASYSWOOLE_ROOT . "/Down/{$data['title']}/{$data['alt']}.{$pathInfo['extension']}";
        $httpClient = new HttpClient($data['url']);
        File::createFile($path,'');
        $result = $httpClient->download($path);
        return $result;
    }

    function beforeHandel($data)
    {
    }

    function afterHandel($result, $data)
    {
        if ($result===false) {
        $this->console("下载错误");
        $this->tryAgain($data);
        return;
    }
    }


}