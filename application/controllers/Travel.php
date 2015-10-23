<?php
class TravelController extends BasicController{
	protected $m_travel;
	protected $m_travel_sort;
    protected $m_comment;
	public function init(){
		$this->m_travel = $this->load("Travel");	
		$this->m_travel_sort = $this->load("Travel_sort");
        $this->m_comment = $this->load("Comment");
        $this->getGlobal();
	}
	//首页操作
	public function indexAction(){
		$buffer["curNav"] = 3;
		$sid = (int)$this->get("sid");
		switch ($sid){
			case 1:
			$where = "d.status='1' AND d.sort_id=1";
			$where2 = "status='1' AND sort_id=1";
			$url = '/travel?sid=1';
			$buffer['sortTitle'] = "泰国旅游攻略";
			$buffer['sortBrief'] = "小伙伴们可以在泰国旅游攻略栏目，分享关于泰国旅游的攻略,帮助喜欢泰国的朋友";
			break;
			case 2:
			$where = "d.status='1' AND d.sort_id=2";
			$where2 = "status='1' AND sort_id=2";
			$url = '/travel?sid=2';
			$buffer['sortTitle'] = "泰国游记分享";
			$buffer['sortBrief'] = "分享你在泰国游玩的经历，用文字记录，帮助喜欢，即将去泰国的小伙伴。";
			break;
			case 3:
			$where = "d.status='1' AND d.sort_id=3";
			$where2 = "status='1' AND sort_id=3";
			$url = '/travel?sid=3';
			$buffer['sortTitle'] = "泰国美食";
			$buffer['sortBrief'] = "分享泰国遇见的令人独特的美食，帮助同为吃货的TA";
			break;
			case 4:
			$where = "d.status='1' AND d.sort_id=4";
			$where2 = "status='1' AND sort_id=4";
			$url = '/travel?sid=4';
			$buffer['sortTitle'] = "泰国历史文化";
			$buffer['sortBrief'] = "泰国作为东南亚有独特文化的国度，历史悠久，文化绚烂。";
			break;
			default:
			$where = "d.status='1'";
                        $where2 = "status='1'";
                        $url = '/travel';
			$buffer['sortTitle'] = "泰语旅游";
			$buffer['sortBrief'] = "分享关于泰国旅游的攻略、泰国旅游时的经历、经验，帮助喜欢泰国的朋友";
			break;
		}
		$total = $this->m_travel->Where($where2)->Total();
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
		$buffer["list"] = $this->m_travel->getTravelList($limit,$where);
		$buffer["linkSort"] = $this->m_travel_sort->Where(array("status"=>1))->Field(array("id","sort_name"))->Select();
		foreach($buffer["linkSort"] as $k=>$v){
			$buffer["linkSort"][$k]["url"] = "/travel?sid=".$v["id"];
		}
		$buffer['pageTitle'] = "泰国旅游攻略,游记分享,泰国美食，历史文化_泰语导航网";
		$this->getView()->assign($buffer);
	}
	public function detailAction(){
		$buffer["curNav"] = 3;
		$id = (int)$this->get("id");
		$article = $this->m_travel->getTravel($id);
		if(!$article[0]){
			exit("<script type='text/javascript'>window.location.href=history.go(-1)</script>");
		}
		$buffer["article"] = $article[0];
		$this->m_travel->addClickNum($id);
		$linkNav = $this->m_travel->getLinkNav($id,$article[0]["sort_id"]);
		$buffer["linkNav"] = $linkNav;
		$buffer["linkSort"] = deep_htmlspecialchars_decode($this->m_travel_sort->Where(array("status"=>1))->Field(array("id","sort_name"))->Select());
		foreach($buffer["linkSort"] as $k=>$v){
			$buffer["linkSort"][$k]["url"] = "/travel?sid=".$v["id"];
		}
		$buffer['pageTitle'] = $article[0]["travel_name"]."_泰语导航网";
        //读取评论
        $buffer["commentTotal"] = $this->m_comment->Where(array("article_id"=>$id,"status"=>1,"type"=>2))->Total();
        $tmpSql = "select c.*,u.username,u.avatar from ".TB_PREFIX."comment as c left join ".TB_PREFIX."user as u on c.from_id=u.id where c.article_id = ".$id." AND c.status=1 AND c.type=2  order by c.ctime desc";
        $buffer["commentList"] = $this->m_comment->Query($tmpSql);
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
        $sort_list = $this->m_travel_sort->Where("status='1'")->Field("id,sort_name")->Select();
        $buffer['sort_list'] = $sort_list;
        $buffer['pageTitle'] = "发布文章_泰旅游_泰语导航网";
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
        $result = $this->m_travel->Insert(array("travel_name"=>$title,"sort_id"=>$category,"content"=>$content,"brief"=>$brief,"uid"=>$uid,"add_time"=>time()));;
        if($result){
            die(json_encode(array("status"=>1,"msg"=>"发布文章成功","url"=>"http://".$_SERVER["HTTP_HOST"]."/travel/detail?id=".$result)));
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
        $article = $this->m_travel->Where("id=".$id." AND uid=".$uid)->SelectOne();
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
        $sort_list = $this->m_travel_sort->Where("status='1'")->Field("id,sort_name")->Select();
        $buffer['sort_list'] = $sort_list;
        $article["content"] = deep_htmlspecialchars_decode($article["content"]);
        $buffer['article'] = $article;
        $buffer['pageTitle'] = "编辑文章_泰旅游_泰语导航网";
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
        $article = $this->m_travel->Where("id=".$id." AND uid=".$uid)->SelectOne();
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
        $result = $this->m_travel->UpdateByID(array("travel_name"=>$title,"sort_id"=>$category,"content"=>$content,"brief"=>$brief),$id);
        if($result){
            die(json_encode(array("status"=>1,"msg"=>"编辑文章成功","url"=>"http://".$_SERVER["HTTP_HOST"]."/travel/detail?id=".$id)));
        }else{
            die(json_encode(array("status"=>0,"msg"=>"编辑文章失败")));
        }
    }
}
