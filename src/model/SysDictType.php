<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 字典类型数据模型
 * Class SysDictType
 * @package app\system\model
 */
class SysDictType extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}