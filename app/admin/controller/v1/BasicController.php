<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/19 0019
 * Time: 18:02
 */

namespace app\admin\controller\v1;


use app\core\BaseController;
use think\App;

class BasicController extends BaseController
{
    protected $param; // 保存请求的参数

    protected $user_info; // 用户信息

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->set_user_info($this->_set_user_relative_param()); // 处理参数和用户信息
    }

    /**
     * 设置获取用户相关的参数
     * User: 万奇
     * Date: 2020/9/3 0003
     * @return array
     */
    private function _set_user_relative_param(){
        return [ 'user_id' , 'time', 'token', 'sign'];
    }


    /**
     * 设置用户信息
     * User: 万奇
     * Date: 2020/9/3 0003
     * @param array $handle_param 需要处理的参数
     */
    protected function set_user_info($handle_param = []){
        $post_param         = input('post.');

//        param_receive([ 'uid' ]);

        if(!empty($handle_param)){
            foreach($handle_param as $hk => $hv){
                $this->user_info[$hv]   = isset($post_param[$hv]) ? $post_param[$hv] : '';
            }
        }

        $this->param        = array_del_key($post_param , $handle_param, false);
    }

}