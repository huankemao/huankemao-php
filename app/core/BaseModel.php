<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/6 0006
 * Time: 11:18
 */

namespace app\core;
use think\Model;

class BaseModel extends Model
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

}