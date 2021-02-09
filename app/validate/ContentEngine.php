<?php
declare (strict_types=1);

namespace app\validate;

use think\Validate;

class ContentEngine extends Validate
{
    /**
     * @var array
     */
    protected $rule = [
        'id' => 'require',
        'content_group_id' => 'require',
        'type' => 'require',
        'content' => 'require',
    ];

    /**
     * @var array
     */
    protected $message = [
        'id.require' => 'id为空',
        'content_group_id.require' => '分类必须选择',
        'type.require' => '类型为空',
        'content.require' => '内容为空',
    ];


    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $scene = [
        'add' => ['content_group_id', 'type', 'content'],
        'edit' => ['id', 'content_group_id', 'type', 'content'],
        'del' => ['id'],
    ];
}
