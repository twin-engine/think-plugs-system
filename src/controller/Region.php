<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysRegion;
use think\admin\Controller;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 省市区接口（无需权限）
 * Class Role
 * @package app\system\controller
 */
class Region extends Controller
{
    /**
     * 获取所有地区(树状)
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function tree()
    {
        $query = SysRegion::mQuery();
        $query->field('id, pid, name, level');
        $lists = $query->order('id asc')->page();
        if (count($lists['list']) > 0) $lists['list'] = $this->getTreeList($lists['list']);
        $this->success('获取成功！', $lists['list']);
    }

    /**
     * 格式化为树状格式
     * @param $allList
     * @return array
     */
    private function getTreeList($allList)
    {
        $treeList = [];
        foreach ($allList as $pKey => $province) {
            if ($province['level'] == 1) {    // 省份
                $treeList[$province['id']] = $province;
                unset($allList[$pKey]);
                foreach ($allList as $cKey => $city) {
                    if ($city['level'] == 2 && $city['pid'] == $province['id']) {    // 城市
                        $treeList[$province['id']]['city'][$city['id']] = $city;
                        unset($allList[$cKey]);
                        foreach ($allList as $rKey => $region) {
                            if ($region['level'] == 3 && $region['pid'] == $city['id']) {    // 地区
                                $treeList[$province['id']]['city'][$city['id']]['region'][$region['id']] = $region;
                                unset($allList[$rKey]);
                            }
                        }
                    }
                }
            }
        }
        return $treeList;
    }

}