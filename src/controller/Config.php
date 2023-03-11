<?php

declare (strict_types=1);

namespace app\system\controller;

use app\system\model\SysConfig;


/**
 * 系统配置接口
 * Class Config
 * @package app\system\controller
 */
class Config extends Auth
{
    /**
     * 系统配置分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysConfig::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('name,code');
        $lists = $query->order('id ASC')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 添加系统配置
     * @auth true
     * @return void
     */
    public function save()
    {
        SysConfig::mForm('form');
    }

    /**
     * 更新系统配置
     * @auth true
     * @return void
     */
    public function update()
    {
        SysConfig::mForm('form');
    }


    /**
     * 修改系统配置状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysConfig::mSave($this->_vali([
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
        SysConfig::mSave(['is_deleted' => 1]);
    }

}