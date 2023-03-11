<?php

declare (strict_types=1);

namespace app\system\controller;

use think\admin\storage\AliossStorage;
use think\admin\storage\QiniuStorage;
use think\admin\storage\TxcosStorage;


/**
 * 系统基础参数配置
 * Class Setting
 * @package app\system\controller
 */
class Setting extends Auth
{
    /**
     * 系统参数配置
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSysConfig()
    {
        $this->success('请求成功！', sysconf('system.|raw'));
    }

    /**
     * 存储引擎
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStorage()
    {
        $this->success('请求成功', syconfig('STORAGE', 'type'));
    }

    /**
     * 存储引擎参数获取
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStorageConfig()
    {
        $this->success('请求成功！', sysconf('storage.|raw'));
    }

    /**
     * 存储引擎参数配置
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setStorageConfig()
    {
        $post = $this->request->post();
        if (!empty($post['allow_exts'])) {
            $deny = ['sh', 'asp', 'bat', 'cmd', 'exe', 'php'];
            $exts = array_unique(str2arr(strtolower($post['allow_exts'])));
            if (count(array_intersect($deny, $exts)) > 0) $this->error('禁止上传可执行的文件！');
            $post['allow_exts'] = join(',', $exts);
        }
        foreach ($post as $name => $value) sysconf('storage.' . $name, $value);
        sysoplog($this->user['username'], '系统配置管理', "修改系统存储参数");
        $this->success('修改文件存储成功！');
    }

    /**
     * 云端区域列表
     * @return void
     */
    public function getRegion()
    {
        $res = [];
        $res['alioss'] = AliossStorage::region();
        $res['qiniu'] = QiniuStorage::region();
        $res['txcos'] = TxcosStorage::region();
        $this->success('请求成功', $res);
    }

    /**
     * 保存系统参数配置
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveSystemConfig()
    {
        $post = $this->request->post();
        // 数据数据到系统配置表
        foreach ($post as $k => $v) sysconf('system.' . $k, $v);
        sysoplog($this->user['username'], '系统配置管理', "修改系统参数成功");
        $this->success('修改系统参数成功！');
    }
}