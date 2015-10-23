<?php
class LoginController extends BasicController {
	private $m_user;
	private function init(){
		$this->m_user = $this->load('User');
        $this->getGlobal();
	}
	public function indexAction() {
        $buffer["curNav"] = 1;
        $this->getView()->assign($buffer);
       		 
  	}
  	public function handleLoginAction(){
		if(!$this->getRequest()->isPost()){
			$this->redirect("/login");	
		}
        $email = parent::getPost("email",false);
        if(!regex($email,"require")) die(json_encode(array("status"=>0,"msg"=>"邮箱不能为空")));
        if(!regex($email,"email")) die(json_encode(array("status"=>0,"msg"=>"邮箱格式不正确")));
        if(empty($this->m_user->checkEmail($email))) die(json_encode(array("status"=>0,"msg"=>"邮箱不存在，请先注册")));
		$password = $this->getPost('password',false);
        if(!regex($password,"require")) die(json_encode(array("status"=>0,"msg"=>"密码不能为空")));
        $checkcode = parent::getPost("checkcode",false);
        if(strtolower($checkcode) != strtolower(parent::getSession("verify_code"))) die(json_encode(array("statuc"=>0,"msg"=>"验证码不正确")));
		$field = array('id','password','username','email','point','status','refuse','login_time');
		$where = array('email' => $email);
		$userData  = $this->m_user->Field($field)->Where($where)->SelectOne();
        if($userData["password"] != md5($password)) die(json_encode(array("status"=>0,"msg"=>"密码不正确")));
		if(!$userData["refuse"]) die(json_encode(array("status"=>0,"msg"=>"账号被锁定，请联系管理员")));
        //登录+point 2
        $today = strtotime(date("Y-m-d"));
        if($userData["login_time"] < $today) $this->m_user->Where(array("id"=>$userData["id"]))->Limit(1)->Update(array("point"=>"point+2"),TRUE);
        //更新最后登录时间+ip
        $this->m_user->UpdateByID(array("login_time"=>time(),"login_ip"=>getClientIP()),$userData["id"]);
        $remember = $this->getPost('remember',false);
        //如果选择记住密码
        if(isset($remember) && $remember==1){
            $login_salt="SALTISDIFFCULT";
            $identifier = md5($login_salt.md5($userData["email"].$login_salt));
            $token = md5(uniqid(rand(),TRUE));
            $timeout = 60*60*24*7;//过期时间
            parent::setCookie("auth","$identifier:$token",$timeout);
            $this->m_user->UpdateByID(array("identifier"=>$identifier,"identifier_token"=>$token),$userData["id"]);
        }
        //登录成功写入SESSION
        parent::setSession('uid', $userData["id"]);
        parent::setSession('email', $userData["email"]);
        die(json_encode(array("status"=>1,"msg"=>"登录成功")));
	}
	public function registerAction(){
        $buffer["curNav"] = 9;
        $this->getView()->assign($buffer);
	}  	
	public function handleRegAction(){
		Yaf_Dispatcher::getInstance()->disableView();
		if(!$this->getRequest()->isXmlHttpRequest()){
			parent::notify("页面不存在");
		}	
		//过滤判断
		$email = parent::getPost("email",false);
		if(!regex($email,"require")) die(json_encode(array("status"=>0,"msg"=>"邮箱不能为空")));
		if(!regex($email,"email")) die(json_encode(array("status"=>0,"msg"=>"邮箱格式不正确")));
		if(!empty($this->m_user->checkEmail($email))) die(json_encode(array("status"=>0,"msg"=>"邮箱已经注册过，请直接登录")));
		$username = parent::getPost("username",false);
		if(!regex($username,"require")) die(json_encode(array("status"=>0,"msg"=>"昵称不能为空")));
		if(!regex($username,"two")) die(json_encode(array("status"=>0,"msg"=>"昵称至少2个字符")));	
		if(!empty($this->m_user->checkUsername($username))) die(json_encode(array("status"=>0,"msg"=>"昵称已经存在，请换一个")));
		$password = parent::getPost("password",false);
		if(!regex($password,"require")) die(json_encode(array("statuc"=>0,"msg"=>"密码不能为空")));
		if(!regex($password,"six")) die(json_encode(array("statuc"=>0,"msg"=>"密码必须6-18位")));
		$repassword = parent::getPost("repassword",false);
		if(!regex($repassword,"require")) die(json_encode(array("statuc"=>0,"msg"=>"确认密码不能为空")));
		if($password != $repassword) die(json_encode(array("statuc"=>0,"msg"=>"前后密码不一致")));	
		$checkcode = parent::getPost("checkcode",false);
		if(strtolower($checkcode) != strtolower(parent::getSession("verify_code"))) die(json_encode(array("statuc"=>0,"msg"=>"验证码不正确")));
		$time = time();
		$token = md5($username.$password.$time);
		$data = array();
		$data["email"] = $email;
		$data["avatar"] = "/img/face.jpg";
		$data["username"] = $username;
		$data["password"] = md5($password);
		$data["reg_time"] = $time;
		$data["login_time"] = time();
		$data["login_ip"] = getClientIP();
        $data["point"] = 5;//注册point +5
		$data["token"] = $token;//用户名+密码+注册时间md5生成
		$data["token_expire"] = time()+60*60*24*365;//token失效时间为365天
		$uid = $this->m_user->Insert($data);
		//如果插入成功
		if($uid){
            $verifyUrl = "http://thaidh.com/login/active/verify/";
			$link = "<a href='".$verifyUrl.$token."' target='_blank'>".$verifyUrl.$token."</a>";
			$body = "亲爱的".$username.":<br/>感谢您在泰语导航注册账号。<br/>请点击下面的链接激活您的账号。<br/>".$link."<br/>如果以上链接无法点击，请将它复制到浏览器地址栏，进行访问，该链接48小时内有效"; //邮件内容
			if(sendMail($email,"新用户注册激活邮件",$body)){
				//登录成功写入SESSION并且跳转到首页
				parent::setSession('uid', $uid);
				parent::setSession('email', $email);
				die(json_encode(array("status"=>1,"msg"=>"恭喜您，注册成功！<br/>请登录到您的邮箱及时激活您的账号，如果没有找到，请到垃圾箱查找")));
			}else{
				die(json_encode(array("status"=>0,"msg"=>"邮件发送失败，可能您填写的邮箱国内服务器到不了")));
			}	
		}else{
			die(json_encode(array("status"=>0,"msg"=>"注册失败，请稍候再试")));
		}
	}
    //邮箱激活验证
    public function activeAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $nowTime = time();
        $verify = $this->get('verify',true);
        if($verify == '') $this->notify("页面不存在");
        $result = $this->m_user->Where('`token` = "'.$verify.'" AND `status` = 0')->Field(array('id','token','token_expire','status'))->SelectOne();
        if($result){
            if($nowTime > $result['token_expire']){
                $this->notify("您的激活日期已经超过48小时，请重新发送激活邮件","/member");
            }else{
                $data['status'] = 1;
                $data['token'] = '';
                $data['token_expire'] = '';
                $this->m_user->UpdateByID($data,$result["id"]);
                $this->notify("激活成功，欢迎加入我们的大家庭","/login","success");
            }
        }else{
            $this->notify("激活参数出错，请稍后再试或者联系管理员","http://".$_SERVER["HTTP_HOST"]);
        }
    }
    //忘记密码
    public function forgetPwdAction(){
        $buffer["curNav"] = 9;
        $this->getView()->assign($buffer);
    }

    //忘记密码
    public function handleForgetPwdAction(){
        Yaf_Dispatcher::getInstance()->disableView();
            $email = parent::getPost("email",false);
            if(!regex($email,"require")) die(json_encode(array("status"=>0,"msg"=>"邮箱不能为空")));
            if(!regex($email,"email")) die(json_encode(array("status"=>0,"msg"=>"邮箱格式不正确")));
            $checkcode = parent::getPost("checkcode",false);
            if(strtolower($checkcode) != strtolower(parent::getSession("verify_code"))) die(json_encode(array("statuc"=>0,"msg"=>"验证码不正确")));
            $data = $this->m_user->checkEmail($email);
            if(empty($data)) die(json_encode(array("status"=>0,"msg"=>"邮箱不存在，请先注册")));
            $tmpArr = range("a","z");
            shuffle($tmpArr);
            $tmpPwd = implode("",array_slice($tmpArr,0,6));
            $this->m_user->UpdateByID(array("password"=>md5($tmpPwd)),$data["id"]);
            $link = "<a href='http://".$_SERVER["HTTP_HOST"]."/login' target='_blank'>http://".$_SERVER["HTTP_HOST"]."/login</a>";
            $body = "亲爱的".$data['username'].":<br/>由于您忘记密码，系统帮你生成一个临时密码：".$tmpPwd."<br/>请点击下面链接重新登录进会员中心进行修改<br/>".$link;
            if(sendMail($email,"忘记密码",$body)){
                die(json_encode(array("status"=>1,"msg"=>"修改密码成功，请到邮件查看")));
            }else{
                die(json_encode(array("status"=>0,"msg"=>"发送邮件失败，请稍后再试")));
            }
    }
    //微博、QQ登录
    public function threeLoginAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $type = parent::get("type",false);
        if(isMobile()){
            $display="mobile";
        }else{
            $display="pc";
        }
        if($type=="qq"){
            Yaf_loader::import(LIB_PATH . '/L_QqConnect.class.php');
            $qqObj = new L_QqConnect();
            $qqObj->login($display);
        }else if($type=="weibo"){
            Yaf_loader::import(LIB_PATH . '/L_Weibo.class.php');
            $weiboObj = new L_Weibo();
            $weiboObj->login($display);
        }else{
            $this->notify("无效登录的类型","http://".$_SERVER["HTTP_HOST"]);
        }
    }
    //微博、QQ登录回调地址
    public function authAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $type = parent::get("type",false);
        if($type=="qq"){
            Yaf_loader::import(LIB_PATH . '/L_QqConnect.class.php');
            $qqObj = new L_QqConnect();
            $info = $qqObj->callback();
            $model_user_three = $this->load('user_three');
            $user_three = $model_user_three->Where("openid='".$info["openid"]."' AND type=0")->SelectOne();
            if($user_three){
            $user_info = $this->m_user->Where("id=".$user_three["user_id"])->Field(array("id","email"))->SelectOne();
            parent::setSession('uid', $user_info["id"]);
            parent::setSession('email', $user_info["email"]);
                $this->notify("QQ授权登录成功...","/member","success");die;
            }else{
                    $user_info = $qqObj->get_user_info($info["token"],$info["openid"]);
                parent::setSession('openid', $info["openid"]);
                parent::setSession("token",$info["token"]);
                parent::setSession("nickname",$user_info["nickname"]);
                parent::setSession("type",0);
                $this->notify("QQ授权成功...","/login/three","success");die;
            }
        }else if($type=="weibo"){
            Yaf_loader::import(LIB_PATH . '/L_Weibo.class.php');
            $weiboObj = new L_Weibo();
            $info = $weiboObj->callback();
            $model_user_three = $this->load('user_three');
            $user_three = $model_user_three->Where("openid='".$info["openid"]."' AND type=1")->SelectOne();
            if($user_three){
                $user_info = $this->m_user->Where("id=".$user_three["user_id"])->Field(array("id","email"))->SelectOne();
                parent::setSession('uid', $user_info["id"]);
                parent::setSession('email', $user_info["email"]);
                $this->notify("微博授权登录成功...","/member","success");die;
            }else{
                $user_info = $weiboObj->get_user_info($info["token"],$info["openid"]);
                parent::setSession('openid', $info["openid"]);
                parent::setSession("token",$info["token"]);
                parent::setSession("nickname",$user_info["screen_name"]);
                parent::setSession("type",1);
                $this->notify("微博授权成功...","/login/three","success");die;
            }
        }else{
            $this->notify("无效回调的类型","http://".$_SERVER["HTTP_HOST"]);die;
        }
    }
    //第三方登录填下邮箱密码信息，页面
    public function threeAction(){
	$openid = parent::getSession('openid');
	$buffer["openid"] = $openid;
	$buffer["token"] = parent::getSession("token");
	$buffer["nickname"] = parent::getSession("nickname");
	$buffer["type"] = parent::getSession("type");
	if(!isset($openid) || empty($openid)){
	     $this->notify("授权失败","http://".$_SERVER["HTTP_HOST"]);die;		
	}
	$this->getView()->assign($buffer);
    }
    public function handleThreeAction(){
        Yaf_Dispatcher::getInstance()->disableView();
	$openid = parent::getPost("openid");
	$token = parent::getPost("token");
	$type = parent::getPost("type");
	$username = parent::getPost("username");
	if(!regex($username,"require")) die(json_encode(array("status"=>0,"msg"=>"昵称不能为空")));
	$email = parent::getPost("email");
	if(!regex($email,"require")) die(json_encode(array("status"=>0,"msg"=>"邮箱不能为空")));
	if(!regex($email,"email")) die(json_encode(array("status"=>0,"msg"=>"邮箱格式不正确")));
	$exist_email = $this->m_user->Where("email='".$email."'")->Field("id")->SelectOne();
	if($exist_email) die(json_encode(array("status"=>0,"msg"=>"邮箱已经注册过,请直接登录,如果忘记密码,请点击找回密码")));
	$exist_username = $this->m_user->Where("username='".$username."'")->Field("id")->SelectOne();
	if($exist_username) die(json_encode(array("status"=>0,"msg"=>"昵称已经存在,请换一个")));
	$result = $this->m_user->Insert(array("email"=>$email,"avatar"=>"/img/face.jpg","brief"=>"这家伙有点懒，还没有写个性签名! ","username"=>$username,"reg_time"=>time(),"login_time"=>time(),"login_ip"=>getClientIP(),"reg_type"=>2));
	if($result){
             $model_user_three = $this->load('user_three');
	     $model_user_three->Insert(array("user_id"=>$result,"openid"=>$openid,"type"=>$type));
	     //写入session
              parent::setSession('uid', $result);
              parent::setSession('email', $email);
	     die(json_encode(array("status"=>1,"msg"=>"绑定QQ成功。")));	
	}else{
	     die(json_encode(array("status"=>0,"msg"=>"绑定QQ失败，请稍后再试...")));	
	}
    }
    //退出登录
    public function logoutAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $this->unsetSession("uid");
        $this->unsetSession("email");
        $this->unsetCookie("auth");
        $this->notify("退出登录成功","/","success",1);
    }
		
}
