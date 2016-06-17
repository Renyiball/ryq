<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-10-21
	描述：球币扣除
**********************************/
class ExchangeAction extends AppframeAction {
	protected $Bets_obj,$Userbets_obj,$Contacts_obj;
	function _initialize() {
		parent::_initialize();
		$this->Bets_obj = D("Bets");
		$this->Userbets_obj = D("Userbets");
		$this->Contacts_obj = D("Contacts");
	}
    public function index() {
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if(empty($url_userid) || $url_userid < 0){
	    	$output = array('ret'=>'Unauthorized','code'=>401,'info'=>'详情请登录后再来查看！');
			$this->assign("output",$output);
	 	}
		$nowday = date("Y-m-d");
		$today = date("Y-m-d",strtotime('monday'));
		$lastmonday = date("Y-m-d",strtotime('last monday -1 week'));
		$nextmonday = date("Y-m-d",strtotime('next monday -1 week'));
		if($today == $nowday){
			$monday = $nextmonday;
		}else{
			$monday = $lastmonday;
		}
		$sunday = date("Y-m-d",strtotime('next sunday -1 week'));
		$num_field = $this->Bets_obj
		->where("'$monday' <= date(expiredate) && date(expiredate) <= '$sunday'")
		->order("id desc")
		->select();
		for($f=0;$f<count($num_field);$f++){
			if($f<count($num_field)-1){
				$betid = "(betid = ".$num_field[$f]['id'].") || ";
			}else{
				$betid = "(betid = ".$num_field[$f]['id'].")";
			}
			$bid = $bid.$betid;
		}
		
		$people = $this->Userbets_obj
		->where("userid = $url_userid && pointtype = 15000 && ($bid)")
		->order("id desc")
		->count();
		$correct = $this->Userbets_obj
		->where("userid = $url_userid && status = 160 && ($bid)")
		->order("id desc")
		->count();
		$contacts = $this->Contacts_obj
		->where("relatedType = 5 && relatedID = $url_userid")
		->order("modified desc")
		->find();
		$record = $this->Userbets_obj
		->where("userid = $url_userid && betid=-101 && date(created) = '$sunday'")
		->order("id desc")
		->find();
  		$field = count($num_field);

		$timeday = date("w");
		if($timeday == 0 || $timeday > 2){
			$tuesday = date("m-d 17:00",strtotime('tuesday -1 week'));
		}
		if($timeday == 1){
			$tuesday = date("m-d 17:00",strtotime('tuesday'));
		}
		if($timeday == 2){
			$tuesday = date("m-d 17:00");
		}

		if($timeday == 1){
			$last1monday = date("Y-m-d",strtotime('last monday -1 week'));
			$last1sunday = date("Y-m-d",strtotime('last sunday -1 week'));
			$last2monday = date("Y-m-d",strtotime('next monday -1 week'));
			$last2sunday = date("Y-m-d",strtotime('next sunday -1 week'));
		}else{
			$last1monday = date("Y-m-d",strtotime('last monday -2 week'));
			$last1sunday = date("Y-m-d",strtotime('last sunday -1 week'));
			$last2monday = date("Y-m-d",strtotime('last monday -1 week'));
			$last2sunday = date("Y-m-d",strtotime('next sunday -1 week'));
		}
		$last1bets = $this->Bets_obj
		->where("'$last1monday' <= date(expiredate) && date(expiredate) <= '$last1sunday'")
		->order("id desc")
		->select();
		$last2bets = $this->Bets_obj
		->where("'$last2monday' <= date(expiredate) && date(expiredate) <= '$last2sunday'")
		->order("id desc")
		->select();
		for($f=0;$f<count($last1bets);$f++){
			if($f<count($last1bets)-1){
				$bet1 = "betid = ".$last1bets[$f]['id']." || ";
			}else{
				$bet1 = "betid = ".$last1bets[$f]['id'];
			}
			$betid1 = $betid1.$bet1;
		}
		for($f=0;$f<count($last2bets);$f++){
			if($f<count($last2bets)-1){
				$bet2 = "betid = ".$last2bets[$f]['id']." || ";
			}else{
				$bet2 = "betid = ".$last2bets[$f]['id'];
			}
			$betid2 = $betid2.$bet2;
		}
		$correct1 = $this->Userbets_obj
		->where("userid = $url_userid && status = 160 && ($betid1)")
		->order("id desc")
		->count();
		$correct2 = $this->Userbets_obj
		->where("userid = $url_userid && status = 160 && ($betid2)")
		->order("id desc")
		->count();
  		$field1 = count($last1bets);
        $percent1 = floor(($correct1/$field1)*100).'%';
  		$field2 = count($last2bets);
        $percent2 = floor(($correct2/$field2)*100).'%';
		$currency=M()->query("select fget_my15k_points($url_userid)");
	    $this->assign('currency',$currency[0]['fget_my15k_points('.$url_userid.')']);
	    $this->assign('off',0);
	    $this->assign('monday',$monday);
	    $this->assign('sunday',$sunday);
	    $this->assign('field',$field);
	    $this->assign('people',$people);
	    $this->assign('correct',$correct);
	    $this->assign('contacts',$contacts);
	    $this->assign('record',$record);
	    $this->assign('timeday',$timeday);
	    $this->assign('tuesday',$tuesday);
	    $this->assign('percent1',$percent1);
	    $this->assign('percent2',$percent2);
	    $this->assign('userid',$url_userid);
       	$this->display();

	}
    public function barter_post() {
    	$monday=$_POST['monday'];
		$sunday=$_POST['sunday'];
		$url_userid=$_POST['userid'];
		$record = $this->Userbets_obj
		->where("userid = $url_userid && betid=-101 && date(created) = '$sunday'")
		->order("id desc")
		->find();
		if(!$record){
			$userbets['betid'] = -101;
		    $userbets['betoption'] = $_POST['telephone'];
	        $userbets['points'] = $_POST['currency'];
	        $userbets['odds'] = 1;
	        $userbets['pointtype'] = 15000;
	        $userbets['status'] = 140;
	        $userbets['description'] = $monday.'至'.$sunday.'猜中'.$_POST['percent'].'场次，兑换话费'.$_POST['calls'].'元[award_101]';
	        $userbets['points_expire'] = date("Y-m-d H:i:s");
	        $userbets['created'] = $_POST['sunday'].' 23:59:59';
	        $userbets['userid'] = $_POST['userid'];
			if($this->Userbets_obj->add($userbets)){
			    $this -> error('申请兑换成功！');
			}
		}else{
			$this -> error('您已申请过兑换，请耐心等待！');
	    }
    }
}
