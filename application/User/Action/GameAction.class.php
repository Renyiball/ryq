<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-11-25
	描述：比赛
**********************************/
class GameAction extends MemberbaseAction {
	protected $Matchinfos_obj,$Bets_obj,$Actioninfos_obj,$Teams_obj,$Userroles_obj,$Typeconfigs_obj,$Userdetails_obj,$Playerscores_obj,$Userbets_obj;
    function _initialize() {
		parent::_initialize();
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Bets_obj = D("Bets");
		$this->Actioninfos_obj = D("Actioninfos");
		$this->Teams_obj = D("Teams");
		$this->Userroles_obj = D("Userroles");
		$this->Typeconfigs_obj = D("Typeconfigs");
		$this->Userdetails_obj = D("Userdetails");
		$this->Playerscores_obj = D("Playerscores");
		$this->Userbets_obj = D("Userbets");
	}
	public function index(){
		$role=$_SESSION['user']['role_id'];
		if($role!=7){
    		$this->expert();
		}else{
    		$this->amateur();
		}
    }
	public function amateur(){
		$role_id=$_SESSION['user']['role_id'];
		$constid = $_SESSION['user']['constid'];
		if($role_id == 7 && $constid){
			$const = "constid = '".$constid."' && ";
			$match = "matchtype like '".$constid."%' && ";
		}
		if($role_id == 7 && !$constid){
			$this->error("您不属于任何比赛，已被请出！",U("Index/logout"));
		}
		$action = $this->Actioninfos_obj
		->where("$const parentid = 0 && subtype = 20000")
		->order("id asc")
		->select();
		if($action[0]['status'] != 100 && $role_id != 1){
			$this->error("赛事已关闭，您已退出！",U("Index/logout"));
		}
		$datetime = date('Y-m-d H:i:s');
		$count=$this->Matchinfos_obj-> where("$match (length(matchtype)=10) && matchdatetime < '$datetime' && status = 0 ")->count();
		$page = $this->page($count, 10);
		$items = $this->Matchinfos_obj
		->where("$match (length(matchtype)=10) && matchdatetime < '$datetime' && status = 0 ")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$t=0;
		for($i=0;$i<count($items);$i++){
			$teamids[$t] = $items[$i]['teamid1'];
			$t++;
			$teamids[$t] = $items[$i]['teamid2'];
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
		->where("$team")
		->order("id desc")
		->select();
		$this->assign("action",$action[0]);
		$this->assign("role_id",$role_id);
		$this->assign("lists",$items);
		$this->assign("teams",$teams);
		$this->assign("Page", $page->show('Admin'));
		$this->display("game:amateur");
	}
	public function expert(){
		$role_id=$_SESSION['user']['role_id'];
		$constid = $_SESSION['user']['constid'];
		if($role_id > 7){
			$this->error("您的权限不足，已被请出！",U("Index/logout"));
		}
		$datetime = date('Y-m-d H:i:s');
		$count=$this->Matchinfos_obj-> where("(length(matchtype)=7) && matchdatetime < '$datetime' && status = 0 ")->count();
		$page = $this->page($count, 10);
		$items = $this->Matchinfos_obj
		->where("(length(matchtype)=7) && matchdatetime < '$datetime' && status = 0 ")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$t=0;
		for($i=0;$i<count($items);$i++){
			$teamids[$t] = $items[$i]['teamid1'];
			$t++;
			$teamids[$t] = $items[$i]['teamid2'];
			$t++;
		}
		$teamids = array_merge(array_unique($teamids));
		for($i=0;$i<count($teamids);$i++){
			if($i<count($teamids)-1){
				$teamid = "constid ='".$teamids[$i]."' || ";
			}else{
				$teamid = "constid ='".$teamids[$i]."'";
			}
			$team =$team.$teamid;
		}
		$teams = $this->Teams_obj
		->where("$team")
		->order("id desc")
		->select();
		$this->assign("lists",$items);
		$this->assign("teams",$teams);
		$this->assign("Page", $page->show('Admin'));
		$this->display("game:expert");
    }
    public function edit(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$infos = $this->Matchinfos_obj
		->where("id = $id")
		->find();
		$teamtype = substr($infos['matchtype'],0,5);
		$teamname = $this->Teams_obj
		->where("teamdesc = '球队分类' && teamtype = '$teamtype'")
		->order("id desc")
		->find();
		$teamid1 = $infos['teamid1'];
		$teamid2 = $infos['teamid2'];
		if($teamname){
			$teams = $this->Teams_obj
			->where("constid = '$teamid1' || constid = '$teamid2'")
			->order("id desc")
			->select();
			$this->assign("teamname",$teamname);
			$this->assign("teams",$teams);
		}else{
			$matchtype = substr($infos['matchtype'],0,8);
			$action = $this->Actioninfos_obj
			->where("parentid = 0 && status = 100 && subtype = 20000 && constid = '$matchtype'")
			->order("id asc")
			->find();
			$teams = $this->Teams_obj
			->where("constid = '$teamid1' || constid = '$teamid2' || teamnumber = '$teamid1' || teamnumber = '$teamid2'")
			->order("id desc")
			->select();
			$this->assign("action",$action);
			$this->assign("teams",$teams);
		}
//		echo json_encode($teamname);
		$matchconstid = $infos['matchconstid'];
		$bets = $this->Bets_obj
		->where("matchinfoid ='$matchconstid'")
		->order("id desc")
		->select();
		$datetime = date('Y-m-d H:i:s');
		$this->assign("datetime",$datetime);
    	$this->assign('infos',$infos);
    	$this->assign('bets',$bets[0]);
    	$this->display();
		}else{
			$this->error($this->Matchinfos_obj->getError());
		}
    }
	public function edit_post(){
		if($_SESSION['user']['userid']>0){
			$userid = $_SESSION['user']['userid'];
		}else{
			$userid = get_current_userid();
		}
    	if(isset($_POST)){
			$score1 = $_POST['score1'];
			$score2 = $_POST['score2'];
			$betsmid = $_POST['bets_mid'];
			$betsid = $_POST['bets_id'];
			$matchid = $_POST['info_id'];
			if($score1 > 99 ||  $score2 > 99 || $score1 < 0 ||  $score2 < 0){ $this->error("你不要骗我");}
    		if( $score1 !='' && $score2 !=''){
		        $match['score1'] = $score1;
		        $match['score2'] = $score2;
			    $match['status'] = 160;
		        $match['userid'] = $userid;
				if($_POST['odds_w'] !='' && $_POST['odds_d'] !='' && $_POST['odds_l'] !='' ){
					$bets['status'] = 160;
				    $bets['expiredate'] = $_POST['start_time'];
			        $bets['userid'] = $userid;
					$bet = $this->Bets_obj -> where("matchinfoid = '$betsmid'")->data($bets)->save();
					$ubet=array();
					$userbet = $this->Userbets_obj->where("betid = $betsid")->order("id desc")->select();
					for($i=0;$i<count($userbet);$i++){
						$ubid = $userbet[$i]['id'];
						if($score1 > $score2){
							if($userbet[$i]['betoption'] == 'w'){
								$ubet['status'] = 160;
							}else{
								$ubet['status'] = 150;
							}
							$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
							$ubet=array();
						}
						if($score1 == $score2){
							if($userbet[$i]['betoption'] == 'd'){
								$ubet['status'] = 160;
							}else{
								$ubet['status'] = 150;
							}
							$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
							$ubet=array();
						}
						if($score1 < $score2){
							if( $userbet[$i]['betoption'] == 'l'){
								$ubet['status'] = 160;
							}else{
								$ubet['status'] = 150;
							}
							$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
							$ubet=array();
						}
					}
				}
				$this->Matchinfos_obj -> where("id = $matchid")->data($match)->save() ;
				$this -> success('修改成功!', U("game/index"));
    		}else{
				$this->error("请填写比分！");     
    		}
		} else {
			$this->error("非法提交！");                              
		}
    }


    public function update() {
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$infos = $this->Matchinfos_obj
		->where("id = $id")
		->find();
		$teamid1 =$infos['teamid1'];
		$teamid2 =$infos['teamid2'];
		$teams = $this->Teams_obj
		->where("constid = '$teamid1' || constid = '$teamid2' || teamnumber = '$teamid1' || teamnumber = '$teamid2' ")
		->order("id desc")
		->select();
		$id1 = $teams[0]['id'];
		$id2 = $teams[1]['id'];
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10  && roleTypeID < 20000 && relatedID = '$id1' || relatedID = '$id2'  && roleTypeID < 20000")
		->order("play_number asc")
		->select();
		$typeconfigs = $this->Typeconfigs_obj
		->where("typeGroup = 9 && typeid != 0")
		->order("id desc")
		->select();
		$userdetails = $this->Userdetails_obj
		->where("realname != ''")
		->order("id desc")
		->select();
		$matchconstid = $infos['matchconstid'];
		$count=$this->Playerscores_obj-> where("matchconstid = '$matchconstid' ")->count();
		$page = $this->page($count, 10);
		$items = $this->Playerscores_obj
		->where("matchconstid = '$matchconstid'")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
    	$this->assign('infos',$infos);
		$this->assign("teams",$teams);
		$this->assign("userroles",$userroles);
		$this->assign("typeconfigs",$typeconfigs);
		$this->assign("userdetails",$userdetails);
		$this->assign("lists",$items);
		$this->assign("Page", $page->show('Admin'));
    	$this->display();
		}else{
			$this->error($this->Matchinfos_obj->getError());
		}
    }
    public function update_post() {    	
	$created = date("Y-m-d H:i:s");
		if($_SESSION['user']['userid']>0){
			$userid = $_SESSION['user']['userid'];
		}else{
			$userid = get_current_userid();
		}
    		if($_POST['twoclass'] == '0' && $_POST['threeclass'] == '0'){ $this->error("请选择球员");}
    		if($_POST['enter'] == ''){$_POST['enter'] =0;}
    		if($_POST['yellow'] == ''){$_POST['yellow'] =0;}
    		if($_POST['red'] == ''){$_POST['red'] =0;}
			if($_POST['enter'] <= 0 && $_POST['yellow'] <= 0 && $_POST['red'] <= 0){ $this->error("不能提交空数据");}
			if($_POST['enter'] > 99 ||   $_POST['yellow'] > 9 || $_POST['red'] > 9){ $this->error("你不要骗我");}
    		$oneclass = $_POST['oneclass'];
    		$twoclass = $_POST['twoclass'];
    		$threeclass = $_POST['threeclass'];
			//$this->error(json_encode($oneclass));
    		list($oneclass1,$oneclass2,$oneclass3) = split ('[,]', $oneclass);
    		list($twoclass1,$twoclass2,) = split ('[,]', $twoclass);
    		list($threeclass1,$threeclass2,) = split ('[,]', $threeclass);
			if($oneclass3 == 1){ $userroleid = $twoclass1;}
			if($oneclass3 == 2){ $userroleid = $threeclass1;}
			if($oneclass3 == 1){ $description = $twoclass2;}
			if($oneclass3 == 2){ $description = $threeclass2;}
    	if(isset($_POST)){
    		$date['userroleid'] = $userroleid;
			$date['matchconstid'] = $oneclass1;
			$date['goals'] = $_POST['enter'];
			$date['yellow_card'] = $_POST['yellow'];
			$date['red_card'] = $_POST['red'];
			$date['description'] = $oneclass2.' - '.$description;
			$date['created'] = $created;
	        $date['userid'] = $userid;
    	}
			$playerscores = $this->Playerscores_obj
			->where("userroleid = $userroleid && matchconstid = '$oneclass1'")
			->order("id desc")
			->select();
		if($playerscores){
			$this -> error('此人本场的比赛信息已存在。请在下方修改更新');
		}else{
			if($this->Playerscores_obj -> add($date))
			{
				$this -> success('比赛信息更新成功!');
			}else{
			    $this -> error('比赛信息更新失败');
			}
		}
    }
	public function player(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$players = $this->Playerscores_obj
		->where("id = '$id'")
		->order("id desc")
		->select();
		$this->assign("player",$players[0]);
    	$this->display();
		}
	}
	public function player_post(){
		if($_SESSION['user']['userid']>0){
			$userid = $_SESSION['user']['userid'];
		}else{
			$userid = get_current_userid();
		}
		$created = date("Y-m-d H:i:s");
		if($_POST['enter'] == ''){$_POST['enter'] =0;}
		if($_POST['yellow'] == ''){$_POST['yellow'] =0;}
		if($_POST['red'] == ''){$_POST['red'] =0;}
		if($_POST['enter'] <= 0 && $_POST['yellow'] <= 0 && $_POST['red'] <= 0){ $this->error("不能提交空数据");}
		if($_POST['enter'] > 99 ||   $_POST['yellow'] > 9 || $_POST['red'] > 9){ $this->error("你不要骗我");}
    	if(isset($_POST)){
			$id = $_POST['playerid'];
			$date['goals'] = $_POST['enter'];
			$date['yellow_card'] = $_POST['yellow'];
			$date['red_card'] = $_POST['red'];
			$date['created'] = $created;
	        $date['userid'] = $userid;
			if($this->Playerscores_obj -> where("id = $id")->data($date)->save()){
				$matchconstid = $_POST['matchconstid'];
				$infos = $this->Matchinfos_obj
				->where("matchconstid = '$matchconstid'")
				->limit(1)
				->order("id desc")
				->select();
				$this -> success('球员状况更新成功!',U("game/update",array('id'=>$infos[0]['id'])));
			}else{
				$this -> error('球员状况更新失败');
			}
		}
	}
}
