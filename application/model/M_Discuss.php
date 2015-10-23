<?php
/*
*泰语问答model
*/
class M_Discuss extends M_Model{
	public function __construct() {
		$this->table = TB_PREFIX.'discuss';
		parent::__construct();
	}
	//获取泰语学习列表
	public function getDiscussList($limit=15,$where="d.status = '1'",$order="d.add_time desc"){
		//SELECT d.*,s.sort_name FROM gob_discuss AS d LEFT JOIN gob_discuss_sort AS s ON d.sort_id = s.id where d.status = "1" limit 10
		$sql = "select d.*,s.sort_name,u.username,u.avatar from ".TB_PREFIX."discuss as d left join ".TB_PREFIX."discuss_sort as s on d.sort_id = s.id left join ".TB_PREFIX."user as u on d.uid=u.id  where ".$where." order by ".$order." limit ".$limit;
		return deep_htmlspecialchars_decode($this->Query($sql));
	}
	//根据ID获取文章详情
	public function getDiscuss($id){
		$sql = "select s.*,u.username from ".TB_PREFIX."discuss as s left join ".TB_PREFIX."user as u on s.uid = u.id where s.status = '1' and s.id=".$id;
		return deep_htmlspecialchars_decode($this->Query($sql));
	}
	//增加文章阅读数
	public function addClickNum($id){
		$this->Where(array("id"=>$id))->Update(array("click_number"=>"click_number+1"),True);
	}
	//获取上一篇 下一篇 返回当前栏目
	public function getLinkNav($id,$sort_id){
		$last = $this->Where('id < '.$id.' AND sort_id='.$sort_id)->Field(array("id","discuss_name"))->Order("id desc")->SelectOne();
		$next = $this->Where('id > '.$id.' AND sort_id='.$sort_id)->Field(array("id","discuss_name"))->Order("id asc")->SelectOne();
		$linkNav = array();
		$linkNav["last"] = $last;	
		$linkNav["next"] = $next;
		return $linkNav;
	}	

}
