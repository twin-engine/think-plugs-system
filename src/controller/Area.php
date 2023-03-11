<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\BasePostageRegion;
use app\system\service\SystemMenuAntService;
use app\system\service\SystemMenuService;

/**
 * 区域接口
 * Class App
 * @package app\system\controller
 */
class Area extends Auth
{
    /**
     * 应用分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {

        //处理现有分页因数据量大导致前台不响应
        $page = intval($this->request->param('page'));
        $pageSize = intval($this->request->param('pageSize'));
        $top = BasePostageRegion::mk()->where(['pid' => 0])->column('id');
        $start = ($page - 1) * $pageSize;
        $end = $pageSize;
        //根据分页获取前10条pid为0数据
        $top10 = array_slice($top, $start, $end);
        //获取pid为$top10数据
        $sec = BasePostageRegion::mk()->whereIn('pid', $top10)->column('id');
        //合并上述二种数据
        $allId = array_merge($top10, $sec);
        //数组开始位置插位pid=0
        array_unshift($allId, 0);

        $query = BasePostageRegion::mQuery();
        $query->whereIn('pid', $allId);
        $lists = $query->order('id ASC')->page(false, false, false, 20);

        if (count($lists['list']) > 0) $lists['list'] = SystemMenuService::instance()->toTree($lists['list'], 0, 'id', 'pid');

        $lists['page'] = [
            'current' => $page,
            'limit' => 10,
            'pages' => count($lists['list']) > 0 ? ceil(count($lists['list']) / $pageSize) : 0,
            'total' => count($lists['list'])
        ];

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
        $trees = BasePostageRegion::mk()
            ->where(['status' => 0])
            ->field('id,pid as parent_id,name as value,name as label,code')
            ->order('id asc')
            ->select()
            ->toArray();
        $lists = SystemMenuAntService::instance()->toTree($trees);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 添加区域
     * @auth true
     * @return void
     */
    public function save()
    {
        BasePostageRegion::mForm('form');
    }

    /**
     * 更新区域
     * @auth true
     * @return void
     */
    public function update()
    {
        BasePostageRegion::mForm('form');
    }

    /**
     * 修改区域状态
     * @auth true
     * @return void
     */
    public function state()
    {
        BasePostageRegion::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除区域
     * @auth true
     * @return void
     */
    public function remove()
    {
        BasePostageRegion::mDelete();
    }
}