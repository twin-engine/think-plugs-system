<?php

declare(strict_types=1);

namespace app\system\service;

use app\system\model\SysApp;
use app\system\model\SysMenu;
use app\system\model\SysPackage;
use think\admin\Service;


/**
 * 菜单管理服务
 * Class SystemMenuAntService
 * @package app\system\service
 */
class SystemMenuAntService extends Service
{

    /**
     * 获取超级管理员（创始人）的路由菜单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSuperAdminRouters(): array
    {
        $app = SysApp::mk()
            ->field('code')
            ->where(['active' => 'Y'])
            ->findOrEmpty();
        $map = [['status', '=', 0], ['type', '<', 2], ['application', '=', $app['code']], ['is_deleted', '=', 0], ['hide', '=', 0]];//,['visible','=','Y']
        $menus = SysMenu::mk()
            ->where($map)
            ->order('sort asc,id asc')
            ->select()
            ->toArray();
        return $this->sysMenuToRouterTree($menus);
    }

    /**
     * 系统菜单转前端路由树
     * @param array $menus
     * @return array
     */
    public function sysMenuToRouterTree(array $menus): array
    {
        if (empty($menus)) return [];

        $routers = [];
        foreach ($menus as $menu) {
            array_push($routers, $this->setRouter($menu));
        }
        return $routers;
        //return $this->toTree($routers);
    }

    /**
     * 设置路由
     * @param $menu
     * @return array
     */
    public function setRouter(&$menu): array
    {
        return [
            'name' => $menu['code'],
            'parent_id' => $menu['parent_id'],
            'id' => $menu['id'],
            'path' => $menu['router'],
            'hidden' => !($menu['visible'] == 'Y'),
            'meta' => [
                'link' => $menu['link'],
                'icon' => $menu['icon'],
                'title' => $menu['name'],
                'show' => $menu['visible'] == 'Y',
                'target' => $menu['open_type'] == 2 ? '_blank' : null,
            ],
            'redirect' => $menu['redirect'],
            'component' => $menu['component']
        ];
    }

    /**
     * 获取超级管理员（创始人）的APP
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSuperAdminApp(): array
    {
        $map = [['status', '=', 0]];
        $apps = SysApp::mk()
            ->field('code,name,active')
            ->where($map)
            ->where(['is_deleted' => 0])
            ->order('sort DESC,id ASC')
            ->select()
            ->toArray();
        foreach ($apps as &$v) {
            $v['active'] = $v['active'] == 'Y';
        }
        return $apps;
    }

    /**
     * 获取管理员的APP
     * @param int $packageId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAdminApp(int $packageId): array
    {
        if (empty($packageId)) return [];
        $package = SysPackage::mk()->where(['id' => $packageId])->where(['is_deleted' => 0])->findOrEmpty();
        if (empty($package['apps'])) return [];
        $app_ids = explode(',', $package['apps']);
        $map = [['status', '=', 0]];
        $apps = SysApp::mk()
            ->field('code,name,active')
            ->whereIn('id', $app_ids)
            ->where($map)
            ->where(['is_deleted' => 0])
            ->order('sort DESC,id ASC')
            ->select()
            ->toArray();
        foreach ($apps as &$v) {
            $v['active'] = $v['active'] == 'Y';
        }
        return $apps;
    }

    /**
     * 通过菜单ID列表获取菜单数据
     * @param array $ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoutersByIds(array $ids): array
    {
        $app = SysApp::mk()
            ->field('code')
            ->where(['active' => 'Y'])
            ->findOrEmpty();

        $map = [['status', '=', 0], ['type', '<', 2], ['application', '=', $app['code']], ['hide', '=', 0]];//,['visible','=','Y']
        $menus = SysMenu::mk()
            ->where($map)
            ->whereIn('id', $ids)
            ->order('sort asc,id asc')
            ->select()
            ->toArray();
        return $this->sysMenuToRouterTree($menus);
    }

    /**
     * 获取前端选择树
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSelectTree(): array
    {
        $map = [['status', '=', 0]];
        $menus = SysMenu::mk()
            ->field('id', 'parent_id', 'id AS value', 'name AS label')
            ->where($map)
            ->order('sort desc')
            ->select()
            ->toArray();
        return $this->toTree($menus);
    }

    /**
     * 数组转树结构
     * @param array $data
     * @param int $parentId
     * @param string $id
     * @param string $parentField
     * @param string $children
     * @return array
     */
    public function toTree(array $data = [], int $parentId = 0, string $id = 'id', string $parentField = 'parent_id', string $children = 'children'): array
    {
        //$data = $data ?: $this->toArray();
        if (empty($data)) return [];

        $tree = [];

        foreach ($data as $value) {
            if ($value[$parentField] == $parentId) {
                $child = $this->toTree($data, $value[$id], $id, $parentField, $children);
                if (!empty($child)) {
                    $value[$children] = $child;
                }
                array_push($tree, $value);
            }
        }

        unset($data);
        return $tree;
    }

    /**
     * 查询菜单code
     * @param array|null $ids
     * @return array
     */
    public function getMenuCode(array $ids = null): array
    {
        $map = [['status', '=', 0], ['type', '=', 2]];
        return SysMenu::mk()
            ->where($map)
            ->whereIn('id', $ids)
            ->column('permission');
    }

    /**
     * 通过 code 查询菜单名称
     * @param string $code
     * @return string|null
     */
    public function findNameByCode(string $code): ?string
    {
        return SysMenu::mk()->where('code', $code)->value('name');
    }

}