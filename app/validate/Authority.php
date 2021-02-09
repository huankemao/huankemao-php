<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class Authority extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id' => 'require',
        'parent_code' => 'require',
        'tree_code' => 'require',
        'title' => 'require',
        'sort' => 'require',
        'is_menu' => 'require',
        'disable' => 'require',

    ];

    protected $scene = [
        'add' => ['parent_code', 'tree_code', 'title', 'sort', 'is_menu', 'disable'],
        'edit' => ['id','parent_code', 'tree_code', 'title', 'sort', 'is_menu', 'disable'],
    ];

}
