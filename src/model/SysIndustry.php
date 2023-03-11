<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 行业分类数据模型
 * Class SysIndustry
 * @package app\system\model
 */
class SysIndustry extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}