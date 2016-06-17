<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-09-18
	描述：球队群组
**********************************/
class GroupsAction extends AdminbaseAction {
	protected $Teams_obj,$Users_obj,$Userroles,$Usercodes_obj;
	function _initialize() {
		parent::_initialize();
		$this->Teams_obj = D("Teams");
		$this->Users_obj = D("Users");
		$this->Userroles_obj = D("Userroles");
		$this->Usercodes_obj = D("Usercodes");
	}
	public function _arr(){
		$typearr  = array(
					array("id"=>"10","name"=> "安卓应用"),
					array("id"=>"20","name"=> "苹果应用"),
					array("id"=>"30","name"=> "手机页面"),
					array("id"=>"40","name"=> "微信平台"),
					array("id"=>"50","name"=> "电脑网站"),
					array("id"=>"60","name"=> "分享用户")
				);
		return $typearr;
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('teamid','teamname','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['teamid'] !='')
			{
				$teamid = " && (id = '".$parameters['teamid']."')";
				$_GET['teamid'] = $parameters['teamid'];
			}
			if($parameters['teamname'] !='')
			{
				$teamname = " && (teamname like '%".$parameters['teamname']."%')";
				$_GET['teamname'] = $parameters['teamname'];
			}
			if($parameters['start_time'] != '')
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(created))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !='')
			{
				$end_time = " && (date(created) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $teamid.$teamname.$start_time.$end_time;
		}
		$count=$this->Teams_obj-> where("teamtype = '10100' $search")->count();
		$page = $this->page($count, 20);
		$teams = $this->Teams_obj
		->where("teamtype = '10100' $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($teams);$i++){
			$tteams[$i] = $teams[$i]['id'];
		}
		$tteams = array_merge(array_unique($tteams));
		for($i=0;$i<count($tteams);$i++){
			if($i<count($tteams)-1){
				$relatedID = "relatedID = ".$tteams[$i]." || ";
			}else{
				$relatedID = "relatedID = ".$tteams[$i];
			}
			$related =$related.$relatedID;
		}
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID > 0 && ($related)")
		->order("id desc")
		->select();
		for($i=0;$i<count($userroles);$i++){
			$urid[$i] = $userroles[$i]['relatedUserID'];
		}
		$urid = array_merge(array_unique($urid));
		for($i=0;$i<count($urid);$i++){
			if($i<count($urid)-1){
				$uid = "id = ".$urid[$i]." || ";
			}else{
				$uid = "id = ".$urid[$i];
			}
			$userid =$userid.$uid;
		}
		$users = $this->Users_obj
		->where("$userid")
		->order("id desc")
		->select();
	    $this->assign('num',$count);
		$this->assign("teams",$teams);
		$this->assign("userroles",$userroles);
		$this->assign("users",$users);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
		$this->display();
	}
    public function look() {
		if($_GET['id']){
			$id = $_GET['id'];
		}else{
			$id = $_POST['id'];
		}
		$count=$this->Userroles_obj
		->where("relatedtype = 10 && relatedID > 0 && relatedID = $id")
		->group("relatedUserID")
		->select();
		$count=count($count);
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID > 0 && relatedID = $id")
		->order("id desc")
		->group("relatedUserID")
		->select();
		for($i=0;$i<count($userroles);$i++){
			if($i<count($userroles)-1){
				$userrole = "(id = '".$userroles[$i]['relatedUserID']."') || ";
					$code = "relateduserid = '".$userroles[$i]['relatedUserID']."' || ";
			}else{
				$userrole = "(id = '".$userroles[$i]['relatedUserID']."')";
					$code = "relateduserid = '".$userroles[$i]['relatedUserID']."'";
			}
			$uid = $uid.$userrole;
			$codes = $codes.$code;
		}
		$users = $this->Users_obj
		->where("$uid")
		->order("firstuniqueid desc")
		->select();
		$usercodes = $this->Usercodes_obj
		->where("$codes")
		->order("created DESC")
		->select();
		$roles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID = $id")
		->order("id desc")
		->select();
		$type = $this->_arr();
    	$this->assign('type',$type);
		$this->assign("users",$users);
		$this->assign("roles",$roles);
		$this->assign("id",$id);
	    $this->assign('count',$count);
	    $this->assign('usercodes',$usercodes);
		$this->display();
    }
    public function team() {
		if($_GET['userid']){
			$id = $_GET['userid'];
		}else{
			$id = $_POST['userid'];
		}
		$parakey_single = array ('start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['start_time'] != '')
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(created))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !='')
			{
				$end_time = " && (date(created) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $start_time.$end_time;
		}
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID > 0 && relatedUserID = $id")
		->order("id desc")
		->group("relatedID")
		->select();
		for($i=0;$i<count($userroles);$i++){
			if($i<count($userroles)-1){
				$teamid = "id =".$userroles[$i]['relatedID']." || ";
			}else{
				$teamid = "id =".$userroles[$i]['relatedID'];
			}
			$team =$team.$teamid;
		}
		$count = $this->Teams_obj
		->where("$team")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$teams = $this->Teams_obj
		->where("$team")
		->limit($page->firstRow . ',' . $page->listRows)
		->order("id desc")
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$roles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID > 0")
		->order("id desc")
		->select();
		$this->assign("userroles",$userroles);
		$this->assign("users",$users);
		$this->assign("roles",$roles);
		$this->assign("id",$id);
		$this->assign("teams",$teams);
	    $this->assign('count',$count);
		$this->assign("formpost",$parameters);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
    }
}