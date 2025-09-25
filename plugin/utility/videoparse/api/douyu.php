<?php

namespace plugin\utility\videoparse\api;

use Exception;
use plugin\utility\videoparse\api;

class douyu implements api
{
    private static $version = '220320250920';

    public function parse($url){
        if(preg_match('!\/show\/([a-zA-Z0-9]+)!', $url, $match)){
            $vid = $match[1];
            $requrl = 'https://v.douyu.com/show/' . $vid;
            $res = get_curl($requrl);
            if(preg_match('/DATA:\{content: (.*?),videoTag:/', $res, $match)){
                $arr = json_decode($match[1], true);
                if(isset($arr['point_id'])){
                    $result = [
                        'title' => $arr['title'],
                        'desc' => $arr['contents'],
                        'cover' => $arr['video_pic'],
                        'time' => date('Y-m-d H:i:s', $arr['create_time']),
                        'author' => $arr['author'],
                        'avatar' => $arr['authorIcon']
                    ];
                    $requrl = 'https://v.douyu.com/wgapi/vodnc/front/stream/getStreamUrlWeb';
                    $dy_did = md5(microtime(true).rand(1000,9999));
                    $time = time();
                    $sign = $this->getsign($arr['point_id'], $dy_did, $time);
                    $data = [
                        'v' => self::$version,
                        'did' => $dy_did,
                        'tt' => $time,
                        'sign' => $sign,
                        'vid' => $arr['hash_id']
                    ];
                    $cookie = 'dy_did='.$dy_did.';';
                    $res = get_curl($requrl, http_build_query($data), $url, $cookie);
                    $arr = json_decode($res, true);
                    if(isset($arr['error']) && $arr['error'] == 0 && isset($arr['data']['thumb_video'])){
                        if(isset($arr['data']['thumb_video']['super'])) $info = $arr['data']['thumb_video']['super'];
                        elseif(isset($arr['data']['thumb_video']['high'])) $info = $arr['data']['thumb_video']['high'];
                        elseif(isset($arr['data']['thumb_video']['normal'])) $info = $arr['data']['thumb_video']['normal'];
                        else throw new Exception('视频解析失败，无可用视频地址');
                        $result['url'] = $info['url'];
                        return $result;
                    }else{
                        throw new Exception('视频解析失败，'.($arr['msg'] ?? ''));
                    }
                }
            }
            throw new Exception('视频解析失败');
        }else{
            throw new Exception('视频id获取失败');
        }
    }

    private function getsign($xx0, $xx1, $xx2)
    {
        $k2 = [0x551983fb, 0x63c94be2, 0x0054dfe2, 0x3ba6bd08];
        $MASK = 0xFFFFFFFF;

        $add  = fn(int $a, int $b) => ($a + $b) & $MASK;
        $rotr = fn(int $x, int $n) => ((($x & $MASK) >> ($n & 31)) | (($x << (32 - ($n & 31))) & $MASK)) & $MASK;
        $rotl = fn(int $x, int $n) => (((($x << ($n & 31)) & $MASK) | (($x & $MASK) >> (32 - ($n & 31))))) & $MASK;

        $cb = $xx0 . $xx1 . $xx2 . self::$version;
        $md = hash('md5', $cb, true);
        $re = array_values(unpack('V4', $md));

        for ($I = 0; $I < 2; $I++) {
            $v0 = $re[$I * 2]; $v1 = $re[$I * 2 + 1];
            $sum = 0; $delta = 0x9e3779b9;
            for ($i = 0; $i < 32; $i++) {
                $sum = $add($sum, $delta);
                $v0  = $add($v0, ((($v1 << 4) + $k2[0]) ^ ($v1 + $sum) ^ (((($v1) & $MASK) >> 5) + $k2[1])) & $MASK);
                $v1  = $add($v1, ((($v0 << 4) + $k2[2]) ^ ($v0 + $sum) ^ (((($v0) & $MASK) >> 5) + $k2[3])) & $MASK);
            }
            $re[$I * 2]     = $v0 & $MASK;
            $re[$I * 2 + 1] = $v1 & $MASK;
        }

        $re[0] = $rotr($re[0], $k2[0] % 16);
        $re[0] = $rotl($re[0], $k2[2] % 16);
        $re[0] = $rotl($re[0], $k2[0] % 16);
        $re[0] = $rotr($re[0], $k2[2] % 16);
        $re[0] = $rotl($re[0], $k2[2] % 16);

        $re[1] = ($re[1] ^ $k2[1]) & $MASK;
        $re[1] = $add($re[1], $k2[3]);
        $re[1] = $add($re[1], $k2[1]);
        $re[1] = ($re[1] ^ $k2[3]) & $MASK;
        $re[1] = ($re[1] ^ $k2[3]) & $MASK;

        $re[2] = $add($re[2], $k2[0]);
        $re[2] = ($re[2] - $k2[2]) & $MASK;
        $re[2] = ($re[2] - $k2[0]) & $MASK;
        $re[2] = $add($re[2], $k2[2]);
        $re[2] = $rotl($re[2], $k2[2] % 16);

        $re[3] = $rotl($re[3], $k2[1] % 16);
        $re[3] = $rotr($re[3], $k2[3] % 16);
        $re[3] = $rotr($re[3], $k2[1] % 16);
        $re[3] = ($re[3] - $k2[3]) & $MASK;

        $re[0] = $add($re[0], $k2[0]);
        $re[0] = $add($re[0], $k2[2]);
        $re[0] = $rotl($re[0], $k2[2] % 16);

        $re[1] = $rotr($re[1], $k2[1] % 16);
        $re[1] = $rotl($re[1], $k2[3] % 16);
        $re[1] = ($re[1] ^ $k2[3]) & $MASK;

        $re[2] = $add($re[2], $k2[0]);
        $re[2] = ($re[2] - $k2[2]) & $MASK;
        $re[2] = $add($re[2], $k2[2]);
        $re[2] = $rotr($re[2], $k2[2] % 16);

        $re[3] = $rotl($re[3], $k2[1] % 16);
        $re[3] = ($re[3] - $k2[3]) & $MASK;
        $re[3] = $rotl($re[3], $k2[3] % 16);

        $sign = bin2hex(pack('V*', $re[0], $re[1], $re[2], $re[3]));

        return $sign;
    }
}