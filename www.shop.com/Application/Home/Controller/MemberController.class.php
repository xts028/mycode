<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-17 
 * @license kunx-edu@qq.com.
 */

namespace Home\Controller;

/**
 * Description of MemberController
 *
 * @author kunx <kunx-edu@qq.com>
 */
class MemberController extends \Think\Controller {

    /**
     * @var \Home\Model\MemberModel 
     */
    private $_model;

    protected function _initialize() {
        $this->_model = D('Member');
    }

    /**
     * 用户注册
     */
    public function reg() {
        if (IS_POST) {
            if ($this->_model->create('', 'reg') === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->addMember() === false) {
                $this->error(get_error($this->_model));
            }
            $this->success('注册成功,请查收邮件激活账号', U('Index/index'));
        } else {
            $this->assign('title', '用户注册');
            $this->display();
        }
    }

    public function login() {
        if (IS_POST) {
            if ($this->_model->create() === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->login() === false) {
                $this->error(get_error($this->_model));
            }
            //登录并成功
            $url = cookie('referer');
            cookie('referer', null);
            if (empty($url)) {
                $url = U('Index/index');
            }
            $this->success('登录成功', $url);
        } else {
            $this->display();
        }
    }

    public function active($email, $token) {
        //修改数据库中对应的账户
        if ($this->_model->where(['email' => $email, 'active_token' => $token, 'status' => 0])->setField('status', 1)) {
            $this->success('激活成功', U('Index/index'));
        } else {
            $this->error('激活失败', U('Index/index'));
        }
    }

    /**
     * 验证是否已被注册.
     */
    public function checkByParam() {
        $cond = I('get.');
        if ($this->_model->where($cond)->count()) {
            $this->ajaxReturn(false);
        } else {
            $this->ajaxReturn(true);
        }
    }

    /**
     * 发送验证码,ajax调用
     * @param type $tel
     */
    public function sms($tel) {
        if (IS_AJAX) {
            vendor('Alidayu.TopSdk');
            date_default_timezone_set('Asia/Shanghai');
            $c            = new \TopClient;
            $c->appkey    = '23535693';
            $c->secretKey = '54836e841d9b9752e54f5985ab9f491a';
            $req          = new \AlibabaAliqinFcSmsNumSendRequest;
            $req->setExtend("");
            $req->setSmsType("normal");
            $req->setSmsFreeSignName("注册测试签名");
            $data         = [
                'product' => '啊咿呀哟',
                'code'    => \Org\Util\String::randNumber(1000, 9999),
            ];
            //将验证码存放到session中
            $code         = [
                'tel'  => $tel,
                'code' => $data['code'],
            ];
            session('TEL_CODE', $code);
            $data         = json_encode($data);
            $req->setSmsParam($data);
            $req->setRecNum($tel);
            $req->setSmsTemplateCode("SMS_11480818");
            $resp         = $c->execute($req);
            if (isset($resp->result->success)) {
                //发送成功了
                $this->ajaxReturn(true);
            }
        }
        //代表发送失败,可能是接口速度限制,缺钱,或者是非ajax调用
        $this->ajaxReturn(false);
    }

    /**
     * 退出
     */
    public function logout() {
        session(null);
        cookie(null);
        $this->success('退出成功', U('login'));
    }

}
