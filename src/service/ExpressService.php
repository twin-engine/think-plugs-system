<?php

namespace app\system\service;

use app\system\model\BasePostageRegion;
use app\system\model\BasePostageTemplate;
use think\admin\Exception;
use think\admin\extend\DataExtend;
use think\admin\Service;
use think\admin\service\InterfaceService;


/**
 * 快递查询数据服务
 * Class ExpressService
 * @package app\data\service
 */
class ExpressService extends Service
{
    /**
     * 模拟计算快递费用
     * @param array $codes
     * @param string $provName
     * @param string $cityName
     * @param int $truckCount
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function amount(array $codes, string $provName, string $cityName, int $truckCount = 0): array
    {
        if (empty($codes)) return [0, $truckCount, '', '邮费模板编码为空！'];
        $map = [['status', '=', 1], ['deleted', '=', 0], ['code', 'in', $codes]];
        $template = BasePostageTemplate::mk()->where($map)->order('sort desc,id desc')->find();
        if (empty($template)) return [0, $truckCount, '', '邮费模板编码无效！'];
        $rule = json_decode($template['normal'] ?: '[]', true) ?: [];
        foreach (json_decode($template['content'] ?: '[]', true) ?: [] as $item) {
            if (isset($item['city']) && is_array($item['city'])) foreach ($item['city'] as $city) {
                if ($city['name'] === $provName && in_array($cityName, $city['subs'])) {
                    $rule = $item['rule'];
                    break 2;
                }
            }
        }
        [$firstNumber, $firstAmount] = [$rule['firstNumber'] ?: 0, $rule['firstAmount'] ?: 0];
        [$repeatNumber, $repeatAmount] = [$rule['repeatNumber'] ?: 0, $rule['repeatAmount'] ?: 0];
        if ($truckCount <= $firstNumber) {
            return [$firstAmount, $truckCount, $template['code'], "首件计费，不超过{$firstNumber}件"];
        } else {
            $amount = $repeatNumber > 0 ? $repeatAmount * ceil(($truckCount - $firstNumber) / $repeatNumber) : 0;
            return [$firstAmount + $amount, $truckCount, $template['code'], "续件计费，超出{$firstNumber}件续件{$amount}元"];
        }
    }


    /**
     * 获取快递模板数据
     * @return array
     */
    public function templates(): array
    {
        $query = BasePostageTemplate::mk()->where(['status' => 1, 'deleted' => 0]);
        return $query->order('sort desc,id desc')->column('code,name,normal,content', 'code');
    }

    /**
     * 配送区域树型数据
     * @param int $level
     * @param int|null $status
     * @return array
     */
    public function region(int $level = 3, ?int $status = null): array
    {
        $query = BasePostageRegion::mk();
        if (is_numeric($level)) $query->where('level', '<=', $level);
        if (is_numeric($status)) $query->where(['status' => $status]);
        $items = DataExtend::arr2tree($query->column('id,pid,name,status', 'id'), 'id', 'pid', 'subs');
        // 排序子集为空的省份和城市
        foreach ($items as $ik => $item) {
            foreach ($item['subs'] as $ck => $city) {
                if (isset($city['subs']) && empty($city['subs'])) unset($items[$ik]['subs'][$ck]);
            }
            if (isset($item['subs']) && empty($item['subs'])) unset($items[$ik]);
        }
        return $items;
    }

    /**
     * 开放平台快递查询
     * @param string $code
     * @param string $number
     * @return array
     * @throws Exception
     */
    public function query(string $code, string $number): array
    {
        return $this->_getInterface()->doRequest('api.auth.express/query', [
            'type' => 'free', 'express' => $code, 'number' => $number,
        ]);
    }

    /**
     * 获取开放平台接口实例
     * @return InterfaceService
     */
    private function _getInterface(): InterfaceService
    {
        $service = InterfaceService::instance();
        // 测试的账号及密钥随时可能会变更，请联系客服更新
        $service->getway('https://open.cuci.cc/user/');
        $service->setAuth("6998081316132228", "193fc1d9a2aac78475bc8dbeb9a5feb1");
        return $service;
    }

    /**
     * 开放平台快递公司
     * @return array
     * @throws Exception
     */
    public function company(): array
    {
        return $this->_getInterface()->doRequest('api.auth.express/getCompany');
    }
}