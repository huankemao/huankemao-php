<?php
/**
 * Created by Shy
 * Date 2020/12/16
 * Time 16:12
 */


namespace app\admin\model;


use think\Model;

class CmsContentOperating extends Model
{

    protected $createTime = 'created_at';

    static function ContentAdd($data)
    {
        if (is_array($data['arr'])) {
            foreach ($data['arr'] as &$v) {
                $v['id'] = uuid();
                if ($data['type'] == 1) {
                    $v['search_num'] = 1;
                }elseif ($data['type'] == 2){
                    $v['send_num'] = 1;
                }else{
                    $v['open_num'] = 1;
                }
                $v['created_at'] = date('Y-m-d');
            }

            if ($data['type'] == 1 && empty($data[0]['search_name'])){
                return rsp(200, '成功');
            }

            $model = new self();
            $model->insertAll($data['arr']);
            return rsp(200, '成功');
        }
    }
}