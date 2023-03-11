<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SysDept;
use app\system\model\SysPackage;
use app\system\model\SysPost;
use app\system\model\SysRole;
use app\system\model\SystemUser;
use app\system\model\SysTenant;
use app\system\model\SysUserPost;
use app\system\model\SysUserRole;
use think\admin\extend\CodeExtend;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 用户接口
 * Class User
 * @package app\system\controller
 */
class User extends Auth
{


    /**
     * 用户信息列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $query = SystemUser::mQuery();
        if ($this->request->param('dept_id') > 0) $query->whereIn('dept_id', $this->_deptAll($this->request->param('dept_id')));
        $query->where(['is_deleted' => 0]);
        $query->dataScope('created_by');//数据权限
        $query->where('id', '<>', 10000);
        // 数据列表搜索过滤
        $query->equal('status,user_type,tenant_id')->dateBetween('login_at,create_at');
        $query->like('username,nickname,realname,contact_phone#phone,contact_mail#mail');
        $lists = $query->hidden(['password'])->order('sort desc,id desc')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 列出相关联的所有部门
     * @auth true
     * @param $deptId
     * @return array|string
     */
    private function _deptAll($deptId)
    {
        if (!$deptId) return [];
        $dept = SysDept::mk()->where('level', 'like', '%' . $deptId . '%')->where(['is_deleted' => 0])->column('id');
        $dept_ids = count($dept) > 0 ? implode(',', $dept) . ',' . $deptId : $deptId . '';
        return $dept_ids;
    }

    /**
     * 用户选择器
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function selector()
    {
        $query = SystemUser::mQuery();
        $query->where(['is_deleted' => 0]);
        // 数据列表搜索过滤
        $query->equal('status,user_type,tenant_id,dept_id')->dateBetween('login_at,create_at');
        $query->like('username,nickname,realname,contact_phone#phone,contact_mail#mail');
        $lists = $query->field(['id,realname as name'])->order('sort desc,id desc')->page(false,false,false,20);
        $this->success('数据获取成功', $lists['list']);
    }

    /**
     * 销售员列表-仓库管理
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getPersonByNumType()
    {
        $query = SystemUser::mQuery();
        $query->where(['is_deleted' => 0]);
        // 数据列表搜索过滤
        $query->equal('status,user_type,tenant_id,dept_id')->dateBetween('login_at,create_at');
        $query->field('realname as text,id as value');
        $lists = $query->order('sort desc,id desc')->page();
        $this->success('数据获取成功', $lists['list']);
    }

    /**
     * 读取用户信息
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function read()
    {
        $res = SystemUser::mk()->where(['id' => intval($this->request->param('id'))])->findOrEmpty();
        if (!$res->isEmpty()) {
            $role_ids = SysUserRole::mk()->where(['user_id' => $this->request->param('id')])->column('role_id');
            $res['roleList'] = SysRole::mk()->whereIn('id', $role_ids)->select()->toArray();
            $post_ids = SysUserPost::mk()->where(['user_id' => $this->request->param('id')])->column('post_id');
            $res['postList'] = SysPost::mk()->whereIn('id', $post_ids)->select()->toArray();
        } else {
            $this->error('不存在用户信息');
        }
        $this->success('数据获取成功', $res);
    }

    /**
     * 修改用户状态
     * @auth true
     * @return void
     */
    public function state()
    {
        $this->_checkInput();
        SystemUser::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 检查输入变量
     * @return void
     */
    private function _checkInput()
    {
        if (in_array('10000', str2arr(input('id', '')))) {
            $this->error('系统超级账号禁止删除！');
        }
    }

    /**
     * 移到回收站
     * @return void
     */
    public function remove()
    {
        SystemUser::mSave(['is_deleted' => 1]);
    }

    /**
     * 更新个人信息
     * @return void
     */
    public function upDateInfo()
    {
        SystemUser::mForm('form');
    }

    /**
     * 修改密码
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function modifyPassword()
    {

        $data = $this->_vali([
            'id.require' => '用户ID不能为空！',
            'oldPassword.require' => '登录密码不能为空！',
            'newPassword.require' => '重复密码不能为空！',
            'newPassword_confirmation.confirm:newPassword' => '两次输入的密码不一致！',
        ]);
        $user = SystemUser::mk()->find($data['id']);
        if (!empty($user) && $user->save(['password' => md5($data['newPassword'])])) {
            sysoplog($this->user['username'], '系统用户管理', "修改用户{$data['id']}密码成功");
            $this->success('密码修改成功，请使用新密码登录！', '');
        } else {
            $this->error('密码修改失败，请稍候再试！');
        }
    }

    /**
     * 保存用户信息
     * @return void
     */
    public function save()
    {
        SystemUser::mForm('form');
    }

    /**
     * 重置密码
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function resetPwd()
    {
        $password = CodeExtend::random(10, 3);
        $data = $this->_vali([
            'id.require' => '用户ID不能为空！',
            'password.value' => md5($password),
        ]);
        $user = SystemUser::mk()->find($data['id']);
        if (!empty($user)) {
            SystemUser::mk()->where(['id' => $data['id']])->update($data);
            sysoplog($this->user['username'], '系统用户管理', "重置用户[{$user['id']}]密码成功");
            $this->success('密码重置成功!', $password);
        } else {
            $this->error('密码重置失败，请稍候再试！');
        }
    }

    /**
     * 更新用户信息
     * @return void
     */
    public function update()
    {
        SystemUser::mForm('form');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @return void
     * @throws DbException
     */
    protected function _form_filter(array &$data)
    {
        if (!empty($data['id']) && $data['id']) {
            SysUserPost::mk()->where(['user_id' => $data['id']])->delete();
            SysUserRole::mk()->where(['user_id' => $data['id']])->delete();
            if (!empty($data['contact_phone'])) {
                $u = SystemUser::mk()->where(['contact_phone' => $data['contact_phone']])->where('id', '<>', $data['id'])->where(['is_deleted' => 0])->findOrEmpty();
                if (!$u->isEmpty()) {
                    $this->error('手机号已存在，请更换手机号');
                }
            }
            if (!empty($data['username'])) {
                $u = SystemUser::mk()->where(['username' => $data['username']])->where('id', '<>', $data['id'])->where(['is_deleted' => 0])->findOrEmpty();
                if (!$u->isEmpty()) {
                    $this->error('用户名已存在，请更换用户名');
                }
            }
            if (!empty($data['contact_mail'])) {
                $u = SystemUser::mk()->where(['contact_mail' => $data['contact_mail']])->where('id', '<>', $data['id'])->where(['is_deleted' => 0])->findOrEmpty();
                if (!$u->isEmpty()) {
                    $this->error('邮箱已存在，请更换邮箱');
                }
            }
        } else {
            if (!$this->_checkAccountNum()) {
                $this->error('您团队的版本管理账号已达到最大数量，无法再新增，如需更多账号支持，请联系双擎码客服升级版本。');
            }
            $data['user_type'] = '201';
            if (!empty($data['contact_phone'])) {
                $u = SystemUser::mk()->where(['contact_phone' => $data['contact_phone']])->where(['is_deleted' => 0])->findOrEmpty();
                if (!$u->isEmpty()) {
                    $this->error('手机号已存在，请更换手机号');
                }
            }
            if (!empty($data['username'])) {
                if (in_array($data['username'], ['admin', 'superadmin', 'super', 'administrator', 'manager', 'sqm', 'dualengine', 'twinengine', '1234', 'yjw'], false)) {
                    $this->error('该用户名已被系统保留，不可使用。');
                }
                $u = SystemUser::mk()->where(['username' => $data['username']])->where(['is_deleted' => 0])->findOrEmpty();
                if (!$u->isEmpty()) {
                    $this->error('用户名已存在，请更换用户名');
                }
            }
            if (!empty($data['contact_mail'])) {
                $u = SystemUser::mk()->where(['contact_mail' => $data['contact_mail']])->where(['is_deleted' => 0])->findOrEmpty();
                if (!$u->isEmpty()) {
                    $this->error('邮箱已存在，请更换邮箱');
                }
            }
        }

        //$data['user_type'] = '201';//后台添加的用户类型为201，首个注册租户的用户为管理员类型为200
    }

    /**
     * 检查套餐最大支持账号数量
     * @return bool|void
     * @throws DbException
     */
    private function _checkAccountNum()
    {
        $tenant = SysTenant::mk()->where(['id' => $this->tenant_id])->where(['is_deleted' => 0, 'status' => 0])->findOrEmpty();
        $package = SysPackage::mk()->where(['id' => $tenant['package_id']])->findOrEmpty();
        $usernum = SystemUser::mk()->where(['tenant_id' => $this->tenant_id])->where(['is_deleted' => 0])->count();
        if (!$package->isEmpty()) {
            if ($package['accounts_num'] <= $usernum) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 表单结果处理
     * @param bool $result
     * @param array $data
     * @return void
     */
    protected function _form_result(bool $result, array $data)
    {
        if (isset($data['postIdList'])) {
            $post_ids = explode(',', $data['postIdList']);
            $post_user = [];
            foreach ($post_ids as $v) {
                $pu['post_id'] = $v;
                $pu['user_id'] = $data['id'];
                array_push($post_user, $pu);
            }
            SysUserPost::mk()->insertAll($post_user);
        }
        if (isset($data['roleIdList'])) {
            $role_ids = explode(',', $data['roleIdList']);
            $role_user = [];
            foreach ($role_ids as $v) {
                $ru['role_id'] = $v;
                $ru['user_id'] = $data['id'];
                array_push($role_user, $ru);
            }
            SysUserRole::mk()->insertAll($role_user);
        }
        unset($data['postIdList']);
        unset($data['roleIdList']);
    }
}