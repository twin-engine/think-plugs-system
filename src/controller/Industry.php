<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysIndustry;
use app\system\service\SystemMenuAntService;


/**
 * 行业分类管理
 * class Industry
 * @package app\system\controller
 */
class Industry extends Auth
{
    /**
     * 行业分类分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysIndustry::mQuery();
        $query->where(['is_deleted' => 0]);
        // 数据列表搜索过滤
        $query->equal('status,parent_id,cate_id')->dateBetween('created_at');
        $query->like('title');
        $lists = $query->order('sort desc,id asc')->page(false,false,false);
        if (count($lists['list']) > 0) $lists['list'] = SystemMenuAntService::instance()->toTree($lists['list']);
        $this->success('数据获取成功', $lists['list']);
    }

    /**
     * 行业分类列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {
        $lists = SysIndustry::mk()
            ->where(['is_deleted' => '0', 'status' => 0])
            ->order('id DESC')
            ->select()
            ->toArray();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 获取分类选择树
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function tree()
    {
        $trees = SysIndustry::mk()
            ->where(['is_deleted' => 0])
            ->where(['status' => 0])
            ->field('id,parent_id,title,id as value')
            //->field('id,parent_id,name as label')
            ->order('sort desc')
            ->select()
            ->toArray();
        $lists = SystemMenuAntService::instance()->toTree($trees);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 添加行业分类
     * @auth true
     * @return void
     */
    public function save()
    {
        sysoplog($this->user['username'], '行业分类管理', '新增行业分类');
        SysIndustry::mForm('form');
    }

    /**
     * 编辑行业分类
     * @auth true
     * @return void
     */
    public function update()
    {
        sysoplog($this->user['username'], '行业分类管理', '编辑行业分类');
        SysIndustry::mForm('form');
    }

    /**
     * 修改状态
     * @auth true
     * @return void
     */
    public function state()
    {
        sysoplog($this->user['username'], '分类管理', '修改分类状态');
        SysIndustry::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除行业分类
     * @auth true
     * @return void
     */
    public function remove()
    {
        $id = $this->request->param('id');//input('id');
        $ids = explode(',', $id);

        if ($this->checkChildrenExists($ids)) {
            $this->error('您要删除的分类下还有下级分类，如需删除请先删除下级分类后再删除该分类。');
        } else {
            sysoplog($this->user['username'], '分类管理', '删除分类');
            SysIndustry::mSave(['is_deleted' => 1, 'status' => 1], 'id', [['id', 'in', $ids]]);
        }
    }

    /**
     * 检查子分类是否存在
     * @param array $ids
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkChildrenExists(array $ids): bool
    {
        if (SysIndustry::mk()->whereIn('parent_id', $ids)->where(['is_deleted' => 0])->find()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 表单数据处理
     * @param array $data
     * @return void
     */
    protected function _form_filter(array &$data)
    {
        if (!empty($data['id']) && $data['id'] === $data['parent_id']) {
            $this->error('上级分类不能为本分类');
        }
    }

}
