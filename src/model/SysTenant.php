<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 租户数据模型
 * Class SysTenant
 * @package app\system\model
 */
class SysTenant extends Model
{
    protected $hidden = [
        'updated_at','updated_by'
        
    ];
}