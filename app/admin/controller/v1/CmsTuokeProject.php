<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/20 0020
 * Time: 19:25
 */

namespace app\admin\controller\v1;



use think\App;

class CmsTuokeProject extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 添加拓客计划
     * User: 万奇
     * Date: 2021/1/21 0021
     */
    public function add_tuoke_project(){
        param_receive(['date_year', 'year_target']);
        $project        = new \app\admin\model\CmsTuokeProject();
        $project->add_tuoke_project($this->param);

        response(200, '操作成功');
    }

}