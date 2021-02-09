<?php
/**
 * 客户crm
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/29 0029
 * Time: 23:15
 */

namespace app\admin\model;


use app\core\Wechat;
use think\facade\Db;

class WxkCustomer extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 首页引流数据统计
     * User: 万奇
     * Date: 2021/1/23 0023
     * @return array
     */
    public function index_drainage_data(){
        $drainage_type      = \StaticData::RESOURCE_NAME['drainage_type'];
        $customer_total     = $this->count();

        $result             = [];
        foreach ($drainage_type as $k => $v){
            $result[$k]['name']     = $v;
            switch ($k){
                case 1 :
                    $result[$k]['count']     = $this->where([['follow_state', '<>', '']])->count();
                    break;
                case 7 :
                    $result[$k]['count']     = $customer_total - $result[1]['count'];
                    break;
                default :
                    $result[$k]['count']     = 0;
                    break;
            }
        }

        return array_values($result);
    }

    /**
     * 客户列表
     * User: 万奇
     * Date: 2020/12/3 0003
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function get_list_customer($param){
        $where        = [];

        if (is_exists($param['keyword'])){
            $where[]    = ['name|follow_remark', 'like', "%{$param['keyword']}%"];
        }

        if (is_exists($param['gender'], false, true)){
            $where[]    = ['gender', '=', $param['gender']];
        }

        if (is_exists($param['follow_remark_mobiles'])){
            $where[]    = ['follow_remark_mobiles', 'like', "%{$param['follow_remark_mobiles']}%"];
        }

        if (is_exists($param['follow_add_way'], false, true)){
            $where[]    = ['follow_add_way', '=', $param['follow_add_way']];
        }

        if (is_exists($param['follow_status'])){
            $where[]    = ['follow_status', '=', $param['follow_status']];
        }

        if (is_exists($param['follow_userid'])){
            $where[]    = ['follow_userid', '=', $param['follow_userid']];
        }

        if (is_exists($param['start_time']) && is_exists($param['end_time'])){
            $where[]    = ['follow_createtime', 'between', [$param['start_time'], $param['end_time']]];
        }

        $list         = $this->where($where)->order(['follow_createtime' => 'desc'])->paginate($param['limit'])->toArray();

        $staff        = Db::name('wxk_staff')->where([['user_id', 'in', implode(',', array_column($list['data'], 'follow_userid'))]])->column('name,department_id', 'user_id');
        $tag_name     = Db::name('wxk_customer_tag')->column('name', 'id');
        $section      = Db::name('wxk_department')->column('code,name', 'code');

        $staff_model  = new WxkStaff();
        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['tag_ids']             = get_name_attr($tag_name, $v['tag_ids']);
            $list['data'][$k]['follow_name']         = $staff[$v['follow_userid']]['name'];
            $list['data'][$k]['follow_section_name'] = $staff_model->section_attr($section, $staff[$v['follow_userid']]['department_id']);
            $list['data'][$k]['follow_add_way']      = $v['follow_add_way'] ? \StaticData::RESOURCE_NAME['follow_add_way'][$v['follow_add_way']] : \StaticData::RESOURCE_NAME['follow_add_way'][0];
            $list['data'][$k]['follow_status']       = \StaticData::RESOURCE_NAME['follow_status'][$v['follow_status']];
        }

        return ['data' => $list['data'], 'count' => $list['total']];
    }

    /**
     * 客户打标签/移除标签
     * User: 万奇
     * Date: 2021/1/15 0015
     * @param $param
     */
    public function customer_tagging($param){
        $wechat             = new Wechat();
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/mark_tag';
        $customer           = $this->where([['id', 'in', implode(',', $param['id'])]])->column('external_user_id,tag_ids,follow_userid', 'id');

        if ($param['type'] == 1){
            foreach ($customer as $k => $v){
                $data               = ['userid' => $v['follow_userid'], 'external_userid' => $v['external_user_id'], 'add_tag' => $param['tag_ids']];
                $mark_tag           = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $data, true, true);

                if ($mark_tag['errcode'] != 0){
                    response(500, '操作失败');
                }

                $update['tag_ids']     = implode(',', $v['tag_ids'] ? array_unique(array_merge(explode(',', $v['tag_ids']), $param['tag_ids'])) : $param['tag_ids']);
                $this->where(['id' => $k])->update($update);
            }
        } else{
            foreach ($customer as $k => $v){
                $data               = ['userid' => $v['follow_userid'], 'external_userid' => $v['external_user_id'], 'remove_tag' => $param['tag_ids']];
                $mark_tag           = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $data, true, true);

                if ($mark_tag['errcode'] != 0){
                    response(500, '操作失败');
                }

                $update['tag_ids']     = implode(',', array_diff(explode(',', $v['tag_ids']), $param['tag_ids']));
                $this->where(['id' => $k])->update($update);
            }
        }

    }

    /**
     * 客户打标签回显已有的标签
     * User: 万奇
     * Date: 2021/1/14 0014
     * @param $param
     * @return array
     */
    public function show_customer_tag($param){
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
     * 重复客户列表
     * User: 万奇
     * Date: 2021/1/14 0014
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function repeat_list_customer($param){
        $where        = [];

        if (is_exists($param['keyword'])){
            $where[]    = ['name|follow_remark', 'like', "%{$param['keyword']}%"];
        }

        if (is_exists($param['gender'], false, true)){
            $where[]    = ['gender', '=', $param['gender']];
        }

        if (is_exists($param['follow_userid'])){
            $where[]    = ['follow_userid', '=', $param['follow_userid']];
        }

        if (is_exists($param['start_time']) && is_exists($param['end_time'])){
            $where[]    = ['follow_createtime', 'between', [$param['start_time'], $param['end_time']]];
        }

        $list         = $this->where($where)->order(['follow_createtime' => 'desc'])->group('external_user_id')->having('count(external_user_id) > 1')->paginate($param['limit'])->toArray();

        $tag_name     = Db::name('wxk_customer_tag')->column('name', 'id');
        $staff        = Db::name('wxk_staff')->column('name,department_id', 'user_id');
        $section      = Db::name('wxk_department')->column('code,name', 'code');

        $staff_model  = new WxkStaff();
        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['follow_info']         = $this->field('tag_ids,follow_userid,follow_remark,follow_createtime')->where(['external_user_id' => $v['external_user_id']])->select()->toArray();
            foreach ($list['data'][$k]['follow_info'] as $f_k => $f_v){
                $list['data'][$k]['follow_info'][$f_k]['follow_section_name']     = $staff_model->section_attr($section, $staff[$f_v['follow_userid']]['department_id']);
                $list['data'][$k]['follow_info'][$f_k]['follow_name']     = $staff[$f_v['follow_userid']]['name'];
            }
            $list['data'][$k]['follow_add_way']      = $v['follow_add_way'] ? \StaticData::RESOURCE_NAME['follow_add_way'][$v['follow_add_way']] : \StaticData::RESOURCE_NAME['follow_add_way'][0];
            $list['data'][$k]['follow_status']       = \StaticData::RESOURCE_NAME['follow_status'][$v['follow_status']];
            $list['data'][$k]['tag_ids']             = get_name_attr($tag_name, implode(',', array_filter(array_column($list['data'][$k]['follow_info'], 'tag_ids'))));
        }

        return ['data' => $list['data'], 'count' => $list['total']];
    }

    /**
     * 客户列表渲染
     * User: 万奇
     * Date: 2021/1/14 0014
     * @return mixed
     */
    public function show_list_customer(){
        $result['gender']           = \StaticData::RESOURCE_NAME['gender'];
        $result['follow_add_way']   = \StaticData::RESOURCE_NAME['follow_add_way'];
        $result['follow_status']    = \StaticData::RESOURCE_NAME['follow_status'];

        $result['all_num']          = $this->count();
        $result['actual_num']       = $this->group('external_user_id')->count();

        return $result;
    }

    /**
     * 同步企业微信客户
     * User: 万奇
     * Date: 2020/12/3 0003
     */
    public function synchro_customer(){
        $wechat             = new Wechat();
        $staff_user_id      = Db::name('wxk_staff')->column('user_id');

        Db::name('wxk_customer')->delete(true);

        foreach ($staff_user_id as $k => $v){
            $param      = ['userid' => $v];
            $list       = $wechat->get_client_all_info(['type' => 'wxk_customer_admin_secret'], $param);
            $data       = [];

            if (count($list)){
                foreach ($list as $i_k => $i_v){
                    $data[$i_k]['id']                   = uuid();
                    $data[$i_k]['external_user_id']     = $i_v['external_contact']['external_userid'];
                    $data[$i_k]['name']                 = $i_v['external_contact']['name'];
                    $data[$i_k]['avatar']               = $i_v['external_contact']['avatar'];
                    $data[$i_k]['customer_type']        = $i_v['external_contact']['type'];
                    $data[$i_k]['gender']               = $i_v['external_contact']['gender'];
                    $data[$i_k]['tag_ids']              = isset($i_v['follow_info']['tag_id']) ? implode(',', $i_v['follow_info']['tag_id']) : '';
                    $data[$i_k]['follow_userid']        = $param['userid'];
                    $data[$i_k]['follow_remark']        = $i_v['follow_info']['remark'];
                    $data[$i_k]['follow_createtime']    = format_time($i_v['follow_info']['createtime']);
                    $data[$i_k]['follow_remark_mobiles']= count($i_v['follow_info']['remark_mobiles']) ? implode(',', $i_v['follow_info']['remark_mobiles']) : '';
                    $data[$i_k]['follow_add_way']       = isset($i_v['follow_info']['add_way']) ? $i_v['follow_info']['add_way'] : '';
                    $data[$i_k]['follow_oper_userid']   = isset($i_v['follow_info']['oper_userid']) ? $i_v['follow_info']['oper_userid'] : '';
                    $data[$i_k]['follow_state']         = isset($i_v['follow_info']['state']) ? $i_v['follow_info']['state'] : '';
                }
                $this->insertAll($data);
            }
        }
    }

}