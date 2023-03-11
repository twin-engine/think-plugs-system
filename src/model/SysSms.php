<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 短信数据模型
 * Class SysSms
 * @package app\system\model
 */
class SysSms extends Model
{
    protected $hidden = [
        'updated_at','updated_by'
        
    ];
}