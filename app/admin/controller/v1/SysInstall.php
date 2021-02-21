<?php
/**
 * 安装
 * Created by Shy
 * Date 2020/12/7
 * Time 10:43
 */


namespace app\admin\controller\v1;


use app\admin\model\SysApp;
use Exception;
use think\App;
use think\facade\Cache;
use think\facade\Db;
use think\Request;
use think\response\Json;

class SysInstall
{

    /**
     * 项目初始化同步
     * User: 万奇
     * Date: 2020/12/21 0021
     * @throws \think\db\exception\DbException
     * @throws \Exception
     */
    public function config_synchro(){
        set_time_limit(60);
        $cache = Cache::get('detect');
        Cache::clear();
        Cache::set('detect', $cache);

        // 同步部门
        $section    = new \app\admin\model\WxkDepartment();
        $section->synchro_department();

        // 同步成员
        $wechat_staff   = new \app\admin\model\WxkStaff();
        $wechat_staff->synchro_user();

        // 同步外部联系人权限
        $wechat_staff->synchro_follow_user();

        // 成员标签同步
        $staff_tag      = new \app\admin\model\WxkStaffTag();
        $staff_tag->sync_staff_tag();

        // 同步客户标签
        $customer_tag       = new \app\admin\model\WxkCustomerTag();
        $customer_tag->synchro_customer_tag();

        // 同步客户
        $wechat_customer    = new \app\admin\model\WxkCustomer();
        $wechat_customer->synchro_customer();

        response(200);
    }

    /**
     * 删除config配置
     * User: 万奇
     * Date: 2020/12/21 0021
     */
    public function del_config(){
        $model      = new \app\admin\model\WxkConfig();
        $model->del_config();

        response(200, '操作成功');
    }

    /**
     * 安装获取配置
     * User: 万奇
     * Date: 2020/12/24 0024
     * @throws \think\db\exception\DbException
     */
    public function get_config_random_string(){
        param_receive(['type']);
        $result                     = [];

        switch (input('type')){
            case 1 :
                $result['token']    = random_string('alnum', 15);
                $update_data        = ['wxk_customer_callback_token' => $result['token']];
                Cache::set('wxk_customer_callback_token', $result['token']);
                break;
            case 2 :
                $result['aes_key']  = random_string('alnum', 43);
                $update_data        = ['wxk_customer_callback_key' => $result['aes_key']];
                Cache::set('wxk_customer_callback_key', $result['aes_key']);
                break;
            case 4 :
                param_receive(['wxk_customer_callback_token']);
                $update_data        = ['wxk_customer_callback_token' => input('wxk_customer_callback_token')];
                Cache::set('wxk_customer_callback_token', input('wxk_customer_callback_token'));
                break;
            case 5 :
                param_receive(['wxk_customer_callback_aes_key']);
                $update_data        = ['wxk_customer_callback_key' => input('wxk_customer_callback_aes_key')];
                Cache::set('wxk_customer_callback_key', input('wxk_customer_callback_aes_key'));
                break;
            case 6 :
                param_receive(['wxk_id']);
                $update_data        = ['wxk_id' => input('wxk_id')];
                Cache::set('wxk_id', input('wxk_id'));
                break;
            default :
                $result['path']     = \request()->domain() . '/admin.php/external_contact';
                $result['token']    = random_string('alnum', 15);
                $result['aes_key']  = random_string('alnum', 43);
                $result['domain']   = \request()->host();

                $update_data        = ['wxk_customer_callback_token' => $result['token'], 'wxk_customer_callback_key' => $result['aes_key']];
                Cache::set('wxk_customer_callback_token', $result['token']);
                Cache::set('wxk_customer_callback_key', $result['aes_key']);
                break;
        }

        if (file_exists('../install/install.lock')){
            Db::name('wxk_config')->where('1=1')->update($update_data);
        }

        response(200, '', $result);
    }

    /**
     * User:Shy
     * 验证
     * @return Json
     */
    public function check()
    {
        try {
            file_get_contents('../install/install.lock');
            $content = 1;
            $msg = '已安装';
        } catch (Exception $e) {
            $content = 0;
            $msg = '未安装';
        }
        return rsp(200, $msg, ['check_status' => $content]);
    }

    /**
     * User:Shy
     * 环境检测
     * @return Json
     */
    public function CheckEnvironment()
    {
        define('PATH_ROOT', str_replace("\\", '/', dirname(dirname(__FILE__))));

        //服务器操作系统
        $os_name = php_uname();
        $os = '';
        if (strpos($os_name, "Linux") !== false) {
            $os = "Linux";
        } else if (strpos($os_name, "Windows") !== false) {
            $os = "Windows";
        }


        //web服务器环境
        $web = $_SERVER['SERVER_SOFTWARE'];

        //php版本
        $php = phpversion();
        if ($php < 7.1) {
            $php = 'PHP版本至少为7.1';
        }

        //程序安装目录
        $path = str_replace("\\", '/', \think\facade\App::getRootPath() . 'public');

        //磁盘大小
        if (function_exists('disk_free_space')) {
            $size = floor(disk_free_space(PATH_ROOT) / (1024 * 1024)) . 'M';
        } else {
            $size = 'unknow';
        }

        //上传限制
        $upload = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';


        //mysql
        try {
            if (function_exists('mysqli_connect')) {
                $mysql = 1;
            }
        } catch (Exception $e) {
            $mysql = '您的环境没有mysql，请安装mysql ';
        }


        //mysqli_pdo
        if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
            $pdo = 1;
        } else {
            $pdo = '您的PHP环境不支持 pdo，请开启此扩展 ';
        }

        //
        if (@ini_get('allow_url_fopen') && function_exists('fsockopen')) {
            $allowed = 1;
        } else {
            $allowed = '您的PHP环境不支持 allow_url，请开启此扩展 ';
        }


        //curl
        if (extension_loaded('curl') && function_exists('curl_init')) {
            $curl = 1;
        } else {
            $curl = '您的PHP环境不支持 curl，请开启此扩展 ';
        }


        //gd2
        if (extension_loaded('gd')) {
            $gd = 1;
        } else {
            $gd = '您的PHP环境不支持 curl，请开启此扩展 ';
        }

        //openssl
        if (extension_loaded('openssl')) {
            $openssl = 1;
        } else {
            $openssl = '没有启用openssl扩展';
        }


        //bcmath
        if (extension_loaded('bcmath')) {
            $bcmath = 1;
        } else {
            $bcmath = '没有启用bcmath扩展';
        }

        //DOMDocument
        if (class_exists('DOMDocument')) {
            $DOMDocument = 1;
        } else {
            $DOMDocument = '没有启用DOMDocument, 将无法正常安装使用模块, 系统无法正常运行';
        }


        //auto_start
        $auto_start = ini_get('session.auto_start');
        if ($auto_start == 0 || strtolower($auto_start) == 'off') {
            $auto_start = 1;
        } else {
            $auto_start = '系统session.auto_start开启, 将无法正常注册会员, 系统无法正常运行';
        }


        //asp_tags
        if (empty(ini_get('asp_tags')) || strtolower(ini_get('asp_tags')) == 'off') {
            $asp_tags = 1;
        } else {
            $asp_tags = '请禁用可以使用ASP 风格的标志，配置php.ini中asp_tags = Off';
        }


        if ($this->local_writeable('../runtime/')) {
            $runtime = 1;
        } else {
            $runtime = 'runtime无法写入, 将无法使用自动更新功能, 系统无法正常运行';
        }
        if ($this->local_writeable('./static/')) {
            $uploads = 1;
        } else {
            $uploads = 'uploads无法写入, 将无法使用自动更新功能, 系统无法正常运行';
        }
        if ($this->local_writeable('../config/')) {
            $database = 1;
        } else {
            $database = 'database无法写入, 将无法使用自动更新功能, 系统无法正常运行';
        }

        if ($this->local_writeable('./')) {
            $install = 1;
        } else {
            $install = 'install无法写入, 将无法使用自动更新功能, 系统无法正常运行';
        }
        $cache = [
            'php' => $php,
            'mysql' => $mysql,
            'pdo' => $pdo,
            'allowed' => $allowed,
            'curl' => $curl,
            'gd' => $gd,
            'openssl' => $openssl,
            'bcmath' => $bcmath,
            'DOMDocument' => $DOMDocument,
            'auto_start' => $auto_start,
            'asp_tags' => $asp_tags,
            'runtime' => $runtime,
            'uploads' => $uploads,
            'database' => $database,
            'install' => $install,
        ];
        $result = [
            'os' => $os,
            'web' => $web,
            'php' => $php,
            'path' => $path,
            'size' => $size,
            'upload' => $upload,
            'pdo' => $pdo,
            'allowed' => $allowed,
            'curl' => $curl,
            'mysql' => $mysql,
            'gd' => $gd,
            'openssl' => $openssl,
            'bcmath' => $bcmath,
            'DOMDocument' => $DOMDocument,
            'auto_start' => $auto_start,
            'asp_tags' => $asp_tags,
            'runtime' => $runtime,
            'uploads' => $uploads,
            'database' => $database,
            'install' => $install,
        ];

        Cache::clear();

        Cache::set('detect', json_encode($cache));
        return rsp(200, '成功', $result);
    }

    /**
     * User:Shy
     * @param $dir
     * @return int
     */
    function local_writeable($dir)
    {
        $writeable = 0;
        if (!is_dir($dir)) {
            @mkdir($dir, 0777);
        }
        if (is_dir($dir)) {
            if ($fp = fopen("$dir/test.txt", 'w')) {
                fclose($fp);
                unlink("$dir/test.txt");
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }
        return $writeable;
    }

    /**
     * User:Shy
     * 安装
     * @param Request $request
     * @return Json
     */
    public function Install(Request $request)
    {
        $cache = json_decode(Cache::get('detect'), true);
        if (!empty($cache)) {
            foreach ($cache as $key => $v) {
                if ($key == 'php') {
                    if ($v < '7.1') {
                        return rsp(500, $key . '达不到要求！');
                    }
                } else {
                    if ($v != 1) {
                        return rsp(500, $key . '达不到要求！');
                    }
                }
            }
        } else {
            return rsp(500, '请检查环境在进行安装');
        }
        //连接数据库
//        try {
        try {
            $_mysqli = mysqli_connect($request->data['host'], $request->data['user'], $request->data['pas'], $request->data['database'], $request->data['port']);
            //修改数据库
            $database = SysApp::datas($request->data);
            if ($_mysqli && $request->data['way'] == 1) {
                return rsp(200, '成功');
            }
        } catch (Exception $e) {
            return rsp(500, '数据库连接失败，请检查配置信息');
        }
        verify_data('host,port,user,pas,database,prefix,password,phone,test,way', $request->data);
        //查看是否有表
        $table = $request->data['prefix'] . 'sys_user';
        $is_table = Db::query("select t.table_name from information_schema.TABLES t where t.TABLE_SCHEMA ='" . $request->data['database'] . "' and t.TABLE_NAME ='" . $table . "';");
        if (!empty($is_table) && empty($request->data['is_cover'])) {
            return rsp(201, '数据库已存在，是否覆盖此数据库?');
        }
        $database = iconv("utf-8", "gbk//IGNORE", $database);;
        $database = mb_convert_encoding($database, 'UTF-8', 'GBK');
        if (!file_exists('../config/database.php')) {
            return rsp(500, '安装包不正确, 数据库文件缺失');
        }
        file_put_contents('../config/database.php', $database);

        //刷新数据库配置
        //生成数据库
        if ($request->data['is_cover'] == 1 || empty($request->data['is_cover'])) {
            //清库
            $drop_table = Db::query("show tables like '{$request->data['prefix']}%';");
            $drop_table_sql = [];
            if (!empty($drop_table)) {
                foreach ($drop_table as $v) {
                    $drop_table_sql[] = $v['Tables_in_' . $request->data['database'] . " ({$request->data['prefix']}%)" ];
                }
            }
            if (is_array($drop_table_sql) && !empty($drop_table_sql)) {
                $drop_table_sql = implode(',', $drop_table_sql);
                Db::query("DROP TABLE {$drop_table_sql};");
            }
            if (file_exists('../install/create.sql')) {
                $table = file_get_contents('../install/create.sql');
                $table_arr = explode(';', $table);
                foreach ($table_arr as $k) {
                    $_mysqli->query($k . ';');
                }
            } else {
                return rsp(500, '安装包不正确, 数据安装脚本缺失');
            }
            //生成测试数据
            if (file_exists('../install/init.sql')) {
                $sql = file_get_contents('../install/init.sql');
                $sql_arr = explode(';', $sql);
                foreach ($sql_arr as $v) {
                    $_mysqli->query($v . ';');
                }
            } else {
                return rsp(500, '安装包不正确, 数据安装脚本缺失');
            }
            //改表前缀
            $result = Db::query("show tables from {$request->data['database']}");
            if (!empty($result)) {
                foreach ($result as $key => $v) {
                    $t_name = $v['Tables_in_' . $request->data['database']];
                    $tabel_name = $request->data['prefix'] . $t_name;
                    Db::execute("ALTER TABLE {$v['Tables_in_'.$request->data['database']]} RENAME TO {$tabel_name};");
                }
            }
            $_mysqli->close();
        }
        //添加用户
        validate(\app\validate\SysUser::class)
            ->scene('install')
            ->check($request->data);

        $user = ['phone' => $request->data['phone'], 'password' => sha1($request->data['password']), 'id' => uuid(), 'role_id' => ''];
        //添加企业微信配置
        $wxk_config = new \app\admin\model\WxkConfig();
        $wxk_config->wxk_address_book_secret = $request->data['wxk_address_book_secret'] ?: '';
        $wxk_config->wxk_id = $request->data['wxk_id'] ?: '';
        $wxk_config->wxk_customer_admin_secret = $request->data['wxk_customer_admin_secret'] ?: '';
        $wxk_config->wxk_customer_callback_token = $request->data['wxk_customer_callback_token'] ?: '';
        $wxk_config->wxk_customer_callback_key = $request->data['wxk_customer_callback_key'] ?: '';
        $wxk_config->wxk_customer_callback_url = $request->data['wxk_customer_callback_url'] ?: '';
        $wxk_config->wxk_app_agent_id = $request->data['wxk_app_agent_id'] ?: '';
        $wxk_config->wxk_app_secret = $request->data['wxk_app_secret'] ?: '';
        $wxk_config->wxk_public_app_id = isset($request->data['wxk_public_app_secret']) ? $request->data['wxk_public_app_secret'] : '';
        $wxk_config->wxk_public_app_secret = isset($request->data['wxk_public_app_secret']) ? $request->data['wxk_public_app_secret'] : '';
        $wxk_config->id = uuid();
        if (!$wxk_config->save() || !\app\admin\model\SysUser::AddUser($user)) {
            return rsp(500, '安装错误');
        }
        //生成install.lock文件
        file_put_contents('../install/install.lock', '');
        return rsp(200, '安装成功');
//        } catch (Exception $e) {
//            return rsp(500, $e->getMessage());
//        }
    }

}