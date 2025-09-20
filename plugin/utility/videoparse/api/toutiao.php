<?php

namespace plugin\utility\videoparse\api;

use Exception;
use plugin\utility\videoparse\api;

class toutiao implements api
{
    public function parse($url){
        if(preg_match('!/video\/(\d+)\/!', $url, $match)){
            $id = $match[1];
            $requrl = 'https://m.toutiao.com/video/' . $id . '/';
            $ua = 'Mozilla/5.0 (Linux; Android 12; M2011K2C Build/SKQ1.211006.001) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.74 Mobile Safari/537.36';
            $res = get_curl($requrl, 0, 0, 0, 0, $ua);
            if(preg_match('/<script id="RENDER_DATA" type="application\/json">(.*?)<\/script>/', $res, $match)){
                $json = rawurldecode($match[1]);
                $arr = json_decode($json, true);
                if(isset($arr['articleInfo']['playAuthTokenV2'])){
                    $result = [
                        'title' => $arr['articleInfo']['title'],
                        'author' => $arr['articleInfo']['mediaUser']['screenName'],
                        'avatar' => $arr['articleInfo']['mediaUser']['avatarUrl'],
                        'time' => date('Y-m-d H:i:s', $arr['articleInfo']['publishTime']),
                        'cover' => $arr['articleInfo']['posterUrl'],
                    ];
                    $playinfo = base64_decode($arr['articleInfo']['playAuthTokenV2']);
                    $playinfo = json_decode($playinfo, true);
                    if(isset($playinfo['GetPlayInfoToken'])){
                        $requrl = 'https://vod.bytedanceapi.com/?'.$playinfo['GetPlayInfoToken'];
                        $res = get_curl($requrl, 0, $url, 0, 0, $ua);
                        $arr = json_decode($res, true);
                        if(isset($arr['Result']['Data']['PlayInfoList']) && !empty($arr['Result']['Data']['PlayInfoList'])){
                            $playinfolist = $arr['Result']['Data']['PlayInfoList'];
                            array_multisort(array_column($playinfolist, 'Bitrate'), SORT_DESC, $playinfolist);
                            $result['url'] = $playinfolist[0]['MainPlayUrl'];
                            return $result;
                        }
                    }
                }
            }
            throw new Exception('视频解析失败');
        }else{
            throw new Exception('视频id获取失败');
        }
    }
}