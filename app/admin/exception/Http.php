<?php

namespace app\admin\exception;

use think\exception\Handle;
use think\Response;
use Throwable;

class Http extends Handle
{
    public $httpCode = 500;

    public function render($request, Throwable $e): Response

    {
            return rsp($this->httpCode,$e->getMessage());
    }
}