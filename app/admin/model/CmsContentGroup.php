<?php
/**
 * Created by Shy
 * Date 2020/12/11
 * Time 15:03
 */


namespace app\admin\model;


use think\Model;

class CmsContentGroup extends Model
{

    /**
     * 获取素材组列表
     * User: 万奇
     * Date: 2021/1/8 0008
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_content_group_list(){
        $list       = $this->field('id,name')->select()->toArray();

        return $list;
    }

    /**
     * User：shy
     * @param $data
     * @param $type
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function OperatingData($data, $type)
    {
        if ($type == 1) {
            $model = new self();
            $model->id = uuid();
        } else {
            $model = self::find($data['id']);
        }
        if ($type == 3) {
            $model->delete();
            //此分组下面所有内容分组ID  -> 未分组
            CmsContentEngine::update(['content_group_id'=>1],['content_group_id'=>$data['id']]);
        } else {
            if (!$model->save($data)) {
                return rsp(500, '失败');
            }
        }
        return rsp(200, '成功');
    }
}