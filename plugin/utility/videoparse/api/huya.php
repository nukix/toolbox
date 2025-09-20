<?php

namespace plugin\utility\videoparse\api;

use Exception;
use plugin\utility\videoparse\api;

class huya implements api
{
    public function parse($url){
        if(!preg_match('/\/(\d+).html/', $url, $match)) throw new Exception('视频id获取失败');
        $id = $match[1];
        $requrl = 'https://liveapi.huya.com/moment/getMomentContent?videoId=' . $id;
        $res = get_curl($requrl, 0, 'https://v.huya.com/');
        $arr = json_decode($res, true);
        if(isset($arr['status']) && $arr['status'] == 200){
            $url = $arr["data"]["moment"]["videoInfo"]["definitions"][0]["url"];
            $cover = $arr["data"]["moment"]["videoInfo"]["videoCover"];
            $title = $arr["data"]["moment"]["videoInfo"]["videoTitle"];
            $avatarUrl = $arr["data"]["moment"]["videoInfo"]["avatarUrl"];
            $author = $arr["data"]["moment"]["videoInfo"]["nickName"];
            $time = date('Y-m-d H:i:s', $arr["data"]["moment"]["cTime"]);
            $like = $arr["data"]["moment"]["favorCount"];
            return [
                'title' => $title,
                'cover' => $cover,
                'url' => $url,
                'time' => $time,
                'like' => $like,
                'author' => $author,
                'avatar' => $avatarUrl
            ];
        }else{
            throw new Exception('视频解析失败');
        }
    }
}