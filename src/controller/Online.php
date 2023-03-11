<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SystemUser;
use app\system\model\SystemUserToken;
use app\system\model\SysTenant;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 在线用户接口
 * Class Online
 * @package app\system\controller
 */
class Online extends Auth
{

    /**
     * 在线用户信息列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $ids = SystemUserToken::mk()->where('time', '>=', time())->order('id desc')->column('uuid');
        $query = SystemUser::mQuery();
        $query->whereIn('id', $ids);

        // 数据列表搜索过滤
        $query->dateBetween('create_at');
        $lists = $query->hidden(['password'])->order('id desc')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 强退用户
     * @auth true
     * @return void
     */
    public function kick()
    {
        $user_id = intval($this->request->param('id'));
        $user = SystemUser::mk()->where(['id' => $user_id])->findOrEmpty();
        if (!$user->isEmpty() && $user['user_type'] != 100) {
            SystemUserToken::mDelete('uuid', ['uuid' => $user['id']]);
        } else {
            $this->error('无权强退超管账号');
        }
    }

    /**
     * 列表数据处理
     * @param $data
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function _page_filter(&$data)
    {
        $tenants = SysTenant::mk()->where(['is_deleted' => '0', 'status' => '0'])->select()->toArray();
        foreach ($data as &$vo) {
            foreach ($tenants as $tenant) if ($tenant['id'] === $vo['tenant_id']) $vo['tenant'] = $tenant;
        }
    }
}