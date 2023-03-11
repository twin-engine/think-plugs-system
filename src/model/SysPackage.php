<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 租户套餐数据模型
 * Class SysPackage
 * @package app\system\model
 */
class SysPackage extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}