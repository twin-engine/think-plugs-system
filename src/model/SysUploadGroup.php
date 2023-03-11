<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 文件分组数据模型
 * Class SysUploadGroup
 * @package app\system\model
 */
class SysUploadGroup extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}