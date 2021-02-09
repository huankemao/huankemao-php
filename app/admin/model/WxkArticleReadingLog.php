<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/7 0007
 * Time: 16:47
 */

namespace app\admin\model;


use app\core\BaseModel;
use think\facade\Db;

class WxkArticleReadingLog extends BaseModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 素材数据统计详情列表
     * User: 万奇
     * Date: 2021/1/12 0012
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function content_data_details($param){
        switch ($param['type']){
            case 1 :
                $where[]      = ['content_id', '=', $param['id']];

                if (is_exists($param['keyword'])){
                    $where[]      = ['b.name', 'like', "%{$param['keyword']}%"];
                }
                $list       = $this->alias('a')->field('a.*,b.name staff_name')
                            ->join('wxk_staff b', 'a.staff_user_id=b.user_id', 'left')
                            ->where($where)
                            ->order(['reading_time' => 'desc'])
                            ->paginate($param['limit'])
                            ->toArray();
                break;
            case 2 :
                $where[]      = ['a.content_engine_id', '=', $param['id']];

                if (is_exists($param['keyword'])){
                    $where[]      = ['b.name', 'like', "%{$param['keyword']}%"];
                }
                $list       = Db::name('cms_content_operating')->alias('a')
                            ->field('b.name staff_name,a.content_engine_title,c.name customer_name,a.create_at')
                            ->join('wxk_staff b', 'a.wx_user_id=b.user_id', 'left')
                            ->join('wxk_customer c', 'a.wx_customer_id=c.external_user_id', 'left')
                            ->where($where)
                            ->where(['a.send_num' => 1])
                            ->order(['create_at' => 'desc'])
                            ->group('a.id')
                            ->paginate($param['limit'])
                            ->toArray();
                break;
            case 3 :
                $where[]      = ['content_engine_id', '=', $param['id']];

                if (is_exists($param['keyword'])){
                    $where[]      = ['b.name', 'like', "%{$param['keyword']}%"];
                }

                $list       = Db::name('cms_content_operating')->alias('a')
                            ->field('b.name staff_name,COUNT(DISTINCT case when a.open_num = 1 then a.wx_customer_id end) read_number,sum(a.open_num) read_times,
                            COUNT(DISTINCT case when a.send_num = 1 then a.wx_customer_id end) share_number,sum(a.send_num) share_times')
                            ->join('wxk_staff b', 'a.wx_user_id=b.user_id', 'left')
                            ->where($where)
                            ->order(['a.create_at' => 'desc'])
                            ->group('a.wx_user_id')
                            ->paginate($param['limit'])
                            ->toArray();
                break;
        }

        return $list;
    }

    /**
     * 素材数据统计次数
     * User: 万奇
     * Date: 2021/1/11 0011
     * @param $param
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function content_data_total($param){

        $result     = Db::name('cms_content_operating')
                    ->field('sum(open_num = 1) as read_num,sum(send_num = 1) as share_num,sum(search_num = 1) as search_num')
                    ->where(['content_engine_id' => $param['id']])
                    ->find();

        return $result;
    }


    /**
     * 新增文章阅读记录
     * User: 万奇
     * Date: 2021/1/7 0007
     * @param $param
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function add_article_reading($param){
        if (is_exists($param['id'])){
            param_receive([ 'reading_duration']);

            $result             = $this->where(['id' => $param['id']])->update(['reading_duration' => $param['reading_duration']]);
        } else{
            param_receive(['content_id', 'openid', 'staff_user_id', 'reading_time', 'reading_duration']);

            $param['id']        = uuid();
            $this->save($param);
            $result             = $param['id'];

            $content_info       = Db::name('cms_content_operating')->where(['id' => $param['content_id']])->find();
            $insert             = ['id' => uuid(), 'open_num' => 1, 'content_engine_id' => $param['content_id'], 'wx_customer_id' => $param['openid'],
                                'wx_user_id' => $param['staff_user_id'], 'content_engine_title' => $content_info['title'],
                                'content_engine_group_id' => $content_info['content_group_id'], 'content_engine_type' => $content_info['type'], 'created_at' => date('Y-m-d')];
            Db::name('cms_content_operating')->insert($insert);
        }

        return $result;
    }

}