<?php

declare (strict_types=1);

namespace app\system\controller;

use app\system\model\SystemUser;
use app\system\service\MessageService;
use think\admin\service\EmailService;

/**
 * 用户管理
 * class Account
 * @package app\system\controller
 */
class Account extends Auth
{
    /**
     * 个人中心头像更新
     * @auth true
     * @return void
     */
    public function updateAvatar()
    {
        $data = $this->_vali([
            'id.require' => '用户ID不能为空',
            'headimg.require' => '头像必须上传!'
        ]);
        SystemUser::mSave($data);
    }

    /**
     * 修改手机号
     * @auth true
     * @return void
     */
    public function updatePhone()
    {
        $data = $this->_vali([
            'id.require' => '用户ID不能为空',
            'password.require' => '登录密码不能为空!',
            'phone.mobile' => '手机格式错误！',
            'phone.require' => '手机不能为空！',
            'captcha.require' => '验证码不能为空！'
        ]);
        if (!MessageService::instance()->checkVerifyCode($data['captcha'], $data['phone'])) {
            $this->error('手机短信验证失败！');
        }
        $user = SystemUser::mk()->where(['id' => $data['id']])->where(['is_deleted' => 0])->findOrEmpty();
        $userCheck = SystemUser::mk()->where('id', '<>', $data['id'])->where(['contact_phone' => $data['phone']])->where(['is_deleted' => 0])->findOrEmpty();
        if (!$userCheck->isEmpty()) {
            sysoplog($this->user['username'], '修改手机号', '手机号已存在');
            $this->error('手机号已存在!');
        }
        if ($user->isEmpty()) {
            sysoplog($this->user['username'], '修改手机号', '用户账号不存在');
            $this->error('账号错误，请联系平台客服!');
        }
        if (md5("{$user['password']}") != $data['password']) {
            sysoplog($user['username'], '修改手机号', '用户密码错误');
            $this->error('密码错误，请重新输入!');
        }
        if ($user['contact_phone'] === $data['phone']) {
            sysoplog($user['username'], '修改手机号', '原手机号与新手机号相同');
            $this->error('原手机号与新手机号相同，请重新输入!');
        } else {
            if (SystemUser::mk()->where(['id' => $data['id']])->update(['contact_phone' => $data['phone']])) {
                sysoplog($user['username'], '修改手机号', '手机号修改成功');
                $this->success('手机号修改成功，以后请用新手机号登录！');
            } else {
                sysoplog($user['username'], '修改手机号', '手机号修改失败');
                $this->error('手机号修改失败！');
            }
        }


    }

    /**
     * 个人中心资料更新
     * @auth true
     * @return void
     */
    public function update()
    {
        SystemUser::mForm('form');
    }

    /**
     * 修改邮箱
     * @auth true
     * @return void
     */
    public function updateEmail()
    {
        $data = $this->_vali([
            'id.require' => '用户ID不能为空',
            'password.require' => '登录密码不能为空!',
            'email.email' => '邮箱格式错误！',
            'email.require' => '手机不能为空！',
            'captcha.require' => '验证码不能为空！'
        ]);
        if (!EmailService::instance()->checkVerifyCode($data['captcha'], $data['email'])) {
            $this->error('邮件验证码验证失败！');
        }
        $user = SystemUser::mk()->where(['id' => $data['id']])->where(['is_deleted' => 0])->findOrEmpty();
        $userCheck = SystemUser::mk()->where('id', '<>', $data['id'])->where(['contact_mail' => $data['email']])->where(['is_deleted' => 0])->findOrEmpty();
        if (!$userCheck->isEmpty()) {
            sysoplog($this->user['username'], '修改邮箱', '邮箱已存在');
            $this->error('邮箱已存在!');
        }
        if ($user->isEmpty()) {
            sysoplog($this->user['username'], '修改邮箱', '用户账号不存在');
            $this->error('账号错误，请联系平台客服!');
        }
        if (md5("{$user['password']}") != $data['password']) {
            sysoplog($user['username'], '修改邮箱', '用户密码错误');
            $this->error('密码错误，请重新输入!');
        }
        if ($user['contact_mail'] === $data['email']) {
            sysoplog($user['username'], '修改邮箱', '原手机号与新手机号相同');
            $this->error('原邮箱与新邮箱相同，请重新输入!');
        } else {
            if (SystemUser::mk()->where(['id' => $data['id']])->update(['contact_mail' => $data['email']])) {
                sysoplog($user['username'], '修改邮箱', '邮箱修改成功');
                $this->success('邮箱修改成功，以后请用新邮箱登录！');
            } else {
                sysoplog($user['username'], '修改邮箱', '邮箱修改失败');
                $this->error('邮箱修改失败！');
            }
        }
    }

    /**
     * 密码检测
     * @auth true
     * @return bool
     */
    public function checkPwd()
    {
        $data = $this->_vali([
            'id.require' => '用户ID不能为空',
            'password.require' => '登录密码不能为空!'
        ]);

        $user = SystemUser::mk()->where(['id' => $data['id']])->where(['is_deleted' => 0])->findOrEmpty();
        if (md5("{$user['password']}") !== $data['password']) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 表单数据处理
     * @param array $data
     * @return void
     */
    protected function _form_filter(array &$data)
    {
        if (strpos($data['contact_phone'], '*') !== false) {
            unset($data['contact_phone']);
        }
        if (strpos($data['contact_mail'], '*') !== false) {
            unset($data['contact_mail']);
        }

        if (!empty($data['username'])) {
            if (in_array($data['username'], ['admin', 'superadmin', 'super', 'administrator', 'manager', 'sqm', 'dualengine', 'twinengine', '1234', 'yjw'], false)) {
                $this->error('该用户名已被系统保留，不可使用。');
            }
            $user = SystemUser::mk()->where(['username' => $data['username']])->where(['is_deleted' => 0])->findOrEmpty();
            if (!$user->isEmpty()) {
                $this->error('该用户名已被注册，请更换一个新的用户名。');
            }
        }
        if (!empty($data['contact_phone'])) {
            $u = SystemUser::mk()->where(['contact_phone' => $data['contact_phone']])->where(['is_deleted' => 0])->findOrEmpty();
            if (!$u->isEmpty()) {
                $this->error('手机号已存在，请更换手机号');
            }
        }

        if (!empty($data['contact_mail'])) {
            $u = SystemUser::mk()->where(['contact_mail' => $data['contact_mail']])->where(['is_deleted' => 0])->findOrEmpty();
            if (!$u->isEmpty()) {
                $this->error('邮箱已存在，请更换邮箱');
            }
        }
    }
}
