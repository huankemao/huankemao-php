<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/25 0025
 * Time: 16:10
 */

namespace app\admin\controller\v1;


use app\admin\model\WxkDepartment;
use think\App;

class WxkStaff extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 成员打标签/移除标签
     * User: 万奇
     * Date: 2021/1/15 0015
     */
    public function staff_tagging(){
        param_receive(['staff_user_id', 'tag_ids', 'type']);
        $customer = new \app\admin\model\WxkStaff();
        $customer->staff_tagging($this->param);

        response(200, '操作成功');
    }

    /**
     * 成员打标签回显已有的标签
     * User: 万奇
     * Date: 2021/1/14 0014
     */
    public function show_staff_tag(){
        param_receive(['id', 'type']);
        $customer = new \app\admin\model\WxkStaff();
        $result = $customer->show_staff_tag($this->param);

        response(200, '', $result);
    }

    /**
     * 同步企业微信成员
     * User: 万奇
     * Date: 2020/11/26 0026
     * @throws \Exception
     */
    public function synchro_staff(){
        // 同步部门
        $section    = new WxkDepartment();
        $section->synchro_department();

        // 同步成员
        $wechat_user    = new \app\admin\model\WxkStaff();
        $wechat_user->synchro_user();

        // 同步外部联系人权限
        $wechat_user->synchro_follow_user();

        // 同步成员行为数据
        $wechat_user->sync_staff_behavior();

        // 成员标签同步
        $staff_tag      = new \app\admin\model\WxkStaffTag();
        $staff_tag->sync_staff_tag();

        response(200,'操作成功');
    }

    /**
     * 同步成员行为数据
     * User: 万奇
     * Date: 2021/1/21 0021
     * @throws \think\db\exception\DbException
     */
    public function sync_staff_behavior(){
        $staff    = new \app\admin\model\WxkStaff();
        $staff->sync_staff_behavior();

        response(200,'操作成功');
    }

    /**
     * 获取企业成员最后一次同步时间
     * User: 万奇
     * Date: 2020/12/4 0004
     */
    public function get_synchro_staff_date(){
        $wechat_user    = new \app\admin\model\WxkStaff();
        $result         = $wechat_user->get_synchro_staff_date();

        response(200, '', $result);
    }

    /**
     * 获取成员列表
     * User: 万奇
     * Date: 2020/11/27 0027
     */
    public function get_user_simple_list(){
        param_receive(['page', 'limit', 'time_behavior']);

        $wechat_user    = new \app\admin\model\WxkStaff();
        $result         = $wechat_user->get_user_simple_list($this->param, $this->param['limit']);

        response(200, '', $result['data'], $result['count']);
    }

    /**
     * 根据部门获取成员
     * User: 万奇
     * Date: 2021/1/29 0029
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_department_staff(){
        param_receive(['code']);

        $staff          = new \app\admin\model\WxkStaff();
        $result         = $staff->get_department_staff($this->param);

        response(200, '', $result);
    }

    /**
     * 回显成员筛选列表
     * User: 万奇
     * Date: 2021/1/26 0026
     */
    public function show_get_staff_screen(){
        $result['staff_status'] = \StaticData::RESOURCE_NAME['staff_status'];

        response(200, '', $result);
    }

    /**
     * 同步企业成员部门
     * User: 万奇
     * Date: 2020/11/25 0025
     * @throws \Exception
     */
    public function synchro_department(){
        $section    = new WxkDepartment();
        $section->synchro_department();

        response(200, '操作成功');
    }

    /**
     * 获取企业微信部门列表
     * User: 万奇
     * Date: 2020/11/25 0025
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_department_list(){
        $section    = new WxkDepartment();
        $list      = $section->get_section_list();

        response(200, '', $list);
    }

}