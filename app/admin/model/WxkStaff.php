<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/25 0025
 * Time: 18:03
 */

namespace app\admin\model;


use app\core\Wechat;
use think\facade\Db;

class WxkStaff extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 成员打标签/移除标签
     * User: 万奇
     * Date: 2021/1/26 0026
     * @param $param
     */
    public function staff_tagging($param){
        $wechat             = new Wechat();
        $staff              = $this->where([['user_id', 'in', implode(',', $param['staff_user_id'])]])->column('tag_ids', 'id');

        if ($param['type'] == 1){
            $url                = 'https://qyapi.weixin.qq.com/cgi-bin/tag/addtagusers';
            foreach ($param['tag_ids'] as $t_v){
                $data       = ['tagid' => $t_v, 'userlist' => $param['staff_user_id']];
                $add        = $wechat->request_wechat_api($url, 'wxk_address_book_secret', $data, true, true);

                if ($add['errcode'] != 0){
                    response(500, '操作失败');
                }
            }

            // 打标签
            foreach ($staff as $k => $v){
                $update['tag_ids']     = implode(',', $v ? array_unique(array_merge(explode(',', $v), $param['tag_ids'])) : $param['tag_ids']);
                $this->where(['id' => $k])->update($update);
            }
        } else{
            $url                = 'https://qyapi.weixin.qq.com/cgi-bin/tag/deltagusers';
            foreach ($param['tag_ids'] as $t_v){
                $data       = ['tagid' => $t_v, 'userlist' => $param['staff_user_id']];
                $add        = $wechat->request_wechat_api($url, 'wxk_address_book_secret', $data, true, true);

                if ($add['errcode'] != 0){
                    response(500, '操作失败');
                }
            }

            // 移除标签
            foreach ($staff as $k => $v){
                $update['tag_ids']     = implode(',', array_diff(explode(',', $v), $param['tag_ids']));
                $this->where(['id' => $k])->update($update);
            }
        }

    }

    /**
     * 成员打标签回显已有的标签
     * User: 万奇
     * Date: 2021/1/14 0014
     * @param $param
     * @return array
     */
    public function show_staff_tag($param){
        $list       = $this->where([['id', 'in', implode(',', $param['id'])], ['tag_ids', '<>', '']])->column('tag_ids');

        $result     = [];
        if (!count($list)){
            return $result;
        }
        if ($param['type'] == 1){
            $list_count       = array_count_values(explode(',', implode(',', $list)));

            foreach ($list_count as $k => $v){
                if($v >= count($list)){
                    $result[] = $k;
                }
            }
        } else{
            $result     = array_unique(explode(',', implode(',', $list)));
        }

        return $result;
    }

    /**
     * 首页统计成员top
     * User: 万奇
     * Date: 2021/1/5 0005
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index_staff_top($param){
        $field      = $param['stat_type'] == 2 ? 'sum(b.deleted_staff)' : 'sum(b.add_customer)';
        $list       = Db::name('wxk_staff')->alias('a')
            ->field('a.name,' . $field . ' num')
            ->join('wxk_live_qr_statistics b', 'a.user_id=b.user_id', 'left')
            ->whereBetweenTime('b.create_at', $param['start_time'], $param['end_time'])
            ->group('a.user_id')
            ->having($field . ' > 0')
            ->order(['num' => 'desc'])
            ->limit(10)
            ->select()
            ->toArray();

        return $list;
    }

    /**
     * 获取成员详情
     * User: 万奇
     * Date: 2021/1/5 0005
     * @param $param
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_staff_info($param){
        $where          = '';
        if (is_exists($param['phone'])){
            $where      = ['mobile' => $param['phone']];
        }

        if (is_exists($param['id'])){
            $where      = ['id' => $param['id']];
        }

        if (!$where){
            return [];
        }

        $result         = $this->where($where)->find();

        if (!$result){
            return [];
        }

        $result                     = $result->toArray();
        $result['section_name']     = $this->section_attr(Db::name('wxk_department')->column('code,name', 'code'), $result['department_id']);
        $result['company_name']     = Db::name('wxk_department')->where(['parent_code' => 0])->value('name');

        return $result;
    }

    /**
     * 企业成员列表
     * User: 万奇
     * Date: 2020/11/26 0026
     * @param $param
     * @param int $limit
     * @return array
     */
    public function get_user_simple_list($param, $limit = 10){
        $where       = [];

        if (is_exists($param['department_id'])){
            $where[] = ['a.id', 'in', implode(',', $this->where("find_in_set({$param['department_id']}, department_id)")->column('id'))];
        }

        if (is_exists($param['staff_id'])){
            $where[] = ['a.user_id', '=', $param['staff_id']];
        }

        if (is_exists($param['status'])){
            $where[] = ['a.status', '=', $param['status']];
        }

        if (is_exists($param['external_authority'], false, true)){
            $where[] = ['a.external_authority', '=', $param['external_authority']];
        }

        // 排序
        if (is_exists($param['order'])){
            $order = 'b.' . $param['time_behavior']. '_' .$param['order'];
        } else{
            $order = ['a.create_at' => 'desc', 'a.status' => 'asc'];
        }

        $field_behavior       = "b.{$param['time_behavior']}_new_apply_cnt new_apply_cnt,b.{$param['time_behavior']}_new_contact_cnt new_contact_cnt,b.{$param['time_behavior']}_chat_cnt chat_cnt,
                                b.{$param['time_behavior']}_message_cnt message_cnt,b.{$param['time_behavior']}_reply_percentage reply_percentage,b.{$param['time_behavior']}_avg_reply_time avg_reply_time,
                                b.create_at behavior_time";

        $list         = $this->alias('a')
            ->field('a.*,' . $field_behavior)
            ->join('wxk_staff_behavior b', 'a.user_id=b.staff_user_id', 'left')
            ->where($where)
            ->order($order)
            ->paginate($limit)
            ->toArray();

        $tag_name     = Db::name('wxk_staff_tag')->column('name', 'code');
        $section      = Db::name('wxk_department')->column('code,name', 'code');

        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['tag_ids']             = get_name_attr($tag_name, $v['tag_ids']);
            $list['data'][$k]['section_name']       = $this->section_attr($section, $v['department_id']);
            $list['data'][$k]['status']             = [1 => '已激活', 2 => '已禁用', 4 => '未激活', 5 => '退出企业'][$v['status']];
        }

        return ['data' => $list['data'], 'count' => $list['total']];
    }

    /**
     * 根据部门获取成员
     * User: 万奇
     * Date: 2021/1/29 0029
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_department_staff($param){
        $where       = [];

        if (is_exists($param['code'])){
            $where[] = ['id', 'in', implode(',', $this->where("find_in_set({$param['code']}, department_id)")->column('id'))];
        }

        $list        = $this->where($where)->select()->toArray();

        return $list;
    }

    /**
     * 获取企业成员最后一次同步时间
     * User: 万奇
     * Date: 2020/12/4 0004
     * @return mixed
     */
    public function get_synchro_staff_date(){
        $result     = $this->order(['create_at' => 'asc'])->limit(1)->value('create_at');

        return $result;
    }

    /**
     * 拼接部门
     * User: 万奇
     * Date: 2020/11/26 0026
     * @param $data
     * @param $value
     * @return string
     */
    public function section_attr($data, $value){
        $section_name       = '';
        $value      = explode(',', $value);

        foreach ($value as $item){
            $section_name   .= $section_name ? '/' .$data[$item]['name'] : $data[$item]['name'];
        }

        return $section_name;
    }

    /**
     * 获取外部联系人权限成员
     * User: 万奇
     * Date: 2020/12/14 0014
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function get_add_live_staff($param){
        $list               = $this->field('user_id,name')->where(['external_authority' => 1])->paginate($param['limit'])->toArray();

        return $list;
    }

    /**
     * 同步成员行为数据
     * User: 万奇
     * Date: 2021/1/21 0021
     * @throws \think\db\exception\DbException
     */
    public function sync_staff_behavior(){
        $wechat                = new Wechat();
        $staff_user_id         = $this->column('user_id');
        $data                  = ['start_time' => strtotime('-29 day'), 'end_time' => time()];
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get_user_behavior_data';

        foreach ($staff_user_id as $k => $v){
            $data['userid']    = [$v];
            $result[$v]        = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $data, true, true);

            $insert[$k]['id']                     = uuid();
            $insert[$k]['staff_user_id']          = $v;

            $day_behavior                         = array_slice($result[$v]['behavior_data'], -1, 1);
            $insert[$k]['day_new_apply_cnt']      = array_sum(array_column($day_behavior, 'new_apply_cnt'));
            $insert[$k]['day_new_contact_cnt']    = array_sum(array_column($day_behavior, 'new_contact_cnt'));
            $insert[$k]['day_chat_cnt']           = array_sum(array_column($day_behavior, 'chat_cnt'));
            $insert[$k]['day_message_cnt']        = array_sum(array_column($day_behavior, 'message_cnt'));
            $insert[$k]['day_reply_percentage']   = isset($day_behavior['reply_percentage']) ? $day_behavior['reply_percentage'] : '';
            $insert[$k]['day_avg_reply_time']     = isset($day_behavior['avg_reply_time']) ? $day_behavior['avg_reply_time'] : '';

            $week_behavior                         = array_slice($result[$v]['behavior_data'], -7, 7);
            $insert[$k]['week_new_apply_cnt']      = array_sum(array_column($week_behavior, 'new_apply_cnt'));
            $insert[$k]['week_new_contact_cnt']    = array_sum(array_column($week_behavior, 'new_contact_cnt'));
            $insert[$k]['week_chat_cnt']           = array_sum(array_column($week_behavior, 'chat_cnt'));
            $insert[$k]['week_message_cnt']        = array_sum(array_column($week_behavior, 'message_cnt'));
            $insert[$k]['week_reply_percentage']   = isset($day_behavior['reply_percentage']) ? $day_behavior['reply_percentage'] : '';
            $insert[$k]['week_avg_reply_time']     = isset($day_behavior['avg_reply_time']) ? $day_behavior['avg_reply_time'] : '';

            $insert[$k]['month_new_apply_cnt']      = array_sum(array_column($result[$v]['behavior_data'], 'new_apply_cnt'));
            $insert[$k]['month_new_contact_cnt']    = array_sum(array_column($result[$v]['behavior_data'], 'new_contact_cnt'));
            $insert[$k]['month_chat_cnt']           = array_sum(array_column($result[$v]['behavior_data'], 'chat_cnt'));
            $insert[$k]['month_message_cnt']        = array_sum(array_column($result[$v]['behavior_data'], 'message_cnt'));
            $insert[$k]['month_reply_percentage']   = isset($day_behavior['reply_percentage']) ? $day_behavior['reply_percentage'] : '';
            $insert[$k]['month_avg_reply_time']     = isset($day_behavior['avg_reply_time']) ? $day_behavior['avg_reply_time'] : '';
        }

        Db::name('wxk_staff_behavior')->delete(true);
        Db::name('wxk_staff_behavior')->insertAll($insert);
    }


    /**
     * 同步配置了外部联系人权限的联系人
     * User: 万奇
     * Date: 2020/12/14 0014
     * @return WxkStaff
     */
    public function synchro_follow_user(){
        $wechat             = new Wechat();
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get_follow_user_list';
        $follow_user        = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', '', true, true);

        if ($follow_user['errcode'] != 0){
            response(500, '操作失败');
        }

        $this->where('1=1')->update(['external_authority' => 0]);

        $result             = $this->where([['user_id', 'in', implode(',', $follow_user['follow_user'])]])->update(['external_authority' => 1]);

        return $result;
    }

    /**
     * 同步企业微信成员
     * User: 万奇
     * Date: 2020/11/26 0026
     * @return bool
     * @throws \Exception
     */
    public function synchro_user(){
        $wechat     = new Wechat();
        $param      = ['department_id' => 1, 'fetch_child' => 1];
        $data       = $wechat->get_user_simple_list(['type' => 'wxk_address_book_secret'], $param);
        if (count($data) == 0){
            return false;
        }

        Db::name('wxk_staff')->delete(true);

        foreach ($data as $k => $v){
            $insert[$k]['id']             = uuid();
            $insert[$k]['user_id']        = $v['userid'];
            $insert[$k]['name']           = $v['name'];
            $insert[$k]['department_id']  = implode(',', $v['department']);
            $insert[$k]['mobile']         = $v['mobile'];
            $insert[$k]['status']         = $v['status'];
            $insert[$k]['gender']         = $v['gender'];
            $insert[$k]['avatar']         = $v['avatar'];
            $insert[$k]['qr_code']        = $v['qr_code'];
        }

        $this->insertAll($insert);

        return true;
    }

}