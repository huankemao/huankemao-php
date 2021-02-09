<?php
/**
 * Created by Shy
 * Date 2020/12/3
 * Time 18:02
 */


namespace app\admin\model;


use Exception;
use think\Cache;
use think\facade\Db;
use think\facade\Request;
use think\Model;
use think\response\Json;

class SysRole extends Model
{
    protected $name = 'sys_role';

    /**
     * User:Shy
     * @param $data
     * @param $type 1.添加 2.修改 3.删除
     * @return Json
     */
    static function RolesData($data, $type)
    {
        Db::startTrans();
        try {
            if ($type == 3) {
                $model = self::find($data['id']);
                $model->delete();
            } else {
                if ($type == 1) {
                    $model = new self();
                    $data['id'] = uuid();
                } else {
                    $model = self::find($data['id']);
                }
                if ($model->save($data) && !empty($data['module'])) {
                    $modules = json_decode($data['module'],true);
                    $new_arr = [];
                    $cache_arr = [];
                    foreach ($modules as $key => $v) {
                        $new_arr[$key]['role_id'] = $model->id;
                        $new_arr[$key]['module_id'] = $v['module_id'];
                        $cache_arr[$model->id][] = $v['uri'];
                    }
                    $del = SysRoleModule::where(['role_id' => $model->id])->find();
                    $del->delete();
                    \think\facade\Cache::delete('role_module_' . $model->id);
                    \think\facade\Cache::set('role_module_' . $model->id, $cache_arr);
                    $sys_module = new SysRoleModule();
                    $sys_module->saveAll($new_arr);
                }
            }
            Db::commit();
            return rsp(200, '成功');
        } catch (Exception $e) {
            Db::rollback();
            return rsp(500, $e->getMessage());
        }

    }

}