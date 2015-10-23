<?php
class StudyController extends BasicController{
	protected $m_study;
	protected $m_study_sort;
    protected $m_comment;
	public function init(){
		$this->m_study = $this->load("Study");	
		$this->m_study_sort = $this->load("Study_sort");
        $this->m_comment = $this->load("Comment");
        $this->getGlobal();
	}
	//首页操作
	public function indexAction(){
		$buffer["curNav"] = 2;
		$sid = (int)$this->get("sid");
		switch ($sid){
			case 1:
			$where = "d.status='1' AND d.sort_id=1";
			$where2 = "status='1' AND sort_id=1";
			$url = '/study?sid=1';
			$buffer['sortTitle'] = "泰语初级入门";
			$buffer['sortBrief'] = "分享跟泰语入门的文章，音频，视频，资料等";
			break;
			case 2:
			$where = "d.status='1' AND d.sort_id=2";
			$where2 = "status='1' AND sort_id=2";
			$url = '/study?sid=2';
			$buffer['sortTitle'] = "泰语学习教程";
			$buffer['sortBrief'] = "分享跟泰语语法，泰语听力，泰语阅读等学习相关的的文章。";
			break;
			case 3:
			$where = "d.status='1' AND d.sort_id=3";
			$where2 = "status='1' AND sort_id=3";
			$url = '/study?sid=3';
			$buffer['sortTitle'] = "泰语词汇阅读";
			$buffer['sortBrief'] = "分享跟泰语相关的分类词汇和阅读。";
			break;
			case 4:
			$where = "d.status='1' AND d.sort_id=4";
			$where2 = "status='1' AND sort_id=4";
			$url = '/study?sid=4';
			$buffer['sortTitle'] = "泰语学习经验故事";
			$buffer['sortBrief'] = "分享你学习泰语的故事和一些经验为正在学习泰语的小伙伴架起一道彩虹桥。";
			break;
			case 5:
			$where = "d.status='1' AND d.sort_id=5";
			$where2 = "status='1' AND sort_id=5";
			$url = '/study?sid=5';
			$buffer['sortTitle'] = "泰语资料下载";
			$buffer['sortBrief'] = "分享跟泰语相关的学习资料下载。";
			break;
			default:
			$where = "d.status='1'";
            $where2 = "status='1'";
            $url = '/study';
			$buffer['sortTitle'] = "泰语学习";
			$buffer['sortBrief'] = "分享泰语入门，泰语学习，泰语听力、阅读，泰语资料下载，泰语学习经验等，努力给正在学习泰语的小伙伴带来帮助。";
			break;
		}
		$total = $this->m_study->Where($where2)->Total();
		$page = $this->get('p');
		$page = $page ? $page : 1;
		$size  = 15;
		$pages = ceil($total/$size);
		$start = ($page-1)*$size;
		$limit = $start.','.$size;
		if(strpos($url,"?") !== false){
			$phpfile=$url."&p=";
		}else{
			$phpfile=$url."?p=";
		}
		
		$buffer['pageNav'] = buildPage($page,$total,$phpfile,$size,5);
		$buffer["list"] = $this->m_study->getStudyList($limit,$where);
		$buffer["linkSort"] = $this->m_study_sort->Where(array("status"=>1))->Field(array("id","sort_name"))->Select();
		foreach($buffer["linkSort"] as $k=>$v){
			$buffer["linkSort"][$k]["url"] = "/study?sid=".$v["id"];
		}
		$buffer['pageTitle'] = "泰语入门,泰语学习教程，泰语词汇阅读，泰语资料下载,泰语经验故事_泰语导航网";
		$this->getView()->assign($buffer);
	}
	public function detailAction(){
		$buffer["curNav"] = 2;
		$id = (int)$this->get("id");
		$article = $this->m_study->getStudy($id);
		if(!$article[0]){
			exit("<script type='text/javascript'>window.location.href=history.go(-1)</script>");
		}
		$buffer["article"] = $article[0];
		$this->m_study->addClickNum($id);
		$linkNav = $this->m_study->getLinkNav($id,$article[0]["sort_id"]);
		$buffer["linkNav"] = $linkNav;
		$buffer["linkSort"] = deep_htmlspecialchars_decode($this->m_study_sort->Where(array("status"=>1))->Field(array("id","sort_name"))->Select());
		foreach($buffer["linkSort"] as $k=>$v){
			$buffer["linkSort"][$k]["url"] = "/study?sid=".$v["id"];
		}
		$buffer['pageTitle'] = $article[0]["study_name"]."_泰语导航网";
        //读取评论
        //$buffer["commentList"] = $this->m_comment->Where(array("article_id"=>$id,"status"=>1))->Select();
        $buffer["commentTotal"] = $this->m_comment->Where(array("article_id"=>$id,"status"=>1,"type"=>1))->Total();
        $tmpSql = "select c.*,u.username,u.avatar from ".TB_PREFIX."comment as c left join ".TB_PREFIX."user as u on c.from_id=u.id where c.article_id = ".$id." AND c.status=1 AND c.type=1 order by c.ctime desc";
        $buffer["commentList"] = $this->m_comment->Query($tmpSql);
		$this->getView()->assign($buffer);
	}
    //文章添加
    public function addArticleAction(){
        $uid = $this->getSession("uid");
        $email = $this->getSession("email");
        if(!isset($uid) || empty($uid) || !isset($email) || empty($email)){
            $this->notify("请先登录","http://".$_SERVER["HTTP_HOST"]."/login");
        }
        //分类
        $sort_list = $this->m_study_sort->Where("status='1'")->Field("id,sort_name")->Select();
        $buffer['sort_list'] = $sort_list;
        $buffer['pageTitle'] = "发布文章_泰语学习_泰语导航网";
        $this->getView()->assign($buffer);
    }
    //处理文章添加
    public function handleAddArticleAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if(!$this->getRequest()->isXmlHttpRequest()){
            parent::notify("页面不存在");return false;
        }
        $uid = $this->getSession("uid");
        $title = parent::getPost("title");
        $category = (int)parent::getPost("category");
        $brief = parent::getPost("brief");
        //$content = deep_htmlspecialchars(parent::getPost("content"),false);
        $content = deep_htmlspecialchars(parent::getPost("content",false));
        $result = $this->m_study->Insert(array("study_name"=>$title,"sort_id"=>$category,"content"=>$content,"brief"=>$brief,"uid"=>$uid,"add_time"=>time()));;
        if($result){
            die(json_encode(array("status"=>1,"msg"=>"发布文章成功","url"=>"http://".$_SERVER["HTTP_HOST"]."/study/detail?id=".$result)));
        }else{
            die(json_encode(array("status"=>0,"msg"=>"发布文章失败")));
        }
    }
    //编辑文章
    public function editArticleAction(){
        $uid = $this->getSession("uid");
        $email = $this->getSession("email");
        if(!isset($uid) || empty($uid) || !isset($email) || empty($email)){
            $this->notify("请先登录","http://".$_SERVER["HTTP_HOST"]."/login");
        }
        $id = parent::get("id");
        if(!$id){
            $this->notify("页面不存在");
            Yaf_Dispatcher::getInstance()->disableView();
            return false;
        }
        $article = $this->m_study->Where("id=".$id." AND uid=".$uid)->SelectOne();
        if(!$article){
            $this->notify("页面不存在");
            Yaf_Dispatcher::getInstance()->disableView();
            return false;
        }
        if(!intval($article["status"])){
            $this->notify("页面不存在");
            Yaf_Dispatcher::getInstance()->disableView();
            return false;
        }
        //分类
        $sort_list = $this->m_study_sort->Where("status='1'")->Field("id,sort_name")->Select();
        $buffer['sort_list'] = $sort_list;
        $article["content"] = deep_htmlspecialchars_decode($article["content"]);
        $buffer['article'] = $article;
        $buffer['pageTitle'] = "编辑文章_泰语学习_泰语导航网";
        $this->getView()->assign($buffer);
    }
    public function handleEditArticleAction(){
        if(!$this->getRequest()->isXmlHttpRequest()){
            parent::notify("页面不存在");return false;
        }
        $uid = $this->getSession("uid");
        $email = $this->getSession("email");
        if(!isset($uid) || empty($uid) || !isset($email) || empty($email)){
            $this->notify("请先登录","http://".$_SERVER["HTTP_HOST"]."/login");return false;
        }
        Yaf_Dispatcher::getInstance()->disableView();
        $id = parent::getPost("id");
        $article = $this->m_study->Where("id=".$id." AND uid=".$uid)->SelectOne();
        if(!$article){
            die(json_encode(array("status"=>0,"msg"=>"只能编辑自己的文章")));
        }
        if(!intval($article["status"])){
            $this->notify("页面不存在");
            Yaf_Dispatcher::getInstance()->disableView();
            return false;
        }
        $title = parent::getPost("title");
        $category = (int)parent::getPost("category");
        $brief = parent::getPost("brief");
        $content = deep_htmlspecialchars(parent::getPost("content",false));
        $result = $this->m_study->UpdateByID(array("study_name"=>$title,"sort_id"=>$category,"content"=>$content,"brief"=>$brief),$id);
        if($result){
            die(json_encode(array("status"=>1,"msg"=>"编辑文章成功","url"=>"http://".$_SERVER["HTTP_HOST"]."/study/detail?id=".$id)));
        }else{
            die(json_encode(array("status"=>0,"msg"=>"编辑文章失败")));
        }
    }
}
