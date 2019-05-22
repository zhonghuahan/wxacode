<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 凉笙墨染 <ramins@163.com> <www.ramins.cn>
// +----------------------------------------------------------------------

namespace wxacode;

class wxacode{
    // +----------------------------------------------------------------------
    // | 参数配置项
    // +----------------------------------------------------------------------
    protected $appid; /** 小程序APPID */

    protected $secret;/** 小程序秘钥 */



    /** 获取 ACCESS_TOKEN */
    const GET_ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/token';

    private $access_token = '';



    // +----------------------------------------------------------------------
    // | 小程序登陆配置
    // +----------------------------------------------------------------------
    /** https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&grant_type=authorization_code */
    const GET_OPENID_BY_CODE = 'https://api.weixin.qq.com/sns/jscode2session';

    protected $code;

    protected $openid;

    protected $session_key;

    protected $unionid;

    private $expire = 0; /** access_token 过期时间 */



    // +----------------------------------------------------------------------
    // | 生成小程序二维码配置
    // +----------------------------------------------------------------------
    /** 接口模式A:          https://api.weixin.qq.com/wxa/getwxacode?access_token=ACCESS_TOKEN */
    const POST_CODE_A = 'https://api.weixin.qq.com/wxa/getwxacode';

    /** 接口模式B:          https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=ACCESS_TOKEN */
    const POST_CODE_B = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';

    /** 接口模式C:          https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=ACCESS_TOKEN */
    const POST_CODE_C = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode';

    protected $path;        /** 生成二维码保存路径 */

    protected $resource;    /** 二维码二进制数据 */

    protected $name;        /** 保存的二维码名称  102230888.png */

    protected $filename;    /** 完整的文件名称   20190517/102230888.png*/

    function __construct($config = []){
        $this->appid = $config['appid'];
        $this->secret = $config['secret'];
    }

    public function __set($name, $value){

        $this->$name = $value;

        return $this;
    }

    public function __get($name){

        return $this->$name;

    }

    /**
     * 设置 二维码保存路径
     * @author 凉笙墨染 2019-05-18
     * @param $code 小程序给的CODE码
     */
    public function setPath($path){

        $this->path = $path;

        return $this;
    }

    public function setName($name){

        $this->name = $name;

        return $this;
    }

    /**
     * 设置 CODE
     * @author 凉笙墨染 2019-05-18
     * @param $code 小程序给的CODE码
     */
    public function setCode($code){

        $this->code = $code;

        return $this;
    }

    public function setFile(){

        $path = $this->path .'/'. date('Ymd') .'/';

        !file_exists($path) && @mkdir($path, 0777, true);

        @file_put_contents($path . $this->name, $this->resource);

        $this->filename = date('Ymd') . '/' . $this->name;

        return $this;
    }


    /**
     * 获取二维码地址
     * @author 凉笙墨染 2019-05-17
     */
    public function getFileName(){

        return $this->filename;

    }

    /**
     * 获取二维码名称
     * @author 凉笙墨染 2019-05-17
     */
    public function getName(){

        return $this->name;

    }

    /**
     * 获取 ACCESS_TOKEN
     * @author 凉笙墨染 2019-05-17
     * @return object
     */
    public function getAccessToken(){
        $param = [
            'appid' => $this->appid,
            'secret' => $this->secret,
            'grant_type' => 'client_credential'
        ];

        $res = $this->curl(self::GET_ACCESS_TOKEN, $param);
        $res = json_decode($res);

        $this->access_token = $res->access_token;
        $this->expire = $res->expires_in;

        return $this;
    }

    /**
     * 获取 OPENID 和 SESSION_KEY
     * @return              [type] [description]
     * @author 凉笙墨染 2019-05-18
     * @param array $code 小程序给的CODE
     */
    public function getOpenid(){

        $data = array(
            'appid'      => $this->appid,
            'secret'     => $this->secret,
            'js_code'    => $this->code,
            'grant_type' => 'authorization_code'
        );

        $r = $this->curl(self::GET_OPENID_BY_CODE, $data);

        if ( stripos($r, 'err') !== false) {

            throw new \Exception($r, 1);
        }

        $r = json_decode($r);
        $this->openid = $r->openid;
        $this->session_key = $r->session_key;
        $this->unionid = $r->unionid;

        return $r;
    }

    /**
     * 不同场景：获取小程序二维码
     * @author 凉笙墨染 2019-05-17
     * @param array $data
     * @param string $mode: A, B, C (默认生成B场景（无限制）的小程序码)
     */
    public function data($data, $mode = 'B'){

        /** 获取 ACCESS_TOKEN */
        $this->getAccessToken();

        switch ($mode) {
            case 'A':

                $urlcode = self::POST_CODE_A.'?access_token='.$this->access_token;
                break;

            case 'B':

                $urlcode = self::POST_CODE_B.'?access_token='.$this->access_token;
                break;

            case 'C':
                $urlcode = self::POST_CODE_C.'?access_token='.$this->access_token;
                break;
        }

        $r = $this->curl($urlcode, json_encode($data), 1);

        if ( stripos($r, 'errcode') !== false) {

            throw new \Exception($r, 1);
        }

        $this->resource = $r;

        return $this;
    }

    /**
     * 存储二维码信息
     * @param               [type] $data [description]
     * @param               string $name [description]
     * @param               string $path [description]
     * @author 凉笙墨染 2019-05-17
     */
    public function save($name =''){


        if ( '' == $name) {
            $name = date('His').sprintf('%03d', mt_rand(1,999)).'.png';
        }
        $this->setName($name);

        $this->setFile();

        return $this;
    }

    /**
     * CURL 方式请求资源
     * @param string $url       基于的baseUrl
     * @param array  $params    请求的参数列表
     * @param int $method       标志位: 2: 混合请求 1: POST请求，0: GET请求
     * @return string           返回的资源内容
     */
    public function curl($url, $params, $method = 0){
        // GET和POST混合使用请求
        if( $method != 1 ) {
            $url = $url . '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);// SSL证书认证
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果

        if( $method ){
            curl_setopt($ch, CURLOPT_POST, true);// post传输数据
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // post传输数据
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
