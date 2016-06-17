<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-07-03
	描述：流失统计
**********************************/

class OutflowAction extends AdminbaseAction {
	protected $Accesslogs_obj;
	function _initialize() {
		parent::_initialize();
		$this->Accesslogs_obj = D("Accesslogs");
		$this->Users_obj = D("Users");
	}
	public function index(){
    	$this->_list();
    }

	public function _list(){
		$now = date('Y-m');
		$ucount=$this->Users_obj->where("id > 0")-> count();
		 $Query =$this->Accesslogs_obj
		 ->field('max(id) id')
		 ->where("loginuserid > 0")
		 ->group('loginuserid')
		 ->select(false); 
		 $acount = count($this->Accesslogs_obj
		->field('b.id')
		->table($Query.' a,accesslogs b')
		->where("a.id=b.id && (date(created) > '$now')  $search")
		->order('id desc')
		->select());
		 $count = count($this->Accesslogs_obj
		->field('b.id')
		->table($Query.' a,accesslogs b')
		->where("a.id=b.id && (date(created) < '$now')")
		->order('id desc')
		->select());
		$page = $this->page($count, 20);
		$items = $this->Accesslogs_obj
		->field('b.id , b.funcname , b.art , b.loginuserid,b.remote_addr,b.created,b.remote_port,b.response_data')
		->table($Query.' a,accesslogs b')
		->where("a.id=b.id    && (date(created) < '$now')")
		->order('id desc')
		->limit($page->firstRow . ',' . $page->listRows)
		->select(); 
			$this->assign("now",$now);
			$this->assign("lists",$items);
			$this->assign("formpost",$parameters);
	    	$this->assign('ucount',$ucount);
	    	$this->assign('acount',$acount);
			$this->assign("Page", $page->show('Admin'));
			$this->display();
	}
	public function look(){
    	if(isset($_GET['id'])){
    		$id = $_GET['id'];
			$count=$this->Accesslogs_obj->where("loginuserid = $id $search")-> count();
			$page = $this->page($count, 20);
			$items = $this->Accesslogs_obj
			->where("loginuserid = $id $search")
			->order("id desc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			$this->assign("Page", $page->show('Admin'));
			$this->assign("lists",$items);
			$this->display();
		}else{
			$this->error($this->Accesslogs_obj->getError());
		}
	}
	
	
}