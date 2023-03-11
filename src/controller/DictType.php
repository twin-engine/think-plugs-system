<?php

declare(strict_types=1);

namespace app\system\controller;


use app\system\model\SysDictData;
use app\system\model\SysDictType;


/**
 * 字典类型接口
 * Class dict
 * @package app\system\controller
 */
class DictType extends Auth
{
    /**
     * 字典类型分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysDictType::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('name,code');
        $lists = $query->order('sort desc,id ASC')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 字典类型列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {
        $lists = SysDictType::mk()->where(['is_deleted' => 0])->order('sort desc,id ASC')->select()->toArray();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 字典类型与值的树结构
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function tree()
    {
        $query = SysDictType::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('name,code');
        $lists = $query->order('sort desc,id ASC')->page(false,false,false);
        if (count($lists['list']) > 0) $lists['list'] = $this->_toDictTree($lists['list']);
        $this->success('数据获取成功', $lists['list']);
    }

    /**
     * 字典类型转树内部方法
     * @param array $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function _toDictTree(array &$data)
    {
        if (empty($data)) return [];

        foreach ($data as &$value) {
            $value['children'] = $this->findData($value['id']);
        }

        return $data;
    }

    /**
     * 字典类型获取值
     * @auth true
     * @param int $typeId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function findData(int $typeId)
    {
        if (empty($typeId)) return [];
        $res = SysDictData::mk()->where(['type_id' => $typeId])->where(['is_deleted' => 0])->select()->toArray();
        return $res;
    }

    /**
     * 添加字典类型
     * @auth true
     * @return void
     */
    public function save()
    {
        SysDictType::mForm('form');
    }

    /**
     * 更新字典类型
     * @auth true
     * @return void
     */
    public function update()
    {
        SysDictType::mForm('form');
    }

    /**
     * 修改字典类型状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysDictType::mSave($this->_vali([
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
        SysDictType::mSave(['is_deleted' => 1]);
    }


}