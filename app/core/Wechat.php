<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/23 0023
 * Time: 19:10
 */

namespace app\core;


use app\admin\model\WxkConfig;
use think\facade\Cache;

class Wechat
{
    use Redis_operation;

    private $get_token_url              = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken'; // 获取 token
    private $department_list_url        = 'https://qyapi.weixin.qq.com/cgi-bin/department/list'; // 获取企业微信部门列表
    private $user_list_url              = 'https://qyapi.weixin.qq.com/cgi-bin/user/list'; // 获取企业微信获取部门成员
    private $client_user_all_info_url   = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/batch/get_by_user'; // 根据成员批量获取客户详情
    private $client_user_list_url       = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/list'; // 根据成员批量获取客户详情
    private $add_corp_tag_url           = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/add_corp_tag'; // 添加企业微信客户标签
    private $get_corp_tag_list_url      = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get_corp_tag_list'; // 添加企业微信客户标签

    public function __construct(){
//        $this->redis = $this->redis_connect();
    }

    /**
     * 企业微信api请求接口
     * User: 万奇
     * Date: 2020/12/4 0004
     * @param $url_name -接口地址
     * @param $secret_type - secret类型
     * @param array $data - 数据
     * @param bool $is_post - 请求方式
     * @param bool $get_token - 是否get方式传参（默认否）
     * @return mixed
     */
    public function request_wechat_api($url_name, $secret_type, $data = [], $is_post = true, $get_token = false){
        $access_token           = $this->get_access_token($secret_type);
        $get_token ? $url_name  = $url_name . '?access_token=' . $access_token : $data['access_token']   = $access_token;

        $result                 = json_decode(curl_request($url_name, $data, [], $is_post), true);

        return $result;
    }

    /**
     * 根据成员批量获取客户详情
     * User: 万奇
     * Date: 2020/11/29 0029
     * @param $param
     * @param array $data
     * @return mixed
     */
    public function get_client_all_info($param, $data = []){
        $access_token         = $this->get_access_token($param['type']);

        $result         = json_decode(curl_request($this->client_user_all_info_url . '?access_token=' . $access_token, $data, []), true);
        if ($result['errcode'] != 0){
            response(500, '操作失败');
        }

        return $result['external_contact_list'];
    }

    /**
     * 按部门获取企业微信成员
     * User: 万奇
     * Date: 2020/11/25 0025
     * @param $param
     * @param array $data
     * @return mixed
     */
    public function get_user_simple_list($param, $data = []){
        $access_token         = $this->get_access_token($param['type']);
        $data['access_token'] = $access_token;

        $result         = json_decode(curl_request($this->user_list_url, $data, [], false), true);

        if ($result['errcode'] != 0){
            response(500, '操作失败');
        }

        return $result['userlist'];
    }

    /**
     * 获取企业微信部门列表
     * User: 万奇
     * Date: 2020/11/25 0025
     * @param $param
     * @param $data
     * @return mixed
     */
    public function get_department_list($param, $data = []){
        $access_token         = $this->get_access_token($param['type']);
        $data['access_token'] = $access_token;
        $result         = json_decode(curl_request($this->department_list_url, $data, [], false), true);

        if ($result['errcode'] != 0){
            response(500, '操作失败');
        }

        return $result['department'];
    }

    /**
     * 添加企业微信标签
     * User: 万奇
     * Date: 2020/11/24 0024
     * @param $param
     * @param $data
     * @return bool
     */
    public function add_corp_tag($param, $data){
        $access_token         = $this->get_access_token($param['type']);
        $data['access_token'] = $access_token;

        $result         = json_decode(curl_request($this->add_corp_tag_url, $data), true);

        if ($result['errcode'] != 0){
            response(500, '操作失败');
        }

        return true;
    }

    /**
     * 根据不同业务获取不同 access_token
     * User: 万奇
     * Date: 2020/11/24 0024
     * @param $type - 业务类型格式：（根据企业微信数据库表字段）
     * @return mixed
     */
    private function get_access_token($type){
        $access_token       = Cache::get('access_token_'. $type);
        if ($access_token){
            return $access_token;
        }

        $model          = new WxkConfig();
        $qy_info        = $model->get_qy_info();

        if (!$qy_info){
            response(500, '未查到该企业微信配置');
        }

        $access_token  = json_decode(curl_request($this->get_token_url, ['corpid' => $qy_info['wxk_id'], 'corpsecret' => $qy_info[$type]], [], false), true);

        if (!isset($access_token['access_token'])){
            response(500, 'access_token 获取失败！');
        }

        Cache::set('access_token_'. $type, $access_token['access_token'], $access_token['expires_in'] - 100);

        return $access_token['access_token'];
    }

}