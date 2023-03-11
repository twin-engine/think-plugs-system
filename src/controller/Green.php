<?php

declare (strict_types=1);

namespace app\system\controller;

use app\system\model\SysTenant;
use think\admin\model\SysGreen;



/**
 * 内容监控接口
 * Class Tenant
 * @package app\system\controller
 */
class Green extends Auth
{
    /**
     * 内容监控分页列表
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysGreen::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('suggestion,label,scene');
        $lists = $query->order('id DESC')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 监控详情
     * @return void
     */
    public function detail()
    {
        $id = $this->request->post('id');
        if (!$id) {
            $this->error('请选择要查看的条目');
        }
        $detail = SysGreen::mk()->where(['id' => $id])->findOrEmpty();
        sysoplog($this->user['username'], '内容管理', '查看详情');
        $this->success('数据获取成功', $detail);
    }

    /**
     * 移到回收站
     * @return void
     */
    public function remove()
    {
        $id = $this->request->param('id');//input('id');
        $ids = explode(',', $id);
        SysGreen::mSave(['is_deleted' => 1]);
    }

    /**
     * 列表数据处理
     * @param array $data
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function _page_filter(array &$data)
    {
        $tenants = SysTenant::mk()->where(['status' => '0'])->select()->toArray();
        foreach ($data as &$vo) {
            foreach ($tenants as $tenant) if ($tenant['id'] === $vo['tenant_id']) $vo['tenant'] = $tenant;
        }
    }


}