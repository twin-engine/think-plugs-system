<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 应用数据模型
 * Class SysApp
 * @package app\system\model
 */
class SysApp extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}