<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysDept;
use app\system\model\SysPackage;
use app\system\model\SysRole;
use app\system\model\SysRoleMenu;
use app\system\model\SysTenant;
use app\system\model\SysUserRole;
use app\system\service\SysNoticeService;
use app\system\service\SystemMenuAntService;
use think\admin\service\AdminService;


/**
 * 用户信息菜单权限接口
 * Class Login
 * @package app\system\controller
 */
class Info extends Auth
{


    /**
     * 获取用户信息
     * @login true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo()
    {
        // 刷新用户权限
        AdminService::apply(true);
        $data['user'] = $this->user;
        $data['tenant'] = SysTenant::mk()->where(['id' => $this->tenant_id])->where(['is_deleted' => 0])->findOrEmpty();
        //var_dump($data);
        if ($this->isSuper) {
            $data['apps'] = SystemMenuAntService::instance()->getSuperAdminApp($this->user);
            $data['roles'] = ['superAdmin'];
            $data['menus'] = SystemMenuAntService::instance()->getSuperAdminRouters($this->user);
            $data['permissions'] = [];
        } else {
            $role_ids = SysUserRole::mk()->where(['user_id' => $this->uuid])->column('role_id');
            $ids = SysRoleMenu::mk()->whereIn('role_id', $role_ids)->column('menu_id');
            $data['roles'] = SysRole::mk()->whereIn('id', $role_ids)->where(['is_deleted' => 0])->select()->toArray();
            $data['menus'] = SystemMenuAntService::instance()->getRoutersByIds($ids);
            $data['permissions'] = SystemMenuAntService::instance()->getMenuCode($ids);
            $data['apps'] = $data['tenant']['package_id'] > 0 ? SystemMenuAntService::instance()->getAdminApp($data['tenant']['package_id']) : [];
        }
        $data['notice'] = SysNoticeService::instance()->getNotice($this->uuid);
        $data['dept'] = SysDept::mk()->where(['id' => $data['user']['dept_id']])->findOrEmpty();
        $data['package'] = SysPackage::mk()->where(['id' => $data['tenant']['package_id']])->findOrEmpty();
        $this->success('获取成功！', $data);
    }


    /**
     * 登录退出
     * @return void
     */
    public function logout()
    {
        $this->app->session->destroy();
    }
}