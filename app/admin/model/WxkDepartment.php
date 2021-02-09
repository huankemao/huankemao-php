<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/25 0025
 * Time: 16:34
 */

namespace app\admin\model;


use app\core\Wechat;
use think\facade\Db;

class WxkDepartment extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 同步企业成员部门
     * User: 万奇
     * Date: 2020/11/25 0025
     * @return bool
     * @throws \Exception
     */
    public function synchro_department(){
        $wechat     = new Wechat();
        $data       = $wechat->get_department_list(['type' => 'wxk_address_book_secret']);

        if (count($data) == 0){
            return false;
        }
        Db::name('wxk_department')->delete(true);
        foreach ($data as $k => $v){
            $insert[$k]['id']             = uuid();
            $insert[$k]['code']           = $v['id'];
            $insert[$k]['name']           = $v['name'];
            $insert[$k]['parent_code']    = $v['parentid'];
        }

        $this->insertAll($insert);

        return true;
    }

    /**
     * 部门列表树结构加企业成员
     * User: 万奇
     * Date: 2020/12/15 0015
     * @param bool $is_welcome  是否是欢迎语获取成员
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_section_tree_staff($is_welcome = false){
        $list           = $this->field('parent_code,code,name')->order(['code' => 'asc'])->select()->toArray();

        foreach ($list as $k => $v){
            $list[$k]['group']      = Db::name('wxk_staff')->field('user_id,name')->where(['external_authority' => 1])->where("find_in_set({$v['code']}, department_id)")->select()->toArray();
        }

        // 是否是欢迎语获取成员
        if ($is_welcome){
            $staff_user_list        = Db::name('wxk_welcome')->column('user_id', 'user_id');

            foreach ($list as $w_k => $w_v){
                $list[$w_k]['group']        = $this->welcome_is_staff_disable($w_v['group'], $staff_user_list);
            }
        }

        $list           = array_grouping($list, 'parent_code');
        if (!count($list)){
            response(200);
        }

        $list           = $this->section_group($list, $list[0], 'group', 'code');

        return $list;
    }

    /**
     * 标识是否已有欢迎语成员
     * User: 万奇
     * Date: 2020/12/24 0024
     * @param $data
     * @param $list
     * @return mixed
     */
    public function welcome_is_staff_disable($data, $list){
        foreach ($data as $k => $v){
            $data[$k]['disable']        = isset($list[$v['user_id']]) ? 0 : 1;
        }

       return $data;
    }

    /**
     * 部门列表树结构加企业成员并列结构转换
     * User: 万奇
     * Date: 2020/12/16 0016
     * @param $all
     * @param $data
     * @param string $group_name
     * @param string $key
     * @return mixed
     */
    public function section_group($all, $data, $group_name = 'group', $key = 'id')
    {
        foreach ($data as $k => $v) {
            if (isset($v['user_id']) || !isset($all[$v[$key]])) {
                continue;
            }

            $data[$k][$group_name] = array_merge($data[$k][$group_name], $all[$v[$key]]);

            if (isset($data[$k][$group_name])) {
                $data[$k][$group_name] = $this->section_group($all, $data[$k][$group_name], $group_name, $key);
            }
        }
        return $data;
    }

    /**
     * 获取企业微信部门列表
     * User: 万奇
     * Date: 2020/11/25 0025
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_section_list(){
        $list       = $this->select()->toArray();
        return $list;
    }

    /**
     * 获取企业部门总量
     * User: 万奇
     * Date: 2020/11/25 0025
     * @return int
     */
    public function get_section_count(){
        $count      = $this->count();

        return $count;
    }

}