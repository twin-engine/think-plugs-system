<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 用户与岗位数据模型
 * Class SysUserPost
 * @package app\system\model
 */
class SysUserPost extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}