<?php

declare (strict_types=1);

namespace app\system\controller;

use app\system\model\SysUploadFile;
use Exception;
use think\admin\extend\CodeExtend;
use think\admin\service\GreenService;
use think\admin\Storage;
use think\admin\storage\LocalStorage;
use think\exception\HttpResponseException;
use think\file\UploadedFile;

/**
 * 上传接口
 * Class Upload
 * @package app\system\controller
 */
class Upload extends Auth
{

    /**
     * 头像上传
     * @return void
     */
    public function avatar()
    {
        try {
            $data = $this->_vali(['url.require' => '图片内容不为空！']);
            if (preg_match('|^data:image/(.*?);base64,|i', $data['url'])) {
                [$ext, $img] = explode('|||', preg_replace('|^data:image/(.*?);base64,|i', '$1|||', $data['url']));
                if (empty($ext) || !in_array(strtolower($ext), ['png', 'jpg', 'jpeg'])) {
                    $this->error('图片格式异常！');
                }
                $name = Storage::name($img, $ext, 'image/');
                $info = Storage::instance()->set($name, base64_decode($img));
                $task = ['dataId' => CodeExtend::uniqidNumber(14, 'P'), 'url' => $info['url']];
                //头像安全检测
                if (GreenService::instance()->imageCheck(0, $this->uuid, $this->tenant_id, $task)) {
                    $this->error('图片违规！郑重提醒：违规超过三次你的账号将被锁定，相关数据已保存上链。平台将保留追究法律责任的权利。如若误判，请联系客服解除锁定。');
                }
                $this->success('图片上传成功！', $info['url']);
            } else {
                $this->error('解析内容失败！');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 文件上传入口
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function file()
    {
        if (!($file = $this->getFile())->isValid()) {
            $this->error('文件上传异常，文件过大或未上传！');
        }
        $safeMode = $this->getSafe();
        $extension = strtolower($file->getOriginalExtension());
        $saveName = input('key') ?: Storage::name($file->getPathname(), $extension, '', 'md5_file');
        $size = $file->getSize();
        $oldname = $file->getOriginalName();
        // 检查文件名称是否合法
        if (strpos($saveName, '../') !== false) {
            $this->error('文件路径不能出现跳级操作！');
        }
        // 检查文件后缀是否被恶意修改
        if (strtolower(pathinfo(parse_url($saveName, PHP_URL_PATH), PATHINFO_EXTENSION)) !== $extension) {
            $this->error('文件后缀异常，请重新上传文件！');
        }
        // 屏蔽禁止上传指定后缀的文件
        if (!in_array($extension, str2arr(sysconf('storage.allow_exts|raw')))) {
            $this->error('文件类型受限，请在后台配置规则！');
        }
        if (in_array($extension, ['sh', 'asp', 'bat', 'cmd', 'exe', 'php'])) {
            $this->error('文件安全保护，禁止上传可执行文件！');
        }
        try {
            if ($this->getType() === 'local') {
                $local = LocalStorage::instance();
                $distName = $local->path($saveName, $safeMode);
                $file->move(dirname($distName), basename($distName));
                $info = $local->info($saveName, $safeMode, $file->getOriginalName());
                if (in_array($extension, ['jpg', 'gif', 'png', 'bmp', 'jpeg', 'wbmp'])) {
                    if ($this->imgNotSafe($distName) && $local->del($saveName)) {
                        $this->error('图片未通过安全检查！');
                    }
                    [$width, $height] = getimagesize($distName);
                    if (($width < 1 || $height < 1) && $local->del($saveName)) {
                        $this->error('读取图片的尺寸失败！');
                    }
                }
            } else {
                $bina = file_get_contents($file->getPathname());
                $info = Storage::instance($this->getType())->set($saveName, $bina, $safeMode, $file->getOriginalName());
            }

            if (isset($info['url'])) {
                $data = ['uid' => CodeExtend::random(10), 'name' => $oldname, 'url' => $info['url']];
                if (in_array($extension, ['zip', 'rar'])) {
                    $type = 60;
                } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                    $type = 50;
                } elseif (in_array($extension, ['doc', 'docx'])) {
                    $type = 40;
                } elseif (in_array($extension, ['mp4', 'avi'])) {
                    $type = 30;
                } elseif (in_array($extension, ['pdf'])) {
                    $type = 20;
                } else {
                    $type = 10;
                }
                //$type = $this->getExtension($extension);
                $file_data = [
                    'url' => $info['url'],
                    'storage' => $this->getType(),
                    'domain' => $this->getDomain(),
                    'type' => $type,
                    'group_id' => intval(input('group_id')),
                    'name' => $oldname,
                    'path' => $saveName,
                    'size' => $size,
                    'ext' => $extension,
                    'tenant_id' => $this->tenant_id,
                    'uploader_id' => $this->uuid
                ];
                $res = SysUploadFile::mk()->insertGetId($file_data);
                //三种类型的文件上传安全检测
                if ($type == 10) {
                    //图片安全检测
                    $task = ['dataId' => CodeExtend::uniqidNumber(14, 'P'), 'url' => $file_data['url']];
                    if (GreenService::instance()->imageCheck(intval($res), $this->uuid, $this->tenant_id, $task)) {
                        //违规图片修改状态
                        SysUploadFile::mk()->where(['id' => $res])->update(['is_deleted' => 1, 'status' => 1]);
                        $this->error('图片违规！郑重提醒：违规超过三次你的账号将被锁定，相关数据已保存上链。平台将保留追究法律责任的权利。如若误判，请联系客服解除锁定。');
                    }
                }
                /*if($type==20){
                    //文件安全检测
                    $task = ['dataId'=>CodeExtend::uniqidNumber(14,'P'),'url'=>$file_data['url']];
                    if(GreenService::instance()->fileCheck($res,$this->uuid,$this->tenant_id,$task)){
                        //违规图片修改状态
                        SysUploadFile::mk()->where(['id'=>$res])->update(['is_deleted'=>1,'status'=>1]);
                        $this->error('文件违规！郑重提醒：违规超过三次你的账号将被锁定，相关数据已保存上链。平台将保留追究法律责任的权利。如若误判，请联系客服解除锁定。');
                    }
                }
                if($type==30){
                    //视频安全检测
                    $task = ['dataId'=>CodeExtend::uniqidNumber(14,'P'),'url'=>$file_data['url']];
                    if(GreenService::instance()->videoCheck($res,$this->uuid,$this->tenant_id,$task)){
                        //违规图片修改状态
                        SysUploadFile::mk()->where(['id'=>$res])->update(['is_deleted'=>1,'status'=>1]);
                        $this->error('视频违规！郑重提醒：违规超过三次你的账号将被锁定，相关数据已保存上链。平台将保留追究法律责任的权利。如若误判，请联系客服解除锁定。');
                    }
                }*/

                $this->success('文件上传成功！', ['url' => $safeMode ? $saveName : $data]);//原$info['url']现改为data主要为文件上传不一致修改
            } else {
                $this->error('文件处理失败，请稍候再试！');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 获取本地文件对象
     * @return UploadedFile
     */
    private function getFile(): UploadedFile
    {
        try {
            $file = $this->request->file('file');
            if ($file instanceof UploadedFile) {
                return $file;
            } else {
                $this->error('未获取到上传的文件对象！');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error(lang($exception->getMessage()));
        }
    }

    /**
     * 获取文件上传类型
     * @return bool
     */
    private function getSafe(): bool
    {
        return boolval(input('safe', '0'));
    }

    /**
     * 获取文件上传方式
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getType(): string
    {
        $type = strtolower(input('uptype', ''));
        if (in_array($type, ['local', 'qiniu', 'alioss', 'txcos', 'uptype'])) {
            return $type;
        } else {
            return strtolower(sysconf('storage.type|raw'));
        }
    }

    /**
     * 检查图片是否安全
     * @param string $filename
     * @return bool
     */
    private function imgNotSafe(string $filename): bool
    {
        $source = fopen($filename, 'rb');
        if (($size = filesize($filename)) > 512) {
            $hexs = bin2hex(fread($source, 512));
            fseek($source, $size - 512);
            $hexs .= bin2hex(fread($source, 512));
        } else {
            $hexs = bin2hex(fread($source, $size));
        }
        if (is_resource($source)) fclose($source);
        $bins = hex2bin($hexs);
        /* 匹配十六进制中的 <% ( ) %> 或 <? ( ) ?> 或 <script | /script> */
        foreach (['<?php ', '<% ', '<script '] as $key) if (stripos($bins, $key) !== false) return true;
        return preg_match("/(3c25.*?28.*?29.*?253e)|(3c3f.*?28.*?29.*?3f3e)|(3C534352495054)|(2F5343524950543E)|(3C736372697074)|(2F7363726970743E)/is", $hexs);
    }

    /**
     * 获取上传域名
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getDomain(): string
    {
        $type = $this->getSafe();
        switch ($type) {
            case 'local':
                $domain = sysconf('storage.local_http_domain|raw');
                break;
            case 'qiniu':
                $domain = sysconf('storage.qiniu_http_domain|raw');
                break;
            case 'alioss':
                $domain = sysconf('storage.alioss_http_domain|raw');
                break;
            case 'txcos':
                $domain = sysconf('storage.txcos_http_domain|raw');
                break;
            case 'uptype':
                $domain = sysconf('storage.uptype_http_domain|raw');
                break;
            default:
                $domain = '';
                break;
        }
        return $domain;
    }

    /**
     * 获取文件类型
     * @param $extension
     * @return int
     */
    private function getExtension($extension): int
    {
        switch ($extension) {
            case 'png':
            case 'jpeg':
            case 'gif':
            case 'jpg':
                $type = 10;
                break;
            case 'pdf':
                $type = 20;
                break;
            case 'avi':
            case 'mp4':
                $type = 30;
                break;
            case 'docx':
            case 'doc':
                $type = 40;
                break;
            case 'xlsx':
            case 'xls':
                $type = 50;
                break;
            case 'rar':
            case 'zip':
                $type = 60;
                break;
            default:
                $type = '';
                break;
        }
        return $type;
    }

}
