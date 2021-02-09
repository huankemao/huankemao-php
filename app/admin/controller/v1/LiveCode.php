<?php
/**
 * 渠道活码 模块
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/20 0020
 * Time: 14:57
 */

namespace app\admin\controller\v1;


use think\App;
use think\facade\Cache;

class LiveCode extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 上传活码素材
     * User: 万奇
     * Date: 2021/1/28 0028
     * @throws \think\db\exception\DbException
     */
    public function upload_qr_code(){
        param_receive(['id', 'upload_type']);

        $model      = new \app\admin\model\WxkLiveQr();
        $model->upload_qr_code($this->param, $this->user_info['user_id']);

        response(200, '操作成功');
    }

    /**
     * 活码统计客户属性
     * User: 万奇
     * Date: 2020/12/24 0024
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_stat_attribute(){
        param_receive(['start_time', 'end_time']); // group_code(活码组)
        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->get_live_qr_stat_attribute($this->param);

        response(200, '', $result);
    }

    /**
     * 活码统计客户增长
     * User: 万奇
     * Date: 2020/12/24 0024
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_add_stat(){
        param_receive(['start_time', 'end_time', 'date_type', 'page', 'limit']); // group_code(活码组)
        $model      = new \app\admin\model\WxkLiveQrStatistics();
        $result     = $model->get_live_qr_stat_screen($this->param, false);
        unset($result['during']);

        response(200, '', $result);
    }

    /**
     * 活码统计top10
     * User: 万奇
     * Date: 2020/12/23 0023
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_stat_top(){
        param_receive(['start_time', 'end_time', 'stat_type']); // group_code(活码组)
        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->get_live_qr_stat_top($this->param);

        response(200, '', $result);
    }

    /**
     * 活码统计头部信息
     * User: 万奇
     * Date: 2020/12/23 0023
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_statistics(){
        $model      = new \app\admin\model\WxkLiveQrStatistics();
        $result     = $model->get_live_qr_statistics();

        response(200, '', $result);
    }

    /**
     * 单个活码统计头部信息
     * User: 万奇
     * Date: 2020/12/18 0018
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_stat(){
        param_receive(['id']);
        $model      = new \app\admin\model\WxkLiveQrStatistics();
        $result     = $model->get_live_qr_stat($this->param);

        response(200, '', $result);
    }

    /**
     * 单个活码统计时间筛选
     * User: 万奇
     * Date: 2020/12/18 0018
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_stat_screen(){
        param_receive(['id', 'start_time', 'end_time', 'date_type', 'page', 'limit']);
        $model      = new \app\admin\model\WxkLiveQrStatistics();
        $result     = $model->get_live_qr_stat_screen($this->param);

        response(200, '', $result);
    }

    /**
     * 回显批量修改成员上限
     * User: 万奇
     * Date: 2020/12/21 0021
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show_batch_add_limit(){
        param_receive(['id']);
        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->show_batch_add_limit($this->param);

        response(200, '', $result);
    }

    /**
     * 批量编辑活码成员上限
     * User: 万奇
     * Date: 2020/12/22 0022
     * @throws \think\db\exception\DbException
     */
    public function edit_batch_add_limit(){
        param_receive('data');
        $model      = new \app\admin\model\WxkLiveQr();
        $model->edit_batch_add_limit($this->param['data']);

        response(200, '操作成功');
    }

    /**
     * 批量编辑欢迎语
     * User: 万奇
     * Date: 2020/12/18 0018
     */
    public function edit_batch_live_qr_welcome(){
        param_receive(['id', 'welcome_data']);
        $model      = new \app\admin\model\WxkLiveQr();
        $model->edit_batch_live_qr_welcome($this->param);

        response(200, '操作成功');
    }

    /**
     * 批量编辑活码成员
     * User: 万奇
     * Date: 2020/12/18 0018
     * @throws \think\db\exception\DbException
     */
    public function edit_batch_live_qr_staff(){
        param_receive(['id']); // wxk_staff_id 成员, wxk_department_id 部门

        $model      = new \app\admin\model\WxkLiveQr();
        $model->edit_batch_live_qr_staff($this->param);

        response(200, '操作成功');

    }

    /**
     * 批量获取活码
     * User: 万奇
     * Date: 2020/12/17 0017
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function batch_live_qr_list(){
        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->batch_live_qr_list($this->param);

        response(200, '', $result);
    }

    /**
     * 删除活码
     * User: 万奇
     * Date: 2020/12/11 0011
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete_live_qr(){
        param_receive(['id']);

        $model      = new \app\admin\model\WxkLiveQr();
        $model->delete_live_qr($this->param);

        response(200, '操作成功');
    }

    /**
     * 活码移动分组
     * User: 万奇
     * Date: 2020/12/11 0011
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function live_qr_group_move(){
        param_receive(['group_id', 'id']);

        $model      = new \app\admin\model\WxkLiveQr();
        $model->live_qr_group_move($this->param);

        response(200, '操作成功');
    }

    /**
     * 活码列表客户信息
     * User: 万奇
     * Date: 2020/12/11 0011
     * @throws \think\db\exception\DbException
     */
    public function get_live_qr_customer(){
        param_receive(['page', 'limit', 'id']);

        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->get_live_qr_customer($this->param);

        response(200, '', $result['data'], $result['total']);
    }

    /**
     * 活码列表预览成员
     * User: 万奇
     * Date: 2020/12/11 0011
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_staff(){
        param_receive(['id']);

        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->get_live_qr_staff($this->param);

        response(200, '', $result);
    }

    /**
     * 活码列表
     * User: 万奇
     * Date: 2020/12/10 0010
     * @throws \think\db\exception\DbException
     */
    public function get_live_qr_list(){
        param_receive(['page', 'limit']);

        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->get_live_qr_list($this->param);

        response(200, '', $result['data'], $result['total']);
    }

    /**
     * 新增活码获取多人成员
     * User: 万奇
     * Date: 2020/12/15 0015
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_section_tree_staff(){
        $model      = new \app\admin\model\WxkDepartment();
        $result     = $model->get_section_tree_staff();

        response(200, '', $result);
    }

    /**
     * 新增活码获取单人成员
     * User: 万奇
     * Date: 2020/12/14 0014
     * @throws \think\db\exception\DbException
     */
    public function get_add_live_staff(){
        param_receive(['page', 'limit']);
        $model      = new \app\admin\model\WxkStaff();
        $result     = $model->get_add_live_staff($this->param);

        response(200, '', $result['data'], $result['total']);
    }

    /**
     * 同步配置了外部联系人权限的联系人
     * User: 万奇
     * Date: 2020/12/14 0014
     */
    public function synchro_follow_user(){
        $model      = new \app\admin\model\WxkStaff();
        $model->synchro_follow_user();

        response(200, '操作成功');
    }

    /**
     * 新增编辑活码回显
     * User: 万奇
     * Date: 2020/12/18 0018
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show_live_qr(){
        param_receive(['id']);
        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->show_live_qr($this->param);

        response(200, '', $result);
    }

    /**
     * 新增编辑活码
     * User: 万奇
     * Date: 2020/12/10 0010
     * @throws \think\db\exception\DbException
     */
    public function add_live_qr(){
        param_receive(['group_id', 'name', 'is_add_friends', 'code_type', 'is_welcome_msg']); // wxk_staff_id(成员ID), wxk_department_id(部门ID),welcome_data(欢迎语),tag_ids(标签),add_limit(成员添加上限)

        $this->validate($this->param, ['name' => 'max:30|unique:wxk_live_qr'], ['name.max' => '活码名称限30字符', 'name.unique' => '活码名称不能重复']);

        $model      = new \app\admin\model\WxkLiveQr();
        $result     = $model->add_live_qr($this->param);

        response(200, '操作成功', $result);
    }

    /**
     * 新增编辑活码分组
     * User: 万奇
     * Date: 2020/11/20 0020
     */
    public function add_code_group(){
        $model      = new \app\admin\model\WxkLiveQrGroup();
        $model->add_code_group($this->param);

        response(200, '操作成功');
    }

    /**
     * 获取活码分组列表
     * User: 万奇
     * Date: 2020/11/20 0020
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function live_code_group(){
        $model      = new \app\admin\model\WxkLiveQrGroup();
        $result     = $model->live_code_group();

        response(200, '', $result);
    }

}