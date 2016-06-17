<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-07-02
	描述：用户活跃
**********************************/
class ActiveAction extends AdminbaseAction {
	protected $Accesslogs_obj,$Dailyactives_obj;
	function _initialize() {
		parent::_initialize();
		$this->Accesslogs_obj = D("Accesslogs");
		$this->Dailyactives_obj = D("Dailyactives");
	}
	public function index(){
    	$this->_list();
    }

	public function _list(){
		$now = date('Y-m',strtotime('-2 month'));
		$parakey_single = array ('start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['start_time'] != "")
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(created))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !="")
			{
				$end_time = " && (date(created) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $start_time.$end_time;
		}
		 $Query =$this->Accesslogs_obj
		 ->field('max(id) id')
		 ->where("loginuserid > 0")
		 ->group('loginuserid')
		 ->select(false); 
		 $count = count($this->Accesslogs_obj
		->field('b.id')
		->table($Query.' a,accesslogs b')
		->where("a.id=b.id && (date(created) > '$now')  $search")
		->order('id desc')
		->select());
		$page = $this->page($count, 20);
		$items = $this->Accesslogs_obj
		->field('b.id , b.funcname , b.art , b.loginuserid,b.remote_addr,b.created,b.remote_port,b.response_data')
		->table($Query.' a,accesslogs b')
		->where("a.id=b.id && (date(created) > '$now')  $search")
		->order('id desc')
		->limit($page->firstRow . ',' . $page->listRows)
		->select(); 
			$this->assign("lists",$items);
			$this->assign("formpost",$parameters);
	    	$this->assign('num',$count);
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
	
	
		public function month(){
			$count = array();
			for($i=0;$i<13;$i++){
				$now = date('Y-m',strtotime('-'.$i.' month'));
				$num =$this->Dailyactives_obj
					->where("date_text ='$now' && active_type = '2000'")
					->order("id desc")
					->find();
						$count[$i]['mon'] =$now;
						if($num){
							$count[$i]['num'] = $num['active_cnt'];
						}else{
							$count[$i]['num'] = 0;
						}
			}							
			$this->assign('num',$count);
			$this->display();
	}
		public function day(){
		$parakey_single = array ('start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['start_time'] != "")
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(date_text))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !="")
			{
				$end_time = " && (date(date_text) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $start_time.$end_time;
		}
			$count = array();
			if(!$search){
				for($i=0;$i<31;$i++){
					$now = date('Y-m-d',strtotime('-'.$i.' day'));
					$num =$this->Dailyactives_obj
						->where("date_text ='$now' && active_type = '1000'")
						->order("id desc")
						->find();
							$md = date('m-d',strtotime($now));
							$count[$i]['mon'] =$md;
							if($num){
								$count[$i]['num'] = $num['active_cnt'];
							}else{
								$count[$i]['num'] = 0;
							}
				}
			}else{
				$num =$this->Dailyactives_obj
					->where("active_type = '1000' $search")
					->order("id desc")
					->select();
				for($i=0;$i<count($num);$i++){
					$md = date('m-d',strtotime($num[$i]['date_text']));
					$count[$i]['mon'] =$md;
					$count[$i]['num'] = $num[$i]['active_cnt'];
				}
			}
			//$this -> error(json_encode($search));
			$this->assign('num',$count);
			$this->assign("formpost",$parameters);
			$this->display();
	}

}