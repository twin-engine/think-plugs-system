<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 用户与角色数据模型
 * Class SysUserRole
 * @package app\system\model
 */
class SysUserRole extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}