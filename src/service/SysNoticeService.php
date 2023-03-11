<?php

declare(strict_types=1);

namespace app\system\service;

use app\system\model\SysNotice;
use app\system\model\SysNoticeUser;
use think\admin\Service;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 公告管理服务
 * Class SystemNoticeService
 * @package app\system\service
 */
class SysNoticeService extends Service
{

    /**
     * 获取登录用户的未读公告
     * @param int $userid
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getNotice(int $userid): array
    {
        if (empty($userid)) return [];
        $t = [];
        $g = [];
        $noticeIds = SysNoticeUser::mk()->where(['user_id' => $userid])->where(['status' => 0])->column('notice_id');
        if (count($noticeIds) > 0) {
            $t = SysNotice::mk()->whereIn('id', $noticeIds)->where(['status' => 1])->where(['type' => 1])->select()->toArray();
            $g = SysNotice::mk()->whereIn('id', $noticeIds)->where(['status' => 1])->where(['type' => 2])->select()->toArray();
        }

        $count = count($t) + count($g);
        $res = ['count' => $count, 't' => $t, 'g' => $g];
        return $res;
    }

}