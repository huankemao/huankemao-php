<?php
/**
 * Created by Shy
 * Date 2020/12/1
 * Time 14:22
 */


namespace app\admin\controller\v1;


use app\admin\model\Admin;
use app\admin\model\SysApp;
use app\admin\model\SysModule;
use app\admin\model\SysUser as User;
use app\admin\model\SysUserRole;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use think\Config;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;
use think\Request;
use think\Response;
use think\response\Json;
use function response;

class SysUser
{


    /**
     * 登录
     * User:shy
     * @param Request $request
     * @return Json
     * @throws InvalidArgumentException
     */
    public function Login(Request $request)
    {
        verify_data('phone,password', $request->data);
        $user = User::getByPhone($request->data['phone']);
        if ($user) {
            $result = User::encryption($user->id, $user->password, $request->data['password']);
            if ($result != false) {
                Cache::store('file')->set($user->id,['phone'=> $user->phone]);
                return rsp(200, '登录成功', ['user_id' => $user->id, 'token' => $result, 'phone' => $user->phone]);
            }
            return rsp(500, '密码错误');
        }
        return rsp(500, '用户不存在');
    }


    /**
     * User:Shy
     * 微信登录
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function WxLogin(Request $request)
    {
        $config     = Db::name('wxk_config')->where(true)->find();
        if ($request->isPost()) {
            if (!$config['wxk_id'] || !$config['wxk_app_agent_id']){
                return rsp(500, '未配置应用信息');
            }
            //获取登录url
            $url = Config('common.wx_login_url');
            $redirect_url = $request->domain() . '/login';
            $url .= "appid={$config['wxk_id']}&agentid={$config['wxk_app_agent_id']}&redirect_uri=$redirect_url&state=STATE";
            return rsp(200, '成功', ['url'=>$url]);
        } else {
            //回调 获取access_token
            $url = Config('common.wx_access_token');
            $url .= "corpid={$config['wxk_id']}&corpsecret={$config['wxk_app_secret']}";
            $result = httpGet($url);
            $result = json_decode($result, true);
            $access_token = $result['access_token'];
            //获取微信user_id
            $url1 = Config('common.wx_get_user_info');
            $code = $request->data['code'];
            $url1 .= "access_token=$access_token&code=$code";
            $result1 = httpGet($url1);
            $result1 = json_decode($result1, true);
            if ($result1['errcode'] == 0) {
                $staff = \app\admin\model\WxkStaff::where(['user_id' => $result1['UserId']])->find();
                $user = User::where(['phone' => $staff->mobile])->find();
                if ($staff && $user) {
                    $user->token = User::token();
                    $user->last_login_at = date('Y-m-d H:i:s', time());
                    $user->save();
                    Cache::store('file')->set($user->id,['phone'=> $user->phone]);
                    return rsp(200, '登录成功',['user_id' => $user->id, 'token' => $user->token, 'phone' => $user->phone]);
                }else{
                    return rsp(500, '请先联系管理员注册');
                }
            }
            return rsp(500, '失败');
        }
    }

    /**
     * 注册
     * User: Shy
     * @param Request $request
     * @return Response|Json|void
     * @throws InvalidArgumentException
     */
    public function Register(Request $request)
    {
        verify_data('phone,password,code', $request->data);
        try {
            validate(\app\validate\SysUser::class)
                ->scene('register')
                ->check($request->data);
            $captcha = Cache::store('file')->get(config('common.MsgCode')['2'] . $request->data['phone']);
            if ($captcha != $request->data['code']) {
                return rsp(500, '验证码错误');
            }
            $only_data = \think\facade\Request::only(['phone', 'password']);
            $only_data['password'] = sha1($only_data['password']);
            $only_data['id'] = uuid();
            $user = new User();
            if ($user->save($only_data)) {
                return rsp(200, '成功');
            }
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return rsp(500, $e->getError());
        }
    }


    /**
     * User: Shy
     * 发送短信
     * @param Request $request
     * @return Json
     */
    public function SendMsg(Request $request)
    {

        try {
            verify_data('phone,type', $request->data);
            $user_id = '';
            if (empty($request->data['user_id']) && $request->data['type'] == '1') {
                $user = User::getByPhone($request->data['phone']);
                if (!$user) {
                    return rsp(500, '用户不存在');
                }
                $user_id = $user->id;
            }
            $str = '1234567890';
            $str = mb_substr(str_shuffle($str), 0, 6);
            Cache::store('file')->set(config('common.MsgCode')[$request->data['type']] . $request->data['phone'], $str, 60);
            $res = User::send_sms($request->data['phone'], $str);
            if ($res['Code'] === 'OK') {
                return rsp(200, '验证码发送成功!', ['user_id' => $user_id]);
            }
            return rsp(500, '发送失败');
        } catch (Exception $e) {
            return rsp(500, $e->getMessage());
        }

    }


    /**
     * 验证短信
     * User：Shy
     * @param Request $request
     * @return Json
     * @throws InvalidArgumentException
     */
    public function VerifyCode(Request $request)
    {
        verify_data('phone,code', $request->data);
        $captcha = Cache::store('file')->get(config('common.MsgCode')['1'] . $request->data['phone']);
        if ($captcha != $request->data['code']) {
            return rsp(500, '验证码错误');
        }
        return rsp(200, '验证通过');
    }

    /**
     * User: Shy
     * 重设密码
     * @param Request $request
     * @return Json
     */
    public function ForgetPas(Request $request)
    {
        return User::ResetPas($request->data);
    }


    /**
     * User:shy
     *  菜单列表
     * @param Request $request
     */
    public function MenuList(Request $request)
    {
        $user_tree = User::find($request->data['user_id']);
        if ($user_tree) {
            $role_id = $user_tree->user_role->role_id;
            $menu = SysModule::user_tree($role_id);
        }
        //系统设置
        $app = Db::name('sys_app')->limit(1)->select();
        return rsp(200, '成功', ['user_menu' => $menu, 'system' => $app]);
    }


    /**
     * User:Shy
     * 用户列表
     * @param Request $request
     * @return Json
     * @throws DbException
     */
    public function UserList(Request $request)
    {
        verify_data('disable,search_name,page,limit', $request->data);
        $sql = "(a.`disable` = '{$request->data['disable']}')";
        if ($request->data['search_name']) {
            $asset_class = $request->data['search_name'];
            $sql .= " AND (a.`phone` LIKE '%$asset_class%' OR a.`username` LIKE '%$asset_class%' )";
        }

        $result = Db::name('sys_user')
            ->field('a.id,a.username,a.phone,a.gender,a.disable,a.department,c.name as role,a.sign_up_at')
            ->alias('a')
            ->whereRaw($sql)
            ->leftJoin('sys_user_role b', 'a.id=b.user_id')
            ->leftJoin('sys_role c', 'b.role_id=c.id')
//            ->fetchSql();
            ->paginate($request->data['limit'])
            ->toArray();
        return rsp(200, '成功', $result);

    }


    /**
     * User:Shy
     * 添加用户
     * @param Request $request
     * @return Json
     */
    public function UserAdd(Request $request)
    {
        verify_data('username,phone,gender,disable,password,department,role_id', $request->data);
        try {
            validate(\app\validate\SysUser::class)
                ->scene('add')
                ->check($request->data);
            $add_data = \think\facade\Request::only(['username', 'phone', 'gender', 'disable', 'password', 'department', 'role_id']);
            $add_data['password'] = sha1($request->data['password']);
            $add_data['id'] = uuid();
            return User::AddUser($add_data);
        } catch (ValidateException $e) {
            return rsp(500, $e->getError());
        }
    }

    /**
     * 修改用户
     * User:Shy
     * @param Request $request
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function UserEdit(Request $request)
    {
        verify_data('id,username,gender,disable,department,role_id', $request->data);
        Db::startTrans();
        try {
            validate(\app\validate\SysUser::class)
                ->scene('edit')
                ->check($request->data);
            $model = User::find($request->data['id']);
            if ($model) {
                $add_data = \think\facade\Request::only(['username', 'gender', 'disable', 'department', 'role_id']);
                if ($model->save($add_data)) {
                    $user_role = SysUserRole::find(['user_id' => $request->data['id']]);
                    if ($user_role->save(['user_id' => $request->data['id'], 'role_id' => $request->data['role_id']])) {
                        Db::commit();
                        return rsp(200, '添加成功');
                    }
                }
            }
        } catch (ValidateException $e) {
            Db::rollback();
            return rsp(500, $e->getError());
        }
    }

    /**
     * User:Shy
     * 删除用户
     * @param Request $request
     * @return Json
     */
    public function UserDel(Request $request)
    {
        verify_data('id', $request->data);
        return User::Del($request->data['id']);
    }

    /**
     * User: Shy
     * 退出登录
     * @return Response|Json
     */
    public function logout(Request $request)
    {
        $user = User::find($request->data['user_id']);
        $user->token = User::token();
        if ($user->save()) {
            Cache::store('file')->delete($request->data['user_id']);
            return rsp(200, '退出成功');
        } else {
            return rsp(200, '退出失败');
        }
    }


}