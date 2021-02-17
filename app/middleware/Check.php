<?php
declare (strict_types=1);

namespace app\middleware;

use Closure;
use Exception;
use think\facade\Cache;
use think\facade\Db;
use think\Request;

class Check
{

    protected $data;

    /**
     * Check constructor.
     */
    public function __construct()
    {
        header("Access-Control-Allow-Origin:*");
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:x-requested-with, content-type');
    }

    /**
     * 处理请求
     * User:Shy
     * @param Request $request
     * @param Closure $next
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function handle($request, Closure $next)
    {
        $this->data = $request->request();
        $request->data = $this->data;

        // 安装文件生成后，忽略权限列表
        $ignoreList = ['login', 'wx_login', 'logout', 'send_msg', 'register', 'verify_code', 'check_environment', 'install','install_check','admin/forget_pas',
            'forget_pas','content_type_list','config_synchro', 'del_config', 'get_temporary_preview', 'wechat_login_test', 'wechat_login', 'add_article_reading',
            'get_code_staff_user', 'app_get_content_engine', 'get_js_sdk', 'content_operating'];

        // 安装文件生成前，外网回调路由地址,直接忽略
        $external_route     = ['external_contact', 'get_config_random_string'];
        if (in_array(request()->pathinfo(), $external_route)){
            return $next($request);
        }

        $url = str_replace('/admin.php/','',\think\facade\Request::url());//路由
        if($request->isGet()){
            return $next($request);
        }
        try {
            $content = file_get_contents('../install/install.lock');
        } catch (Exception $e) {
            $content = false;
        }
        if ($url == 'check_environment' || $url == 'install') {
            if ($content != false) {
                return rsp(500, '已安装');
            }

        }else{
            if ($content === false && $url != 'install_check' ) {
                return rsp(505, '未安装');
            }
        }
        if (in_array($url, $ignoreList)) {
            return $next($request);
        } else {
            verify_data('user_id,token,time,sign', $this->data);
            $user = Db::name('sys_user')
                ->alias('a')
                ->leftJoin('sys_user_role b', 'a.id=b.user_id')
                ->field('b.role_id,a.token')
                ->where(['a.id'=>$this->data['user_id']])
                ->select()
                ->toArray();
            if (empty($user)) {
                return rsp(500, '用户不存在');
            }
//            if ($this->data['token'] != $user[0]['token']) {
//                Cache::store('file')->delete($this->data['user_id']);
//                return rsp(501, '您已在其他地方登录');
//            }
//            $user_module = Cache::get('role_module_' . $user[0]['role_id']);
//            if (!in_array($url, array_values($user_module)[0])) {
//                return rsp(502, '没有权限');
//            }
            $sign = md5($this->data['user_id'] . $this->data['token'] . $this->data['time'] . md5('huankemao' . $url));
            if ($sign != $this->data['sign']) {
//                return rsp(500, '传输协议错误');
            }

            // 检测是否配置企业
            if (!in_array(request()->pathinfo(), ['add_wxk_config', 'wxk_config_list', 'get_callback_url', 'upload_domain_verification_file'])){
                $wxk_id     = Db::name('wxk_config')->where(true)->value('wxk_id');
                if (!$wxk_id){
                    return rsp(506, '当前未配置企业微信信息，请前往系统设置页面配置');
                }
            }

            return $next($request);
        }
    }


}
