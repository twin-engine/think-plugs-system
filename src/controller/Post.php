<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysPost;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 岗位接口
 * Class Post
 * @package app\system\controller
 */
class Post extends Auth
{
    /**
     * 岗位分页列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $query = SysPost::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status,tenant_id')->dateBetween('created_at')->dataScope('created_by');//数据权限
        $query->like('name,code');
        $lists = $query->order('sort desc,id ASC')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 岗位列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function list()
    {
        $lists = SysPost::mk()->where(['tenant_id' => $this->tenant_id])->where(['is_deleted' => 0])->order('sort desc,id ASC')->select()->toArray();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 添加岗位
     * @auth true
     * @return void
     */
    public function save()
    {
        SysPost::mForm('form');
    }

    /**
     * 更新岗位
     * @auth true
     * @return void
     */
    public function update()
    {
        SysPost::mForm('form');
    }

    /**
     * 修改岗位状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysPost::mSave($this->_vali([
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
        SysPost::mSave(['is_deleted' => 1]);
    }

}