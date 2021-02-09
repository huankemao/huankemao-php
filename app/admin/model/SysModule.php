<?php
/**
 * Created by Shy
 * Date 2020/12/3
 * Time 15:19
 */


namespace app\admin\model;


use Exception;
use think\facade\Db;
use think\facade\Request;
use think\Model;
use think\response\Json;

class SysModule extends Model
{

    protected $createTime = 'sign_up_at';
    protected $updateTime = 'update_at';


    /**
     *  User:Shy
     * @param $data
     * @param $type 1.添加 2.修改 3.删除
     * @return Json
     */
    static function ModuleData($data, $type)
    {
        try {
            if ($type == 3) {
                $model = self::find($data['id']);
                if ($model->code == 1) {
                    return rsp(500, '根节点无法删除');
                }
                $model->delete();
            } else {
                $last_sql = Db::name('sys_module')->order('create_at desc')->limit(1)->value('code');
                $only_data = Request::only(['id', 'parent_code', 'title', 'uri', 'icon', 'sort', 'is_menu', 'disable', 'tree_code']);
                if (!$last_sql) {
                    $only_data['code'] = 1;
                    $only_data['tree_code'] = 1;
                } else {
                    $only_data['code'] = $last_sql + 1;
                    $only_data['tree_code'] = $only_data['tree_code'] . ',' . $only_data['code'];
                }
                if ($type == 1) {
                    $model = new self();
                    $only_data['id'] = uuid();
                    $model->save($only_data);
                } else {
                    $model = self::find($only_data['id']);
                    $model->save($only_data);
                }
            }
            return rsp(200, '成功');
        } catch (Exception $e) {
            return rsp(500, $e->getMessage());
        }
    }


    /**
     * User:Shy
     * @param $list
     * @param string $code code //唯一标识
     * @param string $parent_code //父code
     * @param string $son $son  //定义名称
     * @param int $parent_root //最上级标识值
     * @return array
     */
    static function tree($list, $code = 'code', $parent_code = 'parent_code', $son = 'son', $parent_root = 0)
    {

        $tree = array();
        if (is_array($list)) {
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$code]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                $parentId = $data[$parent_code];
                if ($parent_root == $parentId) {
                    $tree[] =& $list[$key];

                } else {
                    if (isset($refer[$parentId])) {

                        $parent =& $refer[$parentId];
                        // $list[$key][$code] = $list[$key][$code];
                        $parent[$son][] =& $list[$key];

                    }
                }
            }
        }
        return $tree;
    }


    static function user_tree($role_id){
        $result = Db::name('sys_role_module')
            ->where('role_id', $role_id)
            ->column('module_id');
        $tree = Db::name('sys_module')->where('code', '<>', 1)->order('sort desc')->select()->toArray();
        if($tree){
            foreach ($tree as &$v) {
                $v['whether'] = 0;
                if (in_array($v['id'], $result)) {
                    $v['whether'] = 1;
                }
            }
            $menu = self::tree($tree, 'code', 'parent_code');
        }
        return $menu;
    }
}