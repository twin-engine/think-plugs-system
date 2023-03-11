<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysDept;
use app\system\service\SystemMenuService;


/**
 * 部门接口
 * Class Dept
 * @package app\system\controller
 */
class Dept extends Auth
{
    /**
     * 部门分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysDept::mQuery();
        $query->where(['is_deleted' => 0]);
        // 数据列表搜索过滤
        $query->equal('level,status,parent_id,tenant_id')->dateBetween('created_at');
        $query->like('name,leader,phone');
        $lists = $query->order('sort desc,id asc')->page();
        if (count($lists['list']) > 0) $lists['list'] = SystemMenuService::instance()->toTree($lists['list']);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 部门列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {
        $lists = SysDept::mk()->where(['is_deleted' => 0])->order('sort desc,id ASC')->select()->toArray();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 获取部门选择树
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
        $trees = SysDept::mk()
            ->where(['is_deleted' => 0])
            ->where(['tenant_id' => $tenant_id])
            ->where(['status' => 0])
            ->field('id,parent_id,name as title,id as value')
            //->field('id,parent_id,name as label')
            ->order('sort desc')
            ->select()
            ->toArray();
        //p($trees);
        $lists = SystemMenuService::instance()->toTree($trees);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 添加部门
     * @auth true
     * @return void
     */
    public function save()
    {
        sysoplog($this->user['username'], '角色管理', '添加角色');
        SysDept::mForm('form');
    }

    /**
     * 更新部门
     * @auth true
     * @return void
     */
    public function update()
    {
        sysoplog($this->user['username'], '角色管理', '更新角色');
        SysDept::mForm('form');
    }

    /**
     * 修改部门状态
     * @auth true
     * @return void
     */
    public function state()
    {
        sysoplog($this->user['username'], '角色管理', '修改角色状态');
        SysDept::mSave($this->_vali([
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
        $id = $this->request->param('id');
        $ids = explode(',', $id);
        if ($this->checkChildrenExists($ids)) {
            $this->error('您要删除的部门下还有下级部门，如需删除请先删除下级部门后再删除该部门。');
        } else {
            sysoplog($this->user['username'], '角色管理', '删除角色');
            SysDept::mSave(['is_deleted' => 1]);
        }
    }

    /**
     * 检查子部门是否存在
     * @param array $ids
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkChildrenExists(array $ids): bool
    {
        if (SysDept::mk()->whereIn('parent_id', $ids)->where(['is_deleted' => 0])->find()) {
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

        $pid = !empty($data['parent_id']) ? $data['parent_id'] : 0;
        if ($pid === 0) {
            $data['level'] = $data['parent_id'] = '0';
        } else if (is_array($pid)) {
            array_unshift($pid, '0');
            $data['level'] = implode(',', $pid);
            $data['parent_id'] = array_pop($pid);
        } else {
            $up = SysDept::mk()->where(['id' => $data['parent_id']])->find();
            $data['level'] = $up['level'] . ',' . $data['parent_id'];
        }
        //var_dump($data);
        if (!empty($data['id']) && $data['id'] === $data['parent_id']) {
            $this->error('上级部门不能为本部门');
        }
    }
}