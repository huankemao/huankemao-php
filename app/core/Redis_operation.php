<?php 
namespace app\core;
use think\cache\driver\Redis;

/**
 * redis 操作(链接、数据操作)
 * 注：在这里面写的方法名不能与父类里的方法名相同，否则会覆盖父类的方法
 * User: 万奇
 * Date: 2020/8/31 0031
 */
trait Redis_operation {

    protected $redis        = null;

    /**
     * redis 链接
     * User: 万奇
     * Date: 2020/8/31 0031
     * @return Redis|null
     */
    private function redis_connect(){
        if($this->redis !== null){
            return $this->redis;
        }
        
        // 判断服务器是否安装redis扩展
        if(!extension_loaded('redis')){
            response(105 , '请先安装redis扩展！');
        }

        $path = Config('cache.' . 'redis_trait');
        $this->redis     = new Redis($path);
        return $this->redis;
    }

    /**
     * 删除redis数据
     * User: 万奇
     * Date: 2020/8/31 0031
     * @param $key string $key redis中键值
     * @param bool $match 为true通过匹配key的形式删除所匹配到的redis数据
     */
    protected function del_redis_key($key , $match=false){
        if($match){
            $this->redis->del_match($key);
        }else{
            $this->redis->delete($key);
        }
    }

}
