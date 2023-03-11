<?php

declare(strict_types=1);

namespace app\system\controller;


use app\system\model\SysUploadGroup;
use app\system\service\SystemMenuService;


/**
 * 文件分组接口
 * Class Group
 * @package app\system\controller
 */
class Group extends Auth
{
    /**
     * 文件分组分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysUploadGroup::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status,tenant_id')->dateBetween('created_at');
        $query->like('name,code');
        $lists = $query->order('id ASC')->page();
        $this->success('数据获取成功', $lists);
    }


    /**
     * 文件分组树
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function tree()
    {
        $trees = SysUploadGroup::mk()
            ->where(['is_deleted' => 0])
            ->where(['tenant_id' => $this->tenant_id])
            ->field('id,parent_id,name as title,id as value')
            //->field('id,parent_id,name as label')
            ->order('id asc')
            ->select()
            ->toArray();
        $lists = SystemMenuService::instance()->toTree($trees);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 文件分组列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {
        $lists = SysUploadGroup::mk()
            ->where(['is_deleted' => 0])
            ->field('id,parent_id,name as title,id as value')
            //->field('id,parent_id,name as label')
            ->order('id asc')
            ->select()
            ->toArray();
        $this->success('数据获取成功', $lists);
    }


    /**
     * 文件分组保存
     * @auth true
     * @return void
     */
    public function save()
    {
        SysUploadGroup::mForm('form');
    }

    /**
     * 更新文件分组
     * @auth true
     * @return void
     */
    public function update()
    {
        SysUploadGroup::mForm('form');
    }


    /**
     * 移到回收站
     * @auth true
     * @return void
     */
    public function remove()
    {
        $id = $this->request->param('id');//intval(input('id'));
        if ($this->checkChildrenExists($id)) {
            $this->error('您要删除的菜单下还有下级菜单，如需删除请先删除下级菜单后再删除该菜单。');
        } else {
            SysUploadGroup::mSave(['is_deleted' => 1]);
        }
    }

    /**
     * 检查子分组是否存在
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkChildrenExists(int $id): bool
    {
        if (SysUploadGroup::mk()->where('parent_id', $id)->where(['is_deleted' => 0])->find()) {
            return true;
        } else {
            return false;
        }
    }

}