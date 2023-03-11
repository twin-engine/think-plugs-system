<?php

declare (strict_types=1);

namespace app\system\controller;

use Exception;
use Ip2Region;
use think\admin\model\SystemOplog;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 系统日志管理
 * Class Oplog
 * @package app\system\controller
 */
class Oplog extends Auth
{
    /**
     * 系统日志管理分页列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $query = SystemOplog::mQuery();
        // 数据列表搜索过滤
        $query->equal('username,action')->dateBetween('create_at');
        $query->like('content,geoip,node');
        $lists = $query->order('id desc')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 清理系统日志
     * @auth true
     * @return void
     */
    public function clear()
    {
        try {
            SystemOplog::mQuery()->empty();
            sysoplog($this->user['username'], '系统运维管理', '成功清理所有日志数据');
            $this->success('日志清理成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error("日志清理失败，{$exception->getMessage()}");
        }
    }

    /**
     * 删除系统日志
     * @auth true
     * @return void
     */
    public function remove()
    {
        SystemOplog::mDelete();
    }

    /**
     * 列表数据处理
     * @param array $data
     * @return void
     * @throws Exception
     */
    protected function _index_page_filter(array &$data)
    {
        $region = new Ip2Region();
        foreach ($data as &$vo) {
            $isp = $region->btreeSearch($vo['geoip']);
            $vo['geoisp'] = str_replace(['内网IP', '0', '|'], '', $isp['region'] ?? '') ?: '-';
        }
    }
}
