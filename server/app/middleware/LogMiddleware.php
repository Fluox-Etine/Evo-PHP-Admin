<?php

namespace app\middleware;

use app\admin\service\system\AdminService;
use Ip2Region;
use support\Context;
use support\Db;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 日志中间件
 * Class LogMiddleware
 */
class LogMiddleware implements MiddlewareInterface
{
    private static $ipInstance;

    /**
     * 日志中间件
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        $response = $handler($request);
        if (in_array($request->uri(), config('env.log.exclude_uri'))) {
            return $response;
        }
        $data = [
            'ip' => get_ip(),
            'uri' => $request->uri(),
            'method' => $request->method(),
            'uuid' => Context::get('Request-traceId'),
            'user_agent' => get_user_agent(),
            'query' => jsonEncode($request->all()),
            'created_at' => time(),
        ];
        if (!self::$ipInstance) {
            self::$ipInstance = new Ip2Region();
        }
        $ip = self::$ipInstance->memorySearch(get_ip());
        $parts = explode('|', $ip['region']);
        if (count($parts) == 5) {
            if ($parts[0] == 0 || $parts[2] == 0) {
                $data['address'] = '内网IP';
            } else {
                $data['address'] = $parts[0] . '-' . $parts[2] . '-' . $parts[3];
            }
        }
        $err = $response->exception();
        // 判断当前接口是否异常报错了
        if (isset($err)) {
            $data['status'] = 20;
            $data['response'] = jsonEncode([
                'message' => $err->getMessage(),
                'code' => $err->getCode(),
                'file' => $err->getFile(),
                'line' => $err->getLine()
            ]);
        } else {
            $data['status'] = 10;
            $data['response'] = $response->rawBody();
        }

        $end = microtime(true);
        $exec_time = round(($end - Context::get('Request-start')) * 1000, 2);
        $data['exec_time'] = $exec_time;
        $data['uid'] = AdminService::handleLogUid();
        $data['pid'] = getmypid();
        if (in_array($data['uri'], config('env.log.query_exclude'))) {
            $data['query'] = '';
        }
        if (in_array($data['uri'], config('env.log.response_exclude'))) {
            $data['response'] = '';
        }
        Db::table('sys_log_request')->insert($data);
        return $response;
    }

}