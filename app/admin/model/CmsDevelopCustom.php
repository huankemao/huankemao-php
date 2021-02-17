<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/20 0020
 * Time: 19:27
 */

namespace app\admin\model;

use think\facade\Db;

class CmsDevelopCustom extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 企业计划列表
     * User: 万奇
     * Date: 2021/2/2 0002
     * @param $param
     * @return mixed
     */
    public function get_business_plan_list($param){
        $section        = Db::name('wxk_department')->column('code,name', 'code');

        $list           = $this->alias('a')->field('a.*,b.name staff_name,b.department_id department')
            ->join('wxk_staff b', 'a.staff_id=b.user_id', 'left')
            ->where(['a.type' => $param['type']])
            ->order(['a.create_at' => 'asc'])
            ->paginate($param['limit'])
            ->toArray();

        $staff_model        = new WxkStaff();
        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['department']     = $v['staff_id'] ? $staff_model->section_attr($section, $v['department']) : '';
        }
        return $list;
    }

    /**
     * 回显下拉列表年份
     * User: 万奇
     * Date: 2021/2/2 0002
     * @param $param
     * @return array
     */
    public function show_develop_custom_year($param){
        $result         = $this->where(['type' => $param['type']])->column('date_year');
        return $result;
    }

    /**
     * 拓客完成情况
     * User: 万奇
     * Date: 2021/2/2 0002
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_develop_custom_info($param){
        // 拓客信息
        $info           = $this->where(['date_year' => $param['date_year']])->find()->toArray();

        $where          = ['type' => $param['type']];

        // 客户数量 - 年
        $custom_year    = Db::name('wxk_customer')->where($where)->whereYear('create_at', $param['date_year'])->count();

        // 客户数量 - 季
        $custom_quarter = array_column(Db::name('wxk_customer')->field('count(id) num,quarter(create_at) quarter')
                        ->whereYear('create_at', $param['date_year'])
                        ->where($where)
                        ->group('quarter')
                        ->select()
                        ->toArray(), 'num', 'quarter');
        // 客户数量 - 月
        $custom_month   = array_column(Db::name('wxk_customer')->field('count(id) num,month(create_at) month')
                        ->whereYear('create_at', $param['date_year'])
                        ->where($where)
                        ->group('month')
                        ->select()
                        ->toArray(), 'num', 'month');

        $month          = 1;
        $quarter        = 1;
        $info['fini_year_target']       = $info['year_target'] ? round(($custom_year / $info['year_target']) * 100,2) : 0;
        foreach ($info as $k => $v){
            $field      = explode('_', $k);
            if (isset($field[1]) && $field[1] == 'quarter'){
                $info['fini_' . $k]     = ($v && isset($custom_quarter[$quarter])) ? round(($custom_quarter[$quarter] / $info[$k]) * 100,2) : 0;
                $quarter ++;
            }

            if (isset($field[1]) && $field[1] == 'month'){
                $info['fini_' . $k]     = ($v && isset($custom_month[$month])) ? round(($custom_month[$month] / $info[$k]) * 100,2) : 0;
                $month ++;
            }
        }

        return $info;
    }

    /**
     * 添加拓客计划
     * User: 万奇
     * Date: 2021/1/21 0021
     * @param $param
     */
    public function add_develop_custom($param){
        if (is_exists($param['id'])){
            $this->where(['id' => $param['id']])->strict(false)->update($param);
        } else{
            $param['id']        = uuid();
            $this->save($param);
        }
    }

}