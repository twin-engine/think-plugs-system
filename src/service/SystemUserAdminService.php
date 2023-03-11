<?php

declare(strict_types=1);

namespace app\system\service;

use app\system\model\SystemUser;
use app\system\model\SystemUserToken;
use think\admin\Exception;
use think\admin\extend\JwtExtend;
use think\admin\Service;


/**
 * 管理员用户数据管理服务
 * Class SystemUserAdminService
 * @package app\system\service
 */
class SystemUserAdminService extends Service
{
    const API_TYPE_WAP = 'wap';
    const API_TYPE_WEB = 'web';
    const API_TYPE_WXAPP = 'wxapp';
    const API_TYPE_WECHAT = 'wechat';
    const API_TYPE_IOSAPP = 'iosapp';
    const API_TYPE_ANDROID = 'android';

    const TYPES = [
        // 接口支付配置（不需要的直接注释）
        self::API_TYPE_WAP => [
            'name' => '手机浏览器',
            'auth' => 'phone',
        ],
        self::API_TYPE_WEB => [
            'name' => '电脑浏览器',
            'auth' => 'phone',
        ],
        self::API_TYPE_WXAPP => [
            'name' => '微信小程序',
            'auth' => 'openid1',
        ],
        self::API_TYPE_WECHAT => [
            'name' => '微信服务号',
            'auth' => 'openid2',
        ],
        self::API_TYPE_IOSAPP => [
            'name' => '苹果APP应用',
            'auth' => 'phone',
        ],
        self::API_TYPE_ANDROID => [
            'name' => '安卓APP应用',
            'auth' => 'phone',
        ],
    ];

    /**
     * 更新用户用户参数
     * @param $map
     * @param array $data
     * @param string $type
     * @param bool $force
     * @return array
     * @throws Exception
     */
    public static function set($map, array $data, string $type, bool $force = false): array
    {
        unset($data['id'], $data['is_deleted'], $data['create_at']);
        $user = SystemUser::mk()->where($map)->where(['is_deleted' => 0])->findOrEmpty();
        if (!$user->save($data)) throw new Exception("更新用户资料失败！");
        // 刷新用户认证令牌
        //if ($force) SystemUserTokenService::token((int)$user['id'], $type);
        // 返回当前用户资料
        return static::get((int)$user['id'], $type);
    }

    /**
     * 获取用户数据
     * @param int $uuid
     * @param string $type
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function get(int $uuid, string $type): array
    {
        $map = ['id' => $uuid, 'is_deleted' => 0];
        $user = SystemUser::mk()->where($map)->findOrEmpty();
        if ($user->isEmpty()) throw new Exception('用户还没有注册！');
        // 用户认证令牌处理
        //$map = ['uuid' => $uuid, 'type' => $type];
        //if (!($access = SystemUserToken::mk()->where($map)->find())) {
           // [$state, $message, $access] = SystemUserTokenService::token($uuid, $type);
            //if (empty($state) || empty($access)) throw new Exception($message);
       // }
        $payload['data'] = $user;//['id'=>$user['id'],'tenant_id'=>$user['tenant_id'],'dept_id'=>$user['dept_id'],'contact_phone'=>$user['contact_phone'],'contact_mail'=>$user['contact_mail'],'username'=>$user['username'],'realname'=>$user['realname'],'user_type'=>$user['user_type']];
        $user['token'] = JwtExtend::authorizations($payload);
        //$user['token'] = ['token' => $access['token'], 'expire' => $access['time']];
        return $user->hidden(['is_deleted', 'password', 'login_num', 'sort', 'dashboard'])->toArray();
    }

}