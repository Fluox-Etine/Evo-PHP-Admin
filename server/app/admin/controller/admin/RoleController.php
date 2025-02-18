<?php

namespace app\admin\controller\admin;

class RoleController
{

    public function list(): \support\Response
    {
        return renderSuccess([
            'total' => 0,
            'records' => [],
            'size' => 10,
            'current' => 1
        ]);
    }
}