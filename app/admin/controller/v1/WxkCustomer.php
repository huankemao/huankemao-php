<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/29 0029
 * Time: 23:15
 */

namespace app\admin\controller\v1;


use app\admin\model\WxkCustomerFollow;
use app\Request;
use think\App;
use think\facade\Db;

class WxkCustomer extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 客户打标签/移除标签
     * User: 万奇
     * Date: 2021/1/15 0015
     */
    public function customer_tagging(){
        param_receive(['id', 'tag_ids', 'type']);
        $customer = new \app\admin\model\WxkCustomer();
        $customer->customer_tagging($this->param);

        response(200, '操作成功');
    }

    /**
     * 客户打标签回显已有的标签
     * User: 万奇
     * Date: 2021/1/14 0014
     */
    public function show_customer_tag(){
        param_receive(['id', 'type']);
        $customer = new \app\admin\model\WxkCustomer();
        $result = $customer->show_customer_tag($this->param);

        response(200, '', $result);
    }

    /**
     * 重复客户列表
     * User: 万奇
     * Date: 2021/1/14 0014
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function repeat_list_customer(){
        param_receive(['page', 'limit']);

        $customer = new \app\admin\model\WxkCustomer();
        $result = $customer->repeat_list_customer($this->param);

        response(200, '', $result['data'], $result['count']);
    }

    /**
     * 客户列表渲染
     * User: 万奇
     * Date: 2021/1/14 0014
     */
    public function show_list_customer(){
        $customer = new \app\admin\model\WxkCustomer();
        $result = $customer->show_list_customer();

        response(200, '', $result);
    }

    /**
     * 客户列表
     * User: 万奇
     * Date: 2020/12/3 0003
     * @throws \think\db\exception\DbException
     */
    public function get_list_customer()
    {
        param_receive(['page', 'limit']);

        $customer = new \app\admin\model\WxkCustomer();
        $result = $customer->get_list_customer($this->param);

        response(200, '', $result['data'], $result['count']);
    }

    /**
     * 同步企业微信客户
     * User: 万奇
     * Date: 2020/12/4 0004
     * @throws \think\db\exception\DbException
     */
    public function synchro_customer()
    {
        // 同步客户标签
        $wechat_user = new \app\admin\model\WxkCustomerTag();
        $wechat_user->synchro_customer_tag();

        // 同步客户
        $wechat_user = new \app\admin\model\WxkCustomer();
        $wechat_user->synchro_customer();

        response(200, '操作成功');
    }


    /**
     * 客户跟进记录
     * User:Shy
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function CustomFollowRecord(Request $request)
    {
        verify_data('external_user_id,page,limit',$request->data);
        $result = Db::name('wxk_customer_follow')
            ->alias('a')
            ->leftJoin('sys_user b','a.user_id=b.id')
            ->field('a.id,a.external_user_id,a.user_id,b.username as user_name,a.follow_status,a.follow_record,a.imgs,a.create_at,a.update_at')
            ->where('external_user_id',$request->data['external_user_id'])
            ->paginate()
            ->toArray();
        if($result['data']){
            foreach ($result['data'] as &$v){
                if($v['imgs']){
                    $v['imgs']  = explode(',',$v['imgs']);
                }
            }
        }
        return rsp(200,'成功',$result);
    }


    /**
     * 添加客户跟进记录
     * User:Shy
     * @param Request $request
     * @return \think\response\Json
     */
    public function CustomFollowRecordAdd(Request $request){
        verify_data('external_user_id,follow_status,follow_record,imgs',$request->data);
        return WxkCustomerFollow::Curd($request->data,1);
    }

    /**
     * 修改客户跟进记录
     * User:Shy
     * @param Request $request
     * @return \think\response\Json
     */
    public function CustomFollowRecordEdit(Request $request){
        verify_data('id,external_user_id,follow_status,follow_record,imgs',$request->data);
        return WxkCustomerFollow::Curd($request->data,2);
    }




    public function CustomTrackRecord(Request $request){
        verify_data('external_user_id,page,limit',$request->data);
        $result = Db::name('wxk_customer_track')
            ->where('external_user_id',$request->data['external_user_id'])
            ->paginate()
            ->order('created_time desc')
            ->toArray();
        return rsp(200,'成功',$result);
    }
}