<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/12/24 0024
 * Time: 15:59
 */

namespace app\admin\model;


use think\facade\Db;

class WxkWelcome extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 删除欢迎语
     * User: 万奇
     * Date: 2020/12/24 0024
     * @param $param
     */
    public function del_welcome($param){
        $this->where(['id' => $param['id']])->delete();
    }

    /**
     * 欢迎语列表
     * User: 万奇
     * Date: 2020/12/24 0024
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function get_welcome_list($param){
        $list       = $this->order(['create_at' => 'desc'])->paginate($param['limit'])->toArray();

        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['user_name']      = $v['user_id'] ? $v['user_name'] : '全体成员';
            $list['data'][$k]['welcome_data']   = json_decode($v['welcome_data'], true);
        }

        return $list;
    }

    /**
     * 新增编辑欢迎语
     * User: 万奇
     * Date: 2020/12/24 0024
     * @param $param
     * @return WxkWelcome|int|string
     */
    public function add_welcome($param){
        $welcome_type           = '';
        $user_name              = [];
        $welcome_name           = ['text' => '文本', 'image' => '图片', 'link' => '图文', 'miniprogram' => '小程序'];
        foreach ($param['welcome_data'] as $k => $v){
            $welcome_type       .= !empty($welcome_type) ? '+' . $welcome_name[$k] : $welcome_name[$k];
        }

        $staff_list             = Db::name('wxk_staff')->where(['external_authority' => 1])->column('user_id,name', 'user_id');

        foreach (explode(',', $param['staff_user_id']) as $u_k => $u_v){
            $user_name[$u_k]    = $staff_list[$u_v]['name'];
        }

        $data     = [
            'welcome_type'      => $welcome_type,
            'welcome_data'      => json_encode($param['welcome_data']),
            'user_id'           => $param['user_id'],
            'user_name'         => implode(',', $user_name),
        ];

        if (is_exists($param['id'])){
            $result             = $this->update($data, ['id' => $param['id']]);
        } else{
            $data['id']         = uuid();
            $result             = $this->insert($data);
        }

        return $result;
    }

    /**
     * 回显新增编辑欢迎语
     * User: 万奇
     * Date: 2020/12/24 0024
     * @param $param
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show_add_welcome($param){
        $result['is_currency']      = $this->where(['user_id' => 0])->count() ? 0 : 1;

        $department_model           = new WxkDepartment();
        $result['staff_list']       = $department_model->get_section_tree_staff(true);

        if (isset($param['id'])){
            $welcome_info           = $this->field('user_id,welcome_data')->where(['id' => $param['id']])->find();
            $result['user_list']    = explode(',', $welcome_info['user_id']);
            $result['welcome_data'] = json_decode($welcome_info['welcome_data'], true);
        }

        return $result;
    }


}