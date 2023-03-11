<?php

declare(strict_types=1);

namespace app\system\controller;

use app\system\model\SystemUser;
use app\system\service\LoginService;
use app\system\service\MessageService;
use app\system\service\SystemUserAdminService;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\service\ActionCaptchaService;
use think\admin\service\AdminService;
use think\admin\service\CaptchaService;
use think\admin\service\EmailService;
use think\admin\extend\JwtExtend;


/**
 * 用户登录注册接口
 * Class Login
 * @package app\system\controller
 */
class Login extends Controller
{
    /**
     * 接口认证类型
     * @var string
     */
    private $type;

    /**
     * 获取数字验证码
     * @return void
     */
    public function captcha()
    {
        $image = CaptchaService::instance()->initialize();
        $captcha = ['image' => $image->getData(), 'uniqid' => $image->getUniqid()];
        $this->success('生成验证码成功', $captcha);
    }

    /**
     * 控制器初始化
     * @return void
     */
    protected function initialize()
    {
        // 接收接口类型
        $this->type = $this->request->request('api');
        $this->type = $this->type ?: $this->request->header('api-name');
        $this->type = $this->type ?: $this->request->header('api-type');
        $this->type = $this->type ?: SystemUserAdminService::API_TYPE_WEB;
        if (empty(SystemUserAdminService::TYPES[$this->type])) {
            $this->error("接口[{$this->type}]未定义规则！");
        }
    }

    /**
     * 获取验证码类型
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCaptchaType()
    {
        $this->success('获取验证码类型成功', syconfig('CAPTCHA', 'captchaType'));
    }

    /**
     * 用户登录接口
     * (前端传密码格式：md5(md5(password)+uniqid),uniqid为随机生成)
     * @return void
     * @throws Exception
     */
    public function in()
    {
        if ($this->request->param('customActiveKey') === 'tab1') {
            if ($this->request->param('actionCaptcha') === 'number') {
                $data = $this->_vali([
                    'username.require' => '登录账号不能为空!',
                    'username.min:4' => '登录账号不能少于5位字符!',
                    'password.require' => '登录密码不能为空!',
                    'password.min:4' => '登录密码不能少于6位字符!',
                    'verify.require' => '图形验证码不能为空!',
                    'uniqid.require' => '图形验证标识不能为空!'
                ]);
                if (!CaptchaService::instance()->check($data['verify'], $data['uniqid'])) {
                    sysoplog($data['username'], '系统用户登录失败', '验证码验证失败');
                    $this->error('图形验证码验证失败，请重新输入!');
                }
                /*! 用户信息验证 */
                if (preg_match("/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i", $data['username'])) {
                    $map = ['contact_mail' => $data['username'], 'is_deleted' => 0];
                } elseif (preg_match("/^1[3456789]\d{9}$/", $data['username'])) {
                    $map = ['contact_phone' => $data['username'], 'is_deleted' => 0];
                } else {
                    $map = ['username' => $data['username'], 'is_deleted' => 0];
                }
                $user = SystemUser::mk()->where($map)->findOrEmpty();
                if ($user->isEmpty()) {
                    sysoplog($data['username'], '系统用户登录失败', '登录账号错误');
                    $this->error('登录账号或密码错误，请重新输入!');
                }
                if (md5("{$user['password']}{$data['uniqid']}") != $data['password']) {
                    sysoplog($data['username'], '系统用户登录失败', '密码错误');
                    $this->error('密码错误，请重新输入!');
                }
            } elseif ($this->request->param('actionCaptcha') === 'action') {
                $data = $this->_vali([
                    'username.require' => '登录账号不能为空!',
                    'username.min:4' => '登录账号不能少于5位字符!',
                    'password.require' => '登录密码不能为空!',
                    'password.min:4' => '登录密码不能少于6位字符!',
                ]);
                /*! 用户信息验证 */
                if (preg_match("/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i", $data['username'])) {
                    $map = ['contact_mail' => $data['username'], 'is_deleted' => 0];
                } elseif (preg_match("/^1[3456789]\d{9}$/", $data['username'])) {
                    $map = ['contact_phone' => $data['username'], 'is_deleted' => 0];
                } else {
                    $map = ['username' => $data['username'], 'is_deleted' => 0];
                }

                $user = SystemUser::mk()->where($map)->findOrEmpty();
                if ($user->isEmpty()) {
                    sysoplog($data['username'], '系统用户登录失败', '登录账号错误');
                    $this->error('登录账号或密码错误，请重新输入!');
                }
                if (md5("{$user['password']}") != $data['password']) {
                    sysoplog($data['username'], '系统用户登录失败', '密码错误');
                    $this->error('密码错误，请重新输入!');
                }
            } else {
                $this->error('登录失败！');
            }
            if (intval($user['status']) == 0) {
                $user->inc('login_num')->update([
                    'login_at' => date('Y-m-d H:i:s'),
                    'login_ip' => $this->app->request->ip(),
                ]);
                sysoplog($user['username'], '系统用户登录成功', '登录系统后台成功');

                $this->success('登录成功！', SystemUserAdminService::set($map, [], $this->type, true));
            } else {
                sysoplog($data['username'], '系统用户登录失败', '账号已经被禁用');
                $this->error('账号已经被禁用，请联系管理员!');
            }
        } elseif ($this->request->param('customActiveKey') === 'tab2') {
            $data = $this->_vali([
                'phone.mobile' => '手机格式错误！',
                'phone.require' => '手机不能为空！',
                'captcha.require' => '验证码不能为空！',
            ]);
            if (!MessageService::instance()->checkVerifyCode($data['captcha'], $data['phone'])) {
                $this->error('手机短信验证失败！');
            }
            $map = ['contact_phone' => $data['phone'], 'is_deleted' => 0];
            $user = SystemUser::mk()->where($map)->findOrEmpty();
            if ($user->isEmpty()) {
                sysoplog($data['username'], '系统用户登录失败', '登录账号错误');
                $this->error('登录账号或密码错误，请重新输入!');
            }
            if (intval($user['status'])) {
                sysoplog($data['username'], '系统用户登录失败', '账号已经被禁用');
                $this->error('账号已经被禁用，请联系管理员!');
            }
            $user->inc('login_num')->update([
                'login_at' => date('Y-m-d H:i:s'),
                'login_ip' => $this->app->request->ip(),
            ]);
            // 刷新用户权限
            // AdminService::apiApply(true);
            sysoplog($user['username'], '系统用户登录成功', '登录系统后台成功');
            $this->success('登录成功！', SystemUserAdminService::set($map, [], $this->type, true));
        } else {
            $this->error('登陆失败');
        }
    }


    /**
     * 团队创始人统一注册入口
     * @return void
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register()
    {
        if ($this->request->param('actionCaptcha') === 'action') {
            $data = $this->_vali([
                'phone.mobile' => '手机格式错误！',
                'phone.require' => '手机不能为空！',
                'captcha.require' => '验证码不能为空！',
                'password.require' => '登录密码不能为空！',
            ]);
        } else {
            $data = $this->_vali([
                'phone.mobile' => '手机格式错误！',
                'phone.require' => '手机不能为空！',
                'captcha.require' => '验证码不能为空！',
                'verify.require' => '图形验证码不能为空!',
                'uniqid.require' => '图形验证标识不能为空!',
                'password.require' => '登录密码不能为空！',
            ]);
            if (!CaptchaService::instance()->check($data['verify'], $data['uniqid'])) {
                $this->error('图形验证码验证失败，请重新输入!');
            }
        }
        if (!MessageService::instance()->checkVerifyCode($data['captcha'], $data['phone'])) {
            $this->error('手机短信验证失败！');
        }
        $map = ['contact_phone' => $data['phone'], 'is_deleted' => 0];
        if (SystemUser::mk()->where($map)->count() > 0) {
            $this->error('手机号已注册，请使用其它手机号！');
        }
        $dat = [
            'contact_phone' => $data['phone'],
            'password' => $data['password']
        ];
        unset($map['is_deleted']);
        $result = LoginService::instance()->register($map, $dat, $this->type);
        //p($result);
        !empty($result) ? $this->success('团队创建成功！', $result) : $this->error('团队创建失败！');
    }

    /**
     * 团队创始人重置密码入口
     * @return void
     * @throws Exception
     */
    public function restpassword()
    {
        if ($this->request->param('actionCaptcha') === 'action') {
            $data = $this->_vali([
                'value.require' => '手机或邮箱不能为空！',
                'captcha.require' => '验证码不能为空！',
                'password.require' => '登录密码不能为空！',
            ]);
        } else {
            $data = $this->_vali([
                'value.require' => '手机或邮箱不能为空！',
                'verify.require' => '图形验证码不能为空!',
                'captcha.require' => '验证码不能为空！',
                'uniqid.require' => '图形验证标识不能为空!',
                'password.require' => '登录密码不能为空！',
            ]);
            if (!CaptchaService::instance()->check($data['verify'], $data['uniqid'])) {
                $this->error('图形验证码验证失败，请重新输入!');
            }
        }
        /*! 用户信息验证 */
        if (preg_match("/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i", $data['value'])) {
            $type = 'email';
            $map = ['contact_mail' => $data['value'], 'is_deleted' => 0];
        } elseif (preg_match("/^1[3456789]\d{9}$/", $data['value'])) {
            $type = "phone";
            $map = ['contact_phone' => $data['value'], 'is_deleted' => 0];
        } else {
            $this->error('手机或邮箱格式错误');
        }
        $user = SystemUser::mk()->where($map)->findOrEmpty();
        if ($user->isEmpty()) {
            $this->error('手机号或邮箱不存在!');
        }
        if ($type === 'phone') {
            if (!MessageService::instance()->checkVerifyCode($data['captcha'], $data['value'])) {
                $this->error('手机短信验证失败！');
            }
        }
        if ($type === 'email') {
            if (!EmailService::instance()->checkVerifyCode($data['captcha'], $data['value'])) {
                $this->error('邮箱验证失败！');
            }
        }
        $user = SystemUserAdminService::set($map, $data, $this->type, true);
        empty($user) ? $this->error('重置密码失败！') : $this->success('重置密码成功！', $user);
    }

    /**
     * 发送短信验证码
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sendsms()
    {
        if ($this->request->param('actionCaptcha') === 'number') {
            $data = $this->_vali([
                'phone.mobile' => '手机号格式错误！',
                'phone.require' => '手机号不能为空！',
                'verify.require' => '图形验证码不能为空!',
                'uniqid.require' => '图形验证标识不能为空!'
            ]);

            if (!CaptchaService::instance()->check($data['verify'], $data['uniqid'])) {
                sysoplog($data['phone'], '系统用户登录失败', '验证码验证失败');
                $this->error('图形验证码验证失败，请重新输入!');
            }
        } else {
            $data = $this->_vali([
                'phone.mobile' => '手机号格式错误！',
                'phone.require' => '手机号不能为空！',
            ]);
        }
        $map = ['contact_phone' => $data['phone'], 'is_deleted' => 0];
        $user = SystemUser::mk()->where($map)->findOrEmpty();
        if ($this->request->param('action') !== null) {
            if ($user->isEmpty() && in_array(trim($this->request->param('action')), ['login', 'recover'], true)) {
                $this->error('手机号不存在!');
            }
            if (!$user->isEmpty() && trim($this->request->param('action')) === 'register') {
                $this->error('手机号已被注册!');
            }
            if (!in_array(trim($this->request->param('action')), ['login', 'recover', 'register'], true)) {
                $this->error('未知action!');
            }
        } else {
            $this->error('未知错误!');
        }
        [$state, $message, $data] = MessageService::instance()->sendVerifyCode($data['phone']);
        $state ? $this->success($message, $data) : $this->error($message, $data);
    }

    /**
     * 发送邮件验证码
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sendemail()
    {
        if ($this->request->param('actionCaptcha') === 'number') {
            $data = $this->_vali([
                'email.email' => '邮箱格式错误！',
                'email.require' => '邮箱不能为空！',
                'verify.require' => '图形验证码不能为空!',
                'uniqid.require' => '图形验证标识不能为空!'
            ]);
        } else {
            $data = $this->_vali([
                'email.email' => '邮箱格式错误！',
                'email.require' => '邮箱不能为空！'
            ]);
        }

        $map = ['contact_mail' => $data['email'], 'is_deleted' => 0];
        $user = SystemUser::mk()->where($map)->findOrEmpty();
        if ($this->request->param('action') !== null) {
            if ($user->isEmpty() && in_array(trim($this->request->param('action')), ['login', 'recover'], true)) {
                $this->error('邮箱不存在!');
            }
            if (!$user->isEmpty() && trim($this->request->param('action')) === 'register') {
                $this->error('邮箱已被注册!');
            }
            if (!in_array(trim($this->request->param('action')), ['login', 'recover', 'register'], true)) {
                $this->error('未知action!');
            }
        } else {
            $this->error('未知错误!');
        }
        [$state, $message, $data] = EmailService::instance()->sendVerifyCode($data['email']);
        $state ? $this->success($message, $data) : $this->error($message, $data);
    }

    /**
     * 获取行为验证码信息
     * @return void
     */
    public function getCode()
    {
        $captchaType = $this->request->post('captchaType', null);
        ActionCaptchaService::instance()->getCode($captchaType);
    }

    /**
     * 检测行为验证码
     * @return void
     */
    public function checkActionCaptcha()
    {
        $dat = $this->_vali([
            'captchaType.require' => '验证码类型不能为空',
            'token.require' => 'token不能为空!',
            'pointJson.require' => 'Json数据不能为空!',
        ]);
        ActionCaptchaService::instance()->check($dat['captchaType'], $dat['token'], $dat['pointJson']);
    }

}