<?php

declare (strict_types=1);

namespace app\system\controller;

use Exception;
use Fastknife\Exception\ParamException;
use Fastknife\Service\BlockPuzzleCaptchaService;
use Fastknife\Service\ClickWordCaptchaService;
use think\admin\Controller;
use think\exception\HttpResponseException;
use think\Response;

/**
 * 验证码类
 * Class Captcha
 * @package app\controller
 */
class Captcha extends Controller
{
    /**
     * 获取验证码
     * @return void
     */
    public function getCode()
    {
        try {
            $service = $this->getCaptchaService();
            $data = $service->get();
        } catch (Exception $e) {
            $this->repError($e->getMessage());
        }
        $this->repSuccess($data);
    }

    /**
     * 获取行为验证码服务
     * @return BlockPuzzleCaptchaService|ClickWordCaptchaService
     */
    protected function getCaptchaService()
    {
        $captchaType = $this->request->post('captchaType', null);
        $config = $this->app->config->get('captcha');
        switch ($captchaType) {
            case "clickWord":
                $service = new ClickWordCaptchaService($config);
                break;
            case "blockPuzzle":
                $service = new BlockPuzzleCaptchaService($config);
                break;
            default:
                throw new ParamException('captchaType参数不正确！');
        }
        return $service;
    }

    /**
     * 返回失败数据
     * @param $msg
     * @return mixed
     */
    protected function repError($msg)
    {
        $response = [
            'error' => true,
            'repCode' => '6111',
            'repData' => null,
            'repMsg' => $msg,
            'success' => false,
        ];
        throw new HttpResponseException(Response::create($response, 'json'));
    }

    /**
     * 返回成功数据
     * @param $data
     * @return mixed
     */
    protected function repSuccess($data)
    {
        $response = [
            'error' => false,
            'repCode' => '0000',
            'repData' => $data,
            'repMsg' => null,
            'success' => true,
        ];
        throw new HttpResponseException(Response::create($response, 'json'));
    }

    /**
     * 一次验证
     * @return void
     */
    public function check()
    {
        $data = $this->_vali([
            'token.require' => 'token不能为空!',
            'pointJson.require' => 'Json数据不能为空!',
        ]);
        try {
            $service = $this->getCaptchaService();
            $service->check($data['token'], $data['pointJson']);
        } catch (Exception $e) {
            $this->repError($e->getMessage());
        }
        $this->repsuccess([]);
    }

    /**
     * 二次验证
     * @return void
     */
    public function verification()
    {
        $data = $this->_vali([
            'token.require' => 'token不能为空!',
            'pointJson.require' => 'Json数据不能为空!',
        ]);
        try {
            $service = $this->getCaptchaService();
            $service->verification($data['token'], $data['pointJson']);
        } catch (Exception $e) {
            $this->repError($e->getMessage());
        }
        $this->repSuccess([]);
    }


}
