<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-10 
 * @license kunx-edu@qq.com.
 */

/**
 * 将模型错误信息变成一个有序列表字符串.
 *
 * @param \Think\Model $model 模型.
 *
 * @return string
 */
function get_error(\Think\Model $model) {
    $errors = $model->getError();
    if (!is_array($errors)) {
        $errors = [$errors];
    }
    $html = '<ol>';
    foreach ($errors as $error) {
        $html .= '<li>' . $error . '</li>';
    }
    $html.='</ol>';
    return $html;
}

/**
 * 将二维关联数组转换成下拉列表
 * @param array $data 二维数组.
 * @param string $value_field 值字段
 * @param string $name_field 文案提示字段
 * @param string $form_name 控件名字
 * @param string $select_value 默认选中的项
 * @return string 下拉列表的html代码.
 */
function arr2select(array $data, $value_field, $name_field, $form_name, $select_value) {
    $html = '<select name="' . $form_name . '" class="' . $form_name . '">';
    $html.='<option value="">--请选择--</option>';
    foreach ($data as $item) {
        if ($select_value == $item[$value_field]) {
            $html .= '<option value="' . $item[$value_field] . '" selected="selected">' . $item[$name_field] . '</option>';
        } else {
            $html .= '<option value="' . $item[$value_field] . '">' . $item[$name_field] . '</option>';
        }
    }

    $html .= '</select>';
    return $html;
}

/**
 * 加盐加密
 * @param string $password 原始密码.
 * @param string $salt     盐.
 * @return string 加盐加密后的结果.
 */
function salt_mcrypt($password, $salt) {
    return md5(md5($password) . $salt);
}

function send_mail($address, $subject, $content) {
    vendor('PhpMailer.PHPMailerAutoload');
    $mail             = new \PHPMailer;
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host       = 'smtp.126.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                               // Enable SMTP authentication
    $mail->Username   = 'kunx_edu@126.com';                 // SMTP username
    $mail->Password   = 'iam4ge';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port       = 465;                                    // TCP port to connect to

    $mail->setFrom('kunx_edu@126.com');
    $mail->addAddress($address);     // Add a recipient
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $subject;
    $mail->Body    = $content;
    $mail->CharSet = 'UTF-8';

    if (!$mail->send()) {
        //出错了
        $data = [
            'status' => false,
            'msg'    => $mail->ErrorInfo,
        ];
    } else {
        $data = [
            'status' => true,
            'msg'    => '发送成功',
        ];
    }
    return $data;
}

/**
 * 统一格式化金钱表示形式.
 * @param float $number
 * @return string
 */
function money_format($number) {
    return number_format($number, 2, '.', '');
}

/**
 * 由于常用的就是获取用户的登录id,所以已登录返回ID,否则返回null
 * @return integer|null
 */
function get_user_id() {
    $user_info = session('USER_INFO');
    return isset($user_info['id']) ? $user_info['id'] : null;
}
