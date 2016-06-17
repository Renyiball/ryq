<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-11-03
	描述：赛程更新
**********************************/
class FixturesAction extends AppframeAction {
	protected $Tmatchinfos_obj,$Userroles_obj,$Playerscores_obj,$Userdetails_obj,$Teams_obj,$Users_obj;
	function _initialize() {
		parent::_initialize();
		$this->Tmatchinfos_obj = D("Tmatchinfos");
		$this->Userroles_obj = D("Userroles");
		$this->Playerscores_obj = D("Playerscores");
		$this->Userdetails_obj = D("Userdetails");
		$this->Teams_obj = D("Teams");
		$this->Users_obj = D("Users");
	}
    public function index() {
    	$url_matchid = @$_GET['matchid'] ? $_GET['matchid'] : 0;
		$url_teamid = @$_GET['teamid'] ? $_GET['teamid'] : 0;
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if(empty($url_matchid)){
	   		$out204 = array('ret'=>'No content','code'=>204,'info'=>'请传入所属比赛ID！');
	 	}
		if(empty($url_teamid)){
	   		$out203 = array('ret'=>'No content','code'=>203,'info'=>'请传入球队ID！');
	 	}
		if(empty($url_userid)){
	    	$out401 = array('ret'=>'Unauthorized','code'=>401,'info'=>'详情请登录后再来查看！');
	 	}
		if($url_matchid && $url_teamid && $url_userid){
			//队长-管理权限验证
			$tmatchinfos = $this->Tmatchinfos_obj
			->where("id = $url_matchid")
			->order("id desc")
			->find();
			$teamid1 = $tmatchinfos['teamid1'];
			$teamid2 = $tmatchinfos['teamid2'];
			if($url_teamid == $teamid1){
				$interviewer = $this->Userroles_obj
				->where("relatedtype = 10 && relatedID = $teamid1 && relatedUserID = $url_userid")
				->order("roleTypeID desc")
				->find();
				$userroles = $this->Userroles_obj
				->where("relatedtype = 10 && relatedID = $teamid1")
				->order("play_number asc")
				->select();
			}
			if($url_teamid == $teamid2){
				$interviewer = $this->Userroles_obj
				->where("relatedtype = 10 && relatedID = $teamid2 && relatedUserID = $url_userid")
				->order("roleTypeID desc")
				->find();
				$userroles = $this->Userroles_obj
				->where("relatedtype = 10 && relatedID = $teamid2")
				->order("play_number asc")
				->select();
			}
			if($url_teamid != $teamid1 && $url_teamid != $teamid2){
			   	$out404 = array('ret'=>'Not found','code'=>404,'info'=>'未找到-球队ID不匹配');
			}
			for($r=0;$r<count($userroles);$r++){
				if($r<count($userroles)-1){
					$roles = "userroleid = ".$userroles[$r]['id']." || ";
					$uid = "id = ".$userroles[$r]['relatedUserID']." || ";
				}else{
					$roles = "userroleid = ".$userroles[$r]['id']."";
					$uid = "id = ".$userroles[$r]['relatedUserID']."";
				}
				$uroles = $uroles.$roles;
				$userid = $userid.$uid;
			}
			if($uroles){
				$uroles = '&&('.$uroles.')';
			}
		$playerscores = $this->Playerscores_obj
		->where("matchconstid = '$url_matchid' $uroles")
		->order("id desc")
		->select();
		$users = $this->Users_obj
		->where("$userid")
		->order("id desc")
		->select();
		$this->assign("playerscores",$playerscores);
		$this->assign("users",$users);
		$this->assign("matchid",$url_matchid);
		$this->assign("teamid",$url_teamid);
		$this->assign("userid",$url_userid);
		$this->assign("interviewer",$interviewer);
		$this->assign("userroles",$userroles);
		$this->assign("out404",$out404);
		}else{
			$this->assign("out203",$out203);
			$this->assign("out204",$out204);
			$this->assign("out401",$out401);
		}
       	$this->display();
	}
	public function select(){
		$url_matchid = @$_GET['matchid'] ? $_GET['matchid'] : 0;
		$url_teamid = @$_GET['teamid'] ? $_GET['teamid'] : 0;
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if(empty($url_teamid)){
	   		$out203 = array('ret'=>'No content','code'=>203,'info'=>'请传入球队ID！');
	 	}
			$userroles = $this->Userroles_obj
			->where("relatedtype = 10 && relatedID = $url_teamid && roleTypeID<20100")
			->order("play_number asc")
			->select();
			for($u=0;$u<count($userroles);$u++){
				if($u<count($userroles)-1){
					$uid = "id = ".$userroles[$u]['relatedUserID']." || ";
				}else{
					$uid = "id = ".$userroles[$u]['relatedUserID']."";
				}
				$usersid = $usersid.$uid;
			}
			$users = $this->Users_obj
			->where("$usersid")
			->order("id desc")
			->select();
			$this->assign("userroles",$userroles);
			$this->assign("users",$users);
		if(IS_POST){
			if($_POST['uid']){
				$this->success("选择队员成功",U("Fixtures/add",array('matchid'=>$url_matchid,
																  'teamid'=>$url_teamid,
																  'userid'=>$url_userid,
																  'uid'=>$_POST['uid'],
																  'uname'=>$_POST['uname'])));
			}else{
				$this->error("请选择球员！");
			}
		}
       	$this->display();
	}
	public function add(){
		$url_matchid = @$_GET['matchid'] ? $_GET['matchid'] : 0;
		$url_teamid = @$_GET['teamid'] ? $_GET['teamid'] : 0;
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if(empty($url_matchid)){
	   		$out204 = array('ret'=>'No content','code'=>204,'info'=>'请传入所属比赛ID！');
	 	}
		if(empty($url_teamid)){
	   		$out203 = array('ret'=>'No content','code'=>203,'info'=>'请传入球队ID！');
	 	}
		if(empty($url_userid)){
	    	$out401 = array('ret'=>'Unauthorized','code'=>401,'info'=>'详情请登录后再来查看！');
	 	}
		if($url_matchid && $url_teamid && $url_userid){
			//队长-管理权限验证
			$tmatchinfos = $this->Tmatchinfos_obj
			->where("id = $url_matchid")
			->order("id desc")
			->find();
			$teamid1 = $tmatchinfos['teamid1'];
			$teamid2 = $tmatchinfos['teamid2'];
			if($url_teamid == $teamid1){
				$is_ok = $this->Userroles_obj
				->where("relatedtype = 10 && relatedID = $teamid1 && relatedUserID = $url_userid && roleTypeID>20100 && 20500>roleTypeID")
				->order("roleTypeID desc")
				->select();
				if($is_ok){
					//获取队员
					$userroles = $this->Userroles_obj
					->where("relatedtype = 10 && relatedID = $teamid1")
					->order("play_number asc")
					->select();
				}else{
			   		$out403 = array('ret'=>'It has banned','code'=>403,'info'=>'已禁止访问-您不是队长？');
				}
			}
			if($url_teamid == $teamid2){
				$is_ok = $this->Userroles_obj
				->where("relatedtype = 10 && relatedID = $teamid2 && relatedUserID = $url_userid && roleTypeID>20100 && 20500>roleTypeID")
				->order("roleTypeID desc")
				->select();
				if($is_ok){
					//获取队员
					$userroles = $this->Userroles_obj
					->where("relatedtype = 10 && relatedID = $teamid2")
					->order("play_number asc")
					->select();
				}else{
			   		$out403 = array('ret'=>'It has banned','code'=>403,'info'=>'已禁止访问-您不是队长？');
				}
			}
			if($url_teamid != $teamid1 && $url_teamid != $teamid2){
			   	$out404 = array('ret'=>'Not found','code'=>404,'info'=>'未找到-球队ID不匹配');
			}
			$userdetails = $this->Userdetails_obj
			->order("id desc")
			->select();
			for($u=0;$u<count($userroles);$u++){
				if($u<count($userroles)-1){
					$uid = "id = ".$userroles[$u]['relatedUserID']." || ";
				}else{
					$uid = "id = ".$userroles[$u]['relatedUserID']."";
				}
				$usersid = $usersid.$uid;
			}
			$users = $this->Users_obj
			->where("$usersid")
			->order("id desc")
			->select();
			$teamuserid =$_GET['uid'];
			$teamusername =$_GET['uname'];
			$this->assign("userdetails",$userdetails);
			$this->assign("userroles",$userroles);
			$this->assign("users",$users);
			$this->assign("matchid",$url_matchid);
			$this->assign("teamid",$url_teamid);
			$this->assign("userid",$url_userid);
			$this->assign("teamuserid",$teamuserid);
			$this->assign("teamusername",$teamusername);
			$this->assign("out403",$out403);
			$this->assign("out404",$out404);
		}else{
			$this->assign("out203",$out203);
			$this->assign("out204",$out204);
			$this->assign("out401",$out401);
		}
       	$this->display();
	}
	public function add_post(){
		if(!isset($_POST)){ $this->error("<br/>非法访问");}
		if(!$_POST['teamuserid']){ $this->error("<br/>请选择球员");}
		$created = date("Y-m-d H:i:s");
		$matchid = $_POST['matchid'];
		$teamid = $_POST['teamid'];
		$userid = $_POST['userid'];
		$tmatchinfos = $this->Tmatchinfos_obj
		->where("id = $matchid")
		->order("id desc")
		->find();
		if($tmatchinfos['teamid1'] == $teamid){
			$score=$tmatchinfos['score1'];
		}else{
			$score=$tmatchinfos['score2'];
		}
		if(!$tmatchinfos['score1'] || !$tmatchinfos['score2']){$this->error("<br/>请先更新比分信息!");}
		if($_POST['enter'] > $score ){$this->error("<br/>整场比赛都没进这么多球好吗?");}
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID = $teamid")
		->order("play_number asc")
		->select();
		for($u=0;$u<count($userroles);$u++){
			if($u<count($userroles)-1){
				$roles = "userroleid = ".$userroles[$u]['id']." || ";
			}else{
				$roles = "userroleid = ".$userroles[$u]['id'];
			}
			$rolesid = $rolesid.$roles;
		}
		if($rolesid){
			$rolesid = '&&('.$rolesid.')';
		}
		$player = $this->Playerscores_obj
		->where("matchconstid = '$matchid' $rolesid")
		->order("id desc")
		->select();
		for($p=0;$p<count($player);$p++){
			$goals = $player[$p]['goals'];
			$goal = $goal+$goals;
		}
		$goal=$goal+$_POST['enter'];
		if($goal > $score ){$this->error("<br/>整场比赛都没进这么多球好吗?");}
		$teamuserid =$_POST['teamuserid'];
		if($_POST['enter'] == ''){$_POST['enter'] =0;}
		if($_POST['yellow'] == ''){$_POST['yellow'] =0;}
		if($_POST['red'] == ''){$_POST['red'] =0;}
		if($_POST['enter'] < 0 || $_POST['yellow'] < 0 || $_POST['red'] < 0){ $this->error("<br/>请核对数据");}
		if($_POST['enter'] == 0 && $_POST['yellow'] == 0 && $_POST['red'] == 0){ $this->error("<br/>请核对数据");}
		if($_POST['enter'] > 99 ||   $_POST['yellow'] > 9 || $_POST['red'] > 9){ $this->error("<br/>你不要骗我");}
		$date['userroleid'] = $teamuserid;
		$date['matchconstid'] = $matchid;
		$date['goals'] = $_POST['enter'];
		$date['yellow_card'] = $_POST['yellow'];
		$date['red_card'] = $_POST['red'];
		$date['description'] = $_POST['teamusername'];
		$date['created'] = $created;
        $date['userid'] = $userid;
		$playerscores = $this->Playerscores_obj
		->where("userroleid = $teamuserid && matchconstid = '$matchid'")
		->order("id desc")
		->select();
		if($playerscores){
		    $this -> error('<br/>此人本场的比赛信息已存在，请在返回修改');
		}else{
			if($this->Playerscores_obj -> add($date))
			{
				$this -> success('<br/>比赛信息更新成功!',U("Fixtures/index",array('matchid'=>$matchid,'teamid'=>$teamid,'userid'=>$userid)));
			}else{
			    $this -> error('<br/>比赛信息更新失败');
			}
		}
	}
	public function edit(){
		$url_matchid = @$_GET['matchid'] ? $_GET['matchid'] : 0;
		$url_teamid = @$_GET['teamid'] ? $_GET['teamid'] : 0;
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if(empty($url_matchid)){
	   		$out204 = array('ret'=>'No content','code'=>204,'info'=>'请传入所属比赛ID！');
	 	}
		if(empty($url_teamid)){
	   		$out203 = array('ret'=>'No content','code'=>203,'info'=>'请传入球队ID！');
	 	}
		if(empty($url_userid)){
	    	$out401 = array('ret'=>'Unauthorized','code'=>401,'info'=>'详情请登录后再来查看！');
	 	}
		if($url_matchid && $url_teamid && $url_userid){
			//队长-管理权限验证
			$tmatchinfos = $this->Tmatchinfos_obj
			->where("id = $url_matchid")
			->order("id desc")
			->find();
			$teamid1 = $tmatchinfos['teamid1'];
			$teamid2 = $tmatchinfos['teamid2'];
			if($url_teamid == $teamid1){
				$is_ok = $this->Userroles_obj
				->where("relatedtype = 10 && relatedID = $teamid1 && relatedUserID = $url_userid && roleTypeID>20100 && 20500>roleTypeID")
				->order("roleTypeID desc")
				->select();
				if(!$is_ok){
			   		$out403 = array('ret'=>'It has banned','code'=>403,'info'=>'已禁止访问-您不是队长？');
				}
			}
			if($url_teamid == $teamid2){
				$is_ok = $this->Userroles_obj
				->where("relatedtype = 10 && relatedID = $teamid2 && relatedUserID = $url_userid && roleTypeID>20100 && 20500>roleTypeID")
				->order("roleTypeID desc")
				->select();
				if(!$is_ok){
			   		$out403 = array('ret'=>'It has banned','code'=>403,'info'=>'已禁止访问-您不是队长？');
				}
			}
			if($url_teamid != $teamid1 && $url_teamid != $teamid2){
			   	$out404 = array('ret'=>'Not found','code'=>404,'info'=>'未找到-球队ID不匹配');
			}
			$this->assign("out403",$out403);
			$this->assign("out404",$out404);
		}else{
		$this->assign("out203",$out203);
		$this->assign("out204",$out204);
		$this->assign("out401",$out401);
		}
		if(isset($_GET['id'])){
	    	$id = $_GET['id'];
			$players = $this->Playerscores_obj
			->where("id = '$id'")
			->order("id desc")
			->find();
			$this->assign("player",$players);
			$this->assign("matchid",$url_matchid);
			$this->assign("teamid",$url_teamid);
			$this->assign("userid",$url_userid);
		}
       	$this->display();
	}
	public function edit_post(){
		if(!isset($_POST)){ $this->error("非法访问");}
		$created = date("Y-m-d H:i:s");
		$matchid = $_POST['matchid'];
		$teamid = $_POST['teamid'];
		$userid = $_POST['userid'];
		$id = $_POST['playerid'];
		$tmatchinfos = $this->Tmatchinfos_obj
		->where("id = $matchid")
		->order("id desc")
		->find();
		if($tmatchinfos['teamid1'] == $teamid){
			$score=$tmatchinfos['score1'];
		}else{
			$score=$tmatchinfos['score2'];
		}
		if(!$tmatchinfos['score1']|| !$tmatchinfos['score2']){$this->error("<br/>请先更新比分信息!");}
		if($_POST['enter'] > $score ){$this->error("<br/>整场比赛都没进这么多球好吗?");}
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID = $teamid")
		->order("play_number asc")
		->select();
		for($u=0;$u<count($userroles);$u++){
			if($u<count($userroles)-1){
				$roles = "userroleid = ".$userroles[$u]['id']." || ";
			}else{
				$roles = "userroleid = ".$userroles[$u]['id'];
			}
			$rolesid = $rolesid.$roles;
		}
		if($rolesid){
			$rolesid = '&&('.$rolesid.')';
		}
		$player = $this->Playerscores_obj
		->where("id !=$id && matchconstid = '$matchid' $rolesid")
		->order("id desc")
		->select();
		for($p=0;$p<count($player);$p++){
			$goals = $player[$p]['goals'];
			$goal = $goal+$goals;
		}
		$goal=$goal+$_POST['enter'];
		if($goal > $score ){$this->error("<br/>整场比赛都没进这么多球好吗?");}
		if($_POST['enter'] == ''){$_POST['enter'] =0;}
		if($_POST['yellow'] == ''){$_POST['yellow'] =0;}
		if($_POST['red'] == ''){$_POST['red'] =0;}
		if($_POST['enter'] < 0 || $_POST['yellow'] < 0 || $_POST['red'] < 0){ $this->error("<br/>请核对数据");}
		if($_POST['enter'] == 0 && $_POST['yellow'] == 0 && $_POST['red'] == 0){ $this->error("<br/>请核对数据");}
		if($_POST['enter'] > 99 ||   $_POST['yellow'] > 9 || $_POST['red'] > 9){ $this->error("<br/>你不要骗我");}
    	if(isset($_POST)){
			$date['goals'] = $_POST['enter'];
			$date['yellow_card'] = $_POST['yellow'];
			$date['red_card'] = $_POST['red'];
			$date['created'] = $created;
	        $date['userid'] = $userid;
			if($this->Playerscores_obj -> where("id = $id")->data($date)->save()){
				$this -> success('<br/>球员状况更新成功!',U("Fixtures/index",array('matchid'=>$matchid,'teamid'=>$teamid,'userid'=>$userid)));
			}else{
				$this -> error('<br/>球员状况更新失败');
			}
		}
	}
}
