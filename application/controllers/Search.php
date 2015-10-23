<?php
//搜索控制器
class SearchController extends BasicController {
	private $m_search;
        //首页控制器初始化方法
	public function init(){
		$this->m_search = $this->load('Search');
        $this->getGlobal();
	}
  	//搜索操作
	public function indexAction() {
		$word = deep_htmlspecialchars($this->get("word"));
        if(!$word){
            $where = "status=-1";
        }else{
            $where = "status=1 AND title like '%".$word."%'";
        }
		$total = $this->m_search->getSearchArticle(0,$where);//获取符合条件的总是
        $page = $this->get('page');
        $page = $page ? $page : 1;
        $size  = 15;
        $start = ($page-1)*$size;
        $limit = $start.','.$size;
		$url = '/search?word='.$word."&page=";
		$buffer["word"] = $word;
        $buffer['pageNav'] = buildPage($page,$total,$url,$size,5);
        $buffer["list"] = $this->m_search->getSearchArticle(1,$where,$limit);
		$m_user = $this->load("User");
		foreach($buffer['list'] as $k=>$v){
			$buffer['list'][$k]["username"] = $m_user->SelectFieldByID("username",$v["user_id"]);
            $buffer['list'][$k]["avatar"] = $m_user->SelectFieldByID("avatar",$v["user_id"]);
            if($v["table_name"] == "study"){
                $buffer['list'][$k]["url"] = "/study/detail?id=".$v["id"];
            }elseif($v["table_name"] == "discuss"){
                $buffer['list'][$k]["url"] = "/discuss/detail?id=".$v["id"];
            }elseif($v["table_name"] == "travel"){
                $buffer['list'][$k]["url"] = "/travel/detail?id=".$v["id"];
            }
		}
		$buffer["list"] = deep_htmlspecialchars_decode($buffer['list']);
		$buffer['pageTitle'] = $this->get("word")."_泰语导航网";
        $buffer["curNav"] = 5;
		$this->getView()->assign($buffer);
  	}
}
