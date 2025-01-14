<?php

namespace app\middleware;

use support\Context;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 跨域中间件
 * Class CrossMiddleware
 */
class CrossMiddleware implements MiddlewareInterface
{
    /**
     * 跨域中间件
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        if (in_array($request->uri(), config('env.log.exclude_uri'))) {
            Context::set('Request-Log-Enable', false);
        } else {
            Context::set('Request-Log-Enable', true);
            // 记录每一个请求的traceId
            Context::set('Request-traceId', guidV4());
            // 记录请求开始时间
            Context::set('Request-start', microtime(true));
        }

        // 如果是options请求则返回一个空响应，否则继续向洋葱芯穿越，并得到一个响应
        $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);

        // 给响应添加跨域相关的http头
        $response->withHeaders([
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => $request->header('origin', '*'),
            'Access-Control-Allow-Methods' => $request->header('access-control-request-method', '*'),
            'Access-Control-Allow-Headers' => $request->header('access-control-request-headers', '*'),
        ]);
        $response->header('Server', 'nginx');
        return $response;
    }
}