<?php

namespace plugin\utility\videoparse\api;

use Exception;
use think\helper\Str;
use plugin\utility\videoparse\api;

class xiaohongshu implements api
{
    public function parse($url){
        if(strpos($url, 'xhslink.com/')){
            $url = get_location_url($url);
            if(!$url || !strpos($url, 'xiaohongshu.com/')){
                throw new Exception('短链接解析失败');
            }
        }
        $data = get_curl($url);
        if(preg_match('/window\.__INITIAL_STATE__=(.*?)<\/script>/', $data, $matches)){
            $data = str_replace('undefined', 'null', $matches[1]);
            $arr = json_decode($data, true);
            if(isset($arr['note']['noteDetailMap']) && !empty($arr['note']['noteDetailMap'])){
                $id = array_key_first($arr['note']['noteDetailMap']);
                $info = $arr['note']['noteDetailMap'][$id]['note'];
                $result = [
                    'type' => $info['type'],
                    'title' => $info['title'],
                    'desc' => $info['desc'],
                    'time' => date('Y-m-d H:i:s', intval($info['time']/1000)),
                    'author' => $info['user']['nickname'],
                    'avatar' => $info['user']['avatar'],
                ];
                if($info['type'] == 'video'){
                    $result['cover'] = !empty($info['imageList']) ? $info['imageList'][0]['urlDefault'] : '';
                    $result['url'] = !empty($info['video']['media']['stream']['h264']) ? $info['video']['media']['stream']['h264'][0]['masterUrl'] : '';
                }elseif($info['type'] == 'normal'){
                    $images = [];
                    foreach($info['imageList'] as $img){
                        $images[] = $img['urlDefault'];
                    }
                    $result['images'] = $images;
                }
                return $result;
            }
        }
        throw new Exception('视频解析失败');
    }
}