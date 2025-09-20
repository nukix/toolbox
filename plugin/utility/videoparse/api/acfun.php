<?php

namespace plugin\utility\videoparse\api;

use Exception;
use plugin\utility\videoparse\api;

class acfun implements api
{
    public function parse($url){
        if(preg_match('!/ac(\d+)!', $url, $match)){ //UGC视频
            $id = $match[1];

            $requrl = 'https://www.acfun.cn/player/ac' . $id;
            $res = get_curl($requrl);
            if(preg_match('/window.videoInfo = (.*?);\n/', $res, $match)){
                //echo $match[1];exit;
                $arr = json_decode(trim($match[1]), true);
                $videoinfo = json_decode($arr['currentVideoInfo']['ksPlayJson'], true);
                if($videoinfo){
                    $video_list = $videoinfo['adaptationSet'][0]['representation'];
                    if(empty($video_list))throw new Exception('视频列表解析失败');
                    $url = $video_list[0]['url'];
                    return [
                        'title' => $arr['title'],
                        'cover' => $arr['coverUrl'],
                        'url' => $url,
                        'time' => date('Y-m-d H:i:s', $arr['createTimeMillis']/1000),
                        'like' => $arr['likeCount'],
                        'author' => $arr['user']['name'],
                        'avatar' => $arr['user']['headUrl'],
                    ];
                }else{
                    throw new Exception('视频信息解析失败');
                }
            }else{
                throw new Exception('视频页面解析失败');
            }
        }elseif(preg_match('!/aa([0-9\_]+)!', $url, $match)){ //番剧
            $id = $match[1];

            $requrl = 'https://www.acfun.cn/bangumi/aa' . $id;
            $res = get_curl($requrl);
            if(preg_match('/window.bangumiData = (.*?);\n/', $res, $match)){
                //echo $match[1];exit;
                $arr = json_decode(trim($match[1]), true);
                $videoinfo = json_decode($arr['currentVideoInfo']['ksPlayJson'], true);
                if($videoinfo){
                    $video_list = $videoinfo['adaptationSet'][0]['representation'];
                    if(empty($video_list))throw new Exception('视频列表解析失败');
                    $url = $video_list[0]['url'];
                    return [
                        'title' => $arr['showTitle'],
                        'cover' => $arr['bangumiCoverImageH'],
                        'url' => $url,
                        'time' => date('Y-m-d H:i:s', $arr['onlineTime']/1000),
                        'like' => $arr['bangumiLikeCount'],
                        'author' => $arr['bangumiTitle'],
                    ];
                }else{
                    throw new Exception('视频信息解析失败');
                }
            }else{
                throw new Exception('视频页面解析失败');
            }
        }else{
            throw new Exception('视频id获取失败');
        }
    }
}