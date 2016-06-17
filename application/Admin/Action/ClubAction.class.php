<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-09-29
	描述：俱乐部报名
**********************************/
class ClubAction extends AdminbaseAction {
	protected $Children_obj;

    function _initialize() {
		parent::_initialize();
		$this->Children_obj = D("Children");
	}
	public function _arr(){
		$typearr  = array(
						array("id"=>"101",	"name"=> "体团"),
						array("id"=>"102",	"name"=> "三高"),
						array("id"=>"103",	"name"=> "路虎"),
						array("id"=>"104",	"name"=> "艾伦")
					);
		return $typearr;
	}
	public function index(){
		$this->display();
    }
	
	public function old(){
		$parakey_single = array ('trainId');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['trainId'] !=0)
			{
				$action_select = "(trainId = '".$parameters['trainId']."')";
				$_GET['trainId'] = $parameters['trainId'];
			}
			$search = $action_select;
		}			
			$count=$this->Children_obj->where("$search")-> count();
			$page = $this->page($count, 20);
			$childrens = $this->Children_obj
			->where("$search")
			->order("childId DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			$arrs = $this->_arr();
	    	$this->assign('num',$count);
			$this->assign("childrens",$childrens);
	    	$this->assign('arrs',$arrs);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("formpost",$parameters);
			$this->display();
	}
}