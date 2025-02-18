<?php

namespace app\admin\controller;

use support\Response;

class PassportController
{
    /**
     * 登录
     * @param string $userName
     * @param string $password
     * @return Response
     */
    public function login(string $userName, string $password): Response
    {
        if (empty($userName) || empty($password)) {
            return renderError('非法登录');
        }
        return renderSuccess(
            [
                'token' => 'token',
                'refreshToken' => 'refreshToken'
            ]
        );
    }
}