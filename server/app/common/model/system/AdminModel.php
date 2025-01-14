<?php

namespace app\common\model\system;

use app\common\model\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminModel extends BaseModel
{
    // 软删除
    use SoftDeletes;

    // 表名
    protected $table = 'sys_admin';

    // 主键
    protected $primaryKey = 'id';
}