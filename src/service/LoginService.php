<?php

declare(strict_types=1);

namespace app\system\service;

use app\security\model\SecuritySetting;
use app\system\model\SysApp;
use app\system\model\SysDept;
use app\system\model\SysMenu;
use app\system\model\SysPackage;
use app\system\model\SysPage;
use app\system\model\SysPost;
use app\system\model\SysRole;
use app\system\model\SysRoleMenu;
use app\system\model\SysTenant;
use app\system\model\SysTenantApp;
use app\system\model\SysTenantMenu;
use app\system\model\SysUserPost;
use app\system\model\SysUserRole;
use think\admin\Exception;
use think\admin\extend\CodeExtend;
use think\admin\Service;


/**
 * 登录注册服务
 * Class LoginService
 * @package app\system\service
 */
class LoginService extends Service
{

    /**
     * 注册服务
     * @param $map
     * @param $data
     * @param $type
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register($map, $data, $type)
    {
        if (empty($data)) return [];
        // 接收接口类型
        $tData = $this->createTenant($data);//写入租户信息
        if (!empty($tData) && is_array($tData)) {
            $data['tenant_id'] = $tData['tenant_id'];
            $data['dept_id'] = $tData['dept_id'];
            $user = SystemUserAdminService::set($map, $data, $type, true);
            $this->userBindRole($tData['role_id'], $user['id']);//写入用户与角色关联
            $this->userBindPost($tData['post_id'], $user['id']);//写入用户与岗位关联
            $this->roleBindMenu($tData['tenant_id'], $tData['role_id']);//写入租户角色与菜单关联
            return $user;
        } else {
            return [];
        }
    }

    /**
     * 注册默认租户
     * @param array $data
     * @return array
     */
    public function createTenant(array $data)
    {
        //写入租户信息
        $tenantData = [];
        $dat = [];
        $tenantData['id'] = CodeExtend::uniqidNumber(12);
        $tenantData['cid'] = $this->_getMaxCid();
        $tenantData['progress'] = 0;
        $tenantData['code'] = $data['contact_phone'];
        $tenantData['level'] = '0';
        $tenantData['parent_id'] = 0;
        $tenantData['name'] = '新创建用户_' . $data['contact_phone'];
        $tentantData['contact_number'] = $data['contact_phone'];
        $tenantData['package_id'] = 1;
        $tenantData['gas_total'] = 500000;
        $dat['tenant_id'] = $tenantData['id'];
        if (SysTenant::mk()->insert($tenantData)) {
            $dat['post_id'] = $this->createPost($tenantData['id']);//写入岗位
            $dat['role_id'] = $this->createRole($tenantData['id']);//写入角色
            $dat['dept_id'] = $this->createDept($tenantData['id'], $data['contact_phone']);//写入部门
            $this->creatTenantApp(1, $tenantData['id']);//写入租户与APP关联
            $this->tenantBindMenu($tenantData['id']);//写入菜单与租户关联
            $this->createPageTemplate($tenantData['id']);//写入默认模板
            $this->setScanText($tenantData['id']);//写入默认查询语
            return $dat;
        } else {
            return [];
        }
    }

    /**
     * 获取租户最大的CID
     * @return int|mixed
     */
    public function _getMaxCid()
    {
        $tenant = SysTenant::mk()->where(['is_deleted' => 0, 'status' => 0])->order('id desc')->findOrEmpty();
        if ($tenant->isEmpty()) {
            $cid = 1000;
        } else {
            $cid = $tenant['cid'];
        }
        return $cid;
    }

    /**
     * 注册默认岗位
     * @param $tenant_id
     * @return int|string
     */
    public function createPost($tenant_id)
    {
        return SysPost::mk()->insertGetId(['tenant_id' => $tenant_id, 'name' => '管理部经理', 'code' => 'Manager', 'remark' => '你可以修改默认岗位信息']);
    }

    /**
     * 注册默认角色
     * @param $tenant_id
     * @return int|string
     */
    public function createRole($tenant_id)
    {
        return SysRole::mk()->insertGetId(['tenant_id' => $tenant_id, 'name' => '管理员', 'code' => 'Manager', 'data_scope' => '0', 'remark' => '本租户管理员']);
    }

    /**
     * 注册默认部门
     * @param $tenant_id
     * @param $phone
     * @return int|string
     */
    public function createDept($tenant_id, $phone)
    {
        return SysDept::mk()->insertGetId(['tenant_id' => $tenant_id, 'parent_id' => 0, 'level' => 0, 'name' => '管理部', 'leader' => '', 'phone' => $phone, 'remark' => '你可以修改默认部门']);
    }

    /**
     * 写入租户可用APP关联
     * @param int $package_id
     * @param $tenant_id
     * @return void
     */
    public function creatTenantApp(int $package_id = 1, $tenant_id)
    {
        $appsData = SysPackage::mk()->where(['id' => $package_id])->value('apps');
        $apps = [];
        foreach (explode(',', $appsData) as $v) {
            $apps[] = [
                'tenant_id' => $tenant_id,
                'app_id' => $v
            ];
        }
        SysTenantApp::mk()->insertAll($apps);

    }

    /**
     * 租户关联菜单
     * @param $tenant_id
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function tenantBindMenu($tenant_id)
    {
        $appids = SysTenantApp::mk()->where(['tenant_id' => $tenant_id])->order('id asc')->column('app_id');
        $apps = SysApp::mk()->whereIn('id', $appids)->where(['is_deleted' => 0])->column('code');
        $trees = SysMenu::mk()
            ->where(['is_deleted' => 0])
            ->where(['hide' => 0])
            ->whereIn('application', $apps)
            ->field('id')
            ->order('sort desc')
            ->select()
            ->toArray();
        $menus = [];
        foreach ($trees as $v) {
            $menus[] = [
                'tenant_id' => $tenant_id,
                'menu_id' => $v['id']
            ];
        }
        SysTenantMenu::mk()->insertAll($menus);
    }

    /**
     * 注册默认 用户扫码模板
     * @param $tenant_id
     * @return void
     */
    public function createPageTemplate($tenant_id)
    {
        $pageData = [
            'tenant_id' => $tenant_id,
            'page_type' => 10,
            'res_style' => 1,
            'page_name' => '首页',
            'page_data' => '{"page":{"name":"页面设置","type":"page","params":{"name":"首页","title":"双擎码溯源","shareTitle":"分享标题","backgroundColor":"#ffffff"},"style":{"titleTextColor":"white","titleBackgroundColor":"#00c9b6"}},"items":[{"name":"真伪","type":"result","data":[{"imgUrl":"https:\/\/img.sqm.la\/img\/diy\/banner\/01.png","link":null},{"imgUrl":"https:\/\/img.sqm.la\/img\/diy\/banner\/01.png","link":null}],"params":{"style":"default","type":"product","source":"auto","color":{"once":"#00C9B6","more":"#ff3838"},"bgcolor":"#00C9B6","videoUrl":"https:\/\/img.sqm.la\/ch.mp4","poster":"https:\/\/img.sqm.la\/video.jpg","autoplay":0,"background":{"once":"https:\/\/img.sqm.la\/new\/wsw_02.jpg","more":"https:\/\/img.sqm.la\/new\/wsw.jpg"}},"style":{"shadow":"Y","paddingTop":0,"height":190,"textColor":"#ffffff","btnColor":"#fafafa","btnShape":"round","interval":2.5}},{"name":"溯源认证","type":"basics","style":{"shadow":"Y","paddingTop":0,"paddingLeft":5}},{"name":"首查时间","type":"oncetime","style":{"result":"N","resultTextColor":"#00c9b6","shadow":"Y","paddingTop":0,"paddingLeft":5,"height":92,"background":"#fff","textColor":"#000"}},{"name":"查询纪录","type":"logs","style":{"shadow":"Y","paddingTop":0,"paddingLeft":5}}]}'
        ];

        SysPage::mk()->insert($pageData);
    }

    /**
     * 溯源码查询默认语
     * @param $tenant_id
     * @return void
     */
    public function setScanText($tenant_id)
    {
        $text = [
            'tenant_id' => $tenant_id,
            'num' => 0,
            'pack_num' => 8,
            'first' => '首次查询 值得信赖',
            'many' => '多次查询 谨防假冒',
            'cancel' => '标签已作废 谨防假冒',
            'unissued' => '未发行或已过期的标签 谨防假冒',
            'myself_many' => '本人多次查询 放心使用',
            'many_people' => '多人多次查询 谨防假冒',
            'unbound' => '未绑定批次 请联系商家',
            'not_conform' => '不存在的二维码 谨防假冒'
        ];
        SecuritySetting::mk()->insert($text);
    }

    /**
     * 用户关联角色
     * @param $role_id
     * @param $user_id
     * @return void
     */
    public function userBindRole($role_id, $user_id)
    {
        SysUserRole::mk()->insert(['user_id' => $user_id, 'role_id' => $role_id]);
    }

    /**
     * 用户关联岗位
     * @param $post_id
     * @param $user_id
     * @return void
     */
    public function userBindPost($post_id, $user_id)
    {
        SysUserPost::mk()->insert(['user_id' => $user_id, 'post_id' => $post_id]);
    }

    /**
     * 租户角色关联菜单
     * @param $tenant_id
     * @param $role_id
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function roleBindMenu($tenant_id, $role_id)
    {
        $appids = SysTenantApp::mk()->where(['tenant_id' => $tenant_id])->order('id asc')->column('app_id');
        $apps = SysApp::mk()->whereIn('id', $appids)->where(['is_deleted' => 0])->column('code');
        $trees = SysMenu::mk()
            ->where(['is_deleted' => 0])
            ->where(['hide' => 0])
            ->whereIn('application', $apps)
            ->field('id')
            ->order('sort desc')
            ->select()
            ->toArray();
        $menus = [];
        foreach ($trees as $v) {
            $menus[] = [
                'role_id' => $role_id,
                'menu_id' => $v['id']
            ];
        }
        SysRoleMenu::mk()->insertAll($menus);
    }
}