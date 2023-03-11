<?php

namespace app\system;

use think\admin\Plugin;

/**
 * 插件服务注册
 * Class Service
 * @package app\admin
 */
class Service extends Plugin
{
    /**
     * 定义安装包名称
     * @var string
     */
    protected $package = 'rotoos/think-plugs-system';

    /**
     * 定义插件中心菜单
     * @return array
     */
    public static function menu(): array
    {
        return [];
    }
}