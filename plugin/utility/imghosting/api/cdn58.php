<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class cdn58 implements api
{
    public function upload($filepath, $filename){
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!in_array($file_ext, ['jpg','jpeg','png','gif','bmp'])) throw new Exception('上传失败！不支持的文件格式');

        $user_id = '58Anonymous'.$this->guid();
        $user_info = [
            'user_id' => $user_id,
            'source' => '14',
            'im_token' => $user_id,
            'client_version' => '1.0',
            'client_type' => 'pcweb',
            'os_type' => 'Chrome',
            'os_version' => '122.0.6261.95',
            'appid' => '10140-mcs@jitmouQrcHs',
            'extend_flag' => '0',
            'unread_index' => '1',
            'sdk_version' => '6432',
            'device_id' => $user_id,
            'xxzl_smartid' => '',
            'id58' => 'CkwAd2e0U3tBNxbRAzQ2Ag==',
        ];
        $params = http_build_query($user_info);
        $params = $this->encrypt($params);

        $post = [
            'sender_id' => $user_id,
            'sender_source' => 14,
            'to_id' => '10002',
            'to_source' => 100,
            'file_suffixs' => [$file_ext],
        ];
        $post = json_encode($post);
        $post = $this->encrypt($post);
        
        $url = 'https://im.58.com/msg/get_pic_upload_url?params='.$params.'&version=j1.0';
        $referer = 'https://ai.58.com/pc/';
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.95 Safari/537.36';
        $data = get_curl($url,$post,$referer,0,0,$ua,0,['Content-Type: text/plain;charset=UTF-8', 'Origin: https://ai.58.com']);
        $arr = json_decode($data, true);
        if(isset($arr['error_code']) && $arr['error_code']==0){
            if(empty($arr['data']['upload_info'])) throw new Exception('上传失败！未返回上传地址');
            $url = $arr['data']['upload_info'][0]['url'];
        }else{
            throw new Exception('上传失败！'.(isset($arr['error_msg']) ? $arr['error_msg'] : '接口错误'));
        }
        
        $mine_type = $this->mime_content_type($file_ext);
        [$httpCode, $header, $body] = $this->curl_upload($url, file_get_contents($filepath), ['Content-Type: '.$mine_type]);
        
        if($httpCode == 200){
            $filename = getSubstr($url, '/nowater/im/', '?');
            $imgurl = 'https://pic'.rand(1,8).'.58cdn.com.cn/nowater/im/'.$filename;
            return ['url'=>$imgurl];
        }else{
            throw new Exception('上传失败！httpCode='.$httpCode);
        }
    }

    private function mime_content_type($ext)
    {
        $mime_types = [
            'png' => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
        ];
        return isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
    }

    private function encrypt($data){
        $str = base64_encode($data);
        $equal_count = substr_count($str, '=');
        $str = str_replace(['+','/','='], ['-','_',''], $str).$equal_count;
        $half = floor(strlen($str)/2);
        $str = substr($str, $half).substr($str, 0, $half);
        return $str;
    }

    private function decrypt($data) {
        $half = ceil(strlen($data)/2);
        $data = substr($data, $half).substr($data, 0, $half);
        $equal_count = substr($data, -1);
        $data = substr($data, 0, -1);
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $data .= str_repeat('=', $equal_count);
        return base64_decode($data);
    }

    private function guid(){
        $guid = md5(uniqid(mt_rand(), true));
        return substr($guid,0,8).'-'.substr($guid,8,4).'-4'.substr($guid,12,3).'-'.substr($guid,16,4).'-'.substr($guid,20,12);
    }

    private function curl_upload($url, $body, $header, $timeout = 10)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.95 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $data = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            $errmsg = curl_error($ch);
            curl_close($ch);
            throw new Exception($errmsg, 0);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($data, 0, $headerSize);
        $body = substr($data, $headerSize);
        curl_close($ch);
        return [$httpCode, $header, $body];
    }
}