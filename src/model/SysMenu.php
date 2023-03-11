<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 菜单数据模型
 * Class SysMenu
 * @package app\system\model
 */
class SysMenu extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}