<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysTenant;
use app\system\model\SysUploadFile;
use app\system\model\SysUploadGroup;
use think\admin\Exception;
use think\admin\Storage;


/**
 * 文件上传接口
 * Class file
 * @package app\system\controller
 */
class File extends Auth
{
    /**
     * 文件上传分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysUploadFile::mQuery();
        $query->where(['is_deleted' => 0]);
        if (input('group_id') > 0) {
            $query->whereIn('group_id', $this->_groupAll($this->request->request('group_id')));
        }
        if (null !== input('group_id') && $this->request->request('group_id') == 0) {
            $query->where(['group_id' => $this->request->request('group_id')]);
        }

        if (null !== input('type')) {
            if (input('type') === 20) {
                $query->whereIn('type', [20, 40, 50]);
            } else {
                $query->where('type', '=', $this->request->request('type'));
            }
        }

        // 数据列表搜索过滤
        $query->equal('status,tenant_id')->dateBetween('created_at')->dataScope('created_by');//数据权限
        $query->like('name,code');
        $lists = $query->order('id DESC')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 列出相关联的所有分组
     * @auth true
     * @param $groupId
     * @return array|string
     */
    private function _groupAll($groupId)
    {
        if (!$groupId) return [];
        $group = SysUploadGroup::mk()->where('parent_id', '=', $groupId)->where(['is_deleted' => 0])->column('id');
        //$dept_ids = explode(',',$dept['level']);
        //implode(',',array_unshift($dept_ids,$deptId))
        $group_ids = count($group) > 0 ? implode(',', $group) . ',' . $groupId : $groupId . '';
        return $group_ids;
    }

    /**
     * 文件上传列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {

        $query = SysUploadFile::mQuery();
        $query->where(['is_deleted' => 0]);
        if (!empty($this->request->request('ids')) && $this->request->request('ids')) $query->whereIn('id', $this->request->request('ids'));
        $lists = $query->order('id DESC')->page(false, false, false, 15);
        //p($lists);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 上传文件
     * @auth true
     * @return void
     */
    public function save()
    {
        SysUploadFile::mForm('form');
    }

    /**
     * 移动文件
     * @auth true
     * @return void
     */
    public function move()
    {
        $data = $this->_vali([
            'group_id.require' => '新的分组必须选择！',
            'id.require' => 'ID不能为空！',
        ]);
        $ids = explode(',', $data['id']);
        if (SysUploadFile::mk()->whereIn('id', $ids)->update(['group_id' => $data['group_id']])) {
            $this->success('文件移动成功！');
        } else {
            $this->error('文件移动失败！');
        }
    }

    /**
     * 更新文件
     * @auth true
     * @return void
     */
    public function update()
    {
        SysUploadFile::mForm('form');
    }

    /**
     * 文件预览
     * @auth true
     * @return void
     */
    public function preview()
    {
        $this->success('文件预览！');
    }

    /**
     * 修改文件状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysUploadFile::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 移到回收站
     * @auth true
     * @return array|void
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remove()
    {
        if (empty($this->request->request('id'))) return [];
        $id = $this->request->request('id');
        $ids = explode(',', $id);
        foreach ($ids as $v) {
            $f = SysUploadFile::mk()->where(['id' => $v])->find();
            $name = $f['path'];
            $safeMode = $this->getSafe();
            Storage::instance($f['storage'])->del($name, $safeMode);
        }
        if (SysUploadFile::mk()->whereIn('id', $ids)->delete()) {
            $this->success('成功删除');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 获取文件上传类型
     * @return bool
     */
    private function getSafe(): bool
    {
        return boolval(input('safe', '0'));
    }

    /**
     * 租户图片库是否超额
     * @return array|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkImageCapacity()
    {
        if (!$this->tenant_id) return [];
        $package = SysTenant::mk()->alias('a')->join('sys_package b', 'a.package_id=b.id')->field('b.space_quantity')->where(['a.id' => $this->tenant_id])->where(['a.is_deleted' => 0])->find()->toArray();
        $space_quantity = $package['space_quantity'] * 1024 * 1024;
        $size = SysUploadFile::mk()->where(['is_deleted' => 0])->where(['tenant_id' => $this->tenant_id])->sum('size');
        if ($size < $space_quantity) {
            $this->success('获取成功', true);
        } else {
            $this->error('获取失败', false);
        }
    }
}