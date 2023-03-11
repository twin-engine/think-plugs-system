<?php

declare(strict_types=1);

namespace app\system\service;

use app\system\model\SysPage;
use think\admin\Service;


/**
 * 模板获取
 * Class SysPageService
 * @package app\system\service
 */
class SysPageService extends Service
{

    /**
     * 默认模板
     * @param int $tenant_id
     * @return array|int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDefaulPage(int $tenant_id)
    {
        if (!$tenant_id) return [];
        $result = SysPage::mk()->where(['tenant_id' => $tenant_id, 'page_type' => 10])->where(['status' => 0])->field('id')->find();
        if ($result) {
            return $result['id'];
        } else {
            return 0;
        }

    }

}