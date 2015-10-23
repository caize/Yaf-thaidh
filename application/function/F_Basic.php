<?php
/**
 * file: F_Basic.php
 */
// Anti_SQL Injection, escape quotes
function filter($content) {
    if (!get_magic_quotes_gpc()) {
        return addslashes($content);
    } else {
        return $content;
    }
}

//对字符串等进行过滤
function filterStr($arr) {  
    if (!isset($arr)) {
        return null;
    }

    if (is_array($arr)){
        foreach ($arr as $k => $v) {
            $arr[$k] = filter(stripSQLChars(stripHTML(trim($v), true)));
        }
    } else {
        $arr = filter(stripSQLChars(stripHTML(trim($arr), true)));
    }

    return $arr;
}

function stripHTML($content, $xss = true) {
    $search = array("@<script(.*?)</script>@is",
        "@<iframe(.*?)</iframe>@is",
        "@<style(.*?)</style>@is",
        "@<(.*?)>@is"
    );

    $content = preg_replace($search, '', $content);

    if($xss){
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 
        'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 
        'layer', 'bgsound', 'title', 'base');
                                
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',      'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);
        
        $content = str_ireplace($ra, '', $content);
    }

    return strip_tags($content);
}

function removeXSS($val) {
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <javaΘscript>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);

    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

        // &#x0040 @ search for the hex values
        $val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
        // @ @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 
                            'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 
                            'layer', 'bgsound', 'title', 'base');
                            
    $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',      'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                    $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }

    return $val;
}

/**
 *  Strip specail SQL chars
 */
function stripSQLChars($str) {
    $replace = array('SELECT', 'INSERT', 'DELETE', 'UPDATE', 'CREATE', 'DROP', 'VERSION', 'DATABASES',
        'TRUNCATE', 'HEX', 'UNHEX', 'CAST', 'DECLARE', 'EXEC', 'SHOW', 'CONCAT', 'TABLES', 'CHAR', 'FILE',
        'SCHEMA', 'DESCRIBE', 'UNION', 'JOIN', 'ALTER', 'RENAME', 'LOAD', 'FROM', 'SOURCE', 'INTO', 'LIKE', 'PING', 'PASSWD');
    
    return str_ireplace($replace, '', $str);
}

// Redirect directly
function redirect($URL = '', $second = 0) {
    if (!isset($URL)) {
        $URL = $_SERVER['HTTP_REFERER'];
    }
        ob_start();
        ob_end_clean();
        header("Location: ".$URL, TRUE, 302); //header("refresh:$second; url=$URL", TRUE, 302);
        ob_flush(); //可省略
        exit;
}


// Get current microtime
function calculateTime() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float) $usec + (float) $sec);
}


function pr($arr) {
	echo '<pre>';
    print_r($arr);
	echo '</pre>';
}


function pp() {
	pr($_POST);
}


/**
 *  JavaScript alert
 */
function jsAlert($msg) {
    echo "<script type='text/javascript'>alert(\"$msg\")</script>";
}


/**
 *  JavaScript redirect
 */
function jsRedirect($url, $die = true) {
    echo "<script type='text/javascript'>window.location.href=\"$url\"</script>";
    if($die){
    	die;
    }
}


// Highlight keyword
function highlight($str, $find, $color){
	return str_replace($find, '<font color="'.$color.'">'.$find.'</font>', $str);
}

/*
 * 深度转义预定义字符函数
 * @param mix $mix 要进行转义的字符串或者数组
 * @return mix
 */
function deep_htmlspecialchars($mix, $quotestyle = ENT_QUOTES) {
	if (get_magic_quotes_gpc()) {
		$mix = deep_stripslashes($mix);
	}
	if (gettype($mix) == 'array') {
		foreach ($mix as $key=>$value) {
			if (gettype($value) == 'array') {
				$mix[$key] = deep_htmlspecialchars($value, $quotestyle);
			} else {
				$value = htmlspecialchars($value, $quotestyle);
				$value = str_replace(' ', '&nbsp;', $value);
				$value = preg_replace('#\n#', '\\n', $value);
				$value = preg_replace('#\r#', '\\r', $value);
				$mix[$key] = $value;
			}
		}
		return $mix;
	} else {
		$mix = htmlspecialchars($mix, $quotestyle);
		$mix = str_replace(' ', '&nbsp;', $mix);
		return $mix;
	}
}

/*
 * 深度反转义预定义字符函数
 * @param mix $mix 要进行转义的字符串或者数组
 * @return mix
 */
function deep_htmlspecialchars_decode($mix, $quotestyle = ENT_QUOTES) {
	if (gettype($mix) == 'array') {
		foreach ($mix as $key=>$value) {
			if (gettype($value) == 'array') {
				$mix[$key] = deep_htmlspecialchars_decode($value, $quotestyle);
			} else {
				$value = str_replace('&nbsp;', ' ', $value);
				$value = str_replace('\r', "\r", $value);
				$value = str_replace('\n', "\n", $value);
				$value = htmlspecialchars_decode($value, $quotestyle);
				$mix[$key] = $value;
			}
		}
		return $mix;
	} else {
		$mix = str_replace('&nbsp;', ' ', $mix);
		$mix = htmlspecialchars_decode($mix, $quotestyle);
		return $mix;
	}
}

/*
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $suffix=true, $charset="utf-8") {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
	switch (strtolower($charset)) {
		case 'utf-8' :
			if (strlen($str) > $length*3) {
				return $suffix ? $slice.'...' : $slice;
			} else {
				return $slice;
			}
			break;
		default :
			if (strlen($str) > $length) {
				return $suffix ? $slice.'...' : $slice;
			} else {
				return $slice;
			}
	}
}

/*
 * 提取HTML中的图片
 * @param string $HTML
 * @return array
 */
function get_image_src($HTML) {
  preg_match_all('#(?<=")[^"]*?(?:[^"/]+\.(?:jpg|png|gif))(?=")#s', $HTML, $match);
  return $match[0];
}

/*
 * 深度转义函数
 * @param mix $mix 要进行转义的字符串或者数组
 * @return mix
 */
function deep_addslashes($mix) {
	if (get_magic_quotes_gpc()) {
		return $mix;
	} else {
		if (gettype($mix)=="array") {
			foreach($mix as $key=>$value) {
				if (gettype($value)=="array") {
					$mix[$key] = deep_addslashes($value);
				} else {
					$mix[$key]=addslashes($value);
				}
			}
			return $mix;
		} else {
			return addslashes($mix);
		}
	}
}

/*
 * 深度反转义函数
 * @param mix $mix 要进行转义的字符串或者数组
 * @return mix
 */
function deep_stripslashes($mix) {
	if (gettype($mix)=="array") {
		foreach($mix as $key=>$value) {
			if (gettype($value)=="array") {
				$mix[$key] = deep_stripslashes($value);
			} else {
				$mix[$key]=stripslashes($value);
			}
		}
		return $mix;
	} else {
		return stripslashes($mix);
	}
}

/*
 * 删除文件夹
 * @param string $dir 路径
 * @return string
 */
function dir_delete($dir) {
  $dir = dir_path($dir);
  if (!is_dir($dir)) {
    return false;
  }
  $list = glob($dir . '*');
  foreach ($list as $file) {
    is_dir($file) ? dir_delete($file) : unlink($file);
  }
  return rmdir($dir);
}

/**
 * 格式化时间
 * @param  [type] $time [要格式化的时间戳]
 * @return [type]       [description]
 */
function time_format ($time) {
	//当前时间
	$now = time();
	//今天零时零分零秒
	$today = strtotime(date('y-m-d', $now));
	//传递时间与当前时秒相差的秒数
	$diff = $now - $time;
	$str = '';
	switch ($time) {
		case $diff < 60 :
			$str = $diff . '秒前';
			break;
		case $diff < 3600 :
			$str = floor($diff / 60) . '分钟前';
			break;
		case $diff < (3600 * 8) :
			$str = floor($diff / 3600) . '小时前';
			break;
		case $time > $today :
			$str = '今天&nbsp;&nbsp;' . date('H:i', $time);
			break;
		default : 
			$str = date('Y-m-d', $time);
	}
	return $str;
}

/**
* 使用正则验证数据
* @access public
* @param string $value  要验证的数据
* @param string $rule 验证规则
* @return boolean
*/
function regex($value,$rule) {
        $validate = array(
            'require'   =>  '/\S+/',
            'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency'  =>  '/^\d+(\.\d+)?$/',
            'number'    =>  '/^\d+$/',
            'zip'       =>  '/^\d{6}$/',
            'integer'   =>  '/^[-\+]?\d+$/',
            'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
            'english'   =>  '/^[A-Za-z]+$/',
            'isChinese' => '/[\x{4e00}-\x{9fa5}]+/u',
            'three' => '/^[a-zA-Z0-9_]{3,16}$/',//检测字母数字下划线3-16位
            'six' => '/^\S{6,18}$/',//检测字母数字下划线6-18位
	    'two'=>'/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]{2,16}$/u',
            'telephone'=>'/^((\(d{2,3}\))|(\d{3}\-))?1(3|5|8|9)\d{9}$/',
        );
        // 检查是否有内置的正则表达式
        if(isset($validate[strtolower($rule)]))
            $rule       =   $validate[strtolower($rule)];
        return preg_match($rule,$value)===1;
}

/*
*$page 当前$_GET获得的页码
*$total 总记录数
*$phpfile 页码连接文件名
*$pagesize 不用解释了吧 呵呵
*$pagelen 最多显示几个页码 注意（奇数），对称嘛！
*函数返回 生成的HTML 代码
*/
function buildPage($page,$total,$phpfile,$pagesize=10,$pagelen=7){
        $pagecode = '';//定义变量，存放分页生成的HTML
        $page = intval($page);//避免非数字页码
        $total = intval($total);//保证总记录数值类型正确
        //if(!$total) return array();//总记录数为零返回空数组
        if(!$total) return "";//总记录数为零返回空数组
        $pages = ceil($total/$pagesize);//计算总分页
        //处理页码合法性
        if($page<1) $page = 1;
        if($page>$pages) $page = $pages;
        //计算查询偏移量
        $offset = $pagesize*($page-1);
        //页码范围计算
        $init = 1;//起始页码数
        $max = $pages;//结束页码数
        $pagelen = ($pagelen%2)?$pagelen:$pagelen+1;//页码个数
        $pageoffset = ($pagelen-1)/2;//页码个数左右偏移量
        //生成html
       // $pagecode='<div class="page">';
        $pagecode='';
        //$pagecode.="<span>$page/$pages</span>";//第几页,共几页
        //如果是第一页，则不显示第一页和上一页的连接
        if($page!=1){
            //$pagecode.="<a href=\"{$phpfile}\">&lt;&lt;</a>";//第一页
            $pagecode.="<li><a href=\"".$phpfile.($page-1)."\" title=\"".($page-1)."\">&lt;</a></li>";//上一页
        }
        //分页数大于页码个数时可以偏移
        if($pages>$pagelen){
            //如果当前页小于等于左偏移
            if($page<=$pageoffset){
                $init=1;
                $max = $pagelen;
            }else{//如果当前页大于左偏移
                //如果当前页码右偏移超出最大分页数
                if($page+$pageoffset>=$pages+1){
                    $init = $pages-$pagelen+1;
                }else{
                    //左右偏移都存在时的计算
                    $init = $page-$pageoffset;
                    $max = $page+$pageoffset;
                }
            }
        }
        //生成html
        for($i=$init;$i<=$max;$i++){
            if($i==$page){
                $pagecode.='<li><span style="background:#337ab7;color:#fff;">'.$i.'</span></li>';
            } else {
                $pagecode.="<li><a href=\"".$phpfile.$i."\" title=\"$i\">$i</a></li>";
            }
        }
        if($page!=$pages){
            $pagecode.="<li><a href=\"".$phpfile.($page+1)."\" title=\"".($page+1)."\">&gt;</a></li>";//下一页
            //$pagecode.="<a href=\"{$phpfile}\">&gt;&gt;</a>";//最后一页
        }
        //$pagecode.='</div>';
        //return array('pagecode'=>$pagecode,'sqllimit'=>' limit '.$offset.','.$pagesize);
        return $pagecode;
}

/**
*提示用户操作结果，并跳转到相关页面
*/
function message($msgTitle,$message,$jumpUrl){ 
    $str = '<!DOCTYPE HTML>'; 
    $str .= '<html>'; 
    $str .= '<head>'; 
    $str .= '<meta charset="utf-8">'; 
    $str .= '<title>页面提示</title>'; 
    $str .= '<style type="text/css">'; 
    $str .= '*{margin:0; padding:0}a{color:#369; text-decoration:none;}a:hover{text-decoration:underline}body{height:100%; font:12px/18px Tahoma, Arial,  sans-serif; color:#424242; background:#fff}.message{width:450px; height:120px; margin:16% auto; border:1px solid #99b1c4; background:#ecf7fb}.message h3{height:28px; line-height:28px; background:#2c91c6; text-align:center; color:#fff; font-size:14px}.msg_txt{padding:10px; margin-top:8px}.msg_txt h4{line-height:26px; font-size:14px}.msg_txt h4.red{color:#f30}.msg_txt p{line-height:22px}'; 
    $str .= '</style>'; 
    $str .= '</head>'; 
    $str .= '<body>'; 
    $str .= '<div class="message">'; 
    $str .= '<h3>'.$msgTitle.'</h3>'; 
    $str .= '<div class="msg_txt">'; 
    $str .= '<h4 class="red">'.$message.'</h4>'; 
    $str .= '<p>系统将在 <span style="color:blue;font-weight:bold">3</span> 秒后自动跳转,如果不想等待,直接点击 <a href="{$jumpUrl}">这里</a> 跳转</p>'; 
    $str .= "<script>setTimeout('location.replace(\'".$jumpUrl."\')',2000)</script>"; 
    $str .= '</div>'; 
    $str .= '</div>'; 
    $str .= '</body>'; 
    $str .= '</html>'; 
    echo $str; 
} 

/**
*PHP获取当前页面URL
*/
function curPageURL() { 
    $pageURL = 'http'; 
    if (!empty($_SERVER['HTTPS'])) {$pageURL .= "s";} 
    $pageURL .= "://"; 
    if ($_SERVER["SERVER_PORT"] != "80") { 
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]; 
    } else { 
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; 
    } 
    return $pageURL; 
}

/**
 * 积分转换等级
 */
function point_to_level($point){
    $levelArr = array(
        array("level"=>1,"point"=>1),
        array("level"=>2,"point"=>15),
        array("level"=>3,"point"=>40),
        array("level"=>4,"point"=>80),
        array("level"=>5,"point"=>120),
        array("level"=>6,"point"=>180),
        array("level"=>7,"point"=>255),
        array("level"=>8,"point"=>320),
        array("level"=>9,"point"=>380),
        array("level"=>10,"point"=>450),
        array("level"=>11,"point"=>550),
        array("level"=>12,"point"=>660),
        array("level"=>13,"point"=>800),
        array("level"=>14,"point"=>1000),
        array("level"=>15,"point"=>1500),
        array("level"=>16,"point"=>2000),
        array("level"=>17,"point"=>3600),
        array("level"=>18,"point"=>4200),
        array("level"=>19,"point"=>5500),
        array("level"=>20,"point"=>6800),
        array("level"=>21,"point"=>7200),
        array("level"=>22,"point"=>8800),
        array("level"=>23,"point"=>10000),
        array("level"=>24,"point"=>12000),
        array("level"=>25,"point"=>15000)
    );
    $levelArr = array_reverse($levelArr);
    foreach($levelArr as $k=>$v){
        if($point >= $v["point"]){
            $level = $v["level"];
            if(($k)<=count($levelArr)){
                $next_level_point = $levelArr[$k-1]["point"];
            }
            break;
        }
    }
    return array("level"=>$level,"next_level_point"=>$next_level_point);
}

/**
 * desription 压缩图片
 * @param sting $imgsrc 图片路径
 * @param string $imgdst 压缩后保存路径
 */
function image_png_size_add($imgsrc,$imgdst){
    list($width,$height,$type)=getimagesize($imgsrc);
    $new_width = ($width>1200?1200:$width)*0.9;
    $new_height =($height>1000?1000:$height)*0.9;
    switch($type){
        case 1:
            $giftype=check_gifcartoon($imgsrc);
            if($giftype){
                header('Content-Type:image/gif');
                $image_wp=imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromgif($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($image_wp, $imgdst,75);
                imagedestroy($image_wp);
            }
            break;
        case 2:
            header('Content-Type:image/jpeg');
            $image_wp=imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromjpeg($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($image_wp, $imgdst,75);
            imagedestroy($image_wp);
            break;
        case 3:
            header('Content-Type:image/png');
            $image_wp=imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefrompng($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($image_wp, $imgdst,75);
            imagedestroy($image_wp);
            break;
    }
}
/**
 * desription 判断是否gif动画
 * @param sting $image_file图片路径
 * @return boolean t 是 f 否
 */
function check_gifcartoon($image_file){
    $fp = fopen($image_file,'rb');
    $image_head = fread($fp,1024);
    fclose($fp);
    return preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)?false:true;
}

