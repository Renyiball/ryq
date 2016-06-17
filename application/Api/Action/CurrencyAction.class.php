<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-01-19
	描述：球币记录
**********************************/
class CurrencyAction extends AppframeAction{
	protected $Userbets_obj,$Typeconfigs_obj,$Useractions_obj,$Bets_obj,$Matchinfos_obj,$Teams_obj;
	function _initialize() {
		parent::_initialize();
		$this->Userbets_obj = D("Userbets");
		$this->Typeconfigs_obj = D("Typeconfigs");
		$this->Useractions_obj = D("Useractions");
		$this->Bets_obj = D("Bets");
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Teams_obj = D("Teams");
	}
	public function _arr(){
		$typearr  = array(
						array("id"=>"0",	"name"=> "登陆/签到"),
						array("id"=>"1",	"name"=> "发帖/回复"),
						array("id"=>"2",	"name"=> "猜球记录"),
						array("id"=>"3",	"name"=> "话费兑换"),
						array("id"=>"4",	"name"=> "购物消费"),
						array("id"=>"5",	"name"=> "活动获得")
					);
		return $typearr;
	}
	public function index(){
    	$this->_list();
    }
	
	public function tclass($url_tclass,$url_userid){
		if(!$url_tclass){
			$types = array('betid'=>0,'pointtype'=>15000,'userid'=>$url_userid);
		}
		if($url_tclass==1){
			$configs = $this->Typeconfigs_obj->where("typeID < -101 && typeGroup = 22")->select();
			for($i=0;$i<count($configs);$i++){
				$typeid[$i] = $configs[$i]['typeID'];
			}
			$types = array('betid'=>array("in",array_merge(array_unique($typeid))),'pointtype'=>15000,'userid'=>$url_userid);
		}
		if($url_tclass==2){
			$types = array('betid>0','pointtype'=>15000,'userid'=>$url_userid);
		}
		if($url_tclass==3){
			$types = array('betid'=>-101,'pointtype'=>15000,'userid'=>$url_userid);
		}
		if($url_tclass==4){
			$typeid[0] = 40050;
			$typeid[1] = 40051;
			$types = array('parentID>0','actionType'=>array("in",array_merge(array_unique($typeid))),'userid'=>$url_userid);
		}
		if($url_tclass==5){
			$configs = $this->Typeconfigs_obj->where("typeID <= -200 && typeGroup = 21")->select();
			for($i=0;$i<count($configs);$i++){
				$typeid[$i] = $configs[$i]['typeID'];
			}
			$types = array('betid'=>array("in",array_merge(array_unique($typeid))),'pointtype'=>15000,'userid'=>$url_userid);
		}
		return $types;
	}
	public function _list(){
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if(empty($url_userid) || $url_userid < 0){
	    	$out401 = array('ret'=>'Unauthorized','code'=>401,'info'=>'详情请登录后再来查看！');
			$this->assign("out401",$out401);
	 	}
		$types = $this->tclass($_POST['typeclass'],$url_userid);
		if($_POST['typeclass'] == 4 ){
			$count = $this->Useractions_obj
			->where($types)
			->order("id desc")
			->count();
			$page = $this->page($count, 20);
			$userbets = $this->Useractions_obj
			->field('parentID,actionType,amount,created,userid')
			->where($types)
			->order("id desc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			for($i=0;$i<count($userbets);$i++){
				$amount = $userbets[$i]['amount']*100;
				if($userbets[$i]['actionType'] == 40051){$status = '退回';}
				if($userbets[$i]['actionType'] == 40050){$status = '<b style="color: #F16E1A;">消费</b>';}
				$data[$i]['explanation'] ='购物球币消费-订单号：'.$userbets[$i]['parentID'];
				$data[$i]['created'] =substr($userbets[$i]['created'],0,16);
				$data[$i]['money'] = $amount;
				$data[$i]['status'] = $status;
			}
		}else{
			$count = $this->Userbets_obj
			->where($types)
			->order("id desc")
			->count();
			$page = $this->page($count, 20);
			$userbets = $this->Userbets_obj
			->field('betid,points,odds,status,description,created')
			->where($types)
			->order("id desc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			if($_POST['typeclass']==2){
				for($i=0;$i<count($userbets);$i++){
					$ubet[$i] = $userbets[$i]['betid'];
				}
				$bets = $this->Bets_obj
				->field('id,matchinfoid')
				->where(array('id'=>array("in",array_merge(array_unique($ubet)))))
				->order("id desc")
				->select();
				for($i=0;$i<count($bets);$i++){
					$mbet[$i] = $bets[$i]['matchinfoid'];
				}
				$matchinfos = $this->Matchinfos_obj
				->field('teamid1,teamid2,matchconstid')
				->where(array('matchconstid'=>array("in",array_merge(array_unique($mbet)))))
				->order("id desc")
				->select();
				$t=0;
				for($i=0;$i<count($matchinfos);$i++){
					$teamids[$t] = $matchinfos[$i]['teamid1'];
					$t++;
					$teamids[$t] = $matchinfos[$i]['teamid2'];
					$t++;
				}
				$teamids = array_merge(array_unique($teamids));
				for($i=0;$i<count($teamids);$i++){
					if($i<count($teamids)-1){
						$teamid = "constid ='".$teamids[$i]."'|| teamnumber ='".$teamids[$i]."' || ";
					}else{
						$teamid = "constid ='".$teamids[$i]."'|| teamnumber ='".$teamids[$i]."'";
					}
					$team =$team.$teamid;
				}
				$teams = $this->Teams_obj
				->field('teamname,constid,teamnumber')
				->where("$team")
				->order("id desc")
				->select();
				for($i=0;$i<count($userbets);$i++){
					$points = $userbets[$i]['points'];
					$odds = $userbets[$i]['odds'];
					if($userbets[$i]['status'] == 160){
						$money = $points*$odds;}else{
						$money = $points;}
					if($userbets[$i]['status'] == 160){$status = '获得';}
					if($userbets[$i]['status'] == 150){$status = '扣除';}
					if($userbets[$i]['status'] == 140){$status = '<b style="color: #F16E1A;">使用</b>';}
					for($b=0;$b<count($bets);$b++){
						if($userbets[$i]['betid'] == $bets[$b]['id']){
							for($m=0;$m<count($matchinfos);$m++){
								if($bets[$b]['matchinfoid'] == $matchinfos[$m]['matchconstid']){
									$teamids1 = $matchinfos[$m]['teamid1'];
									$teamids2 = $matchinfos[$m]['teamid2'];
									for($t=0;$t<count($teams);$t++){
										if($teams[$t]['constid']== $teamids1 || $teams[$t]['teamnumber']== $teamids1){
											$teamname1 = $teams[$t]['teamname'];
										}
									}
									for($t=0;$t<count($teams);$t++){
										if($teams[$t]['constid']== $teamids2 || $teams[$t]['teamnumber']== $teamids2){
											$teamname2 = $teams[$t]['teamname'];
										}
									}
									$explanation = $teamname1.' VS '.$teamname2;
								}
							}
						}
					}
					$data[$i]['explanation'] = $explanation;
					$data[$i]['created'] =substr($userbets[$i]['created'],0,16);
					$data[$i]['money'] = $money;
					$data[$i]['status'] = $status;
				}
			}else{
				for($i=0;$i<count($userbets);$i++){
					$points = $userbets[$i]['points'];
					$odds = $userbets[$i]['odds'];
					if($userbets[$i]['status'] == 160){
						$money = $points*$odds;}else{
						$money = $points;}
					if($userbets[$i]['status'] == 160){$status = '获得';}
					if($userbets[$i]['status'] == 150){$status = '扣除';}
					if($userbets[$i]['status'] == 140){$status = '<b style="color: #F16E1A;">使用</b>';}
					$data[$i]['explanation'] = explode('[',$userbets[$i]['description'])[0];
					$data[$i]['created'] =substr($userbets[$i]['created'],0,16);
					$data[$i]['money'] = $money;
					$data[$i]['status'] = $status;
				}
			}
		}
		$select = $this->_arr();
	    $this->assign('num',$count);
	    $this->assign('select',$select);
		$this->assign("userbets",$data);
		$this->assign("formpost",$_POST);
		$this->assign('userid',$url_userid);
	    $this->display();
	}
	public function repage(){
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_tclass = @$_GET['tclass'] ? $_GET['tclass'] : 0;
		$types = $this->tclass($url_tclass,$url_userid);
		if($url_tclass== 4){
			$count = $this->Useractions_obj->where($types)->count();
			$page = $this->page($count, 20);
			if($_GET['p']<= ceil($count/20)){
				$userbets = $this->Useractions_obj
				->field('parentID,actionType,amount,userid')
				->where($types)
				->order("id desc")
				->limit($page->firstRow . ',' . $page->listRows)
				->select();
				for($i=0;$i<count($userbets);$i++){
					$amount = $userbets[$i]['amount']*100;
					if($userbets[$i]['actionType'] == 40051){$status = '退回';}
					if($userbets[$i]['actionType'] == 40050){$status = '<b style="color: #F16E1A;">消费</b>';}
					$data[$i]['explanation'] ='购物球币消费-订单号：'.$userbets[$i]['parentID'];
					$data[$i]['created'] =substr($userbets[$i]['created'],0,16);
					$data[$i]['money'] = $amount;
					$data[$i]['status'] = $status;
				}
				if(count($userbets)<20){
					$over = "<tr><td colspan='3' style='text-align: center;'>没有更多信息了...</td></tr>";
					$this->assign("over",$over);
				}
			}else{
				$data='';
			}
		}else{
			$count = $this->Userbets_obj->where($types)->count();
			$page = $this->page($count, 20);
			if($_GET['p']<= ceil($count/20)){
			$userbets = $this->Userbets_obj
			->field('betid,points,odds,status,description,created')
			->where($types)
			->order("id desc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			if($url_tclass==2){
				for($i=0;$i<count($userbets);$i++){
					$ubet[$i] = $userbets[$i]['betid'];
				}
				$bets = $this->Bets_obj
				->field('id,matchinfoid')
				->where(array('id'=>array("in",array_merge(array_unique($ubet)))))
				->order("id desc")
				->select();
				for($i=0;$i<count($bets);$i++){
					$mbet[$i] = $bets[$i]['matchinfoid'];
				}
				$matchinfos = $this->Matchinfos_obj
				->field('teamid1,teamid2,matchconstid')
				->where(array('matchconstid'=>array("in",array_merge(array_unique($mbet)))))
				->order("id desc")
				->select();
				$t=0;
				for($i=0;$i<count($matchinfos);$i++){
					$teamids[$t] = $matchinfos[$i]['teamid1'];
					$t++;
					$teamids[$t] = $matchinfos[$i]['teamid2'];
					$t++;
				}
				$teamids = array_merge(array_unique($teamids));
				for($i=0;$i<count($teamids);$i++){
					if($i<count($teamids)-1){
						$teamid = "constid ='".$teamids[$i]."'|| teamnumber ='".$teamids[$i]."' || ";
					}else{
						$teamid = "constid ='".$teamids[$i]."'|| teamnumber ='".$teamids[$i]."'";
					}
					$team =$team.$teamid;
				}
				$teams = $this->Teams_obj
				->field('teamname,constid,teamnumber')
				->where("$team")
				->order("id desc")
				->select();
				for($i=0;$i<count($userbets);$i++){
					$points = $userbets[$i]['points'];
					$odds = $userbets[$i]['odds'];
					if($userbets[$i]['status'] == 160){
						$money = $points*$odds;}else{
						$money = $points;}
					if($userbets[$i]['status'] == 160){$status = '获得';}
					if($userbets[$i]['status'] == 150){$status = '扣除';}
					if($userbets[$i]['status'] == 140){$status = '<b style="color: #F16E1A;">使用</b>';}
					for($b=0;$b<count($bets);$b++){
						if($userbets[$i]['betid'] == $bets[$b]['id']){
							for($m=0;$m<count($matchinfos);$m++){
								if($bets[$b]['matchinfoid'] == $matchinfos[$m]['matchconstid']){
									$teamids1 = $matchinfos[$m]['teamid1'];
									$teamids2 = $matchinfos[$m]['teamid2'];
									for($t=0;$t<count($teams);$t++){
										if($teams[$t]['constid']== $teamids1 || $teams[$t]['teamnumber']== $teamids1){
											$teamname1 = $teams[$t]['teamname'];
										}
									}
									for($t=0;$t<count($teams);$t++){
										if($teams[$t]['constid']== $teamids2 || $teams[$t]['teamnumber']== $teamids2){
											$teamname2 = $teams[$t]['teamname'];
										}
									}
									$explanation = $teamname1.' VS '.$teamname2;
								}
							}
						}
					}
					$data[$i]['explanation'] = $explanation;
					$data[$i]['created'] =substr($userbets[$i]['created'],0,16);
					$data[$i]['money'] = $money;
					$data[$i]['status'] = $status;
				}
			}else{
				for($i=0;$i<count($userbets);$i++){
					$points = $userbets[$i]['points'];
					$odds = $userbets[$i]['odds'];
					if($userbets[$i]['status'] == 160){
						$money = $points*$odds;}else{
						$money = $points;}
					if($userbets[$i]['status'] == 160){$status = '获得';}
					if($userbets[$i]['status'] == 150){$status = '扣除';}
					if($userbets[$i]['status'] == 140){$status = '<b style="color: #F16E1A;">使用</b>';}
					$data[$i]['explanation'] = explode('[',$userbets[$i]['description'])[0];
					$data[$i]['created'] =substr($userbets[$i]['created'],0,16);
					$data[$i]['money'] = $money;
					$data[$i]['status'] = $status;
				}
			}
			if(count($userbets)<20){
				$over = "<tr><td colspan='3' style='text-align: center;'>没有更多信息了...</td></tr>";
				$this->assign("over",$over);
			}
			}else{
					$data='';
			}
		}
		$this->assign("userbets",$data);
	    $this->display();

	}
}
