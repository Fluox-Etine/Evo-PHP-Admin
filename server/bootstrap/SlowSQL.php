<?php

namespace bootstrap;

use app\common\enum\RedisKeyEnum;
use support\Context;
use support\Db;
use support\Redis;
use Webman\Bootstrap;

/**
 * SQL 拦截
 * @package bootstrap
 */
class SlowSQL implements Bootstrap
{
    /**
     * @param $worker
     * @return void
     */
    public static function start($worker): void
    {
        $slowSqlData = [];
        Db::listen(function ($query) use (&$slowSqlData) {
            // 这里去除的心跳ping
            if (strlen($query->sql) > 10) {
                if (config('env.show_sql.enable')) {
                    // 执行时间
                    if ($query->time > config('env.show_sql.limit')) {
                        $sqlType = substr($query->sql, 0, 7);
                        if ($sqlType == 'explain') {
                            var_dump('这是执行计划的sql');
                            return;
                        }
                        $md5 = md5($query->sql);
                        if (!in_array($md5, $slowSqlData)) {
                            $slowSqlData[] = $md5;
                            Redis::lpush(RedisKeyEnum::SLOW_SQL_LIST->value, jsonEncode([
                                'sql' => $query->sql,
                                'bindings' => $query->bindings,
                                'time' => $query->time
                            ]));
                        }
                    }
                }
                if (Context::get('Request-Log-Enable')) {
                    // sql类型
                    $table = '';
                    $sqlType = substr($query->sql, 0, 6);
                    // 如果是增加语句，则是拿到 into ` ` 的表名
                    if ($sqlType == 'insert') {
                        preg_match('/`(.+?)`/', $query->sql, $matches);
                        $table = $matches[1] ?? '';
                    } elseif ($sqlType == 'update') {
                        preg_match('/`(.+?)`/', $query->sql, $matches);
                        $table = $matches[1] ?? '';
                    } elseif ($sqlType == 'delete') {
                        preg_match('/`(.+?)`/', $query->sql, $matches);
                        $table = $matches[1] ?? '';
                    }
                    if (!in_array($table, config('env.log.exclude_table'))) {
                        Redis::lPush(RedisKeyEnum::MYSQL_LOG->value, jsonEncode([
                            'sql' => $query->sql,
                            'bindings' => jsonEncode($query->bindings),
                            'exec_time' => $query->time,
                            'pid' => getmypid(),
                            'uuid' => Context::get('Request-traceId') ?? '00000000-0000-0000-0000-0' . time(),
                            'created_at' => time()
                        ]));
                    }
                }
            }
        });
    }
}