<?php

declare (strict_types=1);
namespace app\system\model;

use think\admin\Model;
use think\model\relation\HasOne;

/**
 * 管理员数据模型
 * Class SystemUser
 * @package app\system\model
 */
class SystemUser extends Model
{
    /**
     * 通过中间表关联角色
     */
    //public function roles()
    //{
       //return $this->belongsToMany(SystemRole::class, 'system_user_role', 'user_id', 'role_id');
    //}
    /**
     * 通过中间表关联岗位
     */
    //public function posts()
    //{
        //return $this->belongsToMany(SystemPost::class, 'system_user_post', 'user_id', 'post_id');
    //}
    /**
     * 关联部门
     */
    protected $hidden = [
        'updated_at','updated_by','created_at','created_by'
        
    ];
    
    public function dept(): HasOne
    {
        return $this->hasOne(SystemDept::class, 'id', 'dept_id')->where(['status'=>0]);
    }
}