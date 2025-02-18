<?php

namespace app\admin\controller\admin;

use support\Response;

class UserController
{
    /**
     * 获取当前用户信息
     * @return Response
     */
    public function currentUser(): Response
    {
        return renderSuccess([
            'userId' => 1,
            'userName' => 'admin',
            'buttons' => [],
            'roles' => []
        ]);
    }

    /**
     * 路由
     * @return Response
     */
    public function routes(): Response
    {
        $routes = [
            [
                'name' => 'home',
                'path' => '/home',
                'component' => 'layout.base$view.home',
                'meta' => [
                    'title' => '首页',
                    'icon' => 'mdi:monitor-dashboard',
                    'order' => 1,
                ]
            ],
            [
                'name' => 'manage',
                'path' => '/manage',
                'component' => 'layout.base',
                'meta' => [
                    'title' => '系统管理',
                    'icon' => 'mdi:cog-outline',
                    'order' => 2,
                ],
                'children' => [
                    [
                        'name' => 'manage_role',
                        'path' => '/manage/role',
                        'component' => 'view.manage_role',
                        'meta' => [
                            'title' => '角色管理',
                            'icon' => 'mdi:account-multiple',
                            'order' => 1,
                        ]
                    ]
                ]
            ]
        ];


        return renderSuccess([
            'routes' => $routes,
            'home' => 'home'
        ]);
    }
}