<?php
declare (strict_types=1);

namespace app\validate;

use think\Validate;

class SysUser extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id' => 'require',
        'gender' => 'require',
        'disable' => 'require',
        'username' => 'require|max:50|min:4',
        'phone' => 'require|mobile|unique:sys_user',
        'password' => 'require|max:255|min:6',
        'code' => 'require|length:6',
        'role_id' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'id.require' => 'id必须填写',
        'gender.require' => '性别必须填写',
        'disable.require' => '状态必须填写',
        'phone.mobile' => '填写有效的手机号',
        'phone.unique' => '手机号已存在',
        'password.require' => '密码必须填写',
        'password.max' => '密码最大255',
        'password.min' => '密码最小6',
        'username.require' => '用户名必须填写',
        'username.max' => '用户名最大50',
        'username.min' => '用户名最小4',
        'code.require' => '验证码必须填写',
        'code.length' => '验证码为6位',
        'role_id.require' => '角色不能为空',
    ];

    protected $scene = [
        'install' => [ 'password', 'phone'],
        'register' => ['phone', 'age', 'code'],
        'add' => ['phone', 'age', 'username', 'gender', 'disable', 'role_id'],
        'edit' => ['phone', 'age', 'username', 'id', 'gender', 'disable', 'role_id'],
    ];


    public function sceneEdit()
    {
        return $this->only(['phone', 'age', 'username', 'id', 'gender', 'disable'])
            ->remove('phone', 'unique');
    }


}
