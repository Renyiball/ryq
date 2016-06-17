<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-25
	描述：业余比赛
**********************************/
class AmateurAction extends AdminbaseAction {
	protected $Matchinfos_obj,$Bets_obj,$Actioninfos_obj,$Teams_obj,$Userroles_obj,$Userbets_obj,$Typeconfigs_obj,$Userdetails_obj,$Playerscores_obj,$Users_obj,$Action_obj;
    function _initialize() {
		parent::_initialize();
		$this->Actioninfos_obj = D("Actioninfos");
		$this->Teams_obj = D("Teams");
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Bets_obj = D("Bets");
		$this->Userroles_obj = D("Userroles");
		$this->Typeconfigs_obj = D("Typeconfigs");
		$this->Userdetails_obj = D("Userdetails");
		$this->Playerscores_obj = D("Playerscores");
		$this->Users_obj = D("T_users");
		$this->Userbets_obj = D("Userbets");
		$this->Action_obj = D("Action_teams");
		$this->Gameinfos_obj = D("Gameinfos");
	}
	public function index(){
    	$this->_list();
    }
	public function _arr(){
		$typearr  = array(
						array("id"=>"89",	"name"=> "补赛"),
						array("id"=>"90",	"name"=> "小组赛"),
						array("id"=>"91",	"name"=> "淘汰赛"),
						array("id"=>"92",	"name"=> "1/12决赛"),
						array("id"=>"93",	"name"=> "1/10决赛"),
						array("id"=>"94",	"name"=> "1/8决赛"),
						array("id"=>"95",	"name"=> "1/6决赛"),
						array("id"=>"96",	"name"=> "1/4决赛"),
						array("id"=>"97",	"name"=> "半决赛"),
						array("id"=>"98",	"name"=> "决赛"),
						array("id"=>"99",	"name"=> "轮赛")
					);
		return $typearr;
	}
	public function _list(){
		$datetime = date('Y-m-d H:i:s');
		$parakey_single = array ('typeclass','noupdate');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['typeclass'] > 0)
			{
				$typeclass = " && (matchtype like  '".$parameters['typeclass']."%' )";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['noupdate'] != '')
			{
				$noupdate = " && (status =".$parameters['noupdate']." && matchdatetime < '$datetime' )";
				$_GET['noupdate'] = $parameters['noupdate'];
			}
			$search = $typeclass.$noupdate;
		}
		$ginfos = $this->Gameinfos_obj
		->where(array(array("date(startdate)<='$datetime'"),array("date(enddate)>='$datetime'")))
		->select();
		foreach($ginfos as$k=>$gin){
			$teaml[$k] = $gin['constid'].'%';
		}
		if($search){
			$search = array('matchtype'=>array('like',$teaml),'(length(matchtype)=10)'.$search);
		}else{
			$search = array('matchtype'=>array('like',$teaml));
		}
		$count=$this->Matchinfos_obj-> where($search)->count();
		$page = $this->page($count, 20);
		$minfos = $this->Matchinfos_obj
		->where($search)
		->limit($page->firstRow . ',' . $page->listRows)
		->order("id desc")
		->select();
		$i = 0;
		foreach($minfos as$min){
			$team[$i] = $min['teamid1'];
			$i++;
			$team[$i] = $min['teamid2'];
			$i++;
		}
		$tcid['teamnumber|constid'] = array('in',array_merge(array_unique($team)));
		$teams = $this->Teams_obj
		->where($tcid)
		->order("id desc")
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$this->assign("datetime",$datetime);
		$this->assign("ginfos",$ginfos);
		$this->assign("minfos",$minfos);
		$this->assign("teams",$teams);
		$this->assign("users",$users);
		$this->assign("formpost",$parameters);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	public function old(){
		$datetime = date('Y-m-d H:i:s');
		$parakey_single = array ('typeclass','noupdate','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['typeclass'] > 0)
			{
				$typeclass = " && (matchtype like  '".$parameters['typeclass']."%' )";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['noupdate'] != '')
			{
				$noupdate = " && (status =".$parameters['noupdate']." && matchdatetime < '$datetime' )";
				$_GET['noupdate'] = $parameters['noupdate'];
			}

			if($parameters['start_time'] != '')
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(matchdatetime))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !='')
			{
				$end_time = " && (date(matchdatetime) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $typeclass.$noupdate.$start_time.$end_time;
		}
		$count=$this->Matchinfos_obj-> where("matchtype != '' $search && (length(matchtype)=10)")->count();
		$page = $this->page($count, 20);
		$items = $this->Matchinfos_obj
		->where("matchtype != '' $search && (length(matchtype)=10)")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($items);$i++){
			if($i<count($items)-1){
				$matchinfoid = "matchinfoid = '".$items[$i]['matchconstid']."' || ";
			}else{
				$matchinfoid = "matchinfoid = '".$items[$i]['matchconstid']."'";
			}
			$betsinfo =$betsinfo.$matchinfoid;
		}
		$bets = $this->Bets_obj
		->where("$betsinfo")
		->order("id desc")
		->select();
		$teams = $this->Teams_obj
		->where("teamtype = '10100' || (length(teamtype)=8)")
		->order("id desc")
		->select();
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && subtype = 20000")
		->order("id asc")
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$this->assign("action",$action);
		$this->assign("datetime",$datetime);
		$this->assign("lists",$items);
		$this->assign("bets",$bets);
		$this->assign("teams",$teams);
		$this->assign("formpost",$parameters);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("users",$users);
	    $this->assign('num',$count);
		$this->display();
	}
	public function add(){
		$datetime = date('Y-m-d H:i:s');
		$action = $this->Gameinfos_obj
		->where(array(array("date(startdate)<='$datetime'"),array("date(enddate)>='$datetime'")))
		->select();
		$type = $this->_arr();
    	$this->assign('action',$action);
    	$this->assign('type',$type);
    	$this->display();
	}
	
	function vsteam(){
		if($_POST || $_GET){
			$constid = $type = $round = 0;
			if($_POST['constid']){
				$constid = $_POST['constid'];
				$this->assign("fpost",$_POST);
			}
			if($_GET['constid']){
				$constid = $_GET['constid'];
				$this->assign("fpost",$_GET);
			}
			if(!$_POST['constid'] && !$_GET['constid']){
				$this -> error('请选择赛事和比赛类型');
			}
			$action = $this->Gameinfos_obj
			->where(array('constid'=>$constid))
			->find();
			//暂时不验证球队状态
			//$teamids = $this->Action_obj->where(array('action_constid'=>$constid,'status'=>100))->order("id desc")->select();
			$teamids = $this->Action_obj->where(array('action_constid'=>$constid))->order("id desc")->select();
			foreach($teamids as$k=>$tea){
				$team[$k] = $tea['teamid'];
			}
			$teams = $this->Teams_obj
			->where(array('id'=>array('in',array_merge(array_unique($team)))))
			->order("id asc")
			->select();
			$type = $this->_arr();
	    	$this->assign('action',$action);
	    	$this->assign('actions',$teamids);	
	    	$this->assign('type',$type);
			$this->assign("teams",$teams);
		}
    	$this->display();
	}
	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $created = date("Y-m-d H:i:s");
    	if(isset($_POST)){
			$oneclasstype = $_POST['tid'];
			if($oneclasstype==99 && $_POST['round'] == 0)
				{ $this -> error('轮次不能为空');}
			if($oneclasstype==99 && $_POST['round'] > 0)
				{ $round = '第'.$_POST['round'].'轮';}
			if($_POST['teamid1']==0)
				{ $this -> error('主队不能为空');}
			if($_POST['teamid2']==0)
				{ $this -> error('客队不能为空');}		
		$oneclass = $_POST['constid'];
		$oneclassround = $_POST['round'];
		if($oneclasstype != 99){
			$matchid = $oneclass.$oneclasstype;
		}elseif($oneclasstype == 99){
			$matchid = $oneclass.$oneclassround;
		}
		$countnum=$this->Matchinfos_obj->where("matchtype = '$matchid'")->order("id desc")->find();
		if($countnum['matchconstid']){
			$str = $countnum['matchconstid']+1;
		}else{
			$str = $matchid.'001';
		}
		$constid=$this->Teams_obj->where("matchconstid = '$str'")->order("id desc")->find();
		if($constid){$str = $str+1;}
		        $match['teamid1'] = $_POST['teamid1'];
		        $match['teamid2'] = $_POST['teamid2'];
		        $match['matchtype'] = $matchid;
		        $match['matchconstid'] = $str;
		        $match['matchdatetime'] = $_POST['start_time'];
		        $match['matchdesc'] =  date("y").'年 '.$_POST['gname'].' '.$_POST['tname'].$round;
		        $match['status'] = 0;
		        $match['showorder'] = 0;
		        $match['created'] = $created;
		        $match['userid'] = $userid;
				if($this->Matchinfos_obj -> add($match))
				{
					$this -> success('球赛添加成功!', U("amateur/vsteam",array('constid'=>$oneclass,'gtype'=>$oneclasstype)));
				}else{
				    $this -> error('球赛添加失败');
				}
		} else {
			$this->error("非法提交！");
		}
	}
    public function edit(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$p=$_GET['p'];
		$c=$_GET['c'];
		$infos = $this->Matchinfos_obj
		->where("id = $id")
		->find();
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && status = 100 && subtype = 20000 && constid = '".substr($infos['matchtype'],0,8)."'")
		->order("id asc")
		->find();
		$teams = $this->Teams_obj
		->order("id desc")
		->select();
		$bets = $this->Bets_obj
		->where("matchinfoid ='".$infos['matchconstid']."'")
		->order("id desc")
		->find();
		$ginfos = $this->Gameinfos_obj
		->where("constid = '".substr($infos['matchtype'],0,8)."'")
		->order("id asc")
		->find();
		$datetime = date('Y-m-d H:i:s');
		$this->assign("datetime",$datetime);
		$this->assign("teams",$teams);
		$this->assign("action",$action);
    	$this->assign('infos',$infos);
    	$this->assign('ginfos',$ginfos);
    	$this->assign('bets',$bets);
		$this->assign("c",$c);
		$this->assign("p",$p);
    	$this->display();
		}else{
			$this->error($this->Matchinfos_obj->getError());
		}
    }
	public function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		$modified = date("Y-m-d H:i:s");
		$score1 = $_POST['score1'];
		$score2 = $_POST['score2'];
		$betsmid = $_POST['bets_mid'];
		$betsid = $_POST['bets_id'];
		if($_POST['whether']=='0' || $_POST['whether']=='1'){
			if($_POST['odds_w']=="" || $_POST['odds_d']=="" || $_POST['odds_l']==""){
				$this -> error('赔率不能为空');
			}
			if($_POST['odds_w']>=20 || $_POST['odds_d']>=20 || $_POST['odds_l']>=20 ){
				$this -> error('赔率范围需小于20');
			}
		}
    	if(isset($_POST)){
			$matchid = $_POST['info_id'];
			$betsmid = $_POST['matc_mid'];
			if($_POST['whether']=='0'){
					$bet = $this->Bets_obj->where("matchinfoid = '$betsmid'")->order("id asc")->find();
					if($bet){
						$start = $bet['effectivedate'];
					}else{
						$date = $_POST['start_time'];
						$firstday = date("Y-m-d",strtotime($date));
						$start = date("Y-m-d 06:00:00",strtotime("$firstday -7 day"));
					} 
//					$bets['action_constid'] = date("Y").'A120';
					$bets['action_constid'] = '2015A120';
					$bets['matchinfoid'] = $betsmid;
					$bets['effectivedate'] = $start;
					$bets['score_offset'] = 0;
					$bets['points'] = 200;
					$bets['odds_w'] = $_POST['odds_w'];
					$bets['odds_d'] = $_POST['odds_d'];
					$bets['odds_l'] = $_POST['odds_l'];
					$bets['pointstype'] = 15000;
					$bets['status'] = 100;
					$bets['showorder'] = $betsmid.'0';
					$bets['expiredate'] = $_POST['start_time'];
					$bets['created'] = $modified;
					$bets['userid'] = $userid;
					$this->Bets_obj -> add($bets);
			}
				if($_POST['start_time'] < $modified){
		    		if( $score1 !='' && $score2 !=''){
				        $match['matchdatetime'] = $_POST['start_time'];
				        $match['score1'] = $score1;
				        $match['score2'] = $score2;
					    $match['status'] = 160;
				        $match['userid'] = $userid;
						$mat = $this->Matchinfos_obj -> where("id = $matchid")->data($match)->save() ;
		    		}else{
						$this->error("请填写比分！");     
		    		}
					if($_POST['whether']=='1'){
						$bets['status'] = 160;
					    $bets['expiredate'] = $_POST['start_time'];
				        $bets['userid'] = $userid;
						$this->Bets_obj -> where("matchinfoid = '$betsmid'")->data($bets)->save();
						$userbet = $this->Userbets_obj->where("betid = $betsid")->order("id desc")->select();
						for($i=0;$i<count($userbet);$i++){
								$ubid = $userbet[$i]['id'];
							if($score1 > $score2){
								if($userbet[$i]['betoption'] == 'w')
								{
									$ubet['status'] = 160;
								}else{
									$ubet['status'] = 150;
								}
								$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
								$ubet=array();
							}
							if($score1 == $score2){
								if($userbet[$i]['betoption'] == 'd')
								{
									$ubet['status'] = 160;
								}else{
									$ubet['status'] = 150;
								}
								$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
								$ubet=array();
							}
							if($score1 < $score2){
								if( $userbet[$i]['betoption'] == 'l')
								{
									$ubet['status'] = 160;
								}else{
									$ubet['status'] = 150;
								}
								$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
								$ubet=array();
							}
						}
					}
					if($_POST['c']){
						$this -> success('修改成功!',U("amateur/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
					}else{
						$this -> success('修改成功!',U("amateur/index",array('p'=>$_POST['p'])));
					}
				}else{
		    		if( $score1 =='' && $score2 ==''){
				        $match['matchdatetime'] = $_POST['start_time'];
				        $match['userid'] = $userid;
						$mat = $this->Matchinfos_obj -> where("id = $matchid")->data($match)->save() ;
		    		}
					if($_POST['whether']=='1' ){
				        $bets['odds_w'] = $_POST['odds_w'];
				        $bets['odds_d'] = $_POST['odds_d'];
				        $bets['odds_l'] = $_POST['odds_l'];
						$bets['status'] = 100;
				        $bets['expiredate'] = $_POST['start_time'];
				        $bets['userid'] = $userid;
						$this->Bets_obj -> where("matchinfoid = '$betsmid'")->data($bets)->save();
					}
					if($_POST['c']){
						$this -> success('修改成功!',U("amateur/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
					}else{
						$this -> success('修改成功!',U("amateur/index",array('p'=>$_POST['p'])));
					}
				}
		} else {
			$this->error("非法提交！");                              
		}
    }
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
				$match = $this->Matchinfos_obj->where("id=$id")->order("id desc")->select();
				$matchid = $match[0]['matchconstid'];
						if ($this->Matchinfos_obj->where("id=$id")->delete()) {
								$this->Bets_obj->where("matchinfoid='$matchid'")->delete();
						        $this -> success('删除成功!');
		            	} else {
		                	$this->error("删除失败！");
		            	}
        }else{
			$this->error($this->Matchinfos_obj->getError());
        }

    }
    public function update() {
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$infos = $this->Matchinfos_obj
		->where("id = $id")
		->find();
		$tem[0] = $infos['teamid1'];
		$tem[1] = $infos['teamid2'];
		$tcid['teamnumber|constid'] = array('in',array_merge(array_unique($tem)));
		$teams = $this->Teams_obj
		->where($tcid)
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
		$page = $this->page($count, 20);
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
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
    		if($_POST['enter'] == ''){$_POST['enter'] =0;}
    		if($_POST['yellow'] == ''){$_POST['yellow'] =0;}
    		if($_POST['red'] == ''){$_POST['red'] =0;}
			if($_POST['enter'] == 0 && $_POST['yellow'] == 0 && $_POST['red'] == 0){ $this->error("不能提交空数据");}
			if($_POST['enter'] > 99 ||   $_POST['yellow'] > 9 || $_POST['red'] > 9){ $this->error("你不要骗我");}
    	if(isset($_POST)){
    		$twoclass = $_POST['twoclass'];
    		$oneclass = $_POST['oneclass'];
    		list($twoclass1, $twoclass2,) = split ('[,]', $twoclass);
    		list($oneclass1, $oneclass2,) = split ('[,]', $oneclass);
    		$date['userroleid'] = $twoclass1;
			$date['matchconstid'] = $oneclass1;
			$date['goals'] = $_POST['enter'];
			$date['yellow_card'] = $_POST['yellow'];
			$date['red_card'] = $_POST['red'];
			$date['description'] = $oneclass2.' - '.$twoclass2;
			$date['created'] = $created;
	        $date['userid'] = $userid;
    	}
			$playerscores = $this->Playerscores_obj
			->where("userroleid = $twoclass1 && matchconstid = '$oneclass1'")
			->order("id desc")
			->select();
		if($playerscores){
				    $this -> error('此人本场的比赛信息已存在。');
		}else{
				if($this->Playerscores_obj -> add($date))
				{
					$this -> success('比赛信息添加成功!');
				}else{
				    $this -> error('比赛信息添加失败');
				}
		}
    }
	public function player(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$players = $this->Playerscores_obj
		->where("id = '$id'")
		->order("id desc")
		->find();
		$this->assign("player",$players);
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
		if($_POST['enter'] == 0 && $_POST['yellow'] == 0 && $_POST['red'] == 0){ $this->error("不能提交空数据");}
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
				$this -> success('球员状况更新成功!',U("Amateur/update",array('id'=>$infos[0]['id'])));
			}else{
				$this -> error('球员状况更新失败');
			}
		}
	}
    public function deleteplayer() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
				if ($this->Playerscores_obj->where("id=$id")->delete()) {
				        $this -> success('删除成功!');
            	} else {
                	$this->error("删除失败！");
            	}
        }else{
			$this->error($this->Playerscores_obj->getError());
        }

    }
}
