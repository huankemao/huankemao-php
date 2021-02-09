<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class ContnetGroup extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id' => 'require',
        'name' => 'require|unique:cms_content_group',
        'parent_id' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'id.require' => 'id为空',
        'name.require' => '名称为空',
        'parent_id.require' => '父ID为空',
        'name.unique' => '名称已存在',
    ];

    protected $scene = [
        'add' => ['name', 'parent_id'],
        'edit' => ['id', 'name', 'parent_id'],
        'del' => ['id'],
    ];

//    public function sceneEdit()
//    {
//        return $this->only(['id', 'name', 'parent_id'])
//            ->remove('name', 'unique');
//    }

}
