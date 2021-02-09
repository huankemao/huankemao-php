<?php
/**
 * Created by Shy
 * Date 2020/12/11
 * Time 11:15
 */


namespace app\admin\model;


use Exception;
use think\facade\Db;
use think\Model;
use think\response\Json;

class CmsContentEngine extends Model
{

    /**
     * 素材列表
     * User: 万奇
     * Date: 2021/1/8 0008
     * @param $param
     * @return array
     */
    public function get_content_engine_list($param){
        $where[]      = ['a.source', '=', 2];

        if (is_exists($param['group_id'])){
            $where[]  = ['a.content_group_id', '=', $param['group_id']];
        }

        if (is_exists($param['content_type'])){
            $where[]  = ['a.type', '=', $param['content_type']];
        }

        if (is_exists($param['keyword'])){
            $where[]  = ['a.title|a.content|a.file_name|a.explain', 'like', "%{$param['keyword']}%"];
        }

        $list       = $this->alias('a')->field('a.*,b.name group_name')
                    ->join('cms_content_group b','a.content_group_id=b.id', 'left')
                    ->where($where)
                    ->order(['a.create_at' => 'desc'])
                    ->paginate($param['limit'])
                    ->toArray();

        return ['data' => $list['data'], 'count' => $list['total']];
    }

    /**
     * 获取内容
     * User: 万奇
     * Date: 2021/1/7 0007
     * @param $id
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_temporary_preview($id){
        $info       = $this->find($id);

        if (!$info){
            return [];
        }

        $info                   = $info->toArray();

        $info['phone']     = Db::name('sys_user')->where(['id' => $info['user_id']])->value('phone');

        return $info;
    }

    /**
     * User:Shy
     * curd
     * @param $data
     * @param $type
     * @return Json
     */
    static function OperatingData($data, $type)
    {
        Db::startTrans();
        try {
            $model = new self();
            foreach ($data['arr'] as &$v) {
                if ($type == 3){
                    if ($v['source'] == 1){
                        $model->where(['id' => $v['id']])->update(['source' => $v['del_type']]);
                    } else{
                        $model->where(['id' => $v['id']])->delete();
                    }
                    continue;
                }

                if ((isset($v['title']) || isset($v['explain'])) && strlen($v['title']) > 255) {
                    return rsp(500, '说明最大长度为255');
                }
                if ($type == 1) {
                    $v['id'] = uuid();
                    $v['source']        = $v['source'] == 1 ? 2 : 1;
                }
                $v['user_id'] = $data['user_id'];
                if ($v['type'] == 3) {
                    $v['content'] = htmlspecialchars_decode($v['content']);
                }
                if ($v['type'] == 7) {
                    $file_suffix = @Config('common.content_file_type')[$v['file_suffix']];
                    $v['file_suffix'] = $file_suffix ?: 10;
                }
            }
            if ($type == 1) {
                $model->insertAll($data['arr']);
            } elseif ($type == 2) {
                $model->saveAll($data['arr']);
            }
            Db::commit();
            return rsp(200, '成功');
        } catch (Exception $e) {
            Db::rollback();
            return rsp(500, $e->getMessage());
        }

    }

    /**
     * 企业微信上传临时素材
     * User: 万奇
     * Date: 2021/1/27 0027
     * @param $file_path - 地址 (从public下开始)
     * @param $file_name - 文件名
     * @param $file_type - 类型
     * @return mixed
     */
    public function upload_qy_material($file_path, $file_name, $file_type){
        $wx_request_url = config('common.wx_upload');
        $access_token = getAccessToken();
        $wx_request_url = $wx_request_url . "access_token=$access_token&type=$file_type";
        $medias = new \CURLFile(realpath(substr('@' . $file_path, 1)), 'application/octet-stream', $file_name);
        $result = json_decode($this->curl_post($wx_request_url, ['media' => $medias]),true);

        if ($result['errcode'] != 0) {
            response(500, '企业微信素材上传失败');
        }

        return $result;
    }

    /**
     * @param $url
     * @param $post_data
     * @return bool|string
     */
    public function curl_post($url,$post_data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * User:Shy
     * 时间查询
     * @param $date_type
     * @param $data
     * @param $start_time
     * @param $end_time
     * @param $page
     * @param $limit
     * @return array
     */
    static function date($date_type, $data, $start_time, $end_time, $page, $limit)
    {
        $data_dates = array_column($data, 'created_at');
        if ($date_type == 1) {
            //获取指定时间范围内的每天
            $count = Date::getDateFromRange($start_time, $end_time);
            foreach ($count as $v) {
                if (!in_array($v, $data_dates)) {
                    array_push($data, ['num' => 0, 'created_at' => $v]);
                }
            }
            $result = $data;
        } else {
            if ($date_type == 2) {
                //根据每天获取每周开始结束时间
                foreach ($data as &$l) {
                    $l['created_at'] = Date::weeks($l['created_at']);
                }
                //获取指定时间范围内的每周
                $count = Date::get_weeks($start_time, $end_time);
            } else {
                //根据每天获取每月
                foreach ($data as &$l) {
                    $l['created_at'] = Date::months($l['created_at']);
                }
                //获取指定时间范围内的每月
                $count = Date::get_months($start_time, $end_time);
            }
            foreach ($count as $w) {
                if (!in_array($w, $data_dates)) {
                    array_push($data, ['num' => 0, 'created_at' => $w]);
                }
            }
            $item = [];
            //根据指定value相同的key相加
            foreach ($data as $k => $v) {
                if (!isset($item[$v['created_at']])) {
                    $item[$v['created_at']] = $v;
                } else {
                    $item[$v['created_at']]['num'] += $v['num'];
                }
            }
            $result = array_values($item);
        }
        $result = getDatePageLimit($result, 'created_at', $page, $limit);
        return ['count' => count($count), 'data' => $result];
    }

}