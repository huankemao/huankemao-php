<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/19 0019
 * Time: 18:13
 */

namespace app\admin\controller\v1;


use think\App;
use think\facade\Db;
use think\facade\Filesystem;

class WxkConfig extends BasicController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 获取回调URL
     * User: 万奇
     * Date: 2020/12/30 0030
     * @return string
     */
    public function get_callback_url(){
        $result['wxk_customer_callback_url']         = \request()->domain() . '/admin.php/external_contact';

        // 聊天资料应用主页
        $result['chat_app_home_url']                 = \request()->domain() . '/chat-tool/index.html';

        $result['domain']                            = \request()->host();

        $result['domain_verification_file']          = Db::name('wxk_config')->where(true)->value('domain_verification_file') ? : '';

        response(200, '', $result);
    }

    /**
     * 域名验证文件上传
     * User: 万奇
     * Date: 2021/1/26 0026
     */
    public function upload_domain_verification_file(){
        if (!isset($_FILES['domain_verification_file'])){
            response(500, '文件为空');
        }
        move_uploaded_file($_FILES['domain_verification_file']['tmp_name'], $_FILES['domain_verification_file']['name']);

        response(200, '操作成功', $_SERVER['HTTP_HOST'] . '/' . $_FILES['domain_verification_file']['name']);
    }

    /**
     * 企业微信列表
     * User: 万奇
     * Date: 2020/11/19 0019
     * @throws \think\db\exception\DbException
     */
    public function wxk_config_list(){
        param_receive([ 'page', 'limit']);

        $model      = new \app\admin\model\WxkConfig();
        $result     = $model->wxk_config_list($this->param, $this->param['limit']);

        response(200, '', $result['data'], $result['count']);
    }

    /**
     * 新增编辑企业微信
     * User: 万奇
     * Date: 2020/11/19 0019
     */
    public function add_wxk_config(){
        param_receive([ 'wxk_id']);

        $model      = new \app\admin\model\WxkConfig();
        $model->add_qy_wechat($this->param);

        response(200, '操作成功');
    }

    /**
     * 文件上传
     * User: 万奇
     * Date: 2020/11/11 0011
     * @param $name
     * @return string
     */
    public function upload_payment_file($name){
        $file = request()->file($name);
        try {
            // 验证文件规则
            validate(['file' => ['fileSize:102400']])->check(['file' => $file]);

            //上传到服务器,
            $path = Filesystem::disk('public')->putFile('upload', $file);

            $picCover = Filesystem::getDiskConfig('public', 'url') . '/' . str_replace('\\', '/', $path);

            return $_SERVER['HTTP_HOST'] . $picCover;
        } catch (\think\exception\ValidateException $e) {
            response(105, $e->getMessage());
        }
    }

}