<?php

declare (strict_types=1);

namespace app\system\controller;

use app\system\model\SystemUser;
use think\admin\model\SysEmail;
use think\admin\service\EmailService;


/**
 * 邮箱接口
 * Class Email
 * @package app\system\controller
 */
class Email extends Auth
{
    /**
     * 邮件分页列表
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $query = SysEmail::mQuery();
        $query->where(['is_deleted' => 0]);

        // 数据列表搜索过滤
        $query->equal('status')->dateBetween('created_at');
        $query->like('email');
        $lists = $query->order('id DESC')->page();
        $this->success('数据获取成功', $lists);
    }

    /**
     * 移到回收站
     * @auth true
     * @return void
     */
    public function remove()
    {
        SysEmail::mSave(['is_deleted' => 1]);
    }

    /**
     * 发送邮件
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function send()
    {
        $data = $this->_vali([
            'title.require' => '邮件主题不能为空!',
            'content.require' => '邮件内容不能为空!',
            'userIdList.require' => '发送对象不能为空!'
        ]);
        $userids = explode(',', $data['userIdList']);
        if (count($userids) > 100) {
            $num = 100;//每次导入条数
            $limit = ceil(count($userids) / $num);
            for ($i = 1; $i <= $limit; $i++) {
                $offset = ($i - 1) * $num;
                $udata = array_slice($userids, $offset, $num);
                $emails = SystemUser::mk()->whereIn('id', $udata)->where(['is_deleted' => 0, 'status' => 0])->column('contact_mail');
                [$state, $message, $data] = EmailService::instance()->sendNotice($emails, $data['title'], $data['content']);
                sleep(1);
            }
        } else {
            $emails = SystemUser::mk()->whereIn('id', $userids)->where(['is_deleted' => 0, 'status' => 0])->column('contact_mail');
            [$state, $message, $data] = EmailService::instance()->sendNotice($emails, $data['title'], $data['content']);
        }
        $state ? $this->success($message, $data) : $this->error($message, $data);
    }
}