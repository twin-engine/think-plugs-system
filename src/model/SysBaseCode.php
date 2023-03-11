<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 字母数字全组合基础码表模型
 * Class SysBaseCode
 * @package app\system\model
 */
class SysBaseCode extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}