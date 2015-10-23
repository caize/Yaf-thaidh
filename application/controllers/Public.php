<?php
class PublicController extends BasicController{
    protected $m_study;
    protected $m_travel;
    protected $m_discuss;
    protected $m_comment;
    protected $m_collect;
    protected $m_like;
    public function init(){
        $this->m_study = $this->load("Study");
        $this->m_travel = $this->load("Travel");
        $this->m_discuss = $this->load("Discuss");
        $this->m_comment = $this->load("Comment");
        $this->m_collect = $this->load("collect");
        $this->m_like = $this->load("like");
        $this->getGlobal();
    }
    //统一处理整站顶级评论
    public function handleCommentAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if(!$this->getRequest()->isXmlHttpRequest()){
            header("http/1.1 404 not found");return;
        }
        $return = array();
        $uid = $this->getSession("uid");
        if(!isset($uid)){
            $return = array("status"=>0,"msg"=>"请登录后再评论");
            die(json_encode($return));
        }
        $POST = filterStr($_POST);
        $data  = array();
        $data["article_id"] = $POST["artileId"];//文章id
        $data["from_id"] = $uid;//当前评论用户id
        $data["to_id"] = $POST["toUid"];//文章作者ID
        $data["content"] = $POST["content"];
        $time = time();
        $data["ctime"] = $time;
        $data["type"] = $POST["type"];
        $commentId = $this->m_comment->Insert($data);
        if($commentId){
            //增加评论数
            if($POST["type"]==1){
                $this->m_study->Where("id=".$POST["artileId"])->Update(array("comment_num"=>"comment_num+1"),True);
            }elseif($POST["type"]==2){
                $this->m_travel->Where("id=".$POST["artileId"])->Update(array("comment_num"=>"comment_num+1"),True);
            }elseif($POST["type"]==3){
                $this->m_discuss->Where("id=".$POST["artileId"])->Update(array("comment_num"=>"comment_num+1"),True);
            }
            $return = array("status"=>1,"msg"=>"评论成功","ctime"=>time_format($time),"content"=>$POST["content"],"comment_id"=>$commentId);
            //压入redis消息通知
            $this->setMsg($POST["toUid"],1);
            echo json_encode($return);die;
        }else{
            $return = array("status"=>0,"msg"=>"评论失败，请稍候再试");
            echo json_encode($return);die;
        }
    }
    //统一处理整站回复
    public function handleReplyAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if(!$this->getRequest()->isXmlHttpRequest()){
            header("http/1.1 404 not found");return;
        }
        $return = array();
        $uid = $this->getSession("uid");
        if(!isset($uid)){
            $return = array("status"=>0,"msg"=>"请登录后再评论");
            echo json_encode($return);die;
        }
        $POST = filterStr($_POST);
        $data  = array();
        $data["article_id"] = $POST["artileId"];//文章id
        $data["from_id"] = $uid;//当前评论用户id
        $data["to_id"] = $POST["toUid"];//文章作者ID
        $data["content"] = $POST["content"];
        $time = time();
        $data["ctime"] = $time;
        $data["type"] = $POST["type"];
        $data["comment_id"] = $POST["commentId"];
        $commentId = $this->m_comment->Insert($data);
        if($commentId){
            //增加评论数
            if($POST["type"]==1){
                $this->m_study->Where("id=".$POST["artileId"])->Update(array("comment_num"=>"comment_num+1"),True);
            }elseif($POST["type"]==2){
                $this->m_travel->Where("id=".$POST["artileId"])->Update(array("comment_num"=>"comment_num+1"),True);
            }elseif($POST["type"]==3){
                $this->m_discuss->Where("id=".$POST["artileId"])->Update(array("comment_num"=>"comment_num+1"),True);
            }
            //压入redis消息通知
            $this->setMsg($POST["toUid"],2);
            $return = array("status"=>1,"msg"=>"评论成功","ctime"=>time_format($time),"content"=>$POST["content"],"comment_id"=>$commentId);
            echo json_encode($return);die;
        }else{
            $return = array("status"=>0,"msg"=>"评论失败，请稍候再试");
            echo json_encode($return);die;
        }
    }
    //统一处理整站点赞
    public function handleZanAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if(!$this->getRequest()->isXmlHttpRequest()){
            parent::notify("页面不存在...");
        }
        $uid= $this->getSession("uid");
        if(!$uid) die(json_encode(array("status"=>0,"msg"=>"登录后才能点赞...")));
        $artileId = intval($this->getPost("artileId",false));
        $different = intval($this->getPost("different",false));
        if($artileId===0 || $different===0) die(json_encode(array("status"=>0,"msg"=>"参数不完整...")));
        $rs = $this->m_like->Where("aid=".$artileId." AND uid=".$uid." AND different=".$different)->Field("id")->SelectOne();
        if($rs){
            die(json_encode(array("status"=>0,"msg"=>"已经赞过...")));
        }
        $data=array();
        $data["aid"] = $artileId;
        $data["different"] = $different;
        $data["uid"] = $this->getSession("uid");
        $result = $this->m_like->Insert($data);
        if($result){
            $return = array("status"=>1,"msg"=>"点赞成功...");
            //增加改篇文章的zan数
            if($different == 1){
                $this->m_study->Where("id=".$artileId)->Update(array("zan_num"=>"zan_num+1"),True);
            }elseif($different==2){
                $this->m_travel->Where("id=".$artileId)->Update(array("zan_num"=>"zan_num+1"),True);
            }elseif($different==3){
                $this->m_discuss->Where("id=".$artileId)->Update(array("zan_num"=>"zan_num+1"),True);
            }
        }else{
            $return = array("status"=>0,"msg"=>"点赞失败...，请稍候再试");
        }
        die(json_encode($return));
    }
    //统一处理整站收藏
    public function handleCollectAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if(!$this->getRequest()->isXmlHttpRequest()){
            parent::notify("页面不存在...");
        }
        $uid= $this->getSession("uid");
        if(!$uid) die(json_encode(array("status"=>0,"msg"=>"登录后才能收藏...")));
        $artileId = intval($this->getPost("artileId",false));
        $different = intval($this->getPost("different",false));
        if($artileId===0 || $different===0) die(json_encode(array("status"=>0,"msg"=>"参数不完整...")));
        $rs = $this->m_collect->Where("aid=".$artileId." AND uid=".$uid." AND different=".$different)->Field("id")->SelectOne();
        if($rs){
            die(json_encode(array("status"=>0,"msg"=>"已经收藏过...")));
        }
        $data=array();
        $data["aid"] = $artileId;
        $data["different"] = $different;
        $data["uid"] = $this->getSession("uid");
        $data["time"] =time();
        $result = $this->m_collect->Insert($data);
        if($result){
            $return = array("status"=>1,"msg"=>"收藏成功...");
            //增加改篇文章的收藏数
            if($different == 1){
                $this->m_study->Where("id=".$artileId)->Update(array("collect_num"=>"collect_num+1"),True);
            }elseif($different==2){
                $this->m_travel->Where("id=".$artileId)->Update(array("collect_num"=>"collect_num+1"),True);
            }elseif($different==3){
                $this->m_discuss->Where("id=".$artileId)->Update(array("collect_num"=>"collect_num+1"),True);
            }
        }else{
            $return = array("status"=>0,"msg"=>"收藏失败...，请稍候再试");
        }
        die(json_encode($return));
    }

    /*
    *上传图片var_dump(ImageTool::thumb("./222.jpg","2.jpg","200","100"));
    */
    public function handleUploadImgAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        Yaf_loader::import(LIB_PATH . '/L_Upload.class.php');
        $obj = new L_Upload(); //实例化上传类
        $obj->maxSize = 1000000; //图片最大上传大小
        $obj->savePath = getcwd().'/Uploads/new/'; //图片保存路径
        $obj->saveRule = 'uniqid'; //保存文件名
        $obj->uploadReplace = true; //是否覆盖同名文件 是
        $obj->allowExts = array (
            0 => 'jpg',
            1 => 'jpeg',
            2 => 'gif',
            3 => 'png',
        ); //允许上传文件后缀名
        $obj->thumb = false; //生成缩略图
        $obj->autoSub = true;//使用子目录保存上传文件
        $obj->subType = 'date'; //使用日期为子目录名称
        $obj->dateFormat = 'Y_m_d'; //使用年-月形式
        if(!$obj->upload()){
            echo json_encode(array('status'=>0, 'msg'=>$obj->getErrorMsg()));die;
        }else{
            $info = $obj->getUploadFileInfo();
            $pic = explode('/',$info[0]['savename']);
            $return = array(
                'status' => 1,
                'path' => '/Uploads/new/'.$pic[0] .'/'. $pic[1]
            );
            //压缩图片
            image_png_size_add(getcwd().'/Uploads/new/'.$pic[0] .'/'. $pic[1],getcwd().'/Uploads/new/'.$pic[0] .'/'. $pic[1]);
            echo json_encode($return);die;
        }
    }

    /**
     * 把消息写入redis
     * @param $uid 用户id
     * @param $type 类型 1：评论 2：回复
     */
    protected function setMsg($uid,$type){
        Yaf_Dispatcher::getInstance()->disableView();
        $name = "";
        switch($type){
            case 1:
                $name = "comment";
                break;
            case 2:
                $name = "reply";
                break;
        }
        $redis = Yaf_Registry::get('redis');
        $msg_exits = $redis->get("message_".$uid);
        //内存数据已经存在的时候，让相应类型+1
        if($msg_exits){
            $data = json_decode($msg_exits,true);
            $data[$name]["total"]++;
            $data[$name]["status"] = 0;//未读
            $redis->set("message_".$uid,json_encode($data));
        }else{
            $data = array(
                "comment"=>array("total"=>0,"status"=>1),//默认为已读
                "reply"=>array("total"=>0,"status"=>1),
            );
            $data[$name]["total"]++;
            $data[$name]["status"] = 0;
            $redis->set("message_".$uid,json_encode($data));
        }
    }

    /**
     * 异步轮询消息
     */
    public function getMsgAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if(!$this->getRequest()->isXmlHttpRequest()){
            parent::notify("页面不存在");
        }
        $uid= $this->getSession("uid");
        $redis = Yaf_Registry::get('redis');
        $msg = $redis->get("message_".$uid);
        if($msg){
            $data = json_decode($msg,true);
            if(!$data["comment"]["status"]){
                //$data["comment"]["status"]=0;//标记为已读
                $redis->set("message_".$uid,json_encode($data));
                echo json_encode(array("status"=>1,"total"=>$data["comment"]["total"],"type"=>1));exit();
            }
            if(!$data["reply"]["status"]){
                //$data["reply"]["status"]=0;//标记为已读
                $redis->set("message_".$uid,json_encode($data));
                echo json_encode(array("status"=>1,"total"=>$data["reply"]["total"],"type"=>2));exit();
            }
        }
    }
}
