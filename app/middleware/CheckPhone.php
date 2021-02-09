<?php
declare (strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;

class CheckPhone
{
    /**
     * User:Shy
     * 处理请求
     * @param $request
     * @param Closure $next
     * @return mixed|\think\response\Json
     */
    public function handle($request, Closure $next)
    {
        if (isset($request->request()['phone'])) {
            if (!preg_match("/^1[34578]\d{9}$/", $request->request()['phone'])) {
                return rsp(500, '手机号格式错误');
            }
        }
        return $next($request);
    }
}
