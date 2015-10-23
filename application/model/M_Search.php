<?php
class M_Search extends M_Model {

	function __construct() {
		$this->table = TB_PREFIX."search";
		parent::__construct();
	}
	//获取study travel ask三表里的文章
	function getAllArticle(){
		return $this->Where("status=1")->Limit(15)->Order("add_time desc,click_number desc")->Select();
	}
	//根据关键字搜索相关文章
	function getSearchArticle($type=1,$where,$limit=15){
		//如果type=1说明就查list
		if($type){
			return $this->Where($where)->Limit($limit)->Select();

		}else{//查询总数
			return $this->Where($where)->Total();
		}
	}

}
