<?php
/**
 * 企业微信外部回调
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/12/15 0015
 * Time: 14:41
 */

namespace app\admin\controller\v1;


use app\core\Redis_operation;
use app\core\Wechat;
use EnterpriseWechatApi\callback\WXBizMsgCrypt;
use think\facade\Cache;
use think\facade\Db;

class ExternalCallback
{
    use Redis_operation;
    private $_config        = null;

    public function __construct()
    {
//        $this->redis = $this->redis_connect();
        $this->_get_config();
    }


    /**
     * 企业微信客户管理回调
     * User: 万奇
     * Date: 2020/12/17 0017
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function external_contact(){
        $param          = $_GET;
        $xmlData        = file_get_contents('php://input');

        $_callbackObj   = new WXBizMsgCrypt($this->_config['wxk_customer_callback_token'], $this->_config['wxk_customer_callback_key'], $this->_config['wxk_id']);

        // 验证回调 url有效性
        if (empty($xmlData) && isset($param['echostr'])){
            $sEchoStr       = '';
            $errCode        = $_callbackObj->VerifyURL($param['msg_signature'], $param['timestamp'], $param['nonce'], $param['echostr'], $sEchoStr);
            if ($errCode != 0){
                print("ERR: " . $errCode . "\n\n");exit;
            }

            echo $sEchoStr;exit;
        } else{
            // 解密
            $decryptMsg     = '';
            $errCode        = $_callbackObj->DecryptMsg($param['msg_signature'], $param['timestamp'], $param['nonce'], $xmlData, $decryptMsg);

            if ($errCode != 0){
                print("ERR: " . $errCode . "\n\n");exit;
            }

            $decryptMsg     = xml2Array($decryptMsg);
            switch ($decryptMsg['ChangeType']){
                case 'add_external_contact' :    // 添加外部联系人回调事件
                    $this->add_external_contact($decryptMsg);
                    break;
                case 'del_external_contact' :    // 删除外部联系人回调事件
                    $this->del_external_user($decryptMsg, 'deleted_customer');
                    break;
                case 'del_follow_user' :    // 删除跟进成员回调事件
                    $this->del_external_user($decryptMsg,'deleted_staff');
                    break;
                case 'delete':
                    // 删除客户标签
                    if ($decryptMsg['Event'] == 'change_external_tag'){
                        $this->change_external_tag($decryptMsg);
                    }
                    break;
                default :
                    echo '回调事件类型不合法';exit;
                    break;
            }

        }
    }

    /**
     * 更改客户标签
     * User: 万奇
     * Date: 2021/1/29 0029
     * @param $decryptMsg
     * @throws \think\db\exception\DbException
     */
    function change_external_tag($decryptMsg){
        switch ($decryptMsg['ChangeType']){
            case 'delete':
                if ($decryptMsg['TagType'] == 'tag_group'){
                    $parent_code    = Db::name('wxk_customer_tag')->where(['id' => $decryptMsg['Id']])->value('code');
                    $tag_ids        = Db::name('wxk_customer_tag')->where(['parent_code' => $parent_code])->column('id');
                    array_push($tag_ids, $decryptMsg['Id']);
                } else{
                    $tag_ids        = [$decryptMsg['Id']];
                }

                foreach ($tag_ids as $v){
                    Db::name('wxk_customer')->where("find_in_set('{$v}', tag_ids)")
                        ->exp('tag_ids', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', tag_ids, ','), concat(',','{$v}',','), ','))")
                        ->update();
                    Db::name('wxk_live_qr')->where("find_in_set('{$v}', tag_ids)")
                        ->exp('tag_ids', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', tag_ids, ','), concat(',','{$v}',','), ','))")
                        ->update();
                }

                Db::name('wxk_customer_tag')->where([['id', 'in', implode(',', $tag_ids)]])->delete();
                break;
        }
    }

    /**
     * 删除跟进成员&外部联系人回调事件
     * User: 万奇
     * Date: 2020/12/17 0017
     * @param $decryptMsg
     * @param $type
     * @throws \think\db\exception\DbException
     */
    public function del_external_user($decryptMsg, $type){
        $follow_state       = Db::name('wxk_customer')->where(['external_user_id' => $decryptMsg['ExternalUserID'], 'follow_userid' => $decryptMsg['UserID']])->value('follow_state');

        $wxk_live_qr        = Db::name('wxk_live_qr')->where(['name' => $follow_state ? $follow_state : (isset($decryptMsg['State']) ? $decryptMsg['State'] : $follow_state)])->value('id');

        // 活码统计
        $insert             = ['id' => uuid(), $type => 1, 'add_type' => 1, 'user_id' => $decryptMsg['UserID'], 'external_user_id' => $decryptMsg['ExternalUserID']];
        // 判断是否是活码添加客户被删除
        if ($wxk_live_qr){
            $insert['add_type']         = 2;
            $insert['live_qr_id']       = $wxk_live_qr;
        }
        Db::name('wxk_live_qr_statistics')->insert($insert);

        // 删除客户
        Db::name('wxk_customer')->where(['external_user_id' => $decryptMsg['ExternalUserID'], 'follow_userid' => $decryptMsg['UserID']])->delete();
    }

    /**
     * 添加外部联系人回调事件
     * User: 万奇
     * Date: 2020/12/17 0017
     * @param $decryptMsg
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add_external_contact($decryptMsg){
        $wechat             = new Wechat();

        // 获取客户详情
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get';
        $external_user      = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', ['external_userid' => $decryptMsg['ExternalUserID']], false, false);

        if ($external_user['errcode'] != 0){
            response(500, '操作失败');
        }

        // 客户通过活码方式添加
        $wxk_live_qr                = Db::name('wxk_live_qr')->where(['name' => isset($decryptMsg['State']) ? $decryptMsg['State'] : ''])->find();
        if (isset($decryptMsg['State']) && $wxk_live_qr && ($wxk_live_qr['is_welcome_msg'] == 1)){
            // 发送企业微信欢迎语
            if (isset($decryptMsg['WelcomeCode'])){
                // 发送活码欢迎语
                $is_welcome_msg       = $this->send_welcome($decryptMsg['WelcomeCode'], $external_user['external_contact']['name'], $wxk_live_qr['welcome_data']);

                if ($is_welcome_msg['errcode'] != 0){
                    response(500, '操作失败');
                }
            }

            // 添加客户标签
            if ($wxk_live_qr['tag_ids']){
                $insert['tag_ids']          = $wxk_live_qr['tag_ids'];

                // 添加企业微信客户标签
                $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/mark_tag';
                $mark_tag           = ['external_userid' => $decryptMsg['ExternalUserID'], 'userid' => $decryptMsg['UserID'], 'add_tag' => explode(',', $wxk_live_qr['tag_ids'])];
                $is_mark_tag        = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $mark_tag, true, true);

                if ($is_mark_tag['errcode'] != 0){
                    response(500, '操作失败');
                }
            }

            // 活码统计数据
            $statistics_insert  = ['id' => uuid(), 'add_customer' => 1, 'add_type' => 2, 'live_qr_id' => $wxk_live_qr['id'], 'user_id' => $decryptMsg['UserID'], 'external_user_id' => $decryptMsg['ExternalUserID']];

            // 查询成员添加上限
            if ($wxk_live_qr['is_add_limit']){
                $add_limit      = Db::name('wxk_live_qr_add_limit')->where(['live_qr_id' => $wxk_live_qr['id'], 'user_id' => $decryptMsg['UserID']])->value('add_limit');
                $customer_count = Db::name('wxk_customer')->where(['follow_state' => $wxk_live_qr['name'], 'follow_userid' => $decryptMsg['UserID']])->count() + 1;

                // 编辑活码成员
                if ($customer_count >= $add_limit){
                    $url                    = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/update_contact_way';
                    $user                   = explode(',', $wxk_live_qr['wxk_staff_id']);
                    $user[array_search($decryptMsg['UserID'], $user)]   = $wxk_live_qr['spare_staff_id'];
                    $update_contact_way     = ['config_id' => $wxk_live_qr['id'], 'user' => $user];
                    $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $update_contact_way, true, true);
                }
            }
        } else{
            // 非活码方式添加外部联系人
            $welcome_list       = Db::name('wxk_welcome')->where(['user_id' => 0])->whereOr("find_in_set('WanQi', user_id)")->order(['user_id' => 'desc'])->select()->toArray();
            if (isset($decryptMsg['WelcomeCode']) && isset($welcome_list[0])){
                // 发送活码欢迎语
                $is_welcome_msg       = $this->send_welcome($decryptMsg['WelcomeCode'], $external_user['external_contact']['name'], $welcome_list[0]['welcome_data']);

                if ($is_welcome_msg['errcode'] != 0){
                    response(500, '操作失败');
                }
            }

            // 活码统计数据
            $statistics_insert  = ['id' => uuid(), 'add_customer' => 1, 'add_type' => 1, 'user_id' => $decryptMsg['UserID'], 'external_user_id' => $decryptMsg['ExternalUserID']];
        }

        // 活码统计
        Db::name('wxk_live_qr_statistics')->insert($statistics_insert);

        // 客户添加
        $external_user_info             = $external_user['external_contact'];
        $follow_user                    = set_val_to_key($external_user['follow_user'], 'userid')[$decryptMsg['UserID']];

        // 判断是否重复添加
        $is_external_user               = Db::name('wxk_customer')->where(['external_user_id' => $external_user_info['external_userid'], 'follow_userid' => $follow_user['userid']])->count();

        if ($is_external_user){
            if (isset($follow_user['state'])){
                $update                 = ['follow_state' => $follow_user['state']];
                Db::name('wxk_customer')->where(['external_user_id' => $external_user_info['external_userid'], 'follow_userid' => $follow_user['userid']])->update($update);
            }
        } else{
            $insert['id']                   = uuid();
            $insert['external_user_id']     = $external_user_info['external_userid'];
            $insert['name']                 = $external_user_info['name'];
            $insert['avatar']               = $external_user_info['avatar'];
            $insert['customer_type']        = $external_user_info['type'];
            $insert['gender']               = $external_user_info['gender'];
            $insert['follow_userid']        = $follow_user['userid'];
            $insert['follow_remark']        = $follow_user['remark'];
            $insert['follow_createtime']    = format_time($follow_user['createtime']);
            $insert['follow_add_way']       = isset($follow_user['add_way']) ? $follow_user['add_way'] : '';
            $insert['follow_oper_userid']   = isset($follow_user['oper_userid']) ? $follow_user['oper_userid'] : '';
            $insert['follow_state']         = isset($follow_user['state']) && $wxk_live_qr ? $follow_user['state'] : '';

            Db::name('wxk_customer')->insert($insert);
        }

    }

    /**
     * 发送欢迎语
     * User: 万奇
     * Date: 2020/12/24 0024
     * @param $welcomeCode  - 欢迎语code，可用于发送欢迎语
     * @param $external_user_name - 外部联系人名称
     * @param $welcome_data - 欢迎语数据
     * @return mixed
     */
    public function send_welcome($welcomeCode, $external_user_name, $welcome_data){
        $wechat             = new Wechat();
        $text_msg                   = json_decode($welcome_data);
        $text_msg->welcome_code     = $welcomeCode;
        if (isset($text_msg->text->content)){
            $text_msg->text->content    = str_replace('{name}', $external_user_name, $text_msg->text->content);
            $text_msg->text->content    = str_replace('<br>', "\n", $text_msg->text->content);
            $text_msg->text->content    = str_replace('&nbsp;', " ", $text_msg->text->content);
        }

        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/send_welcome_msg';
        $is_welcome_msg     = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', json_encode($text_msg), true, true);

        return $is_welcome_msg;
    }

    /**
     * 获取配置
     * User: 万奇
     * Date: 2020/12/15 0015
     */
    public function _get_config(){
        if (!$this->_config){
            $is_install        = file_exists('../install/install.lock') ? 1 : 0;
            if ($is_install){
                $this->_config = Db::name('wxk_config')->where(true)->find();
            } else{
                $this->_config['wxk_customer_callback_token']      = Cache::get('wxk_customer_callback_token');
                $this->_config['wxk_customer_callback_key']        = Cache::get('wxk_customer_callback_key');
                $this->_config['wxk_id']                           = Cache::get('wxk_id');
            }

            if (!$this->_config){
                response(500, '未找到回调配置');
            }
        }
    }


}