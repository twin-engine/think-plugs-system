<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 区域数据模型
 * Class BasePostageRegion
 * @package app\system\model
 */
class BasePostageRegion extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}