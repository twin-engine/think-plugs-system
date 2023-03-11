<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysApp;
use app\system\model\SysMenu;
use app\system\model\SysRoleMenu;
use app\system\model\SysTenant;
use app\system\model\SysTenantApp;
use app\system\model\SysTenantMenu;
use app\system\model\SysUserRole;
use app\system\service\SystemMenuAntService;


/**
 * 菜单接口
 * Class Menu
 * @package app\system\controller
 */
class Menu extends Auth
{
    /**
     * 菜单列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysMenu::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('name,application');
        $lists = $query->order('sort desc,id asc')->page(false,false,false);

        if (count($lists['list']) > 0 && empty($this->request->param('name'))) $lists['list'] = SystemMenuAntService::instance()->toTree($lists['list']);
        $this->success('数据获取成功', $lists['list']);
    }


    /**
     * 切换菜单列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function change()
    {
        $application = $this->request->param('application');//trim(input('application'));
        //p($application);
        if (!SysApp::mk()->where(['code' => $application, 'is_deleted' => 0])->find()) {
            $this->error('非法数据，你的IP已被记录');
        }

        if ($this->isSuper) {
            $map = [['status', '=', 0], ['is_deleted', '=', 0], ['type', '<', 2], ['application', '=', $application]];
        } else {
            $role_ids = SysUserRole::mk()->where(['user_id' => $this->uuid])->column('role_id');
            $ids = SysRoleMenu::mk()->whereIn('role_id', $role_ids)->column('menu_id');
            $map = [['status', '=', 0], ['is_deleted', '=', 0], ['type', '<', 2], ['application', '=', $application], ['id', 'in', $ids]];
        }

        $query = SysMenu::mQuery();
        $query->where($map);
        // 数据列表搜索过滤
        $lists = $query->order('sort asc,id asc')->page(false, false, false, 20);
        if (count($lists['list']) > 0) $lists['list'] = SystemMenuAntService::instance()->sysMenuToRouterTree($lists['list']);
        $this->success('数据获取成功', $lists['list']);
    }

    /**
     * 选择应用时获取菜单选择树
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function appTree()
    {
        $menus = [];
        $application = $this->request->param('application');//trim(input('application'));
        if ($application) {
            $menus = SysMenu::mk()
                ->field('id,parent_id,name as title,id as value, parent_id as parentId')
                ->where(['application' => $application])
                ->where(['visible' => 'Y'])
                ->where('type', '<', 2)
                ->order('id asc')
                ->select()
                ->toArray();
            if (count($menus) > 0) $menus = SystemMenuAntService::instance()->toTree($menus);
        } else {
            $menus = [];
        }
        $this->success('数据获取成功', $menus);
    }

    /**
     * 获取菜单选择树(角色选取菜单时)
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function tree()
    {
        if (!empty($this->request->param('tenant_id'))) {
            $tenant_id = trim($this->request->param('tenant_id'));
        } else {
            $tenant_id = $this->tenant_id;
        }
        $tenant = SysTenant::mk()->where(['id' => $tenant_id])->where(['is_deleted' => 0, 'status' => 0])->findOrEmpty();
        $industryIds = $tenant['industry_id'] ? explode(',', $tenant['industry_id']) : [];
        if (in_array('155', $industryIds)) {
            $visibleMap = 1;
        } else {
            $visibleMap = [['hide', '=', 0]];
        }
        //如果是租户账号列出所有该租户可用菜单
        //if($tenant_id){
        $menus = SysTenantMenu::mk()->where(['tenant_id' => $tenant_id])->order('id asc')->column('menu_id');
        if (empty($menus)) $this->error('请先给租户分配菜单权限');
        $map = [['id', 'in', $menus]];
        //}else{
        //$map = 1;
        //}

        $trees = SysMenu::mk()
            ->where(['is_deleted' => 0])
            ->where($visibleMap)
            ->where($map)
            ->field('id,parent_id,name as title,id as value, parent_id as parentId')
            ->order('sort desc')
            ->select()
            ->toArray();
        $lists = SystemMenuAntService::instance()->toTree($trees);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 获取菜单选择树(租户选取菜单时)
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function treeAll()
    {
        $tenant_id = $this->request->param('tenant_id') ? $this->request->param('tenant_id') : '';
        $industry_id = $this->request->param('industry_id') ? explode(',', trim($this->request->param('industry_id'))) : [];
        if ($tenant_id) {
            $appids = SysTenantApp::mk()->where(['tenant_id' => $tenant_id])->order('id asc')->column('app_id');
            $apps = SysApp::mk()->whereIn('id', $appids)->where(['is_deleted' => 0])->column('code');
        } else {
            $apps = SysApp::mk()->where(['is_deleted' => 0])->column('code');
        }
        if (in_array('155', $industry_id)) {
            $map = 1;
        } else {
            $map = [['hide', '=', 0]];
        }
        $trees = SysMenu::mk()
            ->where(['is_deleted' => 0])
            ->where($map)
            ->whereIn('application', $apps)
            ->field('id,parent_id,name as title,id as value, parent_id as parentId')
            ->order('sort desc')
            ->select()
            ->toArray();
        $lists = SystemMenuAntService::instance()->toTree($trees);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 添加菜单
     * @auth true
     * @return void
     */
    public function save()
    {
        SysMenu::mForm('form');
    }

    /**
     * 更新菜单
     * @auth true
     * @param int $id
     * @return void
     */
    public function update(int $id)
    {
        SysMenu::mForm('form');
    }

    /**
     * 修改菜单状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysMenu::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 移到回收站
     * @auth true
     * @return void
     */
    public function remove()
    {
        $id = intval($this->request->param('id'));
        if ($this->checkChildrenExists($id)) {
            $this->error('您要删除的菜单下还有下级菜单，如需删除请先删除下级菜单后再删除该菜单。');
        } else {
            SysMenu::mSave(['is_deleted' => 1]);
        }
    }

    /**
     * 检查子菜单是否存在
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkChildrenExists(int $id): bool
    {
        if (SysMenu::mk()->where('parent_id', $id)->where(['is_deleted' => 0])->find()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 表单数据处理
     * @param array $data
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function _form_filter(array &$data)
    {
        if (isset($data['tenant_id'])) unset($data['tenant_id']);
        if (isset($data['id'])) $data['id'] = intval($data['id']);
        if (!isset($data['parent_id']) || $data['parent_id'] == 0) {
            $data['level'] = '0';
        } else {
            if (is_array($data['parent_id'])) {
                $data['parent_id'] = array_pop($data['parent_id']);
            }
            $parentMenu = SysMenu::mk()->where(['id' => $data['parent_id']])->find();
            $data['level'] = $parentMenu['level'] . ',' . $parentMenu['id'];
        }
        if (!isset($data['name']) || !isset($data['code']) || !isset($data['application'])) $this->error('缺少菜单重要字段，保存失败！');
    }

    /**
     * 表单结果处理
     * @param bool $result
     * @param array $data
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function _form_result(bool $result, array $data)
    {
        // 生成RESTFUL按钮菜单
        if (isset($data['type']) && $data['type'] == 1 && isset($data['restful']) && $data['restful'] == '0') {
            $model = SysMenu::mk()->where(['id' => $data['id']])->field('id,level,name,application,code,router')->find()->toArray();
            $this->genButtonMenu($model);
        }// else {
            //$this->error('生成RESTFUL按钮菜单失败！');
        //}
        if ($result && $this->request->isPost()) {
            $this->success('编辑成功！');
        }

    }

    /**
     * 生成按钮菜单
     * @param array $model
     * @return bool
     */
    public function genButtonMenu(array $model): bool
    {
        $buttonMenus = [
            ['parent_id' => $model['id'], 'level' => $model['level'] . ',' . $model['id'], 'type' => 2, 'name' => $model['name'] . '分页列表', 'permission' => $model['application'] . ':' . substr_replace($model['router'], '', 0, 1) . ':index', 'code' => $model['code'] . '_index', 'application' => $model['application']],
            ['parent_id' => $model['id'], 'level' => $model['level'] . ',' . $model['id'], 'type' => 2, 'name' => $model['name'] . '保存', 'permission' => $model['application'] . ':' . substr_replace($model['router'], '', 0, 1) . ':save', 'code' => $model['code'] . '_save', 'application' => $model['application']],
            ['parent_id' => $model['id'], 'level' => $model['level'] . ',' . $model['id'], 'type' => 2, 'name' => $model['name'] . '更新', 'permission' => $model['application'] . ':' . substr_replace($model['router'], '', 0, 1) . ':update', 'code' => $model['code'] . '_update', 'application' => $model['application']],
            ['parent_id' => $model['id'], 'level' => $model['level'] . ',' . $model['id'], 'type' => 2, 'name' => $model['name'] . '删除', 'permission' => $model['application'] . ':' . substr_replace($model['router'], '', 0, 1) . ':remove', 'code' => $model['code'] . '_remove', 'application' => $model['application']],
            ['parent_id' => $model['id'], 'level' => $model['level'] . ',' . $model['id'], 'type' => 2, 'name' => $model['name'] . '读取', 'permission' => $model['application'] . ':' . substr_replace($model['router'], '', 0, 1) . ':detail', 'code' => $model['code'] . '_detail', 'application' => $model['application']],
            ['parent_id' => $model['id'], 'level' => $model['level'] . ',' . $model['id'], 'type' => 2, 'name' => $model['name'] . '查询', 'permission' => $model['application'] . ':' . substr_replace($model['router'], '', 0, 1) . ':query', 'code' => $model['code'] . '_query', 'application' => $model['application']],
            ['parent_id' => $model['id'], 'level' => $model['level'] . ',' . $model['id'], 'type' => 2, 'name' => $model['name'] . '普通列表', 'permission' => $model['application'] . ':' . substr_replace($model['router'], '', 0, 1) . ':list', 'code' => $model['code'] . '_list', 'application' => $model['application']]
        ];
        if (SysMenu::mk()->insertAll($buttonMenus)) {
            return true;
        } else {
            return false;
        }

    }


}