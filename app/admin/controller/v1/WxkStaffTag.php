<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/22 0022
 * Time: 13:56
 */

namespace app\admin\controller\v1;


use think\App;

class WxkStaffTag extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 获取成员标签树结构
     * User: 万奇
     * Date: 2021/1/27 0027
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_staff_tag_tree(){
        $model    = new \app\admin\model\WxkStaffTag();
        $result   = $model->get_staff_tag_tree();

        response(200, '', $result);
    }

    /**
     * 获取成员标签列表
     * User: 万奇
     * Date: 2021/1/26 0026
     * @throws \think\db\exception\DbException
     */
    public function get_staff_tag_list(){
        param_receive([ 'page', 'limit']);
        $model    = new \app\admin\model\WxkStaffTag();
        $result   = $model->get_staff_tag_list($this->param);

        response(200, '', $result['data'], $result['total']);
    }

    /**
     * 删除成员标签
     * User: 万奇
     * Date: 2021/1/26 0026
     * @throws \think\db\exception\DbException
     */
    public function del_staff_tag(){
        param_receive([ 'id']);
        $model    = new \app\admin\model\WxkStaffTag();
        $model->del_staff_tag($this->param);

        response(200,'操作成功');
    }

    /**
     * 新增编辑成员标签
     * User: 万奇
     * Date: 2021/1/25 0025
     * @throws \think\db\exception\DbException
     */
    public function add_staff_tag(){
        param_receive([ 'name' , 'group_id']);
        $model    = new \app\admin\model\WxkStaffTag();
        $model->add_staff_tag($this->param);

        response(200,'操作成功');
    }

    /**
     * 获取成员客户标签组
     * User: 万奇
     * Date: 2021/1/26 0026
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_staff_tag_group(){
        $model    = new \app\admin\model\WxkStaffTag();
        $result   = $model->get_staff_tag_group();

        response(200, '', $result);
    }

    /**
     * 删除成员标签组
     * User: 万奇
     * Date: 2021/1/25 0025
     * @throws \think\db\exception\DbException
     */
    public function del_staff_tag_group(){
        param_receive([ 'id' ]);
        $model    = new \app\admin\model\WxkStaffTag();
        $model->del_staff_tag_group($this->param);

        response(200,'操作成功');
    }

    /**
     * 新增编辑成员标签组
     * User: 万奇
     * Date: 2021/1/25 0025
     * @throws \think\db\exception\DbException
     */
    public function add_staff_tag_group(){
        param_receive([ 'name' ]);

        $this->validate($this->param, ['name' => 'max:30|unique:wxk_staff_tag_group'], ['name.max' => '组名称限30字符', 'name.unique' => '分组名称不能重复']);

        $model    = new \app\admin\model\WxkStaffTag();
        $model->add_staff_tag_group($this->param);

        response(200,'操作成功');
    }

    /**
     * 成员标签同步
     * User: 万奇
     * Date: 2020/12/4 0004
     * @throws \think\db\exception\DbException
     */
    public function sync_staff_tag(){
        $model    = new \app\admin\model\WxkStaffTag();
        $model->sync_staff_tag();

        response(200,'操作成功');
    }

}