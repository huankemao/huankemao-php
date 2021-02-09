<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/12/4 0004
 * Time: 16:23
 */

namespace app\admin\model;


use app\core\Wechat;
use think\facade\Db;

class WxkCustomerTag extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 编辑删除客户标签
     * User: 万奇
     * Date: 2021/1/18 0018
     * @param $param
     * @throws \think\db\exception\DbException
     */
    public function edit_customer_tag($param){
        $wechat             = new Wechat();
        // 编辑
        if ($param['type'] == 1){
            param_receive([ 'name', 'parent_code']);
            $tag_info       = $this->where(['id' => $param['id']])->find();
            // 改名
            if ($tag_info['name'] != $param['name']){
                $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/edit_corp_tag';
                $result             = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', ['id' => $param['id'], 'name' => $param['name']], true, true);

                if ($result['errcode'] != 0){
                    response(500, '操作失败');
                }
                $this->where(['id' => $param['id']])->update(['name' => $param['name']]);
            }
            // 改组
            if ($tag_info['parent_code'] != $param['parent_code']){
                $group_id       = $this->where(['code' => $param['parent_code']])->value('id');
                $add_data       = ['group_id' => $group_id, 'tag' => [['name' => $param['name']]]];
                $tag_list       = $this->add_customer_tag($add_data, false);
                $this->_update_customer_tag($tag_list['tag'], [['id' => $param['id'], 'name' => $tag_info['name']]]);

                $del_url        = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/del_corp_tag';
                $result         = $wechat->request_wechat_api($del_url, 'wxk_customer_admin_secret', ['tag_id' => $param['id']], true, true);

                if ($result['errcode'] != 0){
                    response(500, '操作失败');
                }

                $this->where(['id' => $param['id']])->delete();
            }
        } else{
         // 删除
            $del_url        = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/del_corp_tag';
            $result         = $wechat->request_wechat_api($del_url, 'wxk_customer_admin_secret', ['tag_id' => $param['id']], true, true);

            if ($result['errcode'] != 0){
                response(500, '操作失败');
            }

            foreach ($param['id'] as $v){
                Db::name('wxk_customer')->where("find_in_set('{$v}', tag_ids)")
                    ->exp('tag_ids', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', tag_ids, ','), concat(',','{$v}',','), ','))")
                    ->update();
                Db::name('wxk_live_qr')->where("find_in_set('{$v}', tag_ids)")
                    ->exp('tag_ids', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', tag_ids, ','), concat(',','{$v}',','), ','))")
                    ->update();
            }

            $this->where([['id', 'in', implode(',', $param['id'])]])->delete();
        }
    }

    /**
     * 编辑删除标签组
     * User: 万奇
     * Date: 2021/1/15 0015
     * @param $param
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit_customer_tag_group($param){
        $wechat             = new Wechat();
        if ($param['type'] == 1){
            param_receive([ 'name']);
            $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/edit_corp_tag';
            $data               = ['id' => $this->where(['code' => $param['code']])->value('id'), 'name' => $param['name']];
            $result             = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $data, true, true);

            if ($result['errcode'] != 0 && $result['errcode'] != 40071){
                response(500, '操作失败');
            }

            $this->where(['code' => $param['code']])->update(['name' => $param['name']]);
        } else{
            $tag_id         = $this->where(['parent_code' => $param['code']])->column('code');
            $code           = $this->where(['name' => '未分组', 'parent_code' => 0])->find();

            // 新增标签并修改客户标签修改企业微信客户标签
            if (count($tag_id)){
                $add_data       = ['group_id' => $code['id'], 'tag' => $this->field('name')->where([['code', 'in', implode(',', $tag_id)]])->select()->toArray()];
                $tag_list       = $this->add_customer_tag($add_data, false);
                $this->_update_customer_tag($tag_list['tag'], $this->field('id,name')->where([['code', 'in', implode(',', $tag_id)]])->select()->toArray());

                $del_url        = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/del_corp_tag';
                $result         = $wechat->request_wechat_api($del_url, 'wxk_customer_admin_secret', ['group_id' => $this->where(['code' => $param['code']])->value('id')], true, true);

                if ($result['errcode'] != 0){
                    response(500, '操作失败');
                }
            }

            $this->where([['code', 'in', implode(',', $tag_id)]])->whereOr(['code' => $param['code']])->delete();
        }
    }

    /**
     * 新增标签并修改客户标签修改企业微信客户标签
     * User: 万奇
     * Date: 2021/1/19 0019
     * @param $list
     * @param $data
     * @throws \think\db\exception\DbException
     */
    public function _update_customer_tag($list, $data){
        $wechat             = new Wechat();
        $list               = array_column($list, 'id', 'name');

        foreach ($data as $k => $v){
            $customer       = Db::name('wxk_customer')->where("find_in_set('{$v['id']}', tag_ids)")->column('external_user_id,follow_userid,tag_ids');
            $url            = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/mark_tag';

            foreach ($customer as $c_k => $c_v){
                $mark_data          = ['userid' => $c_v['follow_userid'], 'external_userid' => $c_v['external_user_id'], 'add_tag' => [$list[$v['name']]]];
                $mark_tag           = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $mark_data, true, true);

                if ($mark_tag['errcode'] != 0){
                    response(500, '操作失败');
                }

                $update['tag_ids']     = implode(',', array_unique(array_merge(explode(',', $c_v['tag_ids']), [$list[$v['name']]])));
                Db::name('wxk_customer')->where(['external_user_id' => $c_v['external_user_id']])->update($update);
            }

            $live_qr       = Db::name('wxk_live_qr')->where("find_in_set('{$v['id']}', tag_ids)")->column('id,tag_ids');

            foreach ($live_qr as $l_k => $l_v){
                $live_qr_update['tag_ids']     = implode(',', array_unique(array_merge(explode(',', $l_v['tag_ids']), [$list[$v['name']]])));
                Db::name('wxk_live_qr')->where(['id' => $l_v['id']])->update($live_qr_update);
            }

            Db::name('wxk_customer')->where("find_in_set('{$v['id']}', tag_ids)")
                ->exp('tag_ids', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', tag_ids, ','), concat(',','{$v['id']}',','), ','))")
                ->update();
            Db::name('wxk_live_qr')->where("find_in_set('{$v['id']}', tag_ids)")
                ->exp('tag_ids', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', tag_ids, ','), concat(',','{$v['id']}',','), ','))")
                ->update();
        }
    }

    /**
     * 新增标签
     * User: 万奇
     * Date: 2020/12/9 0009
     * @param $param
     * @param $is_repeat
     * @return int
     */
    public function add_customer_tag($param, $is_repeat = true){
        $wechat             = new Wechat();
        if (empty($param['tag'][0]['name'])){
            response(500, '标签不能为空');
        }

        $is_name            = $this->where([['name', 'in', implode(',',array_column($param['tag'],'name'))], ['parent_code', '>', 0]])->count();
        if ($is_repeat && $is_name){
            response(500, '标签名重复');
        }

        $data['tag']        = $param['tag'];
        $is_update_id       = 0;
        if (isset($param['group_name'])){
            $this->where(['name' => $param['group_name'], 'parent_code' => 0])->count() ? response(500, '标签组重复') : $data['group_name'] = $param['group_name'];
        } else{
            $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get_corp_tag_list';
            $get_tag            = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', ['group_id' => [$param['group_id']]], true, true);

            if ($get_tag['errcode'] == 0 && !$get_tag['tag_group'][0]['deleted']){
                $data['group_id']       = $param['group_id'];
            }else{
                $param['group_name']    = $this->where(['id' => $param['group_id'], 'parent_code' => 0])->value('name');
                $data['group_name']     = $param['group_name'];
                $is_update_id           = 1;
            }
        }

        // 新增企业微信数据
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/add_corp_tag';
        $list               = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', $data, true, true);

        if ($list['errcode'] == 40071){
            response(500, '标签重复');
        }

        if ($list['errcode'] != 0){
            response(500, '操作失败');
        }

        // 新增数据
        $list                             = $list['tag_group'];
        if (isset($param['group_name']) && !$is_update_id){
            $data_group['id']             = $list['group_id'];
            $data_group['name']           = $list['group_name'];
            $data_group['parent_code']    = 0;
            $group_id                     = $this->insertGetId($data_group);
        } else{
            $group_id                     = $this->where(['id' => $param['group_id']])->value('code');

            if ($is_update_id){
                $this->where(['id' => $param['group_id']])->update(['id' => $list['group_id']]);
            }
        }

        foreach ($list['tag'] as $k => $v){
            $insert[$k]['id']             = $v['id'];
            $insert[$k]['name']           = $v['name'];
            $insert[$k]['parent_code']    = $group_id;
        }

        $this->insertAll($insert);

        return $list;
    }

    /**
     * 获取客户标签树结构
     * User: 万奇
     * Date: 2020/12/8 0008
     * @return array|mixed
     */
    public function get_customer_tag_tree(){
        $list       = array_grouping($this->orderRaw("name='未分组' desc")->order(['create_at' => 'asc'])->select()->toArray(), 'parent_code');

        if (!count($list)){
            response(200);
        }

        $list       = category_group($list, $list[0], 'group', 'code');

        return $list;
    }

    /**
     * 获取客户标签
     * User: 万奇
     * Date: 2020/12/4 0004
     * @param $param
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function get_customer_tag($param, $limit = 10){
        $where          = [];

        if (is_exists($param['code'])){
            $where[]    = ['parent_code', '=', $param['code']];
        } else{
            $where[]    = ['parent_code', '>', 0];
        }

        if (is_exists($param['keyword'])){
            $where[]    = ['name', 'like', "%{$param['keyword']}%"];
        }

        $list         = $this->where($where)->order(['create_at' => 'asc'])->paginate($limit)->toArray();

        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['customer_num']       = Db::name('wxk_customer')->where("find_in_set('{$v['id']}', tag_ids)")->count();
        }

        return ['data' => $list['data'], 'count' => $list['total']];
    }

    /**
     * 获取客户标签组
     * User: 万奇
     * Date: 2020/12/4 0004
     * @return mixed
     */
    public function get_customer_tag_group(){
        $list         = $this->where(['parent_code' => 0])->orderRaw("name='未分组' desc")->order(['create_at' => 'asc'])->select()->toArray();
        return $list;
    }

    /**
     * 客户标签同步
     * User: 万奇
     * Date: 2020/12/4 0004
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function synchro_customer_tag(){
        $wechat             = new Wechat();
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get_corp_tag_list';
        $list               = $wechat->request_wechat_api($url, 'wxk_customer_admin_secret', [], false)['tag_group'];

        $is_group           = $this->where(['name' => '未分组'])->count();

        if (!count($list)){
            if (!$is_group){
                $this->add_customer_tag(['group_name' => '未分组', 'tag' => [['name' => '未分组']]]);
            }
            return false;
        }

        Db::name('wxk_customer_tag')->delete(true);

        foreach ($list as $k => $v){
            $data_group['id']             = $v['group_id'];
            $data_group['name']           = $v['group_name'];
            $data_group['parent_code']    = 0;
            $group_id                     = $this->insertGetId($data_group);

            $data   = [];
            foreach ($list[$k]['tag'] as $t_k => $t_v){
                $data[$t_k]['id']             = $t_v['id'];
                $data[$t_k]['name']           = $t_v['name'];
                $data[$t_k]['parent_code']    = $group_id;

            }
            $this->insertAll($data);
        }

        // 初始化未分组数据
        $is_group           = $this->where(['name' => '未分组'])->count();
        if (!$is_group){
            $this->add_customer_tag(['group_name' => '未分组', 'tag' => [['name' => '未分组']]]);
        }

    }

}