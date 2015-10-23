<?php
/**
 * File: F_Network.php
 * Date: 2012-03-01
 */

/**
 * Get client IP Address
 */
function getClientIP(){
	if (getenv('HTTP_CLIENT_IP')) {
		$clientIP = getenv('HTTP_CLIENT_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
		$clientIP = getenv('HTTP_X_FORWARDED_FOR');
	} elseif (getenv('REMOTE_ADDR')) {
		$clientIP = getenv('REMOTE_ADDR');
	} else {
		$clientIP = $HTTP_SERVER_VARS['REMOTE_ADDR'];
	}

	return $clientIP;
}


/**
 * Is visitor a spider ?
 */
function isSpider(){

	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		return '';
	}

	$searchengine_bot = array(
		'googlebot',
		'mediapartners-google',
		'baiduspider+',
		'msnbot',
		'yodaobot',
		'yahoo! slurp;',
		'yahoo! slurp china;',
		'iaskspider',
		'sogou web spider',
		'sogou push spider'
	);

	$searchengine_name = array(
		'GOOGLE',
		'GOOGLE ADSENSE',
		'BAIDU',
		'MSN',
		'YODAO',
		'YAHOO',
		'Yahoo China',
		'IASK',
		'SOGOU',
		'SOGOU'
	);

	$spider = strtolower($_SERVER['HTTP_USER_AGENT']);

	foreach ($searchengine_bot AS $key => $value) {
		if (strpos($spider, $value) !== false) {
			$spider = $searchengine_name[$key];

			return $spider;
		}
	}

	return '';
}


/**
 *  Get user broswer type
 */
function getUserAgent() {
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
 *  Get user OS
 */
function getUserOS() {
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
 *  Submit HTTP request via CURL
 */
function httpRequest($url, $params, $timeout = 0) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

	/**
	 *  Post ?
	 */
	if (is_array($params) && sizeof($params) > 0) {
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	}

	$response = curl_exec($ch);

	if (curl_errno($ch)) {
		throw new Exception(curl_error($ch), 0);
	}

	curl_close($ch);
	return $response;
}


/**
 *  Submit HTTP request via CURL
 */
function executeHTTPRequest($url, $params, $timeout = 0) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

	/**
	 *  Post ?
	 */
	if (is_array($params) && sizeof($params) > 0) {
		$postBodyString = '';
		foreach ($params as $key => $value) {
			$postBodyString .= "$key=" . urlencode($value) . '&';
		}
		unset($key, $value);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
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
function sendMail($to,$title,$content) {
	Yaf_loader::import(LIB_PATH . '/PHPMailer/PHPMailerAutoload.php');
	$config = Yaf_Application::app()->getConfig();
	$mail = new PHPMailer(); //实例化
        $mail->IsSMTP(); // 启用SMTP
        $mail->Host=$config["mail_server"]; //smtp服务器的名称
        $mail->SMTPAuth = true; //启用smtp认证
        $mail->Username = $config['mail_user']; //你的邮箱名
        $mail->Password = $config['mail_password']; //邮箱密码
        $mail->From = $config['mail_from']; //发件人地址（也就是你的邮箱地址）
        $mail->FromName = $config['mail_name']; //发件人姓名
        $mail->AddAddress($to);
        $mail->WordWrap = 50; //设置每行字符长度
        $mail->IsHTML(true); // 是否HTML格式邮件
        $mail->CharSet="utf-8"; //设置邮件编码
        $mail->Subject =$title; //邮件主题
        $mail->Body = $content; //邮件内容
        $mail->AltBody = $content; //邮件正文不支持HTML的备用显示
        return($mail->Send());
}
function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;
    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}
