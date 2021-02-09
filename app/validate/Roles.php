<?php
declare (strict_types=1);

namespace app\validate;

use think\Validate;

class Roles extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id' => 'require',
        'name' => 'require',
        'disable' => 'require',
        'module' => 'require',
    ];

    protected $scene = [
        'add' => ['name', 'disable', 'module'],
        'edit' => ['id', 'name', 'disable', 'module'],
    ];
}
