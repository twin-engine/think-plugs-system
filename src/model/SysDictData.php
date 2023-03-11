<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 字典数据模型
 * Class SysDictData
 * @package app\system\model
 */
class SysDictData extends Model
{
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
}