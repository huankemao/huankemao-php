<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/22 0022
 * Time: 13:57
 */

namespace app\admin\model;


use app\core\Wechat;
use think\facade\Db;

class WxkStaffTag extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 获取成员标签列表
     * User: 万奇
     * Date: 2021/1/26 0026
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function get_staff_tag_list($param){
        $where         = [];

        if (is_exists($param['group_id'])){
            $where[]    = ['group_id', '=', $param['group_id']];
        }

        if (is_exists($param['keyword'])){
            $where[]    = ['name', 'like', "%{$param['keyword']}%"];
        }

        $list           = $this->where($where)->order(['create_at' => 'asc'])->paginate($param['limit'])->toArray();

        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['staff_num']       = Db::name('wxk_staff')->where("find_in_set('{$v['code']}', tag_ids)")->count();
        }

        return $list;
    }

    /**
     * 删除成员标签
     * User: 万奇
     * Date: 2021/1/26 0026
     * @param $param
     * @throws \think\db\exception\DbException
     */
    public function del_staff_tag($param){
        $wechat             = new Wechat();
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/tag/delete';

        foreach ($param['id'] as $v){
            $code               = $this->where(['id' => $v])->value('code');
            $del                = $wechat->request_wechat_api($url, 'wxk_address_book_secret', ['tagid' => $code], false);

            if ($del['errcode'] != 0){
                response(500, '操作失败');
            }

            Db::name('wxk_staff')->where("find_in_set('{$code}', tag_ids)")
                ->exp('tag_ids', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', tag_ids, ','), concat(',','{$code}',','), ','))")
                ->update();
        }

        $this->where([['id', 'in', implode(',', $param['id'])]])->delete();
    }

    /**
     * 新增编辑成员标签
     * User: 万奇
     * Date: 2021/1/26 0026
     * @param $param
     * @throws \think\db\exception\DbException
     */
    public function add_staff_tag($param){
        $wechat             = new Wechat();
        if (is_exists($param['id'])){
            $group           = $this->where(['id' => $param['id']])->find();

            if ($group['name'] != $param['name']){
                $url        = 'https://qyapi.weixin.qq.com/cgi-bin/tag/update';
                $edit       = $wechat->request_wechat_api($url, 'wxk_address_book_secret', ['tagid' => $group['code'], 'tagname' => $param['name']], true, true);

                if ($edit['errcode'] == 40071){
                    response(500, '标签重复');
                }

                if ($edit['errcode'] != 0){
                    response(500, '操作失败');
                }
            }

            if ($group['group_id'] != $param['group_id']){
                $del_code        = Db::name('wxk_staff_tag_group')->where(['id' => $group['group_id']])->value('child_code');
                Db::name('wxk_staff_tag_group')->where(['id' => $group['group_id']])->update(['child_code' => implode(',', array_diff(explode(',', $del_code), [$group['code']]))]);

                $add_code = Db::name('wxk_staff_tag_group')->where(['id' => $param['group_id']])->value('child_code');
                $add_code = $add_code ? implode(',', array_merge(explode(',' , $add_code), [$group['code']])) : $group['code'];
                Db::name('wxk_staff_tag_group')->where(['id' => $param['group_id']])->update(['child_code' => $add_code]);
            }

            $this->where(['id' => $param['id']])->update(['name' => $param['name'], 'group_id' => $param['group_id']]);
        } else{
            $url        = 'https://qyapi.weixin.qq.com/cgi-bin/tag/create';
            $insert     = [];
            foreach ($param['name'] as $k => $v){
                $add        = $wechat->request_wechat_api($url, 'wxk_address_book_secret', ['tagname' => $v], true, true);

                if ($add['errcode'] == 40071){
                    response(500, '标签重复');
                }

                if ($add['errcode'] != 0){
                    response(500, '操作失败');
                }

                $insert[$k] = ['id' => uuid(), 'code' => $add['tagid'], 'group_id' => $param['group_id'], 'name' => $v];
            }

            $this->insertAll($insert);
            $child_code = Db::name('wxk_staff_tag_group')->where(['id' => $param['group_id']])->value('child_code');
            $child_code = implode(',', $child_code ? array_unique(array_merge(explode(',', $child_code), array_column($insert, 'code'))) : array_column($insert, 'code'));
            Db::name('wxk_staff_tag_group')->where(['id' => $param['group_id']])->update(['child_code' => $child_code]);
        }
    }

    /**
     * 获取成员标签树结构
     * User: 万奇
     * Date: 2021/1/27 0027
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_staff_tag_tree(){
        $list_group     = Db::name('wxk_staff_tag_group')->orderRaw("name='未分组' desc")->order(['create_at' => 'asc'])->select()->toArray();
        $list_tag       = array_grouping($this->select()->toArray(), 'group_id');

        foreach ($list_group as $k => $v){
            $list_group[$k]['group']        = isset($list_tag[$v['id']]) ? $list_tag[$v['id']] : [];
        }

        return $list_group;
    }

    /**
     * 获取成员客户标签组
     * User: 万奇
     * Date: 2021/1/26 0026
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_staff_tag_group(){
        $list       = Db::name('wxk_staff_tag_group')->orderRaw("name='未分组' desc")->order(['create_at' => 'asc'])->select()->toArray();

        return $list;
    }

    /**
     * 删除成员标签组
     * User: 万奇
     * Date: 2021/1/25 0025
     * @param $param
     * @throws \think\db\exception\DbException
     */
    public function del_staff_tag_group($param){
        $group_id        = Db::name('wxk_staff_tag_group')->where(['name' => '未分组'])->value('id');

        $this->where(['group_id' => $param['id']])->update(['group_id' => $group_id]);
        Db::name('wxk_staff_tag_group')->where(['id' => $param['id']])->delete();
    }

    /**
     * 新增编辑成员标签组
     * User: 万奇
     * Date: 2021/1/25 0025
     * @param $param
     * @throws \think\db\exception\DbException
     */
    public function add_staff_tag_group($param){
        if (is_exists($param['id'])){
            Db::name('wxk_staff_tag_group')->where(['id' => $param['id']])->update(['name' => $param['name']]);
        } else{
            Db::name('wxk_staff_tag_group')->insert(['id' => uuid(), 'name' => $param['name']]);
        }
    }

    /**
     * 成员标签同步
     * User: 万奇
     * Date: 2021/1/25 0025
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function sync_staff_tag(){
        $wechat             = new Wechat();
        $url                = 'https://qyapi.weixin.qq.com/cgi-bin/tag/list';
        $list               = $wechat->request_wechat_api($url, 'wxk_address_book_secret', [], false)['taglist'];

        $is_group_id        = Db::name('wxk_staff_tag_group')->where(['name' => '未分组'])->value('id');

        if (!$is_group_id){
            $is_group_id    = uuid();
            Db::name('wxk_staff_tag_group')->insert(['id' => $is_group_id, 'name' => '未分组']);
        }

        if (!count($list)){
            return false;
        }

        $group_list         = $this->get_group_list_attr(Db::name('wxk_staff_tag_group')->where('child_code','not null')->select()->toArray());

        Db::name('wxk_staff_tag')->delete(true);

        $insert             = [];
        foreach ($list as $k => $v){
            $insert[$k]['id']           = uuid();
            $insert[$k]['code']         = $v['tagid'];
            $insert[$k]['group_id']     = isset($group_list[$v['tagid']]) ? $group_list[$v['tagid']] : $is_group_id;
            $insert[$k]['name']         = $v['tagname'];
        }

        $this->insertAll($insert);
    }

    /**
     * 组装标签组结构
     * User: 万奇
     * Date: 2021/1/26 0026
     * @param $param
     * @return array
     */
    public function get_group_list_attr($param){
        $result                 = [];
        foreach ($param as $p_k => $p_v){
            foreach (explode(',', $p_v['child_code']) as $c_k => $c_v){
                $result[$c_v]   = $p_v['id'];
            }
        }
        return $result;
    }

}