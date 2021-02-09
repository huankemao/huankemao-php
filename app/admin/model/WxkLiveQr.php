<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/12/9 0009
 * Time: 20:59
 */

namespace app\admin\model;


use app\core\Wechat;
use think\facade\Db;

class WxkLiveQr extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 活码统计客户属性
     * User: 万奇
     * Date: 2020/12/24 0024
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_stat_attribute($param){
        $where          = [];
        if (is_exists($param['group_code'])){
            $where[]    = ['follow_state', 'in', implode(',', Db::name('wxk_live_qr')->where(['group_id' => $param['group_code']])->column('name'))];
        } else{
            $where[]    = ['follow_state', '<>', ''];
        }

        $live_qr_number = Db::name('wxk_customer')->where($where)->whereBetweenTime('follow_createtime', $param['start_time'], $param['end_time'])->count();

        $total_number   = Db::name('wxk_customer')->whereBetweenTime('follow_createtime', $param['start_time'], $param['end_time'])->count();

        $during         = ['add_num' => $live_qr_number, 'ratio' => $live_qr_number ? round(($live_qr_number / $total_number) * 100, 2) : 0];


        $list           = Db::name('wxk_customer')->field('follow_state, count(id) num')
            ->where($where)
            ->whereBetweenTime('follow_createtime', $param['start_time'], $param['end_time'])
            ->group('follow_state')
            ->select()
            ->toArray();

        foreach ($list as $k => $v){
            $list[$k]['ratio']      = round(($v['num'] / $total_number) * 100, 2);
        }

        // 性别统计
        $gender         = array_column(Db::name('wxk_customer')->field('gender, count(id) num')
            ->where($where)
            ->whereBetweenTime('follow_createtime', $param['start_time'], $param['end_time'])
            ->group('gender')
            ->select()
            ->toArray(), 'num', 'gender');

        $gender_info        = ['未知' => 0, '男' => 0, '女' => 0,];
        foreach ($gender as $g_k => $g_v){
            $gender_info[['未知', '男', '女'][$g_k]]   = round(($g_v / $total_number) * 100, 2);
        }

        $gender_list    = Db::name('wxk_customer')->field("date_format(follow_createtime,'%Y-%m-%d') as created_at," . 'count(gender = 0 OR NULL) as unknown_num,count(gender = 1 OR NULL) as male_num,count(gender = 2 OR NULL) as female_num')
            ->where($where)
            ->whereBetweenTime('follow_createtime', $param['start_time'], $param['end_time'])
            ->group('created_at')
            ->order(['created_at' => 'asc'])
            ->select()
            ->toArray();

        $gender_list    = $this->date_group_list($gender_list, $param['start_time'], $param['end_time']);

        return ['during' => $during, 'add_customer_list' => $list, 'gender_info' => $gender_info, 'gender_list' => $gender_list];
    }

    /**
     * 日期分组
     * User: 万奇
     * Date: 2020/12/24 0024
     * @param $data
     * @param $start_time
     * @param $end_time
     * @return mixed
     */
    public function date_group_list($data, $start_time, $end_time){
        $data_dates = array_column($data, 'created_at');
        $count = Date::getDateFromRange($start_time, $end_time);
        foreach ($count as $v) {
            if (!in_array($v, $data_dates)) {
                array_push($data, ['created_at' => $v, 'unknown_num' => 0, 'male_num' => 0, 'female_num' => 0]);
            }
        }

        return $data;
    }

    /**
     * 活码统计top10
     * User: 万奇
     * Date: 2020/12/23 0023
     * @param $param
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_stat_top($param){
        $where      = [];

        if (is_exists($param['group_code'])){
            $where[]        = ['a.group_id', 'in', $this->get_live_qr_group_id($param['group_code'])];
        }

        switch ($param['stat_type']){
            case 1 :
                $field      = 'sum(add_customer) num';
                break;
            case 2 :
                $field      = 'sum(deleted_staff) num';
                break;
            case 3 :
                $field      = 'sum(deleted_customer) num';
                break;
            case 4 :
                $field      = '(sum(add_customer) - sum(deleted_staff)) num';
                break;
            default :
                $field      = 'sum(add_customer) num';
                break;
        }

        $list = $this->alias('a')
            ->field('a.id,a.name,c.name group_name,' . $field)
            ->join('wxk_live_qr_statistics b', 'a.id=b.live_qr_id and b.add_type=2', 'left')
            ->join('wxk_live_qr_group c', 'a.group_id=c.id', 'left')
            ->where($where)
            ->whereBetweenTime('b.create_at', $param['start_time'], $param['end_time'])
            ->group('a.id')
            ->order(['num' => 'desc'])
            ->limit(10)
            ->select()
            ->toArray();

        return $list;
    }

    /**
     * 删除活码
     * User: 万奇
     * Date: 2020/12/11 0011
     * @param $param
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete_live_qr($param){
        // 删除企业微信联系我
        $wechat             = new Wechat();
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/del_contact_way';
        $data               = ['config_id' => $param['id']];
        $live_qr            = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $data, true, true);

        if ($live_qr['errcode'] != 0){
            response(500, '操作失败');
        }

        // 活码组自减数量
        $group_id           = $this->where(['id' => $param['id']])->value('group_id');
        $this->add_group_amount(-1, $group_id);

        // 删除活码
        $result     = $this->where(['id' => $param['id']])->delete();

        return $result;
    }

    /**
     * 活码移动分组
     * User: 万奇
     * Date: 2020/12/11 0011
     * @param $param
     * @return WxkLiveQr
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function live_qr_group_move($param){
        $count     = $this->where([['id', 'in', $param['id']]])->update(['group_id' => $param['group_id']]);

        if ($count){
            // 活码组自增数量
            $this->add_group_amount($count, $param['group_id']);
        }

        return $count;
    }

    /**
     * 活码列表客户信息
     * User: 万奇
     * Date: 2020/12/11 0011
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function get_live_qr_customer($param){
        $live_name          = $this->where(['id' => $param['id']])->value('name');

        $where              = [];
        if (is_exists($param['keyword'])){
            $where[]        = ['b.name', 'like', "%{$param['keyword']}%"];
        }

        $list               = Db::name('wxk_customer')->alias('a')
            ->field('a.avatar,a.name,a.gender,a.follow_createtime,b.name staff_name,b.department_id')
            ->join('wxk_staff b', 'a.follow_userid=b.user_id', 'left')
            ->where(['a.follow_state' => $live_name])
            ->paginate($param['limit'])
            ->toArray();

        $section      = Db::name('wxk_department')->column('code,name', 'code');
        $staff_model  = new WxkStaff();

        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['section_name']       = $staff_model->section_attr($section, $v['department_id']);
        }

        return $list;
    }

    /**
     * 活码列表预览成员
     * User: 万奇
     * Date: 2020/12/11 0011
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_staff($id){
        $info                   = $this->where(['id' => $id])->find();

        $staff_info             = Db::name('wxk_staff')->column('name,user_id,avatar', 'user_id');
        $department_info        = Db::name('wxk_department')->column('name,code', 'code');

        $result['staff']        = !empty($info['wxk_staff_id']) ? $this->staff_name_attr($staff_info, $info['wxk_staff_id']) : [];
        $result['department']   = !empty($info['wxk_department_id']) ? $this->name_attr($department_info, $info['wxk_department_id']) : [];

        return $result;
    }

    /**
     * 批量获取活码
     * User: 万奇
     * Date: 2020/12/17 0017
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function batch_live_qr_list($param){
        $where      = [];
        if (is_exists($param['group_code'])){
            $where[]        = ['group_id', 'in', $this->get_live_qr_group_id($param['group_code'])];
        }

        $list       = $this->where($where)->order(['create_at' => 'desc'])->column('id,qr_code');

        return $list;
    }

    /**
     * 新增编辑活码回显
     * User: 万奇
     * Date: 2020/12/18 0018
     * @param $param
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show_live_qr($param){
        $param['limit']     = 1;
        $info               = $this->get_live_qr_list($param)['data'][0];

        $info['add_limit']  = Db::name('wxk_live_qr_add_limit')->alias('a')
            ->field('a.user_id,b.name user_name,a.add_limit num')
            ->join('wxk_staff b', 'a.user_id=b.user_id', 'left')
            ->where(['a.live_qr_id' => $param['id']])
            ->select()
            ->toArray();

        return $info;
    }

    /**
     * 活码列表
     * User: 万奇
     * Date: 2020/12/10 0010
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function get_live_qr_list($param){
        $where      = [];

        if (is_exists($param['id'])){
            $where[]        = ['id', '=', $param['id']];
        }

        if (is_exists($param['code_type'])){
            $where[]        = ['code_type', '=', $param['code_type']];
        }

        if (is_exists($param['keyword'])){
            $where[]        = ['name', 'like', "%{$param['keyword']}%"];
        }

        if (is_exists($param['group_code'])){
            $where[]        = ['group_id', 'in', $this->get_live_qr_group_id($param['group_code'])];
        }

        $list       = $this->where($where)->order(['create_at' => 'desc'])->paginate($param['limit'])->toArray();

        $group_name = Db::name('wxk_live_qr_group')->column('id,name', 'id');

        $tag_name   = Db::name('wxk_customer_tag')->column('id,name', 'id');

        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['code_type']      = $v['code_type'] == 1 ? '单人' : '多人';
            $list['data'][$k]['group_name']     = $group_name[$v['group_id']]['name'];
            $list['data'][$k]['welcome_data']   = json_decode($v['welcome_data'], true);
            $list['data'][$k]['tag_name']       = $v['tag_ids'] ? $this->name_attr($tag_name, $v['tag_ids']) : [];
            $list['data'][$k]['customer_num']   = Db::name('wxk_customer')->where(['follow_state' => $v['name']])->count();
            $list['data'][$k]['staff_num']      = count(explode(',', $v['wxk_staff_id']));
        }

        return $list;
    }

    /**
     * 获取活码所有组ID
     * User: 万奇
     * Date: 2020/12/14 0014
     * @param $code
     * @param string $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_group_id($code, $id = ''){
        $parent_code       = Db::name('wxk_live_qr_group')->where(['parent_code' => $code])->find();

        if ($parent_code['code']){
            $id                .= $parent_code['id'] . ',';
            $this->get_live_qr_group_id($parent_code['code'], $id);
        }

        return $id . Db::name('wxk_live_qr_group')->where(['code' => $code])->value('id');
    }

    /**
     * 成员数据转换
     * User: 万奇
     * Date: 2020/12/10 0010
     * @param $data
     * @param $value
     * @return array
     */
    public function staff_name_attr($data, $value){
        $result         = [];
        $value          = explode(',', $value);

        foreach ($value as $k => $item){
            $result[$k]['name']   = $data[$item]['name'];
            $result[$k]['avatar']   = $data[$item]['avatar'];
        }

        return $result;
    }

    /**
     * 数据转换
     * User: 万奇
     * Date: 2020/12/10 0010
     * @param $data
     * @param $value
     * @return array
     */
    public function name_attr($data, $value){
        $result         = [];
        $value          = explode(',', $value);

        foreach ($value as $item){
            $result[]   = $data[$item]['name'];
        }

        return $result;
    }

    /**
     * 批量编辑活码成员上限
     * User: 万奇
     * Date: 2020/12/22 0022
     * @param $param
     * @throws \think\db\exception\DbException
     */
    public function edit_batch_add_limit($param){
        foreach ($param as $k => $v){
            if ($v['is_add_limit'] == 0){
                // 关闭成员添加上限
                $this->where(['id' => $v['id']])->update(['is_add_limit' => 0, 'spare_staff_id' => '']);
                Db::name('wxk_live_qr_add_limit')->where(['live_qr_id' => $v['id']])->delete();
            }else{
                // 开启成员添加上限
                $this->where(['id' => $v['id']])->update(['is_add_limit' => 1, 'spare_staff_id' => $v['spare_staff_id']]);
                Db::name('wxk_live_qr_add_limit')->where(['live_qr_id' => $v['id']])->delete();
                $insert         = [];
                foreach ($v['add_limit_list'] as $a_k => $a_v){
                    $insert[$a_k]['id']           = uuid();
                    $insert[$a_k]['live_qr_id']   = $v['id'];
                    $insert[$a_k]['user_id']      = $a_v['user_id'];
                    $insert[$a_k]['add_limit']    = $a_v['add_limit'];
                }

                Db::name('wxk_live_qr_add_limit')->insertAll($insert);
            }
        }
    }

    /**
     * 回显批量修改成员上限
     * User: 万奇
     * Date: 2020/12/21 0021
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show_batch_add_limit($param){
        $list       = $this->field('id,name,is_add_limit,spare_staff_id,wxk_staff_id')->where([['id', 'in', $param['id']]])->order(['create_at' => 'asc'])->select()->toArray();

        $add_limit  = array_grouping(Db::name('wxk_live_qr_add_limit')->alias('a')
            ->join('wxk_staff b','a.user_id=b.user_id', 'left')
            ->field('a.live_qr_id,a.user_id,b.name,a.add_limit')
            ->where([['a.live_qr_id', 'in', $param['id']]])
            ->select()->toArray(), 'live_qr_id');

        $staff      = Db::name('wxk_staff')->field('user_id,name')->where([['user_id', 'in', implode(',', array_column($list, 'wxk_staff_id'))]])->whereOr([['user_id', 'in', implode(',', array_column($list, 'spare_staff_id'))]])->column('user_id,name', 'user_id');

        foreach ($list as $k => $v){
            $list[$k]['add_limit_list']     = $v['is_add_limit'] ? $add_limit[$v['id']] : $this->staff_attr($staff, $v['wxk_staff_id']);
            $list[$k]['spare_staff_id_name']     = $v['spare_staff_id'] ? $staff[$v['spare_staff_id']]['name'] : '';
        }

        return $list;
    }

    /**
     * 组装上限成员
     * User: 万奇
     * Date: 2020/12/21 0021
     * @param $data
     * @param $user_id
     * @return array
     */
    public function staff_attr($data, $user_id){
        $arr        = [];
        foreach (explode(',', $user_id) as $k => $v){
            $arr[$k]['user_id']     = $data[$v]['user_id'];
            $arr[$k]['name']        = $data[$v]['name'];
            $arr[$k]['add_limit']   = 100;
        }

        return $arr;
    }

    /**
     * 批量编辑活码成员
     * User: 万奇
     * Date: 2020/12/18 0018
     * @param $param
     * @throws \think\db\exception\DbException
     */
    public function edit_batch_live_qr_staff($param){
        $wechat                 = new Wechat();
        $url                    = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/update_contact_way';
        $update                 = [];

        if (is_exists($param['wxk_staff_id'])){
            $data['user']       = explode(',', $param['wxk_staff_id']);
            $update['wxk_staff_id']     = $param['wxk_staff_id'];
        }
        if (is_exists($param['wxk_department_id'])){;
            $data['party']      = explode(',', $param['wxk_department_id']);
            $update['wxk_department_id']     = $param['wxk_department_id'];
        }

        if (!isset($data)){
            response(500, '成员或者部门不能同时为空');
        }

        foreach (explode(',', $param['id']) as $k => $v){
            Db::name('wxk_live_qr')->where(['id' => $param['id']])->update($update);

            $data['config_id']  = $v;
            $live_qr            = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $data, true, true);

            if ($live_qr['errcode'] == 41054){
                response(500, '选择的成员包含了未激活的成员，创建联系我成员必须是在企业微信激活且已经过实名认证的');
            }

            if ($live_qr['errcode'] != 0){
                response(500, '操作失败');
            }
        }
    }

    /**
     * 批量编辑欢迎语
     * User: 万奇
     * Date: 2020/12/18 0018
     * @param $param
     * @return WxkLiveQr
     */
    public function edit_batch_live_qr_welcome($param){
        $result     = $this->where([['id', 'in', $param['id']]])->update(['welcome_data' => $_POST['welcome_data']]);
        return $result;
    }

    /**
     * 编辑活码时处理成员添加上限
     * User: 万奇
     * Date: 2021/1/13 0013
     * @param $wxk_staff_id
     * @param $spare_staff_id
     * @param $live_qr_name
     * @param $add_limit
     * @return array
     */
    public function is_spare_staff($wxk_staff_id, $spare_staff_id, $live_qr_name, $add_limit){
        if ($wxk_staff_id == []){
            return $wxk_staff_id;
        }

        $result             = [];
        $add_limit          = array_column($add_limit, 'num', 'user_id');

        foreach ($wxk_staff_id as $k => $v){
            $customer_count = Db::name('wxk_customer')->where(['follow_state' => $live_qr_name, 'follow_userid' => $v])->count();

            $customer_count < $add_limit[$v] ? $result[]  = $v : $result[]   = $spare_staff_id;
        }

        return $result;
    }

    /**
     * 新增编辑活码
     * User: 万奇
     * Date: 2020/12/10 0010
     * @param $param
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function add_live_qr($param){
        // 新增企业微信联系我
        $data                = [
            'type'           => $param['code_type'],
            'scene'          => 2,
            'remark'         => $param['name'],
            'skip_verify'    => $param['is_add_friends'] == 1 ? true : false,
            'state'          => $param['name'],
            'user'           => isset($param['wxk_staff_id']) ? explode(',', $param['wxk_staff_id']) : [],
            'party'          => isset($param['wxk_department_id']) ? explode(',', $param['wxk_department_id']) : [],
        ];

        $url                        = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/add_contact_way';
        if (is_exists($param['id'])){
            $data['config_id']      = $param['id'];
            $url                    = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/update_contact_way';
            if ($param['is_add_limit'] == 1){
                $data['user']       = $this->is_spare_staff($data['user'], $param['spare_staff_id'], $param['name'], $param['add_limit']);
            }
            unset($data['type'], $data['scene'], $data['remark']);
        }

        $wechat             = new Wechat();
        $live_qr            = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $data, true, true);

        if ($live_qr['errcode'] == 41054 || $live_qr['errcode']== 40098){
            response(500, '选择的成员包含了未激活的成员，创建联系我成员必须是在企业微信激活且已经过实名认证的');
        }

        if ($live_qr['errcode'] != 0){
            response(500, '操作失败');
        }

        $param['welcome_data']  = isset($param['welcome_data']) ? json_encode($_POST['welcome_data']) : '';
        if (is_exists($param['id'])){
            // 编辑活码
            unset($param['code_type'], $param['name']);
            $this->update($param);

            if ($param['is_add_limit'] == 0){
                Db::name('wxk_live_qr_add_limit')->where(['live_qr_id' => $param['id']])->delete();
            }
            // 编辑成员添加上限
            if (isset($param['add_limit'])){
                Db::name('wxk_live_qr_add_limit')->where(['live_qr_id' => $param['id']])->delete();
                $add_limit      = [];
                foreach ($param['add_limit'] as $k => $v){
                    $add_limit[$k]['id']            = uuid();
                    $add_limit[$k]['live_qr_id']    = $param['id'];
                    $add_limit[$k]['user_id']       = $v['user_id'];
                    $add_limit[$k]['add_limit']     = $v['num'];
                }

                Db::name('wxk_live_qr_add_limit')->insertAll($add_limit);
            }

        } else{
            // 新增活码
            $param['id']            = $live_qr['config_id'];
            $param['qr_code']       = $live_qr['qr_code'];
            $this->save($param);

            // 成员添加上限
            if ($param['is_add_limit']){
                $add_limit      = [];
                foreach ($param['add_limit'] as $k => $v){
                    $add_limit[$k]['id']            = uuid();
                    $add_limit[$k]['live_qr_id']    = $live_qr['config_id'];
                    $add_limit[$k]['user_id']       = $v['user_id'];
                    $add_limit[$k]['add_limit']     = $v['num'];
                }

                Db::name('wxk_live_qr_add_limit')->insertAll($add_limit);
            }
        }

        return $param['id'];
    }

    /**
     * 上传活码素材
     * User: 万奇
     * Date: 2021/1/28 0028
     * @param $param
     * @param $user_id - 子账户ID
     * @throws \think\db\exception\DbException
     * @return mixed
     */
    public function upload_qr_code($param, $user_id){
        if ($param['upload_type'] == 1){
            return $param;
        }

        $qr_code            = $this->where(['id' => $param['id']])->find()->toArray();
        $param['url']       = $qr_code['qr_code'];
        $param['name']      = $qr_code['name'];

        $image  = file_get_contents($param['url']);
        $path   = './static/photo/'. $param['name'] . '.jpg';
        file_put_contents(realpath('./static/photo') . '/' .$param['name'] . '.jpg', $image);
        $content_model      = new CmsContentEngine();
        $result             = $content_model->upload_qy_material($path, $param['name'], 'image');

        // 新增活码素材
        if ($param['upload_type'] == 2){
            $group_id       = Db::name('cms_content_group')->where(['name' => '渠道活码'])->value('id');
            if (!$group_id){
                $group_id   = Db::name('cms_content_group')->insertGetId(['id' => uuid(), 'name' => '渠道活码', 'parent_id' => 0, 'purview' => 1]);
            }

            $phone          = Db::name('sys_user')->where(['id' => $user_id])->value('phone');
            $insert         = ['id' => uuid(), 'user_id' => $user_id, 'content' => $param['url'], 'file_name' => $param['name'] . '.jpg', 'file_suffix' => 'JPG',
                            'content_group_id' => $group_id, 'type' => 2, 'source' => 1, 'user' => $phone, 'media_id' => $result['media_id'],
                            'created_at' => $result['created_at']];
            Db::name('cms_content_engine')->insert($insert);
        }

        // 更新素材 media_id
        if ($param['upload_type'] == 3){
            $id             = Db::name('cms_content_engine')->where(['content' => $param['url'], 'type' => 2])->value('id');
            Db::name('cms_content_engine')->where(['id' => $id])->update(['media_id' => $result['media_id']]);
        }
        return $result;
    }

    /**
     * 活码分组自增活码数量
     * User: 万奇
     * Date: 2020/12/11 0011
     * @param $amount
     * @param null $group_id
     * @param null $group_code
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add_group_amount($amount, $group_id = null, $group_code = null){
        $where      = $group_id ? ['id' => $group_id] : ['code' => $group_code];
        $info       = Db::name('wxk_live_qr_group')->where($where)->find();
        Db::name('wxk_live_qr_group')->where(['code' => $info['code']])->inc('amount', $amount)->update();

        if ($info['parent_code'] != 0){
            $this->add_group_amount($amount, null, $info['parent_code']);
        }
    }

}