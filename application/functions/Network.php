<?php

/**
 *处理网络处理文件
 *
 */

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false)
{
    $type      = $type ? 1 : 0;
    static $ip = null;
    if (null !== $ip) {
        return $ip[$type];
    }

    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }

            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}



/**
 * 获取客户端系统
 * @param unknow
 * @return string
 */

function get_client_os()
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return 'Unknown';
    }

    $os = '';
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);

    if (strpos($agent, 'win') !== false) {
        if (strpos($agent, 'nt 5.1') !== false) {
            $os = 'Windows XP';
        } elseif (strpos($agent, 'nt 5.2') !== false) {
            $os = 'Windows 2003';
        } elseif (strpos($agent, 'nt 5.0') !== false) {
            $os = 'Windows 2000';
        } elseif (strpos($agent, 'nt 6.0') !== false) {
            $os = 'Windows Vista';
        } elseif (strpos($agent, 'nt') !== false) {
            $os = 'Windows NT';
        }
    } elseif (strpos($agent, 'linux') !== false) {
        $os = 'Linux';
    } elseif (strpos($agent, 'mac') !== false && strpos($agent, 'pc') !== false) {
        $os = 'Macintosh';
    } else {
        $os = 'Unknown';
    }

    return $os;
}




/**
 *  获取浏览器类型
 *  @param unknow
 *  @return string
 */
function get_user_serAgent() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return '';
    }

    $browser = $browser_ver = '';
    $agent = $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'Internet Explorer';
        $browser_ver = $regs[1];
    } elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'FireFox';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
        $browser = 'Opera';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Netscape';
        $browser_ver = $regs[2];
    } elseif (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Safari';
        $browser_ver = $regs[1];
    } elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') NetCaptor';
        $browser_ver = $regs[1];
    }

    if (!empty($browser)) {
        return addslashes($browser . ' ' . $browser_ver);
    } else {
        return 'Unknow browser';
    }
}

/**
 * 发送CURL请求
 *  @param boolean $params 数组的形式
 *  @param integer $timeout 超时时间
 *  @return mixed
 */


function httpRequest($url, $params, $timeout = 0) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);


    if (is_array($params) && sizeof($params) > 0) {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch), 0);
    }

    curl_close($ch);
    return $response;
}




/**
 *  邮件发送函数
 *  @param  string  $toMail     接收者邮箱
 *  @param  string  $subject    邮件标题
 *  @param  string  $body       邮件内容
 *  @return string  $message    发送成功或失败消息
 */
function sendMail($toMail, $subject, $body) {
    Yaf_loader::import(LIB_PATH . '/PHPMailer/class.phpmailer.php');
    Yaf_Loader::import(LIB_PATH . '/PHPMailer/class.smtp.php');
    $config = Yaf_Application::app()->getConfig();

    $mail = new PHPMailer();
    if(1 == $config['mail_type']){
        $mail->IsSMTP();                               // 经smtp发送
        $mail->SMTPAuth = true;                        // 打开SMTP 认证
        $mail->Host     = $config['mail_server'];      // SMTP 服务器
        $mail->Port     = $config['mail_port'];        // SMTP 端口
        $mail->Username = $config['mail_user'];        // 用户名
        $mail->Password = $config['mail_password'];    // 密码
        $mail->From     = $config['mail_from'];        // 发信人
        $mail->FromName = $config['mail_name'];        // 发信人别名
    }else{
        $mail->IsSendmail();                           // 系统自带的 SENDMAIL 发送
        $mail->From     = $config['mail_sender'];      // 发信人
        $mail->FromName = $config['mail_name'];        // 发信人别名
        $mail->AddAddress($toMail);					   //设置发件人的姓名
    }

    $mail->AddAddress($toMail);                             // 收信人
    $mail->WordWrap = 50;
    $mail->CharSet = "utf-8";
    $mail->IsHTML(true);                                    // 以html方式发送
    $mail->Subject = $subject;                              // 邮件标题
    $mail->Body = $body;                                    // 邮件内空
    $mail->AltBody = "请使用HTML方式查看邮件。";

    // 发送邮件。
    if(!$mail->Send()) {
        $mailerror=$mail->ErrorInfo;
        return array("error"=>1,"message"=>$mailerror);
    }else{
        return array("error"=>0,"message"=>"success");
    }

}