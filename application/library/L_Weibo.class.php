<?php
//weibo登录类
class L_Weibo{
    private $appid = "";
    private $appkey = "";
    private $callback_url = "http://thaidh.com/login/auth/?type=weibo";
    private $scope = "";

    public function login($display="pc"){
        $_SESSION['state'] = md5(uniqid(rand(), TRUE));
        if($display=="pc"){
            $url = "https://api.weibo.com/oauth2/authorize?response_type=code&client_id=".$this->appid."&scope=".$this->scope."&redirect_uri=".urlencode($this->callback_url)."&state=".$_SESSION['state'];
        }else{
            $url = "https://api.weibo.com/oauth2/authorize?response_type=code&client_id=".$this->appid."&scope=".$this->scope."&redirect_uri=".urlencode($this->callback_url)."&state=".$_SESSION['state']."&display=".$display;
        }
        header('Location: '.$url);
    }
    public function callback(){
        $code = $_GET['code'];
        $state = $_SESSION['state'];
        $token = $this->get_access_token($code);
        $openid = $this->get_openid($token);
        if(!$token || !$openid) {
            exit('token或者openid出错了');
        }
        return array('openid' => $openid, 'token' => $token);
    }
    private function get_access_token($code){
        $url = "https://api.weibo.com/oauth2/access_token";
        $param = array(
            "client_id"=>$this->appid,
            "client_secret"=>$this->appkey,
            "grant_type"=>"authorization_code",
            "code"=>$code,
            "redirect_uri"=>$this->callback_url
        );
        $response = $this->http_post($url,$param);
        if($response == false) {
            return false;
        }
        $response_arr = json_decode($response,true);
        return $response_arr["access_token"];
    }
    private function get_openid($token){
        $url ="https://api.weibo.com/oauth2/get_token_info";
        $param = array(
            "access_token"    => $token
        );
        $response = $this->http_post($url,$param);
        if($response == false) {
            return false;
        }
        $response_arr = json_decode($response,true);
        return $response_arr["uid"];
    }
    public function get_user_info($token,$uid){
        $url = "https://api.weibo.com/2/users/show.json?access_token=".$token."&uid=".$uid;
        $response = $this->http_get($url);
        if($response == false) {
            return false;
        }
        $user = json_decode($response, true);
        return $user;
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
