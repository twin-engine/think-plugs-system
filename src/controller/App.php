<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysApp;


/**
 * 应用接口
 * Class App
 * @package app\system\controller
 */
class App extends Auth
{
    /**
     * 应用分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysApp::mQuery();
        $query->where(['is_deleted' => 0]);
        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('name,active');
        $lists = $query->order('sort DESC,id ASC')->page();
        $this->success('数据获取成功', $lists);
    }


    /**
     * 应用列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {
        $lists = SysApp::mk()->where(['is_deleted' => 0, 'status' => 0])->order('sort DESC,id ASC')->select()->toArray();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 添加应用
     * @auth true
     * @return void
     */
    public function save()
    {
        SysApp::mForm('form');
    }

    /**
     * 修改应用状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysApp::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 设置默认应用
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setDefault()
    {
        $id = intval($this->request->param('id'));
        $app = $this->app->db->name('sys_app')->where(['active' => 'Y'])->where(['is_deleted' => 0])->find();
        if ($app) {
            SysApp::mk()->where(['active' => 'Y'])->update(['active' => 'N']);
            SysApp::mSave(['active' => 'Y'], 'id', ['id' => $id]);
        } else {
            SysApp::mSave(['active' => 'Y'], 'id', ['id' => $id]);
        }
    }

    /**
     * 更新应用
     * @auth true
     * @return void
     */
    public function update()
    {
        SysApp::mForm('form');
    }

    /**
     * 删除应用
     * @auth true
     * @return void
     */
    public function remove()
    {
        SysApp::mDelete();
    }

}