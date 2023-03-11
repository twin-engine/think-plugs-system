<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 租户APP数据模型
 * Class SysTenantApp
 * @package app\system\model
 */
class SysTenantApp extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}