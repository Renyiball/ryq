<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-09-29
	描述：活动报名
**********************************/
class ApplyAction extends AdminbaseAction {
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
				$action_select = "(action_constid = '".$parameters['action']."')";
				$_GET['action'] = $parameters['action'];
			}
			$search = $action_select;
		}			
		$infos = $this->Actioninfos_obj
		->where("subtype = 0 && parentid = ''")
		->order("created DESC")
		->select();
			for($i=0;$i<count($infos);$i++){
				if($i<count($infos)-1){
					$actionid = "(action_constid = '".$infos[$i]['constid']."') || ";
				}else{
					$actionid = "(action_constid = '".$infos[$i]['constid']."')";
				}
				$constid = $constid.$actionid;
			}
			if($search == '')
			{
				$search=$constid;
			}
			$count=$this->Actionlogs_obj->where("$search")-> count();
			$page = $this->page($count, 20);
			$logs = $this->Actionlogs_obj
			->where("$search")
			->order("created DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
	    	$this->assign('num',$count);
			$this->assign("logs",$logs);
			$this->assign("infos",$infos);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("formpost",$parameters);
			$this->display();
	}
}