<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\service\NoSqlPageService;
use think\facade\Db;

/**
 *数据表接口
 * Class Data
 * @package app\system\controller
 */
class Data extends Auth
{
    /**
     * 数据表列表
     * @auth true
     * @return void
     */
    public function index()
    {
        $tables = Db::query('show table status');

        // 重组数据
        foreach ($tables as &$table) {
            $table = array_change_key_case($table);
        }

        if ($this->request->param('name')) $tables = seacharr_by_value($tables, 'name', trim($this->request->param('name')));
        sysoplog($this->user['username'], '数据表管理', '查看数据表列表');
        $this->success('数据获取成功', NoSqlPageService::instance()->setPage(intval($this->request->param('page')), intval($this->request->param('pageSize')), $tables));
    }

    /**
     * 数据表详情
     * @auth true
     * @return void
     */
    public function detail()
    {
        $table = $this->request->post('name');
        if (!$table) {
            $this->error('请选择要查看的数据表');
        }
        $detail = array_values(Db::getFields($table));
        sysoplog($this->user['username'], '数据表管理', '查看数据表详情');
        $this->success('数据获取成功', $detail);
    }

    /**
     * 数据表优化
     * @auth true
     * @return void
     */
    public function optimize()
    {
        $tables = $this->request->post('tables');
        if ($tables) {
            $tables = explode(',', $tables);
        } else {
            $this->error('请选择要优化的数据表');
        }
        foreach ($tables as $table) {
            $this->app->db->query("OPTIMIZE TABLE `{$table}`");
        }
        sysoplog($this->user['username'], '数据表管理', '数据表优化');
        $this->success('数据表优化成功');
    }

    /**
     * 修复所有数据表
     * @auth true
     * @return void
     */
    public function repair()
    {
        $tables = $this->request->post('tables');
        if ($tables) {
            $tables = explode(',', $tables);
        } else {
            $this->error('请选择要修复的数据表');
        }
        foreach ($tables as $table) {
            $this->app->db->query("REPAIR TABLE `{$table}`");
        }
        sysoplog($this->user['username'], '数据表管理', '数据表修复');
        $this->success('数据表修复成功');
    }

    /**
     * 清理表碎片
     * @auth true
     * @return void
     */
    public function fragment()
    {
        $tables = $this->request->post('tables');
        if ($tables) {
            $tables = explode(',', $tables);
        } else {
            $this->error('请选择要清理碎片的数据表');
        }
        foreach ($tables as $table) {
            $this->app->db->query("ANALYZE TABLE `{$table}`");
        }
        sysoplog($this->user['username'], '数据表管理', '数据表碎片清理');
        $this->success('数据表碎片清理成功');
    }
}