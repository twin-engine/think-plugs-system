<?php

declare (strict_types=1);

namespace app\system\controller;

use Exception;
use think\admin\model\SystemQueue;
use think\admin\service\QueueService;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 系统任务管理
 * Class Queue
 * @package app\admin\controller
 */
class Queue extends Auth
{
    /**
     * 系统任务管理
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $query = SystemQueue::mQuery();
        $query->equal('status')->like('code,title,command');
        $query->timeBetween('enter_time,exec_time')->dateBetween('create_at');
        $lists = $query->order('id ASC')->page();
        $this->success('数据获取成功', $lists);

    }

    /**
     * 重启系统任务
     * @auth true
     * @return void
     */
    public function redo()
    {
        try {
            $data = $this->_vali(['code.require' => '任务编号不能为空！']);
            $queue = QueueService::instance()->initialize($data['code'])->reset();
            $queue->progress(1, '>>> 任务重置成功 <<<', '0.00');
            $this->success('任务重置成功！', $queue->code);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 清理运行数据
     * @auth true
     * @return void
     */
    public function clean()
    {
        $this->_queue('定时清理系统运行数据', "xadmin:queue clean", 0, [], 0, 3600);
    }

    /**
     * 删除系统任务
     * @auth true
     * @return void
     */
    public function remove()
    {
        SystemQueue::mDelete();
    }

    /**
     * 分页数据回调处理
     * @param array $data
     * @param array $result
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function _index_page_filter(array $data, array &$result)
    {
        $result['extra'] = ['dos' => 0, 'pre' => 0, 'oks' => 0, 'ers' => 0];
        SystemQueue::mk()->field('status,count(1) count')->group('status')->select()->map(function ($item) use (&$result) {
            if ($item['status'] === 1) $result['extra']['pre'] = $item['count'];
            if ($item['status'] === 2) $result['extra']['dos'] = $item['count'];
            if ($item['status'] === 3) $result['extra']['oks'] = $item['count'];
            if ($item['status'] === 4) $result['extra']['ers'] = $item['count'];
        });
    }


}
