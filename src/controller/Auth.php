<?php

declare(strict_types=1);

namespace app\system\controller;

use think\admin\extend\JwtExtend;
use Exception;
use think\admin\Controller;
use think\admin\service\AdminService;
use think\exception\HttpResponseException;

/**
 * 接口授权认证基类
 * Class Auth
 * @package app\system\controller
 */
abstract class Auth extends Controller
{
    /**
     * 当前用户编号
     * @var integer
     */
    protected $uuid;

    /**
     * 当前租户编号
     * @var integer
     */
    protected $tenant_id;

    /**
     * 当前用户是否为超级管理员
     * @var bool
     */
    protected $isSuper;

    /**
     * 当前用户是否为租户创始人
     * @var bool
     */
    protected $isAdmin;

    /**
     * 当前用户数据
     * @var array
     */
    protected $user;

    /**
     * 控制器初始化
     * @return void
     */
    protected function initialize()
    {
        try {
            // 获取用户数据
            $this->user = $this->app->session->get('user')?$this->app->session->get('user'):$this->getUser();
            $this->uuid = AdminService::getUserId();
            $this->isSuper = AdminService::isSuper();
            $this->isAdmin = AdminService::isAdmin();
            $this->tenant_id = AdminService::getTenantId();
            if (empty($this->uuid)) {
                $this->error('用户登录失败！', '{-null-}', 401);
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage(), [], $exception->getCode());
        }

    }

    /**
     * 获取用户数据
     * @return array
     */
    protected function getUser(): array
    {
        try {
            if (empty($this->uuid)) {
                $token = $this->request->param('token') ?: $this->request->header('Jwt-Token', '');
                if (empty($token)) $this->error('登录认证TOKEN不能为空！');
                $payload = JwtExtend::verifyToken($token);
                $this->uuid = $payload['data']['id'];
                if(empty($this->uuid) || !is_numeric($this->uuid)) $this->error('token错误！', '{-null-}', 401);
            }
            return $payload['data'];
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            trace_file($exception);
            $this->error($exception->getMessage());
        }
    }


    /**
     * 显示用户禁用提示
     * @return void
     */
    protected function checkUserStatus()
    {
        if (intval($this->user['status'])) {
            $this->error('账户已被冻结！');
        }
    }
}