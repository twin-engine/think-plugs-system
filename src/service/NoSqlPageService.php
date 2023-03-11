<?php

declare(strict_types=1);

namespace app\system\service;

use think\admin\Service;

/**
 * 非数据库数表生成分页服务
 * Class NoSqlPageService
 * @package app\system\service
 */
class NoSqlPageService extends Service
{
    /**
     * 非数据库数表生成分页数据
     * @param int $page
     * @param int $pageSize
     * @param array $arr
     * @return array
     */
    public function setPage(int $page, int $pageSize, array $arr = []): array
    {
        $start = ($page - 1) * $pageSize;
        $end = $pageSize;
        //if($end>count($arr)) $end = count($arr);
        $lists['list'] = array_slice($arr, $start, $end);
        $lists['page'] = [
            'current' => $page,
            'limit' => $pageSize,
            'pages' => count($arr) > 0 ? ceil(count($arr) / $pageSize) : 0,
            'total' => count($arr)
        ];
        return $lists;
    }

}