<?php
/**
 * 欢迎语
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/12/24 0024
 * Time: 15:58
 */

namespace app\admin\controller\v1;


use think\App;

class WxkWelcome extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 删除欢迎语
     * User: 万奇
     * Date: 2020/12/24 0024
     */
    public function del_welcome(){
        param_receive(['id']);

        $model      = new \app\admin\model\WxkWelcome();
        $model->del_welcome($this->param);

        response(200, '操作成功');
    }

    /**
     * 欢迎语列表
     * User: 万奇
     * Date: 2020/12/24 0024
     * @throws \think\db\exception\DbException
     */
    public function get_welcome_list(){
        param_receive(['page', 'limit']);

        $model      = new \app\admin\model\WxkWelcome();
        $result     = $model->get_welcome_list($this->param);

        response(200, '', $result['data'], $result['total']);
    }

    /**
     * 新增欢迎语
     * User: 万奇
     * Date: 2020/12/24 0024
     */
    public function add_welcome(){
        param_receive(['staff_user_id', 'welcome_data']); // id

        $model      = new \app\admin\model\WxkWelcome();
        $model->add_welcome($this->param);

        response(200, '操作成功');
    }

    /**
     * 编辑欢迎语
     * User: 万奇
     * Date: 2020/12/24 0024
     */
    public function edit_welcome(){
        param_receive(['id', 'staff_user_id', 'welcome_data']);

        $model      = new \app\admin\model\WxkWelcome();
        $model->add_welcome($this->param);

        response(200, '操作成功');
    }

    /**
     * 回显新增编辑欢迎语
     * User: 万奇
     * Date: 2020/12/24 0024
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show_add_welcome(){
        $model      = new \app\admin\model\WxkWelcome();
        $result     = $model->show_add_welcome($this->param);

        response(200, '', $result);
    }

}