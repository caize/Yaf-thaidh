<?php
class M_Collect extends M_Model{
    public function __construct() {
        $this->table = TB_PREFIX.'collect';
        parent::__construct();
    }

    /**
     * @param int $uid 用户ID
     * @param int $different 所属类别
     */
    public function selectCollectById($uid,$different,$limit){
        $sql = "";
        if($different==1){
            $sql = "select c.aid,c.time,s.study_name as name,concat('/study/detail?id=',c.aid) as url from ".TB_PREFIX."collect as c left join ".TB_PREFIX."study as s on c.aid=s.id where c.different=".$different." AND c.uid=".$uid." order by c.time desc limit ".$limit;
        }elseif($different==2){
            $sql = "select c.aid,c.time,s.travel_name as name,concat('/travel/detail?id=',c.aid) as url from ".TB_PREFIX."collect as c left join ".TB_PREFIX."travel as s on c.aid=s.id where c.different=".$different." AND c.uid=".$uid." order by c.time desc limit ".$limit;
        }elseif($different==3){
            $sql = "select c.aid,c.time,s.discuss_name as name,concat('/discuss/detail?id=',c.aid) as url from ".TB_PREFIX."collect as c left join ".TB_PREFIX."discuss as s on c.aid=s.id where c.different=".$different." AND c.uid=".$uid." order by c.time desc limit ".$limit;
        }
        return deep_htmlspecialchars_decode($this->Query($sql));
    }
    public function countCollectById($uid,$different){
        $sql = "select count(*) as total from ".TB_PREFIX."collect where uid=".$uid." AND different=".$different;
        $count = $this->Query($sql);
        return $count[0]["total"];
    }
}
