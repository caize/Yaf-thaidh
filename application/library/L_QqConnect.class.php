<?php
//QQ登录类
class L_QqConnect{
    private $appid = "101226808";
    private $appkey = "baaee5fa9b0fe4eba32045196c4abbb4";
    private $callback_url = "http://thaidh.com/login/auth/?type=qq";
    private $scope = "get_user_info";
    /**
     * 获取Authorization Code PC网站：
     * https://graph.qq.com/oauth2.0/authorize
     * WAP网站：https://graph.z.qq.com/moc2/authorize
     * $display 默认是PC端的样式，如果传入“mobile”，则展示为mobile端下的样式。
     */
    public function login($display="pc"){
        $_SESSION['state'] = md5(uniqid(rand(), TRUE));
        if($display=="pc"){
            $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$this->appid . "&redirect_uri=" . urlencode($this->callback_url)."&state=" . $_SESSION['state']."&scope=".$this->scope;
        }else{
            $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$this->appid . "&redirect_uri=" . urlencode($this->callback_url)."&state=" . $_SESSION['state']."&scope=".$this->scope."&display=".$display;
        }
	header('Location: '.$login_url);
    }

    /**
     *处理回调 获取access_token + openid
     */
    public function callback(){
        //如果用户成功登录并授权，则会跳转到指定的回调地址，并在URL中带上Authorization Code
        $code = $_GET['code'];
        $state = $_SESSION['state'];
        $token = $this->get_access_token($code,$state);
        $openid = $this->get_openid($token);
        if(!$token || !$openid) {
            exit('token或者openid出错了');
        }
        return array('openid' => $openid, 'token' => $token);
    }
    /**
     * 获取用户信息
     *https://graph.qq.com/user/get_user_info
     */
    public function get_user_info($token,$openid){
        $url = "https://graph.qq.com/user/get_user_info?access_token=".$token."&oauth_consumer_key=".$this->appid."&openid=".$openid;
        $response = $this->http_get($url);
        if($response == false) {
            return false;
        }
        $user = json_decode($response, true);
        return $user;
    }
    /**
     * 获取acesss_token
     * PC网站：https:/:/graph.qq.com/oauth2.0/token
     * WAP网站：https://graph.z.qq.com/moc2/token
     */
    private function get_access_token($code,$state){
        if($state == $_SESSION['state']){
            $url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=".$this->appid."&client_secret=".$this->appkey."&code=".$code."&redirect_uri=".urlencode($this->callback_url);
            $response = $this->http_get($url);
            if($response == false){
                return false;
            }
            $tmp_array = array();
            parse_str($response,$tmp_array);
            return $tmp_array["access_token"];
        }else{
            exit("The state not right");
        }
    }

    /**
     * 获取用户OpenID
     * PC网站：https://graph.qq.com/oauth2.0/me
     * WAP网站：https://graph.z.qq.com/moc2/me
     */
    private function get_openid($access_token){
        $url = "https://graph.qq.com/oauth2.0/me?access_token=".$access_token;
        $response = $this->http_get($url);
        if($response == false) {
            return false;
        }
        if (strpos($response, "callback") !== false) {
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($response);
        if (isset($user->error) || $user->openid == "") {
            return false;
        }
        return $user->openid;
    }
    /**
     * http get请求
     * @param $url
     * @return bool
     */
    private function http_get($url) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * http post请求
     * @param $url
     * @param $param
     * @param bool $post_file
     * @return bool
     */
    private function http_post($url, $param, $post_file = false) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }
}
