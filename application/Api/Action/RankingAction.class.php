<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-04-01
	描述：猜球排行
**********************************/
class RankingAction extends AppframeAction{
	protected $Userbets_obj;
	function _initialize() {
		parent::_initialize();
		$this->Userbets_obj = D("Userbets");
		$this->Users_obj = D("Users");
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if($_POST['status'] == 1){
			$status = "status = 150 then points";
		}elseif($_POST['status'] == 2){
			$status = "status = 140 then points";
		}else{
			$status = "status = 160 then points*(odds-1)";
		}
		if($url_userid){
			$currency=M()->query("select fget_my15k_ranking($url_userid)");
	 		$Query1 =$this->Userbets_obj
			 ->field("sum(case when $status else 0 end)points_sum,userid")
			 ->where("betid>0 and userid=$url_userid")
			 ->group("userid")
			 ->select(false);
			$usersum = $this->Userbets_obj
			->table($Query1.'t ')
			->where("points_sum>0")
			->order("points_sum*1 desc")
			->find();
			$user = $this->Users_obj
			->where(array('id'=>$url_userid))
			->find();
			$stable['place'] = $currency[0]["fget_my15k_ranking($url_userid)"];
			$stable['username'] = preg_replace_callback('/@E(.{6}==)/',function($b){return base64_decode($b[1]);},$user['nickname']);
			$stable['points_sum'] = number_format($usersum['points_sum']);
			$this->assign("stable",$stable);
		}
 		$Query2 =$this->Userbets_obj
		 ->field("sum(case when $status else 0 end)points_sum,userid")
		 ->where("betid>0 and userid>0")
		 ->group("userid")
		 ->select(false);
		$userbets = $this->Userbets_obj
		->table($Query2.'t ')
		->where("points_sum>0")
		->order("points_sum*1 desc")
		->limit('0,20')
		->select();
		foreach($userbets as$k=>$uid){
			$userids[$k] = $uid['userid'];
		}
		$users = $this->Users_obj
		->where(array('id'=>array('in',$userids)))
		->select(); 
		$this->assign("userbets",$userbets);
		$this->assign("users",$users);
		$this->assign("userid",$url_userid);
		$this->assign("formpost",$_POST);
	    $this->display();
	}
	public function repage(){
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if($_GET['status'] == 1){
			$status = "status = 150 then points";
		}elseif($_GET['status'] == 2){
			$status = "status = 140 then points";
		}else{
			$status = "status = 160 then points*(odds-1)";
		}
 		$Query =$this->Userbets_obj
		 ->field("sum(case when $status else 0 end)points_sum,userid")
		 ->where("betid>0 and userid>0")
		 ->group("userid")
		 ->select(false);
		 $count = count($this->Userbets_obj
		->table($Query.'t')
		->where("points_sum>0")
		->order("points_sum*1 desc")
		->select());
		$page = $this->page($count, 20);
		if($_GET['p']<= ceil($count/20)){
			$userbets = $this->Userbets_obj
			->table($Query.'t ')
			->where("points_sum>0")
			->order("points_sum*1 desc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select(); 
			if($userbets){
				foreach($userbets as$k=>$uid){
					$userids[$k] = $uid['userid'];
				}
				$users = $this->Users_obj
				->where(array('id'=>array('in',$userids)))
				->select(); 
			}
			if(count($userbets)<20){
				$over = "<tr><td colspan='3' style='text-align: center;'>没有更多信息了...</td></tr>";
				$this->assign("over",$over);
			}
		}else{
			$userbets ='';
		}
		$this->assign("userbets",$userbets);
		$this->assign("users",$users);
		$this->assign("userid",$url_userid);
		$this->assign("a",$_GET['p']*20-19);
	    $this->display();
	}
}
