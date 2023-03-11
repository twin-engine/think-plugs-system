<?php

namespace app\system\controller\api;

use app\system\controller\Auth;

/**
 * 通用插件管理
 * Class Plugs
 * @package app\admin\controller\api
 */
class Plugs extends Auth
{

    /**
     * 优化数据库
     * @return void
     */
    public function optimize()
    {
        if ($this->isSuper) {
            sysoplog($this->user['username'], '系统运维管理', '创建数据库优化任务');
            $this->_queue('优化数据库所有数据表', 'xadmin:database optimize');
        } else {
            $this->error('只有超级管理员才能操作！');
        }
    }
}