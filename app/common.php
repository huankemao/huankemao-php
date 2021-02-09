<?php
// 应用公共文件

/**
 * 数据返回接口
 * @param int|array $code 状态码
 * @param string $msg 状态码说明 200-成功 105-错误 101-用户未登录 102-后台操作权限不足
 * @param int $count 长度 返回列表数据时需要用上
 * @param string|int|bool|array $data 返回数据
 * @author 万奇
 * date 2020/8/28 0028
 */

use app\core\Status_code;
use think\facade\Cache;
use think\response\Json;

if (!function_exists('response')) {
    function response($code = 200, $msg = '', $data = '', $count = 0)
    {
        if (is_array($code)) {
            echo json_encode($code);
            exit;
        }

        $status_code = new Status_code();

        $return = [
            'code' => $code,
            'msg' => $msg ? $msg : (isset($status_code::CODE[$code]) ? $status_code::CODE[$code] : ''),
        ];

        if ($count) {
            $return['count'] = $count;
        }

        if ($data !== '') {
            $return['data'] = $data;
        }
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * 参数接收
 * User: 万奇
 * Date: 2020/8/31 0031
 * @param array $param 需要接收的参数
 * @param string $receive_type 请求类型
 * @param array $target_data 目标数据
 */
if (!function_exists('param_receive')) {
    function param_receive($need_receive = [], $receive_type = 'post', $target_data = [])
    {
        $param = empty($target_data) ? input($receive_type . '.') : $target_data;
        if (empty($need_receive)) {
            return $param;
        }

        $return = [];
        $error = [];
        $need_receive = is_array($need_receive) ? $need_receive : explode(',', str_replace(' ', '', $need_receive));
        foreach ($need_receive as $nk => $nv) {
            if (!is_numeric($nk)) {//说明当前元素为关联数据
                if (!isset($param[$nv])) {//不存在则取默认数据
                    $return[$nk] = format_line_feed($nv);
                } else {
                    $return[$nk] = format_line_feed($param[$nk]);
                }
                continue;
            }

            if (!isset($param[$nv])) {
                $error[] = $nv . '为必传参数';
                continue;
            }
            if ($param[$nv] === '') {
                $error[] = $nv . '不能为空';
                continue;
            }
            $return[$nv] = format_line_feed($param[$nv]);
        }
        if (!empty($error)) {
            response(500, implode(' ', $error));
        }
        $return = array_merge($param, $return);
        return $return;
    }
}

/**
 * 格式化换行数据
 * User: 万奇
 * Date: 2020/8/31 0031
 * @param string $str
 */
if (!function_exists('format_line_feed')) {
    function format_line_feed($str)
    {
        return is_string($str) ? trim(str_replace(PHP_EOL, '', $str)) : $str;
    }
}

/**
 * 判断数据是否为有效数据
 * @param string|int|bool|array $data 操作数据
 * @param string $is_array 判断是否为数组
 * @param bool $zero_requir 是否需要0
 * @author 万奇
 * date 2020/8/28 0028
 */
if (!function_exists('is_exists')) {
    function is_exists(&$data, $is_array = false, $zero_require = false)
    {
        if (!isset($data)) {
            return false;
        }

        if ($is_array) {
            return is_array($data) ? (empty($data) ? false : true) : false;
        }

        if ($data === 0 || $data === '0') {
            if ($zero_require) {
                return true;
            }
        }

        return $data ? true : false;
    }
}

/**
 * 格式化时间
 * @param int $time 时间戳
 * @param string $default 默认返回数据
 * @param string $type 格式化类型
 * @author 万奇
 * date 2020/8/28 0028
 */
if (!function_exists('format_time')) {
    function format_time($time, $default = '-', $type = 'Y-m-d H:i:s')
    {
        if (!$time) {
            return $default;
        }

        return date($type, $time);
    }
}

/**
 * echo 数据
 * @param string|int $data 需要输出的数据源
 * @param string|int $default 如果该数据为空或者不存在时的默认输出值
 * @author 万奇
 * date 2020/8/28 0028
 */
if (!function_exists('my_echo')) {
    function my_echo(&$data, $default = '')
    {
        if (!isset($data) || $data == '') {
            return $default;
        }
        return $data;
    }
}

/**
 * 把指定的value值改成key并重新保存该数据
 * @param array $data 数据数据(只支持二维数组)
 * @param string $key 指定的KEY
 * @author 万奇
 */
if (!function_exists("set_val_to_key")) {
    function set_val_to_key($data, $key = '')
    {
        $return = [];
        if (empty($data) || !$key) {
            return $return;
        }
        foreach ($data as &$dv) {
            $return[$dv[$key]] = $dv;
        }
        return $return;
    }
}

/**
 * 删除数组中的KEY
 * User: 万奇
 * @param array $data 数据源
 * @param array|string $key 需要删除的key
 * @param bool|bool $many 是否需要删除多维
 */
if (!function_exists("array_dee_key")) {
    function array_del_key($data, $key = '', $many = true)
    {
        if (!$key) {
            return $data;
        }
        $key = is_array($key) ? $key : [$key];
        foreach ($data as $dk => $dv) {
            if ($many && is_array($dv)) {
                $data[$dk] = array_del_key($dv, $key);
            }
            foreach ($key as &$kv) {
                if (isset($data[$kv])) {
                    unset($data[$kv]);
                }
            }
        }
        return $data;
    }
}

/**
 * 数据分组
 * User: 万奇
 * Date: 2020/8/28 0028
 * @param array $data 数据源
 * @param string $key 分组的key
 */
if (!function_exists("array_grouping")) {
    function array_grouping($data, $key = '', $count = false)
    {
        if (!$key || empty($data)) {
            return $data;
        }
        $data = array_values($data);
        if (!is_array($data[0])) {
            return $data;
        }
        $return = [];
        foreach ($data as $dk => $dv) {
            if (!isset($dv[$key])) {
                break;
            }
            if ($count == true) {
                $return[$dv[$key]]['count'] = isset($return[$dv[$key]]['count']) ? $return[$dv[$key]]['count'] + 1 : 1;
            }
            $return[$dv[$key]][] = $data[$dk];

        }
        return $return;
    }
}

/**
 * 获取ip地址
 * User: 万奇
 * Date: 2020/8/28 0028
 */
if (!function_exists('get_ip')) {
    function get_ip()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = $ip[0];
        }
        return $ip;
    }
}

/**
 * 拼接地址参数
 * @param array $data 参数数组一维数组
 * @param string $connector 连接符 默认'='
 * @param string $converter 转化符 默认'&'
 * @return string
 * @author 万奇
 * 例: search=3&key=4&
 */
if (!function_exists("url_param")) {
    function url_param($data, $connector = '=', $converter = '&')
    {
        if (empty($data)) {
            return '';
        }
        $return = [];
        foreach ($data as $dk => $dv) {
            if (is_array($dv)) {
                $return[] = url_param($dv);//如果为数组则递归一次
                continue;
            }
            if ($dv === '') {
                continue;
            }
            $return[] = $dk . $connector . $dv;
        }
        return implode($converter, $return);
    }
}

/**
 * CURL请求
 * @param string $url 请求地址
 * @param array $param 请求参数
 * @param array $headers 包头
 * @param bool $is_post 请求类型 true-post false-get
 * @param bool $is_dump_head 是否打印头部信息
 * @return bool|string
 */
if (!function_exists("curl_request")) {
    function curl_request($url, $param, $headers = ["Expect: "], $is_post = true, $is_dump_head = false)
    {
        $ch = curl_init();
        if (!$is_post) {//get请求  依赖url_param函数
            $url .= "?" . url_param($param);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLINFO_HEADER_OUT, $is_dump_head);

        // 跳过SSL验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);

        if ($is_post) {//post请求
            $param = is_array($param) ? json_encode($param) : $param;
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
        $return = curl_exec($ch);
        if ($is_dump_head) {
            echo curl_getinfo($ch, CURLINFO_HEADER_OUT);
            exit;//输出head
        }
        curl_close($ch);
        return $return;
    }
}

/**
 * 将XML数据转换为对象
 * @param $xml - XmlTransfer
 * @return array
 */
if (!function_exists('xml2Array')) {
    function xml2Array($xml)
    {
        $obj = null;
        if (is_string($xml) && !empty($xml)) {
            $obj = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        }
        return (Array)$obj;
    }
}

/**
 * 将数组转换成XML
 * @param $data
 * @return string
 */
if (!function_exists('array2XML')) {
    function array2XML($data)
    {
        $xmlString = "";
        if (is_array($data) && !empty($data)) {
            $xmlString .= "<xml>";
            foreach ($data as $tag => $node) {
                $xmlString .= "<" . $tag . "><![CDATA[" . $node . "]]></" . $tag . ">";
            }
            $xmlString .= "</xml>";
        }
        return $xmlString;
    }
}

/**
 * 自动生成字符串
 * User: 万奇
 * Date: 2020/8/28 0028
 * @param string $type 随机字符类型
 * @param int $len 长度
 */
if (!function_exists('random_string')) {
    function random_string($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            default:
                break;
        }
        return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
    }
}

/**
 * 数据分组 树状结构(无限级)
 * User: 万奇
 * Date: 2020/8/31 0031
 * @param $all - 数据集
 * @param $data - 上级目录
 * @return mixed
 */
if (!function_exists('category_group')) {
    function category_group($all, $data, $group_name = 'group', $key = 'id')
    {
        foreach ($data as $k => $v) {
            $data[$k][$group_name] = [];
            if (!isset($all[$v[$key]])) {
                continue;
            }
            $data[$k][$group_name] = $all[$v[$key]];

            if (isset($data[$k][$group_name])) {
                $data[$k][$group_name] = category_group($all, $data[$k][$group_name], $group_name, $key);
            }
        }
        return $data;
    }
}

/**
 * 判断图片是否是base64编码的格式
 * User: 万奇
 * Date: 2020/9/7 0031
 */
if (!function_exists('img_is_base64')) {
    function img_is_base64($str)
    {
        if (strpos($str, ';base64,') !== false) {
            $array = explode(",", $str);
            $result = ($array[1] == base64_encode(base64_decode($array[1]))) ? true : false;
            return $result;
        } else {
            return false;
        }
    }
}

/**
 * 正则判断电话号码
 * User: 万奇
 * Date: 2020/9/9 0031
 * @param $phone - 电话号码
 * @return bool
 */
if (!function_exists('reg_phone')) {
    function reg_phone($phone)
    {
        $reg = "/^1[3-9]\d{9}$/";
        return preg_match($reg, $phone) ? true : false;
    }
}

/**
 * 正则密码判断(密码由6-16位字符串组成，必须包含数字、字母、符号中至少两种元素)
 * User: 万奇
 * Date: 2020/9/9 0031
 * @param $password string 密码
 * @return bool
 */
if (!function_exists('reg_password')) {
    function reg_password($password)
    {
        $reg = "/(?!^[0-9]+$)(?!^[A-z]+$)(?!^[^A-z0-9]+$)^.{6,16}$/";
        return preg_match($reg, $password) ? true : false;
    }

}

/**
 * 根据key 匹配 value
 * User: 万奇
 * Date: 2020/12/10 0010
 * @param array $data - 键值对数值
 * @param string $value - 需要匹配的值(逗号分隔)
 * @return array
 */
if (!function_exists('')) {
    function get_name_attr($data, $value){
        $result         = [];
        $value          = $value ? explode(',', $value) : [];

        foreach ($value as $item){
            $result[]   = $data[$item];
        }

        return $result;
    }
}


/**
 * User: Shy
 * @param int $status 状态码  200 成功 500 错误 501 用户未登录 502 后台操作权限不足 503 缺少字段 505未安装
 * @param string $msg 提示信息
 * @param array $data 数据
 * @return Json
 */
if (!function_exists('rsp')) {
    function rsp($status = 200, $msg = '', $data = [])
    {
        $result = [
            'code' => $status,
            'msg' => $msg,
            'data' => $data,
        ];
        return json($result);
    }
}


/**
 * User: Shy
 * @param $prams //我需要的字段（字符串） , 隔开
 * @param $v_data //前端数据
 * @return false|string
 */
if (!function_exists('verify_data')) {
    function verify_data($prams, $v_data)
    {
        $prams = explode(',', $prams);

        foreach ($prams as $v) {
            $result = [
                'status' => 503,
                'msg' => '缺少字段:' . $v,
                'data' => [],
            ];
            if (!array_key_exists($v, $v_data)) {
                echo json_encode($result);
                die;
            }
            //指定字段
            if ($v == 'authority') {
                if (is_array($v)) {
                    $result['mes'] = 'authority必须是json格式';
                    echo json_encode($result);
                    die;
                }
            }
        }
    }
}

/**
 * User:Shy
 * UUid
 */
if (!function_exists('uuid')) {
    function uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
        return $uuid;
    }


    /**
     * User:Shy
     * GET请求curl
     * @param $url
     * @param null $data
     * @param null $header
     * @return bool|string
     */
    if (!function_exists('httpGet')) {
        function httpGet($url, $data = null, $header = null)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_URL, $url);
            $ssl = substr($url, 0, 8) == "https://" ? true : false;
            if ($ssl) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            }
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POST, 3);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            if (!empty($header)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            }
            $res = curl_exec($curl);
            if (curl_errno($curl)) {
                echo curl_error($curl);
                die;
            }
            curl_close($curl);
            return $res;
        }
    }

    /**
     * User:Shy
     * 获取微信 access_token
     * @return mixed
     */
    if (!function_exists('getAccessToken')) {
        function getAccessToken()
        {
            $access_token = Cache::store('file')->get('getAccessToken');
            if (!$access_token) {
                $url        = Config('common.wx_access_token');
                $config     = \think\facade\Db::name('wxk_config')->where(true)->find();

                $url .= "corpid={$config['wxk_id']}&corpsecret={$config['wxk_app_secret']}";
                $result = httpGet($url);
                $result = json_decode($result, true);
                $access_token = $result['access_token'];
                Cache::store('file')->set('getAccessToken', $access_token, 7000);
            }
            return $access_token;
        }
    }


    /**
     * User:Shy
     * @param $data
     * @return array
     */
    if (!function_exists('Array_Data')) {
        function Array_Data($data)
        {
            $new_array = array();
            foreach ($data as $key => $value) {
                $new_array[$value]['id'] = $key;
                $new_array[$value]['name'] = $value;
            }
            return array_values($new_array);
        }
    }


    /**
     * User:Shy
     * 数据分页
     * @param $data //数据
     * @param $column //排序字段
     * @param $page //页数
     * @param $limit //条数
     * @return array
     */
    if (!function_exists('getDatePageLimit')) {
        function getDatePageLimit($data, $column, $page, $limit)
        {
            array_multisort(array_column($data, $column), SORT_ASC, $data);
            $page = ($page - 1) * $limit;
            $data_tables = array_slice($data, $page, $limit);
            return $data_tables;
        }
    }
}