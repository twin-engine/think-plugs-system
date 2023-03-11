<?php

declare(strict_types=1);

namespace app\system\service;

use app\system\model\SysMenu;
use think\admin\Service;


/**
 * 菜单管理服务
 * Class SystemMenuService
 * @package app\system\service
 */
class SystemMenuService extends Service
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
        $map = [['status', '=', 0], ['is_deleted', '=', 0]];
        $menus = SysMenu::mk()
            ->field('id,parent_id,name,code,icon,route,is_hidden,component,redirect,type')
            ->where($map)
            ->order('sort desc')
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
        return $this->toTree($routers);
    }

    /**
     * 设置路由
     * @param $menu
     * @return array
     */
    public function setRouter(&$menu): array
    {
        $route = [
            'id' => $menu['id'],
            'parent_id' => $menu['parent_id'],
            'name' => $menu['code'],
            'component' => $menu['component'],
            'path' => '/' . $menu['route'],
            'redirect' => $menu['redirect'],
            'meta' => [
                'type' => $menu['type'],
                'icon' => $menu['icon'],
                'title' => $menu['name'],
                'hidden' => ($menu['is_hidden'] == 0),
                'hiddenBreadcrumb' => false
            ]
        ];
        return $route;
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
     * 通过菜单ID列表获取菜单数据
     * @param array $ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoutersByIds(array $ids): array
    {
        $map = [['status', '=', 0]];
        $menus = SysMenu::mk()
            ->field('id,parent_id,name,code,icon,route,is_hidden,component,redirect,type')
            ->where($map)
            ->whereIn('id', $ids)
            ->order('sort desc')
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
     * 查询菜单code
     * @param array|null $ids
     * @return array
     */
    public function getMenuCode(array $ids = null): array
    {
        $map = [['status', '=', 0]];
        $codes = SysMenu::mk()
            //->field('code')
            ->where($map)
            ->whereIn('id', $ids)
            ->column('code');
        //->select()
        //->toArray();
        return $codes;
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