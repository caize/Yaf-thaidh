<?php
//首页控制器
class IndexController extends BasicController {
	private $m_study;
    private $m_travel;
    private $m_discuss;
        //首页控制器初始化方法
	public function init(){
		$this->m_study = $this->load('Study');
        $this->m_travel = $this->load('Travel');
        $this->m_discuss = $this->load('Discuss');
        $this->getGlobal();
	}
	//首页操作
	public function indexAction() {
		$buffer["curNav"] = 1;
        $studyList = $this->m_study->getStudyList(10);
		$buffer["studyList"] = deep_htmlspecialchars_decode($studyList);
        $travelList = $this->m_travel->getTravelList(10);
        $buffer["travelList"] = deep_htmlspecialchars_decode($travelList);
        $discussList = $this->m_discuss->getDiscussList(10);
        $buffer["discussList"] = deep_htmlspecialchars_decode($discussList);
		$buffer['pageTitle'] = "泰语学习，泰国旅游，泰语在线词典,泰语问答_泰语导航网";
		$this->getView()->assign($buffer);	
  	}
  	//搜索操作
	public function searchAction(){
		echo 111;die;	
		$this->getView();
	}
}
