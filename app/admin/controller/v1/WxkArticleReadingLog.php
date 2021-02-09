<?php
/**
 * 文章阅读/聊天侧边栏
 * 注意 此类对外开放
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/7 0007
 * Time: 16:40
 */

namespace app\admin\controller\v1;


use app\core\BaseController;
use app\core\Wechat;
use think\App;
use think\facade\Cache;
use think\facade\Db;

class WxkArticleReadingLog extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 获取JS_SDK
     * User: 万奇
     * Date: 2021/1/8 0008
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_js_sdk(){
        $param              = param_receive(['url']);

        $wechat             = new Wechat();
        $ticket             = Cache::get('app_jsapi_ticket');
        $api_ticket         = Cache::get('api_jsapi_ticket');

        // 应用
        if (!$ticket){
            $url                = 'https://qyapi.weixin.qq.com/cgi-bin/ticket/get';
            $jsapi_ticket       = $wechat->request_wechat_api($url, 'wxk_app_secret', ['type' => 'agent_config'], false, false);

            if ($jsapi_ticket['errcode'] != 0){
                response(500, $jsapi_ticket['errmsg']);
            }
            Cache::set('app_jsapi_ticket', $jsapi_ticket['ticket'], 7000);
            $ticket             = $jsapi_ticket['ticket'];
        }
        // 企业
        if (!$api_ticket){
            $url                = 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket';
            $get_jsapi_ticket   = $wechat->request_wechat_api($url, 'wxk_app_secret', [], false, false);

            if ($get_jsapi_ticket['errcode'] != 0){
                response(500, $get_jsapi_ticket['errmsg']);
            }
            Cache::set('api_jsapi_ticket', $get_jsapi_ticket['ticket'], 7000);
            $api_ticket             = $get_jsapi_ticket['ticket'];
        }

        $timestamp  = time();
        $nonceStr   = random_string();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string     = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url={$param['url']}";
        $signature  = sha1($string);

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $api_string     = "jsapi_ticket=$api_ticket&noncestr=$nonceStr&timestamp=$timestamp&url={$param['url']}";
        $api_signature  = sha1($api_string);

        $config     = Db::name('wxk_config')->where(true)->find();

        $result['agent']     = [
            'corpid'    => $config['wxk_id'],
            'agentid'   => $config['wxk_app_agent_id'],
            'ticket'    => $ticket,
            'nonceStr'  => $nonceStr,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];

        $result['config']     = [
            'appId'     => $config['wxk_id'],
            'ticket'    => $api_ticket,
            'nonceStr'  => $nonceStr,
            'timestamp' => $timestamp,
            'signature' => $api_signature,
        ];

        response(200, '', $result);
    }

    /**
     * 获取素材
     * User: 万奇
     * Date: 2021/1/8 0008
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function app_get_content_engine(){
        $param      = param_receive(['type']);

        switch ($param['type']){
            case 1 :
                $result     = \StaticData::RESOURCE_NAME['content_type_list'];
                unset($result[4]);
                $result     = Array_Data($result);
                break;
            case 2 :
                $model      = new \app\admin\model\CmsContentGroup();
                $result     = $model->get_content_group_list();
                break;
            case 3 :
                param_receive(['page', 'limit']); // group_id, content_type, keyword
                $model      = new \app\admin\model\CmsContentEngine();
                $result     = $model->get_content_engine_list($param);
                response(200, '', $result['data'], $result['count']);
                break;
            default :
                $result     = [];
                break;
        }

        response(200, '', $result);
    }

    /**
     * 获取企业ID&成员ID
     * User: 万奇
     * Date: 2021/1/8 0008
     */
    public function get_code_staff_user(){
        $param      = input();

        // 获取企业ID
        if (!is_exists($param['code'])){
            $wxk_id =  Db::name('wxk_config')->value('wxk_id');

            response(200, '', $wxk_id);
        }


        $wechat             = new Wechat();
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo';
        $staff_user         = $wechat->request_wechat_api($url, 'wxk_app_secret', ['code' => $param['code']], false, false);

        if ($staff_user['errcode'] != 0){
            response(500, $staff_user['errmsg']);
        }

        $result                     = Db::name('wxk_staff')->field('user_id,name,mobile,qr_code')->where(['user_id' => $staff_user['UserId']])->find();
        $result['company_name']     = Db::name('wxk_department')->where(['parent_code' => 0])->value('name');

        response(200, '', $result);

    }

    /**
     * 新增文章阅读记录
     * User: 万奇
     * Date: 2021/1/7 0007
     */
    public function add_article_reading(){
        $param      = param_receive();

        $model      = new \app\admin\model\WxkArticleReadingLog();
        $result     = $model->add_article_reading($param);

        is_exists($param['id']) ? response(200, '操作成功') : response(200, '操作成功', $result);
    }

}