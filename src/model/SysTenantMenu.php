<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 租户菜单数据模型
 * Class SysTenantMenu
 * @package app\system\model
 */
class SysTenantMenu extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}