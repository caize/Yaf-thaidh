<?php
/*
*User model
*/
class M_User extends M_Model{
	public function __construct() {
		$this->table = TB_PREFIX.'user';
		parent::__construct();
	}
	/**
	*param string $email邮箱
	*/
	public function checkEmail($email){
		$email = deep_htmlspecialchars($email);
		$result = $this->Where("email = '".$email."'")->Field("id,username")->selectOne();
		return $result;
	}
	/**
	*param string $username 昵称
	*/
	public function checkUsername($username){
		$username = deep_htmlspecialchars($username);
		$result = $this->Where("username = '".$username."'")->Field("id")->selectOne();
		return deep_htmlspecialchars_decode($result);
	}
	//根据ID获取文章详情
	public function getStudy($id){
		$sql = "select s.*,u.username from ".TB_PREFIX."study as s left join ".TB_PREFIX."user as u on s.uid = u.id where s.status = '1' and s.id=".$id;
		return deep_htmlspecialchars_decode($this->Query($sql));
	}
	//获取上一篇 下一篇 返回当前栏目
	public function getLinkNav($id,$sort_id){
		$last = $this->Where('id < '.$id.' AND sort_id='.$sort_id)->Field(array("id","study_name"))->Order("id desc")->SelectOne();
		$next = $this->Where('id > '.$id.' AND sort_id='.$sort_id)->Field(array("id","study_name"))->Order("id asc")->SelectOne();
		$linkNav = array();
		$linkNav["last"] = $last;	
		$linkNav["next"] = $next;
		return $linkNav;
	}	

}
