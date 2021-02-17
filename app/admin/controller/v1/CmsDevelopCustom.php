<?php
/**
 * 拓客计划
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/20 0020
 * Time: 19:25
 */

namespace app\admin\controller\v1;



use think\App;

class CmsDevelopCustom extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 企业计划列表
     * User: 万奇
     * Date: 2021/2/2 0002
     */
    public function get_business_plan_list(){
        param_receive(['page', 'limit', 'type']);
        $project        = new \app\admin\model\CmsDevelopCustom();
        $result         = $project->get_business_plan_list($this->param);

        response(200, '', $result['data'], $result['total']);
    }

    /**
     * 回显下拉列表年份
     * User: 万奇
     * Date: 2021/2/2 0002
     */
    public function show_develop_custom_year(){
        param_receive(['type']);
        $project        = new \app\admin\model\CmsDevelopCustom();
        $result         = $project->show_develop_custom_year();

        response(200, '', $result);
    }

    /**
     * 拓客完成情况
     * User: 万奇
     * Date: 2021/2/2 0002
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_develop_custom_info(){
        param_receive(['date_year', 'type']);
        $project        = new \app\admin\model\CmsDevelopCustom();
        $result         = $project->get_develop_custom_info($this->param);

        response(200, '', $result);
    }

    /**
     * 添加拓客计划
     * User: 万奇
     * Date: 2021/1/21 0021
     */
    public function add_develop_custom(){
        param_receive(['date_year', 'year_target']);
        $project        = new \app\admin\model\CmsDevelopCustom();
        $project->add_develop_custom($this->param);

        response(200, '操作成功');
    }

}