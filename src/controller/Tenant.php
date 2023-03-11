<?php

declare (strict_types=1);

namespace app\system\controller;

use app\system\model\SysPackage;
use app\system\model\SysRole;
use app\system\model\SysTenant;
use app\system\model\SysTenantApp;
use app\system\model\SysTenantMenu;
use app\system\model\SysTenantMoney;
use app\system\model\SysUploadFile;
use app\system\service\MessageService;
use app\system\service\SystemMenuService;
use think\admin\extend\CodeExtend;
use think\admin\service\EmailService;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 租户接口
 * Class Tenant
 * @package app\system\controller
 */
class Tenant extends Auth
{
    /**
     * 租户分页列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $query = SysTenant::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status,package_id')->dateBetween('created_at');
        $query->like('name,code');
        $lists = $query->order('sort desc,id ASC')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 租户列表
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function list()
    {
        $lists = SysTenant::mk()->where(['is_deleted' => 0])->order('sort desc,id ASC')->select()->toArray();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 获取租户选择树
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function tree()
    {
        $trees = SysTenant::mk()
            ->where(['status' => 0])
            ->field('id,parent_id,name as title,id as value')
            ->where(['is_deleted' => 0])
            // ->field('id,parent_id,name as label')
            ->order('sort desc')
            ->select()
            ->toArray();
        $lists = SystemMenuService::instance()->toTree($trees);
        $this->success('数据获取成功', $lists);
    }

    /**
     * 通过租户获取菜单
     * @auth true
     * @return array|void
     */
    public function getMenuByTenant()
    {
        if (empty($this->request->param('id'))) return [];
        $id = intval($this->request->param('id'));
        $res = SysTenantMenu::mk()->where(['tenant_id' => $id])->order('id ASC')->column('menu_id');
        //if($res){
        $this->success('数据获取成功', $res);
        //}else{
        //$this->error('暂无关联数据');
        //}

    }

    /**
     * 添加租户
     * @auth true
     * @return void
     */
    public function save()
    {
        SysTenant::mForm('form');
    }

    /**
     * 更新租户
     * @auth true
     * @return void
     */
    public function update()
    {
        SysTenant::mForm('form');
    }

    /**
     * 修改租户状态
     * @auth true
     * @return void
     */
    public function state()
    {
        SysTenant::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 移到回收站
     * @auth true
     * @return void
     */
    public function remove()
    {
        $id = $this->request->param('id');
        $ids = explode(',', $id);
        SysTenant::mSave(['is_deleted' => 1]);
    }

    /**
     * 详情
     * @auth true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function detail()
    {
        $result = SysTenant::mk()
            ->where(['id' => $this->tenant_id, 'is_deleted' => 0, 'status' => 0])
            ->findOrEmpty();
        if (!$result->isEmpty()) {
            $result['industry_id'] = $result['industry_id'] ? explode(',', $result['industry_id']) : [];
            $result['business_licenseArr'] = SysUploadFile::mk()->whereIn('id', $result['business_license'])->select()->toArray();
            $result['qualification_documentsArr'] = SysUploadFile::mk()->whereIn('id', $result['qualification_documents'])->select()->toArray();
        }
        $this->success('操作成功', $result);
    }

    /**
     * 通过租户获取应用
     * @auth true
     * @return void
     */
    public function getAppByTenant()
    {
        $data = $this->_vali([
            'tenant_id.require' => '租户ID不能为空！',
        ]);
        $res = SysTenantApp::mk()->where(['tenant_id' => $data['tenant_id']])->order('id ASC')->column('app_id');
        //if($res){
        $this->success('数据获取成功', $res);
        //}else{
        //$this->error('暂无关联数据');
        //}

    }

    /**
     * 汇款认证
     * @return void
     */
    public function toMoney()
    {
        SysTenantMoney::mForm('form');
    }

    /**
     * 租户审核
     * @return void
     */
    public function examine()
    {
        if ($this->request->param('progress') == 2) {
            $msg = '【双擎码】亲爱的用户，非常抱歉通知您，您的账号认证失败，具体原因请登录：https://cloud.sqm.la';
        }
        if ($this->request->param('progress') == 3) {
            $msg = '【双擎码】亲爱的用户，您的账号已通过基础认证，现在需要您输入对公账户上收到的小于1元的具体金额！登录地址：https://cloud.sqm.la';
        }
        if ($this->request->param('progress') == 4) {
            $msg = '【双擎码】亲爱的用户，您的账号已通过认证，现在开始您可以免费畅享双擎码溯源！登录地址：https://cloud.sqm.la';
        }
        MessageService::instance()->send($this->request->param('contact_number'), rand(1000, 9999), $msg);
        SysTenant::mForm('form');
    }

    /**
     * 获取打款金额
     * @return void
     */
    public function getMoney()
    {
        $data = $this->_vali([
            'tenant_id.require' => '租户ID不能为空！',
        ]);
        $money = SysTenantMoney::mk()->where(['tenant_id' => $data['tenant_id']])->findOrEmpty();
        $this->success('数据获取成功', $money);
    }

    /**
     * 列表数据处理
     * @param array $data
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function _page_filter(array &$data)
    {
        $packages = SysPackage::mk()->where(['is_deleted' => '0', 'status' => '0'])->select()->toArray();
        foreach ($data as &$vo) {
            $vo['industry_id'] = $vo['industry_id'] ? explode(',', $vo['industry_id']) : [];
            foreach ($packages as $package) if ($package['id'] === $vo['package_id']) $vo['package'] = $package;
        }
    }

    /**
     * 表单数据处理
     * @param array $data
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function _form_filter(array &$data)
    {

        if (empty($data['id'])) {
            $data['id'] = CodeExtend::uniqidNumber(12);
            $data['cid'] = $this->_getMaxCid();
            SysRole::mk()->insert(['tenant_id' => $data['id'], 'name' => '管理员', 'code' => 'Manager', 'data_scope' => '0', 'remark' => '本租户管理员', 'created_by' => $this->uuid]);
        }
        $pid = !empty($data['parent_id']) ? $data['parent_id'] : 0;
        if ($pid === 0) {
            $data['level'] = $data['parent_id'] = '0';
        } else if (is_array($pid)) {
            array_unshift($pid, '0');
            $data['level'] = implode(',', $pid);
            $data['parent_id'] = array_pop($pid);
        } else {
            $up = SysTenant::mk()->where(['id' => $data['parent_id']])->find();
            if (!empty($up)) {
                $data['level'] = $up['level'] . ',' . $data['parent_id'];
            }
        }
        if ($data['id'] && $data['id'] === $data['parent_id']) {
            $this->error('上级租户不能为本租户');
        }
        if (isset($data['menu_ids']) && !empty($data['menu_ids'])) {
            if (SysTenantMenu::mk()->where(['tenant_id' => $data['id']])->count() > 0) {
                SysTenantMenu::mk()->where(['tenant_id' => $data['id']])->delete();
            }
            $data['menu_ids'] = explode(',', $data['menu_ids']);
            $data_menus = [];
            foreach ($data['menu_ids'] as $v) {
                $data_menus[] = [
                    'tenant_id' => $data['id'],
                    'menu_id' => $v
                ];
            }
            unset($data['menu_ids']);
            SysTenantMenu::mk()->insertAll($data_menus);

        }
        if (isset($data['app_ids']) && !empty($data['app_ids'])) {
            if (SysTenantApp::mk()->where(['tenant_id' => $data['id']])->count() > 0) {
                SysTenantApp::mk()->where(['tenant_id' => $data['id']])->delete();
            }
            $data['app_ids'] = explode(',', $data['app_ids']);
            $apps = [];
            foreach ($data['app_ids'] as $v) {
                $apps[] = [
                    'tenant_id' => $data['id'],
                    'app_id' => $v
                ];
            }
            unset($data['app_ids']);
            SysTenantApp::mk()->insertAll($apps);

        }
        //$data['progress'] = 3;//从后台添加的租户直接为完善且认证状态
    }

    /**
     * 获取最大的租户CID
     * @return int|mixed
     */
    public function _getMaxCid()
    {
        $tenant = SysTenant::mk()->where(['is_deleted' => 0, 'status' => 0])->order('id desc')->findOrEmpty();
        p($tenant);
        $cid = $tenant['cid'] + 1;
        return $cid;
    }

    /**
     * 表单结果处理
     * @param bool $result
     * @param array $data
     * @return void
     */
    protected function _form_result(bool $result, array $data)
    {
        if ($result && $data['progress'] == 1) {//前台租户注册成功后发邮件通知审核
            $to = 'pay@sqm.la';
            $sub = '有一个新租户注册成功需要处理';
            $content = '新租户：' . $data['name'] . '注册成功需要马上审核，请知悉！';
            EmailService::instance()->sendEmail($to, $sub, $content);
            $msg = '【双擎码】亲爱的' . $data['name'] . '用户，您的企业信息已提交成功，我们将在2个工作日内进行审核，请关注审核短信或登录：https://cloud.sqm.la 查看。';
            MessageService::instance()->send($data['contact_number'], rand(1000, 9999), $msg);
        }
    }

}