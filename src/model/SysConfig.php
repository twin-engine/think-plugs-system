<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 基础配置数据模型
 * Class SysConfig
 * @package app\system\model
 */
class SysConfig extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}