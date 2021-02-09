<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/19 0019
 * Time: 18:59
 */

namespace app\admin\model;


use think\facade\Db;

class WxkLiveQrGroup extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 新增编辑删除活码分组
     * User: 万奇
     * Date: 2020/11/20 0020
     * @param $param
     * @return bool
     */
    public function add_code_group($param){
        if (isset($param['id'])){
            // 删除
            if (isset($param['is_del'])){
                Db::startTrans();
                try{
                    $group_id       = $this->where(['name' => '未分组'])->value('id');
                    Db::name('wxk_live_qr')->where(['group_id' => $param['id']])->update(['group_id' => $group_id]);

                    $id = $this->where(['id' => $param['id']])->delete();

                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    response(500, $e->getMessage());
                }

                return $id;
            }
            // 编辑
            $is_name                    = $this->where(['name' => $param['name']])->where([['id', '<>', $param['id']]])->count();

            if ($is_name){
                response(500, '分组名称重复');
            }

            $id     = $this->where(['id' => $param['id']])->update(['name' => $param['name']]);
        } else{
            param_receive(['name', 'parent_code']);
            $is_name                    = $this->where(['parent_code' => $param['parent_code'], 'name' => $param['name']])->count();

            if ($is_name){
                response(500, '分组名称重复');
            }
            $param['id']                = uuid();
            $param['create_time']       = format_time(time());
            $id                         = $this->save($param);
        }
        return $id;
    }

    /**
     * 获取活码分组列表
     * User: 万奇
     * Date: 2020/11/20 0020
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function live_code_group(){
        $list       = array_grouping($this->order(['create_at' => 'asc'])->select()->toArray(), 'parent_code');

        if (!count($list)){
            response(500);
        }

        $list       = category_group($list, $list[0], 'group', 'code');

        return $list;
    }

}