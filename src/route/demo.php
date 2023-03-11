<?php

use think\admin\Library;
use think\admin\service\RuntimeService;

/*! 演示环境禁止操作路由绑定 */
if (RuntimeService::check('demo')) {
    Library::$sapp->route->post('menu/add', function () {
        return json(['code' => 0, 'info' => '演示环境禁止添加菜单！']);
    });
}