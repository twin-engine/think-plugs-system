<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免费声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\system\controller\api;

use app\system\controller\Auth;
use Exception;
use think\admin\service\QueueService;
use think\exception\HttpResponseException;

/**
 * 后台任务通用接口
 * Class Queue
 * @package app\system\controller\api
 */
class Queue extends Auth
{
    /**
     * 任务进度查询
     * @return void
     * @throws \think\admin\Exception
     */
    public function progress()
    {
        $input = $this->_vali(['code.require' => '任务编号不能为空！']);
        $queue = QueueService::instance()->initialize($input['code']);
        $this->success('获取任务进度成功！', $queue->progress());
    }

    /**
     * WIN停止监听进程
     * @return void
     */
    public function stop()
    {
        try {
            $message = $this->app->console->call('xadmin:queue', ['stop'])->fetch();
            if (stripos($message, 'sent end signal to process')) {
                sysoplog($this->user['username'], '系统运维管理', '尝试停止后台服务主进程');
                $this->success('停止后台服务主进程成功！');
            } elseif (stripos($message, 'processes to stop')) {
                $this->success('没有找到需要停止的进程！');
            } else {
                $this->error(nl2br($message));
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * WIN创建监听进程
     * @return void
     */
    public function start()
    {
        try {
            $message = $this->app->console->call('xadmin:queue', ['start'])->fetch();
            if (stripos($message, 'daemons started successfully for pid')) {
                sysoplog($this->user['username'], '系统运维管理', '尝试启动后台服务主进程');
                $this->success('后台服务主进程启动成功！');
            } elseif (stripos($message, 'daemons already exist for pid')) {
                $this->success('后台服务主进程已经存在！');
            } else {
                $this->error(nl2br($message));
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 任务状态检查api
     * @return void
     */
    public function iswin()
    {
        try {
            $message = $this->app->console->call('xadmin:queue', ['status'])->fetch();
            if (preg_match('/process.*?\d+.*?running/', $message)) {
                $this->success($message, true);
            } else {
                $this->error('未启动', false);
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

    }

    /**
     * 检查任务状态
     * @return void
     */
    public function status()
    {
        if ($this->isSuper) try {
            $message = $this->app->console->call('xadmin:queue', ['status'])->fetch();
            if (preg_match('/process.*?\d+.*?running/', $message)) {
                echo "<span class='color-green pointer' data-tips-text='{$message}'>已启动</span>";
            } else {
                echo "<span class='color-red pointer' data-tips-text='{$message}'>未启动</span>";
            }
        } catch (Exception $exception) {
            echo "<span class='color-red pointer' data-tips-text='{$exception->getMessage()}'>异 常</span>";
        } else {
            echo "<span class='color-red pointer' data-tips-text='只有超级管理员才能操作！'>无权限</span>";
        }
    }
}