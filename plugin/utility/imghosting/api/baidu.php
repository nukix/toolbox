<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class baidu implements api
{
    public function upload($filepath, $filename){
        $url = 'https://wenku.baidu.com/user/api/editorimg';
        $referer = 'https://wenku.baidu.com/';
        $file = new \CURLFile($filepath);
        $file->setPostFilename($filename);
        $param = [
            'file' => $file,
        ];
        $cookie = 'BDUSS=3plTHA0aHpRNGI3MmIxTkpmNVpWTTYtLXpVWjlaRjdRQzFsNmxQNlNufkRiWGhvSVFBQUFBJCQAAAAAAAAAAAEAAAB5WXC40tfDzsTPs8cAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMPgUGjD4FBoS';
        $data = get_curl($url,$param,$referer, $cookie);
        $arr = json_decode($data,true);
        if(isset($arr['link'])){
            $imgurl = str_replace('.cdn.bcebos.com', '.bj.bcebos.com', $arr['link']);
            return ['url'=>$imgurl];
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}