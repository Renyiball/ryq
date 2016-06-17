<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-12-02
	描述：参考赔率
**********************************/
class ReferenceAction extends AdminbaseAction {
	protected $Matchinfos_obj,$Teams_obj,$Users_obj,$Referbets_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Teams_obj = D("Teams");
		$this->Users_obj = D("T_users");//用户
		$this->Referbets_obj = D("Referbets");//用户
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$datetime = date('Y-m-d H:i:s');
		$parakey_single = array ('typeclass','noupdate','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['typeclass'] > 0)
			{
				$typeclass = " && (matchinfoid like  '".$parameters['typeclass']."%' )";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['noupdate'] != '')
			{
				$noupdate = " && ( expiredate < '$datetime' )";
				$_GET['noupdate'] = $parameters['noupdate'];
			}

			if($parameters['start_time'] != '')
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(expiredate))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !='')
			{
				$end_time = " && (date(expiredate) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $typeclass.$noupdate.$start_time.$end_time;
		}
		$count=$this->Referbets_obj-> where("id > 0 $search")->count();
		$page = $this->page($count, 20);
		$referbets = $this->Referbets_obj
		->where("id > 0 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($referbets);$i++){
			if($i<count($referbets)-1){
				$matchconstid = "matchconstid = '".$referbets[$i]['matchinfoid']."' || ";
			}else{
				$matchconstid = "matchconstid = '".$referbets[$i]['matchinfoid']."'";
			}
			$matchid =$matchid.$matchconstid;
		}
		$matchinfos = $this->Matchinfos_obj
		->where("$matchid")
		->order("id desc")
		->select();
		$c=0;
		for($i=0;$i<count($matchinfos);$i++){
			$cteamids[$c] = $matchinfos[$i]['teamid1'];
			$c++;
			$cteamids[$c] = $matchinfos[$i]['teamid2'];
			$c++;
		}
		$cteamids = array_merge(array_unique($cteamids));
		for($i=0;$i<count($cteamids);$i++){
			if($i<count($cteamids)-1){
				$teamid = "constid = '".$cteamids[$i]."' || teamnumber = '".$cteamids[$i]."' ||";
			}else{
				$teamid = "constid = '".$cteamids[$i]."' || teamnumber = '".$cteamids[$i]."'";
			}
			$teamids =$teamids.$teamid;
		}
		$teams = $this->Teams_obj
		->where("$teamids")
		->order("id desc")
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$teamsclass = $this->Teams_obj
		->where("teamdesc = '球队分类' && status = 100 && teamtype!='10100' && length(teamtype)< 8")
		->order("id desc")
		->select();
		$this->assign("datetime",$datetime);
		$this->assign("referbets",$referbets);
		$this->assign("matchinfos",$matchinfos);
		$this->assign("users",$users);
	    $this->assign('num',$count);
		$this->assign("teams",$teams);
		$this->assign("teamsclass",$teamsclass);
		$this->assign("formpost",$parameters);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
    public function edit(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$p=$_GET['p'];
		$c=$_GET['c'];
		$bets = $this->Referbets_obj
		->where("id = $id")
		->order("id desc")
		->find();
		$mid = $bets['matchinfoid'];
		$matchinfos = $this->Matchinfos_obj
		->where("matchconstid = '$mid'")
		->order("id desc")
		->find();
		$m=0;
		$cteamids[$m] = $matchinfos['teamid1'];
		$m++;
		$cteamids[$m] = $matchinfos['teamid2'];
		$m++;
		$cteamids = array_merge(array_unique($cteamids));
		for($i=0;$i<count($cteamids);$i++){
			if($i<count($cteamids)-1){
				$teamid = "constid = '".$cteamids[$i]."' || teamnumber = '".$cteamids[$i]."' ||";
			}else{
				$teamid = "constid = '".$cteamids[$i]."' || teamnumber = '".$cteamids[$i]."'";
			}
			$teamids =$teamids.$teamid;
		}
		$teams = $this->Teams_obj
		->where("$teamids")
		->order("id desc")
		->select();
		$datetime = date('Y-m-d H:i:s');
		$this->assign("datetime",$datetime);
		$this->assign("teams",$teams);
    	$this->assign('matchinfo',$matchinfos);
    	$this->assign('bet',$bets);
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
    	if(isset($_POST)){
			$id = $_POST['id'];
			if($_POST['odds_w']==0 || $_POST['odds_w']=='')
				{$this -> error('主队胜赔率不能为空');}
			if($_POST['odds_d']==0 || $_POST['odds_d']=='')
				{$this -> error('两队平赔率不能为空');}
			if($_POST['odds_l']==0 || $_POST['odds_l']=='')
				{$this -> error('客队胜赔率不能为空');}
			if($_POST['odds_w']>=20 || $_POST['odds_d']>=20 || $_POST['odds_l']>=20 )
				{$this -> error('赔率范围需小于20');}
			if($_POST['odds_w'] >0 && $_POST['odds_d'] >0 && $_POST['odds_l'] >0 ){
		        $bets['odds_w'] = $_POST['odds_w'];
		        $bets['odds_d'] = $_POST['odds_d'];
		        $bets['odds_l'] = $_POST['odds_l'];
		        $bets['expiredate'] = $_POST['start_time'];
		        $bets['userid'] = $userid;
				$bet = $this->Referbets_obj -> where("id = $id")->data($bets)->save();
			}else{
				$this->error("请填写赔率信息！");     
    		}
			if($_POST['c']){
				$this -> success('修改成功!',U("reference/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
			}else{
				$this -> success('修改成功!',U("reference/index",array('p'=>$_POST['p'])));
			}
		} else {
			$this->error("非法提交！");                              
		}
    }
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
				if($this->Referbets_obj->where("id=$id")->delete()){
			        $this -> success('删除成功!');
			    }else {
                	$this->error("比赛删除失败！");
            	}
        }else{
			$this->error("非法提交！");
        }

    }

}
