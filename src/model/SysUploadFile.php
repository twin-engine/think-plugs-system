<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;

/**
 * 文件数据模型
 * Class SysUploadFile
 * @package app\system\model
 */
class SysUploadFile extends Model
{
    protected $hidden = [
        'updated_at','updated_by'
        
    ];
}