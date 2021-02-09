<?php
/**
 * 微信授权登陆
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/6 0006
 * Time: 17:06
 */

namespace app\admin\controller\v1;


use think\facade\Db;

class WechatOauth
{
    private $wechat_config        = null;

    public function __construct()
    {
        $this->_get_config();
    }

    /**
     * 微信授权登录
     * @author 万奇
     * @param string $code
     */
    public function wechat_login($code = '')
    {
        if(empty($code)){
            response(200, '', $this->wechat_config['wxk_public_app_id']);
        }

        $access_token   = $this->getSingleAccessToken($code);
        $user           = $this->getUserInfo($access_token);
        response(200, '登陆成功', $user);
    }


    /**
     * 微信授权链接
     * @param string $redirect_url 要跳转的地址
     * @param string $state
//     * @param string $scope  snsapi_base 参数不弹出授权界面 反之 snsapi_userinfo
     * @return string
     */
    public function getSingleAuthorizeUrl($redirect_url = "", $state = '1') {
        $redirect_url = urlencode($redirect_url);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->wechat_config['wxk_public_app_id']."&redirect_uri=".$redirect_url."&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
    }

    /**
     * 获取token
     * @param $code
     * @return mixed
     */
    public function getSingleAccessToken($code) {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->wechat_config['wxk_public_app_id'].'&secret='.$this->wechat_config['wxk_public_app_secret'].'&code='.$code.'&grant_type=authorization_code';
        $access_token = $this->https_request($url);
        return $access_token;
    }

    /**
     * @explain
     * 通过code获取用户openid以及用户的微信号信息
     * @param array $access_token
     * @return array|mixed
     * @remark
     * 获取到用户的openid之后可以判断用户是否有数据，可以直接跳过获取access_token,也可以继续获取access_token
     * access_token每日获取次数是有限制的，access_token有时间限制，可以存储到数据库7200s. 7200s后access_token失效
     **/
    public function getUserInfo($access_token = [])
    {
        if(!$access_token){
            response(500, '参数错误');
        }

        $userinfo_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token['access_token'].'&openid='.$access_token['openid'].'&lang=zh_CN';
        $userinfo_json = $this->https_request($userinfo_url);

        // 获取用户的基本信息
        if(!$userinfo_json){
            response(500, '参数错误');
        }

        return $userinfo_json;
    }

    /**
     * 发送curl请求
     * @param $url
     * @return mixed
     */
    public function https_request($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $AjaxReturn = curl_exec($curl);
        //获取access_token和openid,转换为数组
        $data = json_decode($AjaxReturn, true);
        curl_close($curl);
        return $data;
    }

    /**
     * 微信授权登录 测试
     * @author 万奇
     * @param string $code
     */
    public function wechat_login_test($code = '')
    {
        if(empty($code)){
            $baseUrl = request()->url(true);
            $url = $this->getSingleAuthorizeUrl($baseUrl,"123");
            Header("Location: $url");
            exit();
        } else{
            $access_token = $this->getSingleAccessToken($code);
            $user = $this->getUserInfo($access_token);
            response(200, '登陆成功', $user);
        }
    }

    /**
     * 获取配置
     * User: 万奇
     * Date: 2020/12/15 0015
     */
    private function _get_config(){
        if (!$this->wechat_config){
//            $this->wechat_config      = ['appid' => 'wx238806f4af29e463', 'appsecret' => '0c7acf6a0257f836e389f383ca5661ce'];
            $this->wechat_config      = Db::name('wxk_config')->where(true)->find();

            if (empty($this->wechat_config['wxk_public_app_id']) || empty($this->wechat_config['wxk_public_app_secret'])){
                response(500, '公众号配置错误');
            }
        }
    }

}