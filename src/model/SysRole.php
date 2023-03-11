<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 角色数据模型
 * Class SysRole
 * @package app\system\model
 */
class SysRole extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}