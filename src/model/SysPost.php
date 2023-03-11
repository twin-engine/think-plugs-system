<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 岗位数据模型
 * Class SysPost
 * @package app\system\model
 */
class SysPost extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}