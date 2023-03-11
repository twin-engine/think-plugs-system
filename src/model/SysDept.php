<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 部门数据模型
 * Class SysDept
 * @package app\system\model
 */
class SysDept extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}