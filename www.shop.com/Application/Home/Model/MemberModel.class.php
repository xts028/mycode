<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-17 
 * @license kunx-edu@qq.com.
 */

namespace Home\Model;

/**
 * Description of MemberModel
 *
 * @author kunx <kunx-edu@qq.com>
 */
class MemberModel extends \Think\Model {

    protected $patchValidate = true;

    /**
     * TODO::用户名长度和密码长度校验
     */
    protected $_validate     = [
        ['username', 'require', '用户名不能为空'],
        ['username', '', '用户名已存在', self::EXISTS_VALIDATE, 'unique', 'reg'],
        ['password', 'require', '密码不能为空'],
        ['repassword', 'require', '重复密码不能为空'],
        ['repassword', 'password', '两次密码不一致', self::EXISTS_VALIDATE, 'confirm'],
        ['email', 'require', '邮箱不能为空'],
        ['email', 'email', '邮箱不合法'],
        ['email', '', '邮箱已存在', self::EXISTS_VALIDATE, 'unique'],
        ['tel', 'require', '手机号码不能为空'],
        ['tel', '/^1[34578]\d{9}$/', '手机号码不合法', self::EXISTS_VALIDATE, 'regex'],
        ['tel', '', '手机号码已存在', self::EXISTS_VALIDATE, 'unique'],
//        ['checkcode','require','验证码不能为空'],
//        ['checkcode','checkCheckcode','验证码不正确',self::EXISTS_VALIDATE,'callback'],
//        ['captcha', 'checkTelcode', '手机验证码不合法', self::MUST_VALIDATE, 'callback', 'reg'],
    ];
    protected $_auto = [
        ['add_time', NOW_TIME, 'reg'],
        ['salt', '\Org\Util\String::randString', 'reg', 'function']
    ];

    /**
     * 检查收集验证码是否匹配
     * @param type $code
     * @return type
     */
    protected function checkTelcode($code) {
        //获取session
        $sess_code = session('TEL_CODE');
        if (empty($sess_code)) {
            return false;
        }
        session('TEL_CODE', null);
        return $code == $sess_code['code'] && I('post.tel') == $sess_code['tel'];
    }

    protected function checkCheckcode($code) {
        $verify = new \Think\Verify();
        return $verify->check($code);
    }

    /**
     * 用户注册,加盐加密.
     * @return type
     */
    public function addMember() {
        $this->data['password'] = salt_mcrypt($this->data['password'], $this->data['salt']);

        //发送邮件
        //邮件中带有一个激活链接,点击就验证参数是否正确(通过一个随机字符串)
        $address = $this->data['email'];
        $subject = '欢迎注册啊咿呀哟';
        $token   = \Org\Util\String::randString(32);
        $url     = U('Member/active', ['token' => $token, 'email' => $address], '', true);
        $content = '<h2>欢迎注册</h2><p>感谢您注册啊咿呀哟,账号需要激活才能使用,请点击<a href="' . $url . '">激活链接</a></p><p>如果无法点击,请复制下面的地址在浏览器中粘贴打开' . $url . '</p>';
        if(!$rst = send_mail($address, $subject, $content)){
            $this->error = '发送邮件失败，请重新激活';
            return false;
        }
        $this->data['active_token'] = $token;
        return $this->add();
    }
    
    /**
     * 检查密码和用户名是否匹配
     */
    public function login() {
        $username  = $this->data['username'];
        $password  = $this->data['password'];
        $user_info = $this->getByUsername($username);
        if (empty($user_info)) {
            $this->error = '账号或密码错误';
            return false;
        }
        $password = salt_mcrypt($password, $user_info['salt']);
        if ($password != $this->data['password']) {
            $this->error = '账号或密码错误';
            return false;
        }
        
        //存储用户信息到session中
        session('USER_INFO',$user_info);
        //todo::自动登陆
        //todo::登录成功记录最后登录时间和ip
        //将购物车数据保存到数据库中
        D('Cart')->cookie2db();
        return true;
    }

}
