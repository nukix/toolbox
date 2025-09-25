<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class locimg implements api
{
    public function upload($filepath, $filename){
        $url = 'https://yunimg.cc/upload/upload.html';
        $referer = 'https://yunimg.cc/';
        $file = new \CURLFile($filepath, 'image/jpeg', $filename);
        $param = [
            'image' => $file,
            'fileId' => $filename,
        ];
        $data = $this->curl($url,$param,$referer,['X-Requested-With: XMLHttpRequest']);
        $arr = json_decode($data,true);
        if(isset($arr['data']['url'])){
            return ['url'=>$arr['data']['url']];
        }elseif(isset($arr['msg'])){
            throw new Exception('上传失败请重试（'.$arr['msg'].'）');
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }

    private function curl($url, $post=0, $referer=0, $addheader=0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        /*curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1');
        curl_setopt($ch, CURLOPT_PROXYPORT, 10809);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);*/
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        if($addheader){
            $httpheader = array_merge($httpheader, $addheader);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if($referer){
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36");
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}