<?php
/**
 * Created by Shy
 * Date 2020/12/10
 * Time 11:43
 */


namespace app\admin\controller\v1;

use app\admin\model\CmsContentEngine as ContentEngineModel;
use app\admin\model\CmsContentEngine;
use app\admin\model\CmsContentGroup;
use app\admin\model\CmsContentOperating;
use app\Request;
use app\validate\ContnetGroup;
use StaticData;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\facade\Db;
use think\response\Json;

class ContentEngine
{

    /**
     * 根据media_id 获取内容
     * User: 万奇
     * Date: 2021/1/4 0004
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get_media_id_content(){
        $param      = param_receive(['media_id']);
        $result     = ContentEngineModel::where(['media_id' => $param['media_id']])->find();

        response(200, '', $result);
    }

    /**
     * 获取临时预览文件
     * User: 万奇
     * Date: 2020/12/31 0031
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get_temporary_preview(){
        $param      = param_receive(['code']);

        if (is_exists($param['type'])){
            $model      = new CmsContentEngine();
            $result     = $model->get_temporary_preview($param['code']);
        } else{
            $result     = Cache::get($param['code']);
        }

        response(200, '', $result);
    }

    /**
     * 添加临时预览文件
     * User: 万奇
     * Date: 2020/12/31 0031
     * @return string
     */
    public function set_content_preview(){
        $param      = param_receive(['content', 'title', 'phone']);

        $data       = ['content' => htmlspecialchars_decode($param['content']), 'title' => $param['title'], 'phone' => $param['phone'], 'create_at' => date('Y-m-d')];

        if (isset($param['link'])){
            $data['link']   = $param['link'];
        }

        $code       = uuid();
        Cache::set($code, $data, 7200);

        response(200, '', $code);
    }

    /**
     * 素材数据统计次数
     * User: 万奇
     * Date: 2021/1/11 0011
     */
    public function content_data_total(){
        $param      = param_receive(['id']);

        $model      = new \app\admin\model\WxkArticleReadingLog();
        $result     = $model->content_data_total($param);

        response(200, '', $result);
    }

    /**
     * 素材数据统计详情列表
     * User: 万奇
     * Date: 2021/1/11 0011
     * @throws \think\db\exception\DbException
     */
    public function content_data_details(){
        $param      = param_receive(['id', 'type', 'page', 'limit']); // keyword(搜索)

        $model      = new \app\admin\model\WxkArticleReadingLog();
        $result     = $model->content_data_details($param);

        response(200, '', $result['data'], $result['total']);
    }

    /**
     * User:shy
     * 分组列表
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function ContentGroupList()
    {
        $results = Db::name('cms_content_group')->order(['create_at' => 'asc'])->select()->toArray();
        if ($results) {
            foreach ($results as &$v) {
                $count = ContentEngineModel::where(['content_group_id' => $v['id']])->select();
                $v['count'] = count($count);
            }
        }
        return rsp(200, '成功', ['total' => count($results), 'data' => $results]);
    }


    /**
     * User:Shy
     * 添加分组
     * @param Request $request
     * @return Json
     */
    public function ContentGroupAdd(Request $request)
    {
        validate(ContnetGroup::class)
            ->scene('add')
            ->check($request->data);
        return CmsContentGroup::OperatingData($request->data, 1);
    }


    /**
     * User:shy
     * 修改分组
     * @param Request $request
     * @return Json
     */
    public function ContentGroupEdit(Request $request)
    {
        validate(ContnetGroup::class)
            ->scene('edit')
            ->check($request->data);
        return CmsContentGroup::OperatingData($request->data, 2);
    }


    /**
     * user:shy
     * 删除分组
     * @param Request $request
     * @return Json
     */
    public function ContentGroupDel(Request $request)
    {
        validate(ContnetGroup::class)
            ->scene('del')
            ->check($request->data);
        return CmsContentGroup::OperatingData($request->data, 3);
    }


    /**
     * User:Shy
     * 内容列表
     * @param Request $request
     * @return Json
     * @throws DbException
     */
    public function ContentList(Request $request)
    {
        verify_data('type,search_name,page,limit', $request->data);
        $sql = "1=1";
        if (isset($request->data['source']) && $request->data['source'] == 2){
            $sql .= " AND (a.`source` <> 2)";
        }else{
            $sql .= " AND (a.`source` <> 3)";
        }
        if ($request->data['type']) {
            $sql .= " AND a.`type` = {$request->data['type']}";
        }
        if ($request->data['content_group_id']) {
            $sql .= " AND a.`content_group_id` =  '{$request->data['content_group_id']}'";
        }
        if ($request->data['search_name']) {
//            from_base64(a.`content`)
            $search_name = $request->data['search_name'];
            $search_sql = "  AND (a.`title` LIKE '%$search_name%' OR a.`content` LIKE '%$search_name%' OR a.`file_name` LIKE '%$search_name%')";
            $search_type_sql = "  AND ((a.content like '%$search_name%' and a.type = 1) or (a.file_name like '%$search_name%' and a.type in(2,4,5,7)) or (a.title like '%$search_name%' and a.type in(3,6,8)))";
            $sql .= $request->data['type'] ? $search_sql : $search_type_sql;
        }

        $data = Db::name('cms_content_engine')
            ->field("a.id,c.phone as user_id,title,a.content,a.file_name,a.file_suffix,a.explain,a.content_group_id,b.name as content_group_name,a.type,a.user,a.cover,a.wx_cover,a.summary,a.link,a.media_id,a.created_at,a.applets_id,a.applets_path,a.source,a.create_at,a.update_at")
            ->alias('a')
            ->leftJoin('cms_content_group b', 'a.content_group_id=b.id')
            ->leftJoin('sys_user c', 'a.user_id=c.id')
            ->whereRaw($sql)
            ->order(['create_at' => 'desc'])
            ->paginate($request->data['limit'])
            ->toArray();
        return rsp(200, '成功', $data ?: []);
    }


    /**
     * User:Shy
     * 内容添加
     * @param Request $request
     * @return Json
     */
    public function ContentAdd(Request $request)
    {

        verify_data('arr', $request->data);
        return ContentEngineModel::OperatingData($request->data, 1);
    }

    /**
     * User:Shy
     * 内容修改
     * @param Request $request
     * @return Json
     */
    public function ContentEdit(Request $request)
    {
        verify_data('arr', $request->data);
        return ContentEngineModel::OperatingData($request->data, 2);
    }


    /**
     * User:Shy
     * 内容删除
     * @param Request $request
     * @return Json
     */
    public function ContentDel(Request $request)
    {
        verify_data('arr', $request->data);
        return ContentEngineModel::OperatingData($request->data, 3);
    }


    /**
     * User:Shy
     * 上传
     * @param Request $request
     * @return Json
     */
    public function photo(Request $request)
    {
        $time = date('YmdHis');
        $media_id = '';
        $created_at = '';
        if ($_FILES["file"]["error"]) {
            return rsp(500, $_FILES["file"]["error"]);
        }
        $end_name = strtoupper(mb_substr(strrchr($_FILES["file"]["name"], '.'), 1));
        //图片
        if (($request->data['type'] == 2 || $request->data['type'] == 3) && ($_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/jpg") && $_FILES["file"]["size"] < StaticData::RESOURCE_NAME['file_type_size']['image']) {
            $filename = './static/photo/' . $time . '.jpg';
            $type = 'image';
        } //音频
        elseif ($request->data['type'] == 4 && $_FILES["file"]["type"] == "application/octet-stream" && $_FILES["file"]["size"] < StaticData::RESOURCE_NAME['file_type_size']['voice']) {
            $filename = './static/audio/' . $time . '.amr';
            $type = 'voice';
        } //视频
        elseif ($request->data['type'] == 5 && $_FILES["file"]["type"] == "video/mp4" && $_FILES["file"]["size"] < StaticData::RESOURCE_NAME['file_type_size']['video']) {
            $filename = './static/video/' . $time . '.mp4';
            $type = 'video';
        } //文件
        elseif ($request->data['type'] == 7 && $_FILES["file"]["size"] < StaticData::RESOURCE_NAME['file_type_size']['file']) {
            $filename = './static/file/' . $time . '.' . $end_name;
            $type = 'file';
        } else {
            return rsp(500, '此类型不支持上传');
        }
        $filename = iconv("UTF-8", "gb2312", $filename);
        move_uploaded_file($_FILES["file"]["tmp_name"], $filename);
        $url = \think\facade\Request::domain() . mb_substr($filename, 1);
        //上传至微信
        if ($request->data['type'] == 2 || $request->data['type'] == 5 || $request->data['type'] == 4 || $request->data['type'] == 7) {
            $model      = new CmsContentEngine();
            $result     = $model->upload_qy_material($filename, $_FILES["file"]['name'], $type);
            $media_id   = $result['media_id'];
            $created_at = $result['created_at'];
        }
        return rsp(200, '成功', ['content' => $url, 'file_name' => $_FILES['file']['name'], 'file_suffix' => $end_name,'media_id'=>$media_id,'created_at'=>$created_at]);
    }

    /**
     * User:Shy
     * 搜索，发送
     * @param Request $request
     * @return Json
     */
    public function ContentOperating(Request $request)
    {
        return CmsContentOperating::ContentAdd($request->data);
    }

    /**
     * User:Shy
     * 内容详情 (废弃)
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function ContentDetails(Request $request)
    {
        verify_data('content_engine_id', $request->data);
        $data = Db::name('cms_content_operating')
            ->alias('a')
            ->leftJoin('wxk_customer b', 'a.wx_customer_id=b.external_user_id')
            ->leftJoin('wxk_staff c', 'a.wx_user_id=c.user_id')
            ->where('content_engine_id', $request->data['content_engine_id'])
            ->field('a.create_at,a.search_name,c.name as wx_staff_name,b.name as wx_customer_name,search_num,send_num,open_num')
            ->select()
            ->toArray();
        $arr['search'] = [];
        $arr['send'] = [];
        $arr['open'] = [];
        $sum = [];
        $sum['search_num'] = array_sum(array_column($data, 'search_num'));
        $sum['send_num'] = array_sum(array_column($data, 'send_num'));
        $sum['open_num'] = array_sum(array_column($data, 'open_num'));
        foreach ($data as $key => $v) {
            if ($v['search_num'] > 0) {
                $arr['search'][$key] = $v;
            }
            if ($v['send_num'] > 0) {
                $arr['send'][$key] = $v;
            }
            if ($v['open_num'] > 0) {
                $arr['open'][$key] = $v;
            }
        }
        return rsp(200, '成功', ['total' => $sum, 'search' => array_values($arr['search']), 'send' => array_values($arr['send']), 'open' => array_values($arr['open'])]);
    }

    public function WxSign(Request $request)
    {
        $access_token = getAccessToken();
        $url =  Config('common.wx_jsapi_ticket');
        $url .= "access_token=$access_token&type=agent_config";
        $result = httpGet($url);
        $result = json_decode($result, true);
        if($result['errcode'] != 0){
            return rsp(500,$result['errmsg']);
        }
        $token['jsapi_ticket'] = $result['ticket'];
        $token['noncestr']   = random_string();
        $token['timestamp'] = time();
        $token['url'] = 'http://huankemao.vip.brt360.com/content/manage';
        $string = '';
        foreach ($token as $key=>$v){
            $string .= ($key . "=" . $v . "&");
        }
        $string = sha1(mb_substr($string, 0, -1));
        return rsp(200,'成功',['signature'=>$string]);
    }

    /**
     * User:Shy
     * 搜索，发送，打开次数
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function ContentSearch(Request $request)
    {
        verify_data('search_name,content_engine_group_id,content_engine_type,page,limit,start_time,end_time,type', $request->data);
        $where = [];
        if ($request->data['wx_user_id']) {
            $where[] = ['wx_user_id', 'in', explode(',', $request->data['wx_user_id'])];
        }
        if ($request->data['search_name']) {
            $where[] = ['search_name', '=', $request->data['search_name']];
        }
        if ($request->data['content_engine_group_id']) {
            $where[] = ['content_engine_group_id', '=', $request->data['content_engine_group_id']];
        }
        if ($request->data['content_engine_type']) {
            $where[] = ['content_engine_type', '=', $request->data['content_engine_type']];
        }

        if ($request->data['type'] == 1) {
            $condition = 'search_num';
        } elseif ($request->data['type'] == 2) {
            $condition = 'send_num';
        } else {
            $condition = 'open_num';
        }
        $where[] = [$condition, '>', 0];
        $data = Db::name('cms_content_operating')
            ->field("count($condition) as num,created_at")
            ->where($where)
            ->whereTime('created_at', 'between', [$request->data['start_time'], $request->data['end_time']])
            ->group('created_at')
            ->select()
            ->toArray();
        if (!empty($data)) {
            $result = ContentEngineModel::date($request->data['date_type'], $data, $request->data['start_time'], $request->data['end_time'], $request->data['page'], $request->data['limit']);
        } else {
            $result = ['count' => 0, 'data' => []];
        }
        return rsp(200, '成功', $result);
    }


    /**
     * User:Shy
     * 内容次数
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function ContentNum()
    {
        $YesToday = date("Y-m-d", strtotime("-1 day"));
        $BeforeYesterday = date("Y-m-d", strtotime("-2 day"));
        $YesToday = Db::name('cms_content_operating')
            ->field('IFNULL(sum(search_num),0) as search_num,IFNULL(sum(send_num),0) as send_num,IFNULL(sum(open_num),0) as open_num')
            ->whereTime('created_at', 'between', [$YesToday, $YesToday])
            ->find();
        $BeforeYesterday = Db::name('cms_content_operating')
            ->field('IFNULL(sum(search_num),0) as search_num,IFNULL(sum(send_num),0) as send_num,IFNULL(sum(open_num),0) as open_num')
            ->whereTime('created_at', 'between', [$BeforeYesterday, $BeforeYesterday])
            ->find();
        foreach ($BeforeYesterday as $key => $v) {
            if ($v == $YesToday[$key]) {
                $YesToday[$key . '_before'] = '0';
            } elseif ($v <= 0 && $YesToday[$key] != 0) {
                $YesToday[$key . '_before'] = '100%';
            } elseif ($v != 0 && $YesToday[$key] == 0) {
                $YesToday[$key . '_before'] = '-100%';
            } else {
                if ($YesToday[$key] > $v) {
                    $YesToday[$key . '_before'] = $v / $YesToday[$key] * 100 . '%';
                } else {
                    $YesToday[$key . '_before'] = -$YesToday[$key] / $v * 100 . '%';
                }
            }
        }
        return rsp(200, '成功', $YesToday);

    }

    /**
     * User:Shy
     * 内容TOP10
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function ContentTop(Request $request)
    {
        verify_data('search_name,content_engine_group_id,content_engine_type,wx_user_id,page,limit,start_time,end_time,type', $request->data);
        $where = [];
        if ($request->data['wx_user_id']) {
            $where[] = ['wx_user_id', 'in', explode(',', $request->data['wx_user_id'])];
        }
        if ($request->data['search_name']) {
            $where[] = ['content_engine_title', 'like', "%{$request->data['search_name']}%"];
        }
        if ($request->data['content_engine_group_id']) {
            $where[] = ['content_engine_group_id', '=', $request->data['content_engine_group_id']];
        }
        if ($request->data['content_engine_type']) {
            $where[] = ['content_engine_type', '=', $request->data['content_engine_type']];
        }

        if ($request->data['type'] == 1) {
            $condition = 'search_num';
            $where[] = ['search_num', '>', 0];
        } elseif ($request->data['type'] == 2) {
            $condition = 'send_num';
            $where[] = ['send_num', '>', 0];
        } else {
            $condition = 'open_num';
            $where[] = ['open_num', '>', 0];
        }
        $data = Db::name('cms_content_operating')->field("count($condition) as num,content_engine_title,content_engine_type")->where($where)->whereTime('created_at', 'between', [$request->data['start_time'], $request->data['end_time']])->group('content_engine_title')->select()->toArray();
        $result = getDatePageLimit($data, 'num', $request->data['page'], $request->data['limit']);
        return rsp(200, '成功', ['count' => count($data), 'data' => $result]);
    }

    /**
     * User:Shy
     * 员工TOP10
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function StaffTop(Request $request)
    {
        verify_data('content_engine_group_id,content_engine_type,page,limit,start_time,end_time', $request->data);
        $where = [];
        if ($request->data['content_engine_group_id']) {
            $where[] = ['a.content_engine_group_id', '=', $request->data['content_engine_group_id']];
        }
        if ($request->data['content_engine_type']) {
            $where[] = ['a.content_engine_type', '=', $request->data['content_engine_type']];
        }

        if ($request->data['type'] == 1) {
            $condition = 'search_num';
            $where[] = ['search_num', '>', 0];
        } elseif ($request->data['type'] == 2) {
            $condition = 'send_num';
            $where[] = ['send_num', '>', 0];
        } else {
            $condition = 'open_num';
            $where[] = ['open_num', '>', 0];
        }

        $data = Db::name('cms_content_operating')
            ->field("b.mobile as phone,b.name,count($condition) as create_count")
            ->where($where)
            ->alias('a')
            ->leftJoin('wxk_staff b', 'a.wx_user_id=b.user_id')
            ->whereTime('a.created_at', 'between', [$request->data['start_time'], $request->data['end_time']])
            ->group('a.wx_user_id')
            ->select()
            ->toArray();

        $result = getDatePageLimit($data, 'create_count', $request->data['page'], $request->data['limit']);
        return rsp(200, '成功', ['count' => count($data), 'data' => $result]);
    }

    /**
     * User:Shy
     * 搜索TP10
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function SearchTop(Request $request)
    {
        verify_data('page,limit,start_time,end_time', $request->data);
        $data = Db::name('cms_content_operating')
            ->field('search_name,count(id) as num')
            ->whereTime('created_at', 'between', [$request->data['start_time'], $request->data['end_time']])
            ->group('search_name')
            ->select()
            ->toArray();
        $result = getDatePageLimit($data, 'num', $request->data['page'], $request->data['limit']);
        return rsp(200, '成功', ['count' => count($data), 'data' => $result]);
    }

    /**
     * User:Shy
     * 类型列表
     * @return Json
     */
    public function ContentTypeList()
    {
        $data = StaticData::RESOURCE_NAME['content_type_list'];
        $result = Array_Data($data);
        return rsp(200, '成功', $result);
    }

}