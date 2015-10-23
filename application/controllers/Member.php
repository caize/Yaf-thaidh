<?php
class MemberController extends BasicController{
    protected $m_user;
    protected $m_study;
    protected $m_travel;
    protected $m_discuss;
    protected $uid;
    protected $email;
    public function init(){
        $this->m_user = $this->load("User");
        $this->m_study = $this->load("Study");
        $this->m_discuss = $this->load("Discuss");
        $this->m_travel = $this->load("Travel");
        $this->uid = $this->getSession("uid");
        $this->email = $this->getSession("email");
        if(!isset($this->uid) || empty($this->uid) || !isset($this->email) || empty($this->email)){
            header("Location:/");
        }
        $this->getGlobal();
    }
    //基本信息
    public function indexAction(){
        $user_info =  $this->m_user->Where("id=".$this->uid)->Field("id,email,username,brief,reg_time,login_time,login_ip")->SelectOne();
        $buffer["user_info"] = $user_info;
        $buffer['pageTitle'] = "个人信息_泰语导航网";
        $this->getView()->assign($buffer);
    }
    //保存基本信息
    public  function handleInfoAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if($this->getRequest()->isGet()){
            $this->notify("页面不存在");die;
        }
        $username=$this->getPost("username");
        if($username==""){
            $this->notify("昵称不能为空");die;
        }
        $email=$this->getPost("email");
        if($email==""){
            $this->notify("邮箱不能为空");die;
        }
        if(!regex($email,"email")){
            $this->notify("邮箱格式不正确");die;
        }
        $session_uid = $this->getSession("uid");
        $brief = $this->getPost("brief");
        $user_info = $this->m_user->Where(array("id"=>$session_uid))->Field("id,email,username,brief")->SelectOne();
        $tmp_data = array();
        if($user_info["email"] != $email){
            $email_exits = $this->m_user->Where(array("email"=>$email))->Field("id")->SelectOne();
            if($email_exits){
                $this->notify("邮箱已经存在，请换一个");die;
            }
            $tmp_data["email"] = $email;
        }else if($user_info["username"] != $username){
            $username_exits = $this->m_user->Where(array("username"=>$username))->Field("id")->SelectOne();
            if($username_exits){
                $this->notify("昵称已经存在，请换一个");die;
            }
            $tmp_data["username"] = $username;
        }else if($user_info["brief"] != $brief){
            $tmp_data["brief"] = $brief;
        }
        if(!empty($tmp_data)){
            $this->m_user->UpdateByID($tmp_data,$user_info["id"]);
        }
        $this->notify("修改成功..","/member","success");die;
    }
    //头像
    public function avatarAction(){
        $user_info =  $this->m_user->Where("id=".$this->uid)->Field("id,avatar")->SelectOne();
        $buffer["user_info"] = $user_info;
        $buffer['pageTitle'] = "修改头像_泰语导航网";
        $this->getView()->assign($buffer);
    }
    public function handleAvatarAction(){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        Yaf_Dispatcher::getInstance()->disableView();
        if($this->getRequest()->isGet()){
            $this->notify("页面不存在");die;
        }
        $current = date("Y-m");
        $upfile_dir = APP_PATH."/public/Uploads/Face/".$current."/";
        if(!is_dir($upfile_dir)){
            mkdir($upfile_dir,true);
        }
        $raw_post_data = file_get_contents('php://input', 'r');
        //$raw_post_data = fopen('php://input', 'r');
        $pic_tmp_name = "tbl".substr(md5(time()),0,10);
        $pic_name = $pic_tmp_name.".png";
        $face = $upfile_dir.$pic_name;
        file_put_contents($face, $raw_post_data);
        //Helper::import('Img');
        //Helper::import('File');
        //createThumb($face,$upfile_dir,$pic_tmp_name,600,600);
	Yaf_loader::import(LIB_PATH . '/L_Image.class.php');
	$new_pic = $upfile_dir.$pic_tmp_name.".jpg";
        //image_png_size_add($face,$new_pic);
	L_Image::thumb($face,$new_pic,150,150);
        $face_dir = str_replace(APP_PATH."/public","",$new_pic);
	@unlink($face);
        $this->m_user->UpdateByID(array("avatar"=>$face_dir),$this->uid);
    }
    //passwd
    public function passwdAction(){
        $buffer['pageTitle'] = "修改密码_泰语导航网";
        $this->getView()->assign($buffer);
    }
    public function handlePasswdAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if($this->getRequest()->isGet()){
            $this->notify("页面不存在");die;
        }
        $old_passwd = $this->getPost("old_passwd",false);
        $new_passwd = $this->getPost("new_passwd",false);
        $re_passwd = $this->getPost("re_passwd",false);
        if(!regex($old_passwd,"require")){
            $this->notify("旧密码不能为空..");die;
        }
        $session_uid = $this->getSession("uid");
        $user_info =  $this->m_user->Where("id=".$session_uid)->Field("id,password")->SelectOne();
        if($user_info["password"] != md5($old_passwd)){
            $this->notify("旧密码不正确..");die;
        }
        if(!regex($new_passwd,"require")){
            $this->notify("新密码不能为空..");die;
        }
        if(!regex($re_passwd,"require")){
            $this->notify("确认密码不能为空..");die;
        }
        if(!regex($new_passwd,"six")){
            $this->notify("新密码必须6-18位");die;
        }
        if($new_passwd != $re_passwd){
            $this->notify("新密码和确认密码不一致");die;
        }
        $tmp_data = array();
        $tmp_data["password"] = md5($new_passwd);
        $result = $this->m_user->UpdateByID($tmp_data,$user_info["id"]);
        if($result){
            $this->notify("修改密码成功..","/member/passwd","success");die;
        }else{
            $this->notify("修改密码失败...");die;
        }
    }
    public function pointAction(){
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
        $user_info =  $this->m_user->Where("id=".$this->uid)->Field("id,email,username,point,reg_time,login_time,login_ip")->SelectOne();
        //等级
        $level = point_to_level($user_info["point"]);
        $remain_point = $level["next_level_point"]-$user_info["point"];
        $buffer['user_info']=$user_info;
        $buffer['level_data']=$levelArr;
        $buffer['level']=$level["level"];
        $buffer['remain_point']=$remain_point;
        $buffer['pageTitle'] = "积分等级_泰语导航网";
        $this->getView()->assign($buffer);
    }
    //我的文章
    public function articleAction(){
        $type = (int)$this->get("type");
        if($type == 0 || $type == 1){
            $total = $this->m_study->Where("uid = ".$this->uid." AND status = '1'")->Total();
        }else if($type == 2){ //泰国旅游
            $total = $this->m_travel->Where("uid = ".$this->uid." AND status = '1'")->Total();
        }
        $page = $this->get('p');
        $page = $page ? $page : 1;
        $size  = 15;
        $pages = ceil($total/$size);
        $start = ($page-1)*$size;
        $limit = $start.','.$size;
        //泰语学习
        if($type == 0 || $type == 1){
            $article_list = $this->m_study->Where("uid = ".$this->uid." AND status = '1'")->Field("id,study_name as name,add_time")->Order("add_time desc")->Limit($limit)->Select();
            if(!empty($article_list)){
                foreach($article_list as $k=>$v){
                    $article_list[$k]["url"] = "/study/detail?id=".$v["id"];
                    $article_list[$k]["edit_url"] = "/study/editarticle?id=".$v["id"];
                }
            }
        }else if($type == 2){ //泰国旅游
            $article_list = $this->m_travel->Where("uid = ".$this->uid." AND status = '1'")->Field("id,travel_name as name,add_time")->Order("add_time desc")->Limit($limit)->Select();
            if(!empty($article_list)){
                foreach($article_list as $k=>$v){
                    $article_list[$k]["url"] = "/travel/detail?id=".$v["id"];
                    $article_list[$k]["edit_url"] = "/travel/editarticle?id=".$v["id"];
                }
            }
        }
        $phpfile = "/member/article?type=".$type."&p=";
        $buffer['type'] = $type;
        $buffer['pageNav'] = buildPage($page,$total,$phpfile,$size,5);
        $buffer["article_list"] = $article_list;
        $buffer['pageTitle'] = "我的文章_泰语导航网";
        $this->getView()->assign($buffer);
    }
    //我的话题
    public function topicAction(){
        $page = $this->get('p');
        $page = $page ? $page : 1;
        $size  = 15;
        $total = $this->m_discuss->Where("uid = ".$this->uid." AND status = '1'")->Total();
        $pages = ceil($total/$size);
        $start = ($page-1)*$size;
        $limit = $start.','.$size;
        $article_list = $this->m_discuss->Where("uid = ".$this->uid." AND status = '1'")->Field("id,discuss_name as name,add_time")->Order("add_time desc")->Limit($limit)->Select();
        $phpfile = "/member/topic?p=";
        $buffer['pageNav'] = buildPage($page,$total,$phpfile,$size,5);
        $buffer["article_list"] = $article_list;
        $buffer['pageTitle'] = "我的话题_泰语导航网";
        $this->getView()->assign($buffer);
    }
    //我的收藏
    public function collectAction(){
        $type = (int)$this->get("type");
        $page = (int)$this->get('p');
        $type = $type ? $type : 1;
        $page = $page ? $page : 1;
        $m_collect = $this->load("Collect");
        $size  = 10;
        $total = $m_collect->countCollectById($this->uid,$type);
        $pages = ceil($total/$size);
        $start = ($page-1)*$size;
        $limit = $start.','.$size;
        $article_list = $m_collect->selectCollectById($this->uid,$type,$limit);
        $phpfile = "/member/collect?type=".$type."&p=";
        $buffer['pageNav'] = buildPage($page,$total,$phpfile,$size,5);
        $buffer['type'] = $type;
        $buffer["article_list"] = $article_list;
        $buffer['pageTitle'] = "我的收藏_泰语导航网";
        $this->getView()->assign($buffer);
    }
    //我的评论
    public function commentAction(){
        $type = (int)$this->get("type");
        $page = (int)$this->get('p');
        $type = $type ? $type : 1;
        $page = $page ? $page : 1;
        $m_comment = $this->load("comment");
        $size  = 10;
        $total = $m_comment->Where("from_id=".$this->uid." AND type=".$type)->Total();
        $pages = ceil($total/$size);
        $start = ($page-1)*$size;
        $limit = $start.','.$size;
        $article_list = $m_comment->Where("from_id=".$this->uid." AND type=".$type)->Order("ctime desc")->Limit($limit)->Select();
        foreach($article_list as $k=>$v){
            if($type==1){
                $title = $this->m_study->Where("id=".$v["article_id"])->Field("study_name")->SelectOne();
                $article_list[$k]["title"] =$title["study_name"];
                $article_list[$k]["url"]="/study/detail?id=".$v["article_id"];
            }else if($type==2){
                $title = $this->m_travel->Where("id=".$v["article_id"])->Field("travel_name")->SelectOne();
                $article_list[$k]["title"] = $title["travel_name"];
                $article_list[$k]["url"]="/travel/detail?id=".$v["article_id"];
            }else if($type==3){
                $title = $this->m_discuss->Where("id=".$v["article_id"])->Field("discuss_name")->SelectOne();
                $article_list[$k]["title"] = $title["discuss_name"];
                $article_list[$k]["url"]="/discuss/detail?id=".$v["article_id"];
            }

        }
        $phpfile = "/member/comment?type=".$type."&p=";
        $buffer["article_list"] = $article_list;
        $buffer['type'] = $type;
        $buffer['pageNav'] = buildPage($page,$total,$phpfile,$size,5);
        $buffer['pageTitle'] = "我的评论_泰语导航网";
        $this->getView()->assign($buffer);
    }
    //我的消息
    public function messageAction(){
        //点击进来就让redis中消息状态为已读
        $uid= $this->getSession("uid");
        $redis = Yaf_Registry::get('redis');
        $msg = $redis->get("message_".$uid);
        if($msg){
            $data = json_decode($msg,true);
            if(!$data["comment"]["status"]){
                $data["comment"]["status"]=1;//标记为已读
                $data["comment"]["total"]=0;//标记为0
                $redis->set("message_".$uid,json_encode($data));
            }
            if(!$data["reply"]["status"]){
                $data["reply"]["status"]=1;//标记为已读
                $data["reply"]["total"]=0;//标记为已读
                $redis->set("message_".$uid,json_encode($data));
            }
        }
        $page = (int)$this->get('p');
        $page = $page ? $page : 1;
        $m_comment = $this->load("comment");
        $size  = 10;
        $total = $m_comment->Where("to_id=".$this->uid)->Total();
        $pages = ceil($total/$size);
        $start = ($page-1)*$size;
        $limit = $start.','.$size;
        $article_list = $m_comment->Where("to_id=".$this->uid)->Order("ctime desc")->Limit($limit)->Select();
        foreach($article_list as $k=>$v){
            if($v["type"]==1){
                $title = $this->m_study->Where("id=".$v["article_id"])->Field("study_name")->SelectOne();
                $article_list[$k]["title"] =$title["study_name"];
                $article_list[$k]["url"]="/study/detail?id=".$v["article_id"];
                $from_user = $this->m_user->Where("id=".$v["from_id"])->Field("email,username")->SelectOne();
                $article_list[$k]["from_username"] =$from_user["username"];
            }else if($v["type"]==2){
                $title = $this->m_travel->Where("id=".$v["article_id"])->Field("travel_name")->SelectOne();
                $article_list[$k]["title"] = $title["travel_name"];
                $article_list[$k]["url"]="/travel/detail?id=".$v["article_id"];
                $from_user = $this->m_user->Where("id=".$v["from_id"])->Field("email,username")->SelectOne();
                $article_list[$k]["from_username"] =$from_user["username"];
            }else if($v["type"]==3){
                $title = $this->m_discuss->Where("id=".$v["article_id"])->Field("discuss_name")->SelectOne();
                $article_list[$k]["title"] = $title["discuss_name"];
                $article_list[$k]["url"]="/discuss/detail?id=".$v["article_id"];
                $from_user = $this->m_user->Where("id=".$v["from_id"])->Field("email,username")->SelectOne();
                $article_list[$k]["from_username"] =$from_user["username"];
            }
        }
        $phpfile = "/member/message?p=";
        $buffer["article_list"] = $article_list;
        $buffer['pageNav'] = buildPage($page,$total,$phpfile,$size,5);
        $buffer['pageTitle'] = "我的消息_泰语导航网";
        $this->getView()->assign($buffer);
    }
}
