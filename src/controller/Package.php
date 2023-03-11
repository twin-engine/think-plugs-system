<?php

declare (strict_types=1);

namespace app\system\controller;

use app\system\model\SysPackage;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 套餐接口
 * Class Package
 * @package app\system\controller
 */
class Package extends Auth
{
    /**
     * 套餐分页列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $query = SysPackage::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('name,code');
        $lists = $query->order('sort desc,id ASC')->page();
        $this->success('数据获取成功', $lists);
    }


    /**
     * 套餐列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function list()
    {
        $lists = SysPackage::mk()->where(['is_deleted' => 0])->order('sort desc,id ASC')->select()->toArray();
        $this->success('数据获取成功', $lists);
    }


    /**
     * 添加套餐
     * @auth true
     * @return void
     */
    public function save()
    {
        SysPackage::mForm('form');
    }

    /**
     * 更新套餐
     * @auth true
     * @return void
     */
    public function update()
    {
        SysPackage::mForm('form');
    }

    /**
     * 修改套餐状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysPackage::mSave($this->_vali([
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
        SysPackage::mSave(['is_deleted' => 1, 'status' => 1], 'id', [['id', 'in', $ids]]);
    }

    /**
     * 通过套餐获取应用
     * @auth true
     * @return array|void
     */
    public function getAppByPackage()
    {
        if (empty($this->request->param('package_id'))) return [];
        $package_id = intval($this->request->param('package_id'));
        $res = SysPackage::mk()->where(['id' => $package_id])->order('id ASC')->findOrEmpty();
        if ($res) {
            $this->success('数据获取成功', $res);
        } else {
            $this->error('暂无关联数据');
        }

    }

    /**
     * 表单数据处理
     * @param array $data
     * @return void
     */
    protected function _form_filter(array &$data)
    {

    }
}