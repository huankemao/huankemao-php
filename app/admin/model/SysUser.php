<?php
/**
 * Created by Shy
 * Date 2020/12/1
 * Time 14:57
 */


namespace app\admin\model;


use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\Model;
use think\response\Json;

class SysUser extends Model
{




    protected $pk = 'id';
    protected $createTime = 'sign_up_at';

    /**
     * 获取首页用户信息
     * User: 万奇
     * Date: 2020/12/30 0030
     * @param $user_id
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index_user_info($user_id){
        $data                   = [ 'host' => $_SERVER['SERVER_NAME'], 'ip' => get_ip(), 'product' => 'open'];
        $license                = json_decode(curl_request(\StaticData::RESOURCE_NAME['license_url'], $data), true);

        if ($license['status'] != 'success'){
            $license                = ['license' => '开源版', 'authorization' => '永久'];
        }

        $staff_model                =  new WxkStaff();
        $phone                      = $this->where(['id' => $user_id])->value('phone');
        $staff_info                 = $staff_model->get_staff_info(['phone' => $phone]);

        $result['phone']            = substr_replace($phone, '****', 3, 4);
        $result['license']          = $license['license'];
        $result['authorization']    = $license['authorization'];
        $result['company_name']     = isset($staff_info['company_name']) ? $staff_info['company_name'] : '';
        $result['section_name']     = isset($staff_info['section_name']) ? $staff_info['section_name'] : '';
        $result['name']             = isset($staff_info['name']) ? $staff_info['name'] : '';
        $result['avatar']           = isset($staff_info['avatar']) ? $staff_info['avatar'] : '';

        return $result;
    }

    public function UserRole()
    {
        return $this->hasOne(SysUserRole::class,'user_id','id');
    }

    /**
     * User: Shy
     * @param $user_id
     * @param $password
     * @param $transport_pass
     * @return bool
     */
    static function encryption($user_id, $password, $transport_pass)
    {
        if ($password == sha1($transport_pass)) {
            $token =  self::token();
             $update =  self::update(['id' => $user_id,'token' =>$token, 'last_login_at' => date('Y-m-d H:i:s', time())]);
             if($update){
                 return $token;
             }
        }
        return false;
    }


    /**
     * User:shy
     * 密钥
     * @return string
     */
    static function token(){
        return $uuid = sha1(md5(uuid()));
    }


    /**
     * User: Shy
     * @param $data
     * @return Json
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    static function ResetPas($data)
    {
        $user = self::find($data['user_id']);
        if ($user) {
            if ($data['password'] == $data['new_password']) {
                self::update(['id' => $data['user_id'], 'password' => sha1($data['password'])]);
                return rsp(200, '成功');
            }
            return rsp(200, '两次密码不相同');
        } else {
            return rsp(500, '用户不存在');
        }
    }

    /**
     *User: Shy
     * @param $phone 手机号
     * @param $str   验证码
     * @return array|bool|string
     * @throws ClientException
     * @throws ServerException
     */
    static function send_sms($phone, $str)
    {
        if (empty($phone)) {
            return false;
        }
        $sign = config('common.SignName'); //签名名称
        $code = config('common.TemplateCode'); //模版CODE
        $AccessKeyId = config('common.AccessKeyId');
        $Secret = config('common.Secret');
        // 创建客户端
        AlibabaCloud::accessKeyClient($AccessKeyId, $Secret)
            ->regionId('cn-hangzhou')
            ->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RigionId' => 'cn_hangzhou',
                        'PhoneNumbers' => $phone, // 输入的手机号
                        'SignName' => $sign,  // 签名信息
                        'TemplateCode' => $code,  // 短信模板id
                        'TemplateParam' => json_encode(['code' => $str]) // 可选，模板变量值，json格式
                    ]
                ])
                ->request();
            return $result->toArray();
        } catch (ClientException $e) {
            return rsp(200, $e->getErrorMessage());
        } catch (ServerException $e) {
            return rsp(200, $e->getErrorMessage());
        }
    }

    static function AddUser($data, $type = 1)
    {
        \think\facade\Db::startTrans();
        try {
            $model = new self();
            if ($model->save($data)) {
                //保存角色
                $user_role = new SysUserRole();
                if ($user_role->replace()->save(['user_id' => $model->id, 'role_id' => $data['role_id']])) {
                    \think\facade\Db::commit();
                    return rsp(200, '添加成功');
                }
            }
            return rsp(500, '添加失败');
        } catch (Exception $e) {
            \think\facade\Db::rollback();
            return rsp(500, $e->getMessage());
        }
    }

    static function del($user_id)
    {
        \think\facade\Db::startTrans();
        try {
            $model = self::find(['id' => $user_id]);
            if ($model && $model->delete()) {
                $sys_user = SysUserRole::where(['user_id' => $user_id])->find();
                if ($sys_user->delete()) {
                    return rsp(200, '删除成功');
                }
            } else {
                return rsp(200, '删除失败');
            }
        } catch (Exception $e) {
            \think\facade\Db::rollback();
            return rsp(500, $e->getMessage());
        }

    }

    /**
     * User: Shy
     * @param $phone
     * @return bool
     */
    static function validatePhone($phone)
    {
        if (!preg_match("/^1[34578]\d{9}$/", $phone)) {
            return false;
        }
        return true;
    }
}