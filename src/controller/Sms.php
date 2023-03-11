<?php

declare (strict_types=1);

namespace app\system\controller;

use app\system\model\SysSms;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 短信接口
 * Class Sms
 * @package app\system\controller
 */
class Sms extends Auth
{
    /**
     * 短信分页列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $query = SysSms::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('phone');
        $lists = $query->order('id DESC')->page();
        $this->success('数据获取成功', $lists);
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
        SysSms::mSave(['is_deleted' => 1]);
    }
}