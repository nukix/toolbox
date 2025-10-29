<?php
/**
 * 短视频去水印解析
 */

namespace plugin\utility\videoparse;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $video_url = input('post.video_url', null, 'trim');
        if(!$video_url) return json(['code'=>-1, 'msg'=>'视频链接不能为空']);
        
        $apitype = $this->get_api_type($video_url);
        if(!$apitype) return json(['code'=>-1, 'msg'=>'不支持该视频链接']);

        $classname = 'plugin\\utility\\videoparse\\api\\'.$apitype;
        if(class_exists($classname)){
            $instance = new $classname();
            try{
                $result = $instance->parse($video_url);
                return json(['code'=>0, 'msg'=>'success', 'data'=>$result]);
            }catch(\Exception $e){
                return json(['code'=>-1, 'msg'=>$e->getMessage()]);
            }
        }else{
            return json(['code'=>-1, 'msg'=>'该平台类型不存在']);
        }
        
    }

    private function get_api_type($url){
        if(strpos($url, 'kg.qq.com/') || preg_match('/kg(\d+).qq.com\//', $url)){
            return 'qmkg';
        }
        elseif(strpos($url, 'weishi.qq.com/')){
            return 'weishi';
        }
        elseif(strpos($url, '.huya.com/')){
            return 'huya';
        }
        elseif(strpos($url, '.acfun.cn/')){
            return 'acfun';
        }
        elseif(strpos($url, '.douyin.com/')){
            return 'douyin';
        }
        elseif(strpos($url, '.kuaishou.com/')){
            return 'kuaishou';
        }
        elseif(strpos($url, '.xiaohongshu.com/') || strpos($url, 'xhslink.com/')){
            return 'xiaohongshu';
        }
        elseif(strpos($url, 'toutiao.com/')){
            return 'toutiao';
        }
        elseif(strpos($url, 'v.douyu.com/')){
            return 'douyu';
        }
        elseif(strpos($url, 'weibo.com/') || strpos($url, 'weibo.cn/')){
            return 'weibo';
        }
        elseif(strpos($url, 'pipix.com/')){
            return 'pipixia';
        }
        elseif(strpos($url, 'izuiyou.com/') || strpos($url, 'xiaochuankeji.cn/')){
            return 'zuiyou';
        }
        return false;
    }

}