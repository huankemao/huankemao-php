<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/19 0019
 * Time: 18:14
 */

namespace app\admin\model;

use think\facade\Db;

class WxkConfig extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 删除config配置
     * User: 万奇
     * Date: 2020/12/21 0021
     * @return WxkConfig
     */
    public function del_config(){
        $update     = ['wxk_id' => '', 'wxk_address_book_secret' => '', 'wxk_customer_admin_secret' => ''];
        $result     = $this->where(true)->update($update);

        return $result;
    }

    /**
     * 企业微信列表
     * User: 万奇
     * Date: 2020/11/19 0019
     * @param $param
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function wxk_config_list($param, $limit = 10){
        $where      = [];

        if (is_exists($param['keyword'])){
            $where[]      = ['wxk_name', 'like', "%{$param['keyword']}%"];
        }

        $list       = $this->where($where)->order('id', 'desc')->paginate($limit)->toArray();

        return ['data' => $list['data'], 'count' => $list['total']];
    }

    /**
     * 初始化未分组数据
     * User: 万奇
     * Date: 2020/12/22 0022
     */
    public function initial_data(){
        // 初始化未分组数据
        $insert       = ['id' => uuid(), 'parent_code' => 0, 'name' => '未分组'];

        // 活码分组
        Db::name('wxk_live_qr_group')->insert($insert);

        // 客户标签
        Db::name('wxk_customer_tag')->insert($insert);
    }

    /**
     * 新增编辑企业微信
     * User: 万奇
     * Date: 2020/11/19 0019
     * @param $param
     * @param $uid
     */
    public function add_qy_wechat($param){
        try {
            // 编辑
            if (isset($param['id'])){
                $this->update($param);
            } else{
                $this->save(array_merge($param, ['id' => uuid()]));
            }

        } catch (\Exception $e){
            response(500, $e->getMessage());
        }
    }

    /**
     * 获取单条企业微信信息
     * User: 万奇
     * Date: 2020/12/3 0003
     * @return array
     */
    public function get_qy_info(){
        try {
            $info = $this->where(true)->find();

            if (!$info) {
                response(500, '请配置企业微信管理');
            }

        } catch (\Exception $e) {
            response(500, $e->getMessage());
        }

        return $info->toArray();
    }

}