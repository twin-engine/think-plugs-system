<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysRole;
use app\system\model\SysRoleDept;
use app\system\model\SysRoleMenu;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 角色接口
 * Class Role
 * @package app\system\controller
 */
class Role extends Auth
{
    /**
     * 角色分页列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $query = SysRole::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('tenant_id,status')->dateBetween('created_at')->dataScope('created_by');//数据权限
        $query->like('name,code');
        $query->dataScope('created_by');
        $lists = $query->order('sort desc,id ASC')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 角色列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function list()
    {
        $tenant_id = intval($this->request->param('tenant_id')) ? intval($this->request->param('tenant_id')) : $this->tenant_id;
        $lists = SysRole::mk()->where(['tenant_id' => $tenant_id, 'is_deleted' => 0])->where('id', '<>', 1)->order('sort desc,id ASC')->select()->toArray();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 通过角色获取菜单
     * @auth true
     * @return array|void
     */
    public function getMenuByRole()
    {
        if (empty($this->request->param('id'))) return [];
        $id = intval($this->request->param('id'));
        $res = SysRoleMenu::mk()->where(['role_id' => $id])->order('id ASC')->column('menu_id');
        //if($res){
        $this->success('数据获取成功', $res);
        //}else{
        //$this->error('暂无关联数据');
        //}

    }

    /**
     * 通过角色获取部门
     * @auth true
     * @return array|void
     */
    public function getDeptByRole()
    {
        if (empty($this->request->param('id'))) return [];
        $id = intval($this->request->param('id'));
        $data_scope = intval($this->request->param('data_scope'));
        if ($data_scope != 5) return [];
        $res = SysRoleDept::mk()->where(['role_id' => $id])->order('id ASC')->column('dept_id');
        $this->success('数据获取成功', $res);
    }

    /**
     * 添加角色
     * @auth true
     * @return void
     */
    public function save()
    {
        SysRole::mForm('form');
    }

    /**
     * 更新角色
     * @auth true
     * @return void
     */
    public function update()
    {
        SysRole::mForm('form');
    }

    /**
     * 修改角色状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysRole::mSave($this->_vali([
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
        SysRole::mSave(['is_deleted' => 1]);
    }

    /**
     * 表单数据处理
     * @param array $data
     * @return void
     * @throws DbException
     */
    protected function _form_filter(array &$data)
    {
        if (isset($data['menu_ids']) && !empty($data['menu_ids'])) {
            if (SysRoleMenu::mk()->where(['role_id' => $data['id']])->count() > 0) {
                SysRoleMenu::mk()->where(['role_id' => $data['id']])->delete();
            }
            $data['menu_ids'] = explode(',', $data['menu_ids']);
            p($data['menu_ids']);
            $data_menus = [];
            foreach ($data['menu_ids'] as $v) {
                $data_menus[] = [
                    'role_id' => $data['id'],
                    'menu_id' => $v
                ];
            }
            unset($data['menu_ids']);
            SysRoleMenu::mk()->insertAll($data_menus);
        }
        if (isset($data['data_scope']) && !empty($data['data_scope'])) {//更改数据边界，则删除角菜与部门关联数据
            if (SysRoleDept::mk()->where(['role_id' => $data['id']])->count() > 0) {
                SysRoleDept::mk()->where(['role_id' => $data['id']])->delete();
            }
            if (!empty($data['dept_ids'])) {//部门ID有传值，则执行写入操作
                $data['dept_ids'] = explode(',', $data['dept_ids']);
                $data_depts = [];
                foreach ($data['dept_ids'] as $v) {
                    $data_depts[] = [
                        'role_id' => $data['id'],
                        'dept_id' => $v
                    ];
                }
                unset($data['dept_ids']);
                SysRoleDept::mk()->insertAll($data_depts);
            }
        }
    }

}