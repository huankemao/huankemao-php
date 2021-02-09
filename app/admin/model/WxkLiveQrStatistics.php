<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/12/17 0017
 * Time: 18:00
 */

namespace app\admin\model;


use app\core\Wechat;
use think\facade\Db;

class WxkLiveQrStatistics extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 首页客户统计
     * User: 万奇
     * Date: 2020/12/28 0028
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index_customer_total(){
        $total                  = Db::name('wxk_customer')->count();
        $total_customer         = ['total' => $total, 'claimed' => $total, 'unclaimed' => 0];

        $loss_customer          = $this->field('COUNT(DISTINCT case when add_customer = 0 then external_user_id end) total,COUNT(DISTINCT case when deleted_customer = 1 then external_user_id end) deleted_customer,COUNT(DISTINCT case when deleted_staff = 1 then external_user_id end) deleted_staff')->select()->toArray()[0];

        $today_count            = $this->field('COUNT(DISTINCT case when add_customer = 1 then external_user_id end) add_customer,COUNT(DISTINCT case when add_customer = 0 then external_user_id end) deleted_customer,COUNT(DISTINCT case when deleted_staff = 1 then external_user_id end) deleted_staff')->whereDay('create_at')->select()->toArray()[0];
        $today_count['growth']  = $today_count['add_customer'] - $today_count['deleted_customer'];

        $follow_total           = Db::name('wxk_customer')->field('count(follow_status = 1 OR NULL) not_followed,count(follow_status = 2 OR NULL) under_follow,count(follow_status = 4 OR NULL) closed')->select()->toArray()[0];

        return ['total_customer' => $total_customer, 'loss_customer' => $loss_customer, 'today_count' => $today_count, 'follow_total' => $follow_total];
    }

    /**
     * 活码统计头部信息
     * User: 万奇
     * Date: 2020/12/23 0023
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_statistics(){
        $where          = ['add_type' => 2];

        $result['yesterday_count']  = $this->field('sum(add_customer) add_customer,sum(deleted_customer) deleted_customer,sum(deleted_staff) deleted_staff, (add_customer - deleted_staff) as growth')->where($where)->whereDay('create_at', 'yesterday')->select()->toArray()[0];
        $result['yesterday_count']['growth'] = $result['yesterday_count']['add_customer'] - $result['yesterday_count']['deleted_staff'];
        $result['before_count']  = $this->field('IFNULL(sum(add_customer), 0) add_customer,IFNULL(sum(deleted_customer), 0) deleted_customer,IFNULL(sum(deleted_staff), 0) deleted_staff')->where($where)->whereDay('create_at', date('Y-m-d',strtotime('-2 day')))->select()->toArray()[0];
        $result['before_count']['growth'] = $result['before_count']['add_customer'] - $result['before_count']['deleted_staff'];

        foreach ($result['before_count'] as $k => $v){
            $result['before_count'][$k]     = (($result['yesterday_count'][$k] - $result['before_count'][$k]) / ($result['yesterday_count'][$k] == 0 ? 1 : $result['yesterday_count'][$k])) * 100;
        }

        return $result;
    }

    /**
     * 单个活码统计底部信息
     * User: 万奇
     * Date: 2020/12/22 0022
     * @param $param
     * @param $is_live_qr_id - 是否指定单个活码
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_stat_screen($param, $is_live_qr_id = true){
        $where[]        = ['add_type', '=', 2];

        // 首页统计
        if (is_exists($param['index'])){
            $where      = [];
        }

        if($is_live_qr_id){
            $where[]    = ['live_qr_id', '=', $param['id']];
        }

        if (is_exists($param['group_code'])){
            $where[]    = ['live_qr_id', 'in', implode(',', Db::name('wxk_live_qr')->where(['group_id' => $param['group_code']])->column('id'))];
        }

        // 成员筛选
        if (is_exists($param['staff_user_id'])){
            $where[]    = ['user_id', 'in', $param['staff_user_id']];
        }


        $during         = $this->field('sum(add_customer) add_customer,sum(deleted_customer) deleted_customer,sum(deleted_staff) deleted_staff, (add_customer - deleted_staff) as growth')->where($where)->whereBetweenTime('create_at', $param['start_time'], $param['end_time'])->select()->toArray();

        $during_list    = $this->field("date_format(create_at,'%Y-%m-%d') as created_at,COUNT(DISTINCT case when add_customer = 1 then external_user_id end) add_customer,COUNT(DISTINCT case when deleted_customer = 1 then external_user_id end) deleted_customer,COUNT(DISTINCT case when deleted_staff = 1 then external_user_id end) deleted_staff, (add_customer - deleted_staff) as growth,COUNT(DISTINCT case when add_customer = 0 then external_user_id end) as loss")
            ->where($where)
            ->whereBetweenTime('create_at', $param['start_time'], $param['end_time'])
            ->group('created_at')
            ->select()
            ->toArray();

        $result = $this->date($param['date_type'], $during_list, $param['start_time'], $param['end_time'], $param['page'], $param['limit']);

        return array_merge(['during' => $during[0]], $result);
    }


    public function date($date_type, $data, $start_time, $end_time, $page, $limit)
    {
        if ($date_type == 1) {
            $data_dates = array_column($data, 'created_at');
            $count = Date::getDateFromRange($start_time, $end_time);
            foreach ($count as $v) {
                if (!in_array($v, $data_dates)) {
                    array_push($data, ['created_at' => $v, 'add_customer' => 0, 'deleted_customer' => 0, 'deleted_staff' => 0, 'growth' => 0, 'loss' => 0]);
                }
            }
            $result = $data;
        } else {
            if ($date_type == 2) {
                //根据每天获取每周开始结束时间
                foreach ($data as &$l) {
                    $l['created_at'] = Date::weeks($l['created_at']);
                }
                $data_weeks = array_column($data, 'created_at');
                //每周
                $count = Date::get_weeks($start_time, $end_time);

            } else {
                //根据每天获取每月
                foreach ($data as &$l) {
                    $l['created_at'] = Date::months($l['created_at']);
                }
                $data_weeks = array_column($data, 'created_at');
                //每月
                $count = Date::get_months($start_time, $end_time);
            }
            foreach ($count as $w) {
                if (!in_array($w, $data_weeks)) {
                    array_push($data, ['created_at' => $w, 'add_customer' => 0, 'deleted_customer' => 0, 'deleted_staff' => 0, 'growth' => 0, 'loss' => 0]);
                }
            }
            $item = [];
            foreach ($data as $k => $v) {
                if (!isset($item[$v['created_at']])) {
                    $item[$v['created_at']] = $v;
                } else {
                    $item[$v['created_at']]['add_customer'] += $v['add_customer'];
                    $item[$v['created_at']]['deleted_customer'] += $v['deleted_customer'];
                    $item[$v['created_at']]['deleted_staff'] += $v['deleted_staff'];
                    $item[$v['created_at']]['growth'] += $v['growth'];
                    if (isset($v['loss'])){
                        $item[$v['created_at']]['loss'] += $v['loss'];
                    }
                }
            }
            $result = array_values($item);
            //排序 分组
        }
        $result = getDatePageLimit($result, 'created_at', $page, $limit);
        return ['count' => count($count), 'data' => $result];
    }


    /**
     * 单个活码统计头部信息
     * User: 万奇
     * Date: 2020/12/18 0018
     * @param $param
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_live_qr_stat($param)
    {
        $result['live_name']    = Db::name('wxk_live_qr')->where(['id' => $param['id']])->value('name');

        $result['accum_count']  = $this->field('sum(add_customer) add_customer,sum(deleted_customer) deleted_customer,sum(deleted_staff) deleted_staff, (add_customer - deleted_staff) as growth')->where(['live_qr_id' => $param['id']])->where(['add_type' => 2])->select()->toArray()[0];
        $result['accum_count']['growth']        = $result['accum_count']['add_customer'] - $result['accum_count']['deleted_staff'];

        $result['today_count']  = $this->field('sum(add_customer) add_customer,sum(deleted_customer) deleted_customer,sum(deleted_staff) deleted_staff, (add_customer - deleted_staff) as growth')->where(['live_qr_id' => $param['id']])->where(['add_type' => 2])->whereDay('create_at')->select()->toArray()[0];
        $result['today_count']['growth']        = $result['today_count']['add_customer'] - $result['today_count']['deleted_staff'];

        return $result;
    }

}