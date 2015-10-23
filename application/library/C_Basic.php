<?php
/**
 * File: C_Basic.php
 * Functionality: Basic Controller
 */
class BasicController extends Yaf_Controller_Abstract {
  /* 公共方法 */
  public function getGlobal(){
      $m_user = $this->load("User");
      //判断cookie是否有-自动登录
      $cookie_auth = $this->getCookie('auth');
      if($cookie_auth){
          $clean = array();
          list($identifier, $token) = explode(':',$cookie_auth);
          if (ctype_alnum($identifier) && ctype_alnum($token)){
              $clean['identifier'] = $identifier;
              $clean['token'] = $token;
          }
          $record = $m_user->Where(array("identifier"=>$clean['identifier']))->Field("id,email,identifier,identifier_token,login_time,point,refuse")->SelectOne();
          if($record && $record["refuse"]){
              if($clean['token'] != $record['identifier_token']){
                  //$this->redirect("Login/index");
              }elseif($clean['identifier'] != md5("SALTISDIFFCULT".md5($record["email"]."SALTISDIFFCULT"))){
                  //$this->redirect("Login/index");
              }else{
                  //更新
                  if($record["login_time"] < strtotime(date("Y-m-d"))){
                      $m_user->UpdateByID(array("login_time"=>time(),"login_ip"=>getClientIP(),"point"=>$record["point"]+2),$record["id"]);
                  }
                  $this->setSession('uid', $record["id"]);
                  $this->setSession('email', $record["email"]);
              }
          }
      }
      //根据session uid 获取该用户的信息
      $session_uid = $this->getSession("uid");
      $session_email = $this->getSession("email");
      if(isset($session_uid) && !empty($session_uid)){
          $current_user_info = $m_user->Where(array("id"=>$session_uid))->Field("id,email,username")->SelectOne();
          $current_user_info = deep_htmlspecialchars_decode($current_user_info);
          $this->getView()->assign("current_user_info",$current_user_info);
      }
  }
  public function get($key, $filter = TRUE){
    if($filter){
      return filterStr($this->getRequest()->get($key));
    }else{
      return $this->getRequest()->get($key);
    }
  }

  public function getPost($key, $filter = TRUE){
    if($filter){
      return filterStr($this->getRequest()->getPost($key));
    }else{
      return $this->getRequest()->getPost($key);
    }
  }

  public function getQuery($key, $filter = TRUE){
    if($filter){
      return filterStr($this->getRequest()->getQuery($key));
    }else{
      return $this->getRequest()->getQuery($key);
    }
  }

  public function getSession($key){
    return Yaf_Session::getInstance()->__get($key);
  }

  public function setSession($key, $val){
    return Yaf_Session::getInstance()->__set($key, $val);
  }

  public function unsetSession($key){
    return Yaf_Session::getInstance()->__unset($key);
  }
  public function unsetCookie($key){
        $this->setCookie($key,"",time()-1);
  }
  public function setCookie($key, $value, $expire = 3600, $path = '/', $domain = ''){
        setCookie($key, $value, CUR_TIMESTAMP + $expire, $path, $domain);
  }
  public function getCookie($key){
        return trim($_COOKIE[$key]);
  }
  // Load model
  public function load($model){
    return Helper::load($model);
  }

  /**
   *通知跳转页面
   *$second int 跳转时间
   *$msg string 提示信息
   *$type string 类型
   *$url string 跳转连接
   */
  public function notify($msg,$url="javascript:history.go(-1);",$type="error",$second=3){
	$this->getView()->assign(array("second"=>$second,"msg"=>$msg,"type"=>$type,"url"=>$url))->display(NOTIFY_HTML);
   } 



}
