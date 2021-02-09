<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2021/1/20 0020
 * Time: 19:27
 */

namespace app\admin\model;


class CmsTuokeProject extends BasicModel
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * 添加拓客计划
     * User: 万奇
     * Date: 2021/1/21 0021
     * @param $param
     */
    public function add_tuoke_project($param){
        if (is_exists($param['id'])){
            $this->where(['id' => $param['id']])->strict(false)->update($param);
        } else{
            $param['id']        = uuid();
            $this->save($param);
        }
    }

}