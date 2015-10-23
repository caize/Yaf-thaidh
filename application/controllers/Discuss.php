<?php
class DiscussController extends BasicController{
	protected $m_discuss;
	protected $m_discuss_sort;
    protected $m_comment;
	public function init(){
		$this->m_discuss = $this->load("Discuss");	
		$this->m_discuss_sort = $this->load("Discuss_sort");
        $this->m_comment = $this->load("Comment");
        $this->getGlobal();
	}
	//首页操作
	public function indexAction(){
		$buffer["curNav"] = 4;
		$sid = (int)$this->get("sid");
		switch ($sid){
			case 1:
			$where = "d.status='1' AND d.sort_id=1";
			$where2 = "status='1' AND sort_id=1";
			$url = '/discuss?sid=1';
			$buffer['sortTitle'] = "泰学习";
			$buffer['sortBrief'] = "发布跟泰语学习相关的问答，互助，共享，成长，认识不同学习泰语的朋友。";
			break;
			case 2:
			$where = "d.status='1' AND d.sort_id=2";
			$where2 = "status='1' AND sort_id=2";
			$url = '/discuss?sid=2';
			$buffer['sortTitle'] = "泰旅游";
			$buffer['sortBrief'] = "发布跟泰国旅游相关的问答，互助交流，分享成长，寻找共同喜欢泰国的朋友。";
			break;
			case 3:
			$where = "d.status='1' AND d.sort_id=3";
			$where2 = "status='1' AND sort_id=3";
			$url = '/discuss?sid=3';
			$buffer['sortTitle'] = "泰娱乐";
			$buffer['sortBrief'] = "分享泰国明星资讯、泰国电影、泰国电视剧。";
			break;
			case 4:
			$where = "d.status='1' AND d.sort_id=4";
			$where2 = "status='1' AND sort_id=4";
			$url = '/discuss?sid=4';
			$buffer['sortTitle'] = "泰生活";
			$buffer['sortBrief'] = "分享在泰国的生活，寻找留学、经商的伙伴。";
			break;
			case 5:
			$where = "d.status='1' AND d.sort_id=5";
			$where2 = "status='1' AND sort_id=5";
			$url = '/discuss?sid=5';
			$buffer['sortTitle'] = "泰职场";
			$buffer['sortBrief'] = "分享交流跟泰语、泰国相关职业的职场感悟。";
			break;
			case 6:
			$where = "d.status='1' AND d.sort_id=5";
			$where2 = "status='1' AND sort_id=5";
			$url = '/discuss?sid=5';
			$buffer['sortTitle'] = "泰招聘";
			$buffer['sortBrief'] = "发布跟泰语、泰国相关的招聘、求职的信息。";
			break;
			default:
			$where = "d.status='1'";
                        $where2 = "status='1'";
                        $url = '/discuss';
			$buffer['sortTitle'] = "泰问答";
			$buffer['sortBrief'] = "小伙伴们可以在泰问答版块发布泰语学习、泰国旅游、娱乐、生活、职场招聘等相关问答和咨询。";
			break;
		}
		$total = $this->m_discuss->Where($where2)->Total();
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
		$buffer["list"] = $this->m_discuss->getDiscussList($limit,$where);
		$buffer["linkSort"] = $this->m_discuss_sort->Where(array("status"=>1))->Field(array("id","sort_name"))->Select();
		foreach($buffer["linkSort"] as $k=>$v){
			$buffer["linkSort"][$k]["url"] = "/discuss?sid=".$v["id"];
		}
		$buffer['pageTitle'] = "泰语问答交流，泰国旅游交流，泰语求职招聘，泰国留学_泰语导航网";
		$this->getView()->assign($buffer);
	}
	public function detailAction(){
		$buffer["curNav"] = 4;
		$id = (int)$this->get("id");
		$article = $this->m_discuss->getDiscuss($id);
		if(!$article[0]){
			exit("<script type='text/javascript'>window.location.href=history.go(-1)</script>");
		}
		$buffer["article"] = $article[0];
		$this->m_discuss->addClickNum($id);
		$linkNav = $this->m_discuss->getLinkNav($id,$article[0]["sort_id"]);
		$buffer["linkNav"] = $linkNav;
		$buffer["linkSort"] = deep_htmlspecialchars_decode($this->m_discuss_sort->Where(array("status"=>1))->Field(array("id","sort_name"))->Select());
		foreach($buffer["linkSort"] as $k=>$v){
			$buffer["linkSort"][$k]["url"] = "/discuss?sid=".$v["id"];
		}
		$buffer['pageTitle'] = $article[0]["discuss_name"]."_泰语导航网";
        //读取评论
        $buffer["commentTotal"] = $this->m_comment->Where(array("article_id"=>$id,"status"=>1,"type"=>3))->Total();
        $tmpSql = "select c.*,u.username,u.avatar from ".TB_PREFIX."comment as c left join ".TB_PREFIX."user as u on c.from_id=u.id where c.article_id = ".$id." AND c.status=1 AND c.type=3 order by c.ctime desc";
        $buffer["commentList"] = $this->m_comment->Query($tmpSql);
        $this->getView()->assign($buffer);
		$this->getView()->assign($buffer);
	}
    //文章添加
    public function addArticleAction(){
        $uid = $this->getSession("uid");
        $email = $this->getSession("email");
        if(!isset($uid) || empty($uid) || !isset($email) || empty($email)){
            $this->notify("请先登录","http://".$_SERVER["HTTP_HOST"]."/login");return false;
        }
        //分类
        $sort_list = $this->m_discuss_sort->Where("status='1'")->Field("id,sort_name")->Select();
        $buffer['sort_list'] = $sort_list;
        $buffer['pageTitle'] = "发布文章_泰话题_泰语导航网";
        $this->getView()->assign($buffer);
    }
    //处理文章添加
    public function handleAddArticleAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        if(!$this->getRequest()->isXmlHttpRequest()){
            parent::notify("页面不存在");
        }
        $uid = $this->getSession("uid");
        $title = parent::getPost("title");
        $category = (int)parent::getPost("category");
        //$content = deep_htmlspecialchars(parent::getPost("content"),false);
	$content = deep_htmlspecialchars(parent::getPost("content",false));
        $result = $this->m_discuss->Insert(array("discuss_name"=>$title,"sort_id"=>$category,"content"=>$content,"uid"=>$uid,"add_time"=>time()));;
        if($result){
            die(json_encode(array("status"=>1,"msg"=>"发布文章成功","url"=>"http://".$_SERVER["HTTP_HOST"]."/discuss/detail?id=".$result)));
        }else{
            die(json_encode(array("status"=>0,"msg"=>"发布文章失败")));
        }
    }
    //编辑文章
    public function editArticleAction(){
        $uid = $this->getSession("uid");
        $email = $this->getSession("email");
        if(!isset($uid) || empty($uid) || !isset($email) || empty($email)){
            $this->notify("请先登录","http://".$_SERVER["HTTP_HOST"]."/login");return false;
        }
        $id = parent::get("id");
        if(!$id){
            $this->notify("页面不存在");
            Yaf_Dispatcher::getInstance()->disableView();
            return false;
        }
        $article = $this->m_discuss->Where("id=".$id." AND uid=".$uid)->SelectOne();
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
        $sort_list = $this->m_discuss_sort->Where("status='1'")->Field("id,sort_name")->Select();
        $buffer['sort_list'] = $sort_list;
        $article["content"] = deep_htmlspecialchars_decode($article["content"]);
        $buffer['article'] = $article;
        $buffer['pageTitle'] = "编辑文章_泰话题_泰语导航网";
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
        $article = $this->m_discuss->Where("id=".$id." AND uid=".$uid)->SelectOne();
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
        $content = deep_htmlspecialchars(parent::getPost("content",false));
        $result = $this->m_discuss->UpdateByID(array("discuss_name"=>$title,"sort_id"=>$category,"content"=>$content),$id);
        if($result){
            die(json_encode(array("status"=>1,"msg"=>"编辑文章成功","url"=>"http://".$_SERVER["HTTP_HOST"]."/discuss/detail?id=".$id)));
        }else{
            die(json_encode(array("status"=>0,"msg"=>"编辑文章失败")));
        }
    }
}
