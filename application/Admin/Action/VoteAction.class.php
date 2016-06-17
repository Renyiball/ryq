<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-09-29
	描述：投票列表
**********************************/
class VoteAction extends AdminbaseAction {
	protected $Actioninfos_obj,$Actionlogs_obj;

    function _initialize() {
		parent::_initialize();
		$this->Actioninfos_obj = D("Actioninfos");
		$this->Actionlogs_obj = D("Actionlogs");
	}
	public function index(){
    	$this->_list();
    }
	
	public function _list(){
		$parakey_single = array ('action');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['action'] !=0)
			{
				$action_select = " && (parentid = '".$parameters['action']."')";
				$_GET['action'] = $parameters['action'];
			}
			$search = $action_select;
		}
		$actions = $this->Actioninfos_obj
		->where("subtype = 30000")
		->order("created DESC")
		->select();
		$teamtype =  ' && ';
		$constid = '';
		for($i=0;$i<count($actions);$i++){
			if($i<count($actions)-1){
				$actionid = "(parentid = '".$actions[$i]['constid']."') || ";
			}else{
				$actionid = "(parentid = '".$actions[$i]['constid']."')";
			}
			$teamtype = $teamtype.$actionid;
		}
		if($search == '')
		{
			$search=$teamtype;
		}
			$count=$this->Actioninfos_obj->where("status = 0 $search")-> count();
			$page = $this->page($count, 20);
			$infos = $this->Actioninfos_obj
			->where("status = 0 $search")
			->order("created DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			
			for($i=0;$i<count($actions);$i++){
				if($i<count($actions)-1){
					$actionid = "(action_constid = '".$actions[$i]['constid']."') || ";
				}else{
					$actionid = "(action_constid = '".$actions[$i]['constid']."')";
				}
				$constid = $constid.$actionid;
			}
			$logs = $this->Actionlogs_obj
			->where("$constid")
			->order("created DESC")
			->select();
	    	$this->assign('num',$count);
			$this->assign("infos",$infos);
			$this->assign("logs",$logs);
			$this->assign("actions",$actions);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("formpost",$parameters);
			$this->display();
	}
}