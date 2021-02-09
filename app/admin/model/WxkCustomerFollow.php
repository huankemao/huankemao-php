<?php
/**
 * Created by Shy
 * Date 2020/12/24
 * Time 16:31
 */


namespace app\admin\model;


use think\facade\Cache;
use think\facade\Db;
use think\Model;

class WxkCustomerFollow extends Model
{

    /**
     * Curd
     * User:Shy
     * @param $data
     * @param $type
     * @return \think\response\Json
     */
    static function Curd($data,$type){
        Db::startTrans();
        try{
            if($type == 1){
                $model = new self();
                $data['id'] = uuid();
            }else{
                $model = self::find($data['id']);
            }
            if($model->save($data)){
                Db::commit();
                return rsp(200,'成功');
            }
        }catch (\Exception $e){
            Db::rollback();
            return rsp(200,'操作数据发生错误，原因:'.$e->getMessage());
        }
    }


    /**
     * 添加互动轨迹
     * User:Shy
     * @param Model $WxkCustomerFollow
     */
    static function onAfterInsert($WxkCustomerFollow){
        $arr[] = ['external_user_id','=',$WxkCustomerFollow->external_user_id];
        $arr[] = ['follow_status','=',$WxkCustomerFollow->follow_status];
        $customer_track = self::where($arr)->count();
        $model = new WxkCustomerTrack();
        $username =  Cache::store('file')->get($WxkCustomerFollow->user_id);
        $customer_track_status = @config('common.customer_track_status')[$WxkCustomerFollow->follow_status]?:'传值错误';
        if($customer_track-1 > 0){
            $number = ($customer_track-1) + 1;
        }else{
            $number = 1;
        }
        $content = "【{$username['phone']}】 第{$number}次跟进，为【{$customer_track_status}】状态";
        $array = ['external_user_id'=>$WxkCustomerFollow->external_user_id,'track_content'=>$content,'track_content_type'=>1,'id'=>uuid()];
        $model->save($array);
    }

}