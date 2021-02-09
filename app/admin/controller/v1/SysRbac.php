<?php
/**
 * Created by Shy
 * Date 2020/12/2
 * Time 13:37
 */


namespace app\admin\controller\v1;


use app\admin\model\SysApp;
use app\admin\model\SysModule;
use app\admin\model\SysRole;
use app\Request;
use app\validate\Authority;
use app\validate\Roles;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class SysRbac
{

    /**
     * User:Shy
     * @param Request $request
     * @return Json
     * @throws DbException
     */
    public function AuthorityList(Request $request)
    {
        verify_data('title', $request->data);
        $prams = [];
        if (!empty($request->data['title'])) {
            $prams[] = ['title', 'like', "%{$request->data['title']}%"];
        }
        $prams[] = ['code', '<>', 1];
        $result = Db::name('sys_module')->where($prams)->where('find_in_set(:tree_code,tree_code)', ['tree_code' => 1])->order('sort desc')->select()->toArray();
        $menu = SysModule::tree($result, 'code', 'parent_code');
        return rsp(200, '成功', $menu);
    }


    /**
     * User:Shy
     * @param Request $request
     * @return Json
     */
    public function AuthorityAdd(Request $request)
    {
        validate(Authority::class)
            ->scene('add')
            ->check($request->data);
        return SysModule::ModuleData($request->data, 1);

    }

    /**
     * User:Shy
     * @param Request $request
     * @return Json
     */
    public function AuthorityEdit(Request $request)
    {
        validate(Authority::class)
            ->scene('edit')
            ->check($request->data);
        return SysModule::ModuleData($request->data, 2);
    }

    /**
     * User:Shy
     * @param Request $request
     * @return Json
     */
    public function AuthorityDel(Request $request)
    {
        verify_data('id', $request->data);
        return SysModule::ModuleData($request->data, 3);
    }


    /**
     * User:Shy
     * @param Request $request
     * @return Json
     * @throws DbException
     */
    public function RolesList(Request $request)
    {

        verify_data('name,disable,page,limit', $request->data);
        $prams = [];
        if (!empty($request->data['name'])) {
            $prams[] = ['name', 'like', "%{$request->data['name']}%"];
        }
        if (!empty($request->data['disable'])) {
            $prams[] = ['disable', '=', $request->data['disable']];
        }
        $result = Db::name('sys_role')->where($prams)->paginate($request->data['limit'])->toArray();
        return rsp(200, '成功', $result);
    }

    /**
     * User:Shy
     * @param Request $request
     * @return Json
     */
    public function RolesAdd(Request $request)
    {
        validate(Roles::class)
            ->scene('add')
            ->check($request->data);
        return SysRole::RolesData($request->data, 1);
    }


    /**
     * User:Shy
     * @param Request $request
     * @return Json
     */
    public function RolesEdit(Request $request)
    {
        validate(Roles::class)
            ->scene('edit')
            ->check($request->data);
        return SysRole::RolesData($request->data, 2);
    }

    /**
     * User:Shy
     * @param Request $request
     * @return Json
     */
    public function RolesDel(Request $request)
    {
        verify_data('id', $request->data);
        return SysRole::RolesData($request->data, 3);
    }


    /**
     * 获取角色拥有的权限
     * User:shy
     * @param Request $request
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function RolesModule(Request $request)
    {
        verify_data('role_id', $request->data);
        $menu =  SysModule::user_tree($request->data['role_id']);
        return rsp(200, '成功', $menu);
    }


    /**
     * User:Shy
     * 修改系统设置
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function AppEdit(Request $request){
        verify_data('id,name,logo', $request->data);
        $model = SysApp::find($request->data['id']);
        if($model->save($request->data)){
            return rsp(200, '成功');
        }
        return rsp(500, '失败');
    }
}