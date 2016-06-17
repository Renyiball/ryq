<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-11-30
	描述：猜球记录
**********************************/
class RecordingAction extends AppframeAction{
	protected $Userbets_obj,$Teams_obj,$Bets_obj,$Matchinfos_obj,$Actioninfos_obj;
	function _initialize() {
		parent::_initialize();
		$this->Userbets_obj = D("Userbets");
		$this->Teams_obj = D("Teams");
		$this->Bets_obj = D("Bets");
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Actioninfos_obj = D("Actioninfos");
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		if(empty($url_userid) || $url_userid < 0){
	    	$out401 = array('ret'=>'Unauthorized','code'=>401,'info'=>'详情请登录后再来查看！');
			$this->assign("out401",$out401);
	 	}
		$userbets = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 && userid = $url_userid")
		->order("id desc")
		->select();
		for($i=0;$i<count($userbets);$i++){
			$betid[$i]=$userbets[$i]['betid'];
		}
		$cbets = $this->Bets_obj
		->where(array('id'=>array('in',array_merge(array_unique($betid)))))
		->order("id desc")
		->select();
		for($i=0;$i<count($cbets);$i++){
			$cmatchid[$i] = $cbets[$i]['matchinfoid'];
		}
		$cmatchinfos = $this->Matchinfos_obj
		->where(array('matchconstid'=>array('in',array_merge(array_unique($cmatchid)))))
		->order("id desc")
		->select();
		$c=0;
		for($i=0;$i<count($cmatchinfos);$i++){
			$cteamids[$c] = $cmatchinfos[$i]['teamid1'];
			$c++;
			$cteamids[$c] = $cmatchinfos[$i]['teamid2'];
			$c++;
		}
		$cteamids = array_merge(array_unique($cteamids));
		for($i=0;$i<count($cteamids);$i++){
			if($i<count($cteamids)-1){
				$cteamid = "constid ='".$cteamids[$i]."'|| teamnumber ='".$cteamids[$i]."' || ";
			}else{
				$cteamid = "constid ='".$cteamids[$i]."'|| teamnumber ='".$cteamids[$i]."'";
			}
			$cteam =$cteam.$cteamid;
		}
		$cteams = $this->Teams_obj
		->where("$cteam")
		->order("id desc")
		->select();
		for($i=0;$i<count($cteams);$i++){
			$teamtype[$i] = $cteams[$i]['teamtype'];
		}
		$teamtype = array_merge(array_unique($teamtype));
		for($i=0;$i<count($teamtype);$i++){
			if($i<count($teamtype)-1){
				$teamid = "teamtype ='".$teamtype[$i]."'||";
				$constid = "constid ='".$teamtype[$i]."'||";
			}else{
				$teamid = "teamtype ='".$teamtype[$i]."'";
				$constid = "constid ='".$teamtype[$i]."'";
			}
			$ttype =$ttype.$teamid;
			$ctype =$ctype.$constid;
		}
		$class = $this->Teams_obj
		->where("teamdesc = '球队分类' && length(teamtype)< 8 && ($ttype)")
		->order("id desc")
		->select();
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && status = 100 && subtype = 20000 && ($ctype)")
		->order("id asc")
		->select();
		if($_POST['typeclass']){
			$typeclass = array('id'=>array('in',array_merge(array_unique($betid))),array("matchinfoid like '".$_POST['typeclass']."%'"));
		}else{
			$typeclass =array('id'=>array('in',array_merge(array_unique($betid))));
			$_POST['typeclass'] = 0;
		}
		$count = $this->Bets_obj
		->where($typeclass)
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$bets = $this->Bets_obj
		->where($typeclass)
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($bets);$i++){
			$matchid[$i] = $bets[$i]['matchinfoid'];
		}
		$matchinfos = $this->Matchinfos_obj
		->where(array('matchconstid'=>array('in',array_merge(array_unique($matchid)))))
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
		->where("$team")
		->order("id desc")
		->select();
		
		$this->assign("userbets",$userbets);
		$this->assign('bets',$bets);
		$this->assign('matchinfos',$matchinfos);
		$this->assign('teams',$teams);
		$this->assign('userid',$url_userid);
		$this->assign("class",$class);
		$this->assign("action",$action);
		$this->assign("num",$count);
		$this->assign("formpost",$_POST);
	    $this->display();
	}
	public function repage(){
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_tclass = @$_GET['tclass'] ? $_GET['tclass'] : 0;
		$userbets = $this->Userbets_obj->where("betid>0 && pointtype=15000 && userid = $url_userid")->order("id desc")->select();
		if($url_tclass){
			for($i=0;$i<count($userbets);$i++){
				$betid[$i] = $userbets[$i]['betid'];
			}
			$typeclass = array('id'=>array('in',array_merge(array_unique($betid))),array("matchinfoid like '".$url_tclass."%'"));
		}else{
			for($i=0;$i<count($userbets);$i++){
				$betid[$i] = $userbets[$i]['betid'];
			}
			$typeclass = array('id'=>array("in",array_merge(array_unique($betid))));
		}
		$count = $this->Bets_obj->where($typeclass)->count();
		$page = $this->page($count, 20);
		if($_GET['p']<= ceil($count/20)){
			$bets = $this->Bets_obj
			->where($typeclass)
			->order("id desc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			for($i=0;$i<count($bets);$i++){
				$matchconstid[$i] =$bets[$i]['matchinfoid'];
			}
			if(count($bets)<20){
				$over = "<tr><td colspan='2' style='text-align: center;'>没有更多信息了...</td></tr>";
				$this->assign("over",$over);
			}
			$matchinfos = $this->Matchinfos_obj
			->where(array('matchconstid'=>array('in',array_merge(array_unique($matchconstid)))))
			->order("id desc")
			->limit('0,' . $page->listRows)
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
			->where("$team")
			->order("id desc")
			->select();
		}else{
			$bets ='';
			$matchinfos ='';
			$teams='';
		}
		$this->assign("userbets",$userbets);
		$this->assign('bets',$bets);
		$this->assign('matchinfos',$matchinfos);
		$this->assign('teams',$teams);
	    $this->display();
	}
	
}
