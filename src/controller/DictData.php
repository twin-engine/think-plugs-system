<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysDictData;
use app\system\model\SysDictType;


/**
 * 字典接口
 * Class dictData
 * @package app\system\controller
 */
class DictData extends Auth
{
    /**
     * 字典分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysDictData::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status,type_id')->dateBetween('created_at');
        $query->like('name,code');
        $lists = $query->order('sort desc,id ASC')
            ->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 字典列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {
        $lists = SysDictData::mk()->where(['is_deleted' => 0])
            ->order('sort desc,id ASC')
            ->select()
            ->toArray();
        $this->success('数据获取成功', $lists);
    }


    /**
     * 添加字典
     * @auth true
     * @return void
     */
    public function save()
    {
        SysDictData::mForm('form');
    }

    /**
     * 更新字典
     * @auth true
     * @param int $id
     * @return void
     */
    public function update(int $id)
    {
        SysDictData::mForm('form');
    }

    /**
     * 修改字典状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysDictData::mSave($this->_vali([
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
        SysDictData::mSave(['is_deleted' => 1]);
    }

    /**
     * 查某个字典
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function dropdown()
    {
        $code = $this->request->param('code');//trim(input('code'));
        $res = SysDictType::mk()->alias('a')
            ->join('sys_dict_data b', 'a.id=b.type_id')
            ->field('b.code,b.value')
            ->where(['a.code' => $code])
            ->where(['a.is_deleted' => 0])
            ->select()
            ->toArray();
        $this->success('数据获取成功', $res);
    }
}