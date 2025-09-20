<?php

namespace plugin\utility\videoparse\api;

use Exception;
use plugin\utility\videoparse\api;

class pipixia implements api
{
    public function parse($url){
        $url = 'https://api.makuo.cc/api/get.video.pipixia?url='.urlencode($url);
        $header = ['Authorization: '.config_get('yapi_token')];
        $data = get_curl($url, 0, 0, 0, 0, 0, 0, $header);
        $arr = json_decode($data, true);
        if(isset($arr['code']) && $arr['code']==200){
            if(isset($arr['data']['url'])){
                return [
                    'title' => $arr['data']['title'],
                    'author' => $arr['data']['author'],
                    'avatar' => $arr['data']['avatar'],
                    'cover' => $arr['data']['cover'],
                    'url' => $arr['data']['url'],
                ];
            }elseif(isset($arr['data']['imgurl'])){
                return [
                    'title' => $arr['data']['title'],
                    'author' => $arr['data']['author'],
                    'avatar' => $arr['data']['avatar'],
                    'cover' => $arr['data']['cover'],
                    'images' => $arr['data']['imgurl'],
                ];
            }else{
                throw new Exception('解析url返回异常');
            }
        }elseif(isset($arr['msg'])){
            throw new Exception('视频解析失败,'.$arr['msg']);
        }else{
            throw new Exception('视频解析失败');
        }
    }
}