<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/12/28 0028
 * Time: 18:00
 */

namespace app\admin\controller\v1;


use think\App;

class Index extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 获取首页用户信息
     * User: 万奇
     * Date: 2020/12/30 0030
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index_user_info(){
        $model      = new \app\admin\model\SysUser();
        $result     = $model->index_user_info($this->user_info['user_id']);

        response(200, '', $result);
    }

    /**
     * 首页客户增长趋势
     * User: 万奇
     * Date: 2021/1/6 0006
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index_customer_trend(){
        param_receive(['start_time', 'end_time', 'date_type', 'page', 'limit']); // staff_user_id(成员user_id)
        $this->param['index']       = 1;
        $model      = new \app\admin\model\WxkLiveQrStatistics();
        $result     = $model->get_live_qr_stat_screen($this->param, false);
        unset($result['during']);

        response(200, '', $result);
    }

    /**
     * 首页引流数据统计
     * User: 万奇
     * Date: 2021/1/23 0023
     */
    public function index_drainage_data(){
        $model      = new \app\admin\model\WxkCustomer();
        $result     = $model->index_drainage_data();

        response(200, '', $result);
    }

//    /**
//     * 首页统计成员top
//     * User: 万奇
//     * Date: 2021/1/5 0005
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\DbException
//     * @throws \think\db\exception\ModelNotFoundException
//     */
//    public function index_staff_top(){
//        param_receive(['start_time', 'end_time', 'stat_type']);
//
//        $model      = new \app\admin\model\WxkStaff();
//        $result     = $model->index_staff_top($this->param);
//
//        response(200, '', $result);
//    }

//    /**
//     * 首页客户明细
//     * User: 万奇
//     * Date: 2020/12/28 0028
//     * @throws \think\db\exception\DbException
//     */
//    public function index_customer_detailed(){
//        param_receive(['page', 'limit']);
//        $model      = new \app\admin\model\WxkCustomer();
//        $result     = $model->get_list_customer($this->param);
//
//        response(200, '', $result['data'], $result['count']);
//    }

//    /**
//     * 首页客户增长趋势
//     * User: 万奇
//     * Date: 2020/12/28 0028
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\DbException
//     * @throws \think\db\exception\ModelNotFoundException
//     */
//    public function index_customer_trend(){
//        param_receive(['start_time', 'end_time', 'date_type', 'page', 'limit']);
//        $model      = new \app\admin\model\WxkLiveQrStatistics();
//        $result     = $model->get_live_qr_stat_screen($this->param, false);
//        unset($result['during']);
//
//        response(200, '', $result);
//    }

    /**
     * 首页客户统计
     * User: 万奇
     * Date: 2020/12/28 0028
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index_customer_total(){
        $model      = new \app\admin\model\WxkLiveQrStatistics();
        $result     = $model->index_customer_total();

        response(200, '', $result);
    }

}