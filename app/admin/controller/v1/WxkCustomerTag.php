<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/12/4 0004
 * Time: 16:17
 */

namespace app\admin\controller\v1;


use think\App;

class WxkCustomerTag extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 编辑删除客户标签
     * User: 万奇
     * Date: 2021/1/18 0018
     * @throws \think\db\exception\DbException
     */
    public function edit_customer_tag(){
        param_receive([ 'id', 'type']);
        $model      = new \app\admin\model\WxkCustomerTag();
        $model->edit_customer_tag($this->param);

        response(200, '操作成功');
    }

    /**
     * 编辑删除标签组
     * User: 万奇
     * Date: 2021/1/15 0015
     * @throws \think\db\exception\DbException
     */
    public function edit_customer_tag_group(){
        param_receive([ 'code', 'type']);
        $model      = new \app\admin\model\WxkCustomerTag();
        $model->edit_customer_tag_group($this->param);

        response(200, '操作成功');
    }

    /**
     * 新增客户标签
     * User: 万奇
     * Date: 2020/11/20 0020
     */
    public function add_customer_tag(){
        param_receive([ 'tag' ]);
        $model      = new \app\admin\model\WxkCustomerTag();
        $model->add_customer_tag($this->param);

        response(200, '操作成功');
    }

    /**
     * 获取客户标签树结构
     * User: 万奇
     * Date: 2020/12/8 0008
     */
    public function get_customer_tag_tree(){
        $model      = new \app\admin\model\WxkCustomerTag();
        $result     = $model->get_customer_tag_tree();

        response(200, '', $result);
    }

    /**
     * 获取客户标签
     * User: 万奇
     * Date: 2020/12/4 0004
     * @throws \think\db\exception\DbException
     */
    public function get_customer_tag(){
        $model      = new \app\admin\model\WxkCustomerTag();
        $result     = $model->get_customer_tag($this->param, $this->param['limit']);

        response(200,'', $result['data'], $result['count']);
    }

    /**
     * 获取客户标签组
     * User: 万奇
     * Date: 2020/12/4 0004
     */
    public function get_customer_tag_group(){
        $model      = new \app\admin\model\WxkCustomerTag();
        $result     = $model->get_customer_tag_group();

        response(200,'', $result);
    }

    /**
     * 客户标签同步
     * User: 万奇
     * Date: 2020/12/4 0004
     * @throws \think\db\exception\DbException
     */
    public function synchro_customer_tag(){
        $wechat_user    = new \app\admin\model\WxkCustomerTag();
        $wechat_user->synchro_customer_tag();

        response(200,'操作成功');
    }
}