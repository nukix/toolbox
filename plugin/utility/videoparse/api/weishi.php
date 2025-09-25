<?php

namespace plugin\utility\videoparse\api;

use Exception;
use plugin\utility\videoparse\api;

class weishi implements api
{
    public function parse($url){
        if(strpos($url,'feed/')){
			$id = getSubstr($url,'feed/','/');
		}else{
			$id = getSubstr($url,'id=','&');
		}
        if(!$id) throw new Exception('视频id获取失败');

        $requrl = 'https://h5.weishi.qq.com/webapp/json/weishi/WSH5GetPlayPage?feedid=' . $id;
        $res = get_curl($requrl);
        $arr = json_decode($res, true);

        if(isset($arr['ret']) && $arr['ret'] == 0){
            if(isset($arr['data']['feeds'][0]['video_url'])){
                return [
                    'author' => $arr['data']['feeds'][0]['poster']['nick'], 
                    'avatar' => $arr['data']['feeds'][0]['poster']['avatar'], 
                    'time' => date('Y-m-d H:i:s', $arr['data']['feeds'][0]['poster']['createtime']), 
                    'title' => $arr['data']['feeds'][0]['feed_desc_withat'], 
                    'cover' => $arr['data']['feeds'][0]['images'][0]['url'], 
                    'url' => $arr['data']['feeds'][0]['video_url']
                ];
            }else{
                throw new Exception('视频解析失败(地址获取失败)');
            }
        }elseif(isset($arr['msg'])){
            throw new Exception('视频解析失败('.$arr['msg'].')');
        }else{
            throw new Exception('视频解析失败');
        }
    }
}