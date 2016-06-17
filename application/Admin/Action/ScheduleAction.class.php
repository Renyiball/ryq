<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-11-10
	描述：球队日程
**********************************/
class ScheduleAction extends AdminbaseAction {
	protected $Tmatchinfos_obj,$Teams_obj,$Users_obj,$Moredetails_obj,$Userroles_obj,$Typeconfigs_obj,$Playerscores_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Tmatchinfos_obj = D("Tmatchinfos");
		$this->Users_obj = D("Users");//用户
		$this->Teams_obj = D("Teams");//球队
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Userroles_obj = D("Userroles");
		$this->Typeconfigs_obj = D("Typeconfigs");
		$this->Playerscores_obj = D("Playerscores");
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('status','start_time','end_time');
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
			if(!$parameters['status']){
				$status = "";
				$_GET['noup'] = $parameters['status'];
			}else{
				$status = "&& (status = ".$parameters['status'].")";
				$_GET['noup'] = $parameters['status'];
			}
			$search = $start_time.$end_time.$status;
		}
		$count=$this->Tmatchinfos_obj-> where("matchtype = '10100' $search")->count();
		$page = $this->page($count, 20);
		$tmatchinfos = $this->Tmatchinfos_obj
		->where("matchtype = '10100' $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$t=0;
		for($i=0;$i<count($tmatchinfos);$i++){
			$tuid[$i] = $tmatchinfos[$i]['userid'];
			$teamid[$t] = $tmatchinfos[$i]['teamid1'];
			$t++;
			$teamid[$t] = $tmatchinfos[$i]['teamid2'];
			$t++;
		}
		$tuid = array_merge(array_unique($tuid));
		for($i=0;$i<count($tuid);$i++){
			if($i<count($tuid)-1){
				$uid = "id = ".$tuid[$i]." || ";
			}else{
				$uid = "id = ".$tuid[$i];
			}
			$userid =$userid.$uid;
		}
		$users = $this->Users_obj
		->where("$userid")
		->order("id desc")
		->select();
		$teamid = array_merge(array_unique($teamid));
		for($i=0;$i<count($teamid);$i++){
			if($teamid[$i]){
				if($i<count($teamid)-1){
					$tid = "id = ".$teamid[$i]." || ";
				}else{
					$tid = "id = ".$teamid[$i];
				}
				$teid =$teid.$tid;
			}
		}
		$teams = $this->Teams_obj
		->where("$teid")
		->order("id desc")
		->select();
		$this->assign("tmatchinfos",$tmatchinfos);
		$this->assign("users",$users);
		$this->assign("teams",$teams);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
       	$this->display();
	}
	public function look(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$tmatchinfos = $this->Tmatchinfos_obj
		->where("id = $id")
		->order("id desc")
		->select();
		$teamid1 = $tmatchinfos[0]['teamid1'];
		$teamid2 = $tmatchinfos[0]['teamid2'];
		$teams = $this->Teams_obj
		->where("id ='$teamid1' || id ='$teamid2' ")
		->order("id desc")
		->select();
		$tid1 = $teams[0]['id'];
		$tid2 = $teams[1]['id'];
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10  && relatedID = '$tid1' || relatedID = '$tid2'")
		->order("play_number asc")
		->select();
		$typeconfigs = $this->Typeconfigs_obj
		->where("typeGroup = 9 && typeid != 0")
		->order("id desc")
		->select();
		$count=$this->Playerscores_obj-> where("matchconstid = '$id' ")->count();
		$page = $this->page($count, 20);
		$items = $this->Playerscores_obj
		->where("matchconstid = '$id'")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$this->assign("teams",$teams);
		$this->assign("userroles",$userroles);
		$this->assign("typeconfigs",$typeconfigs);
		$this->assign("lists",$items);
		$this->assign("users",$users);
		$this->assign("Page", $page->show('Admin'));
    	$this->display();
		}else{
			$this->error($this->Matchinfos_obj->getError());
		}
	}
    public function delete() {
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
	public function edit(){
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
	public function edit_post(){
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
				$this -> success('球员状况更新成功!',U("schedule/index"));
			}else{
				$this -> error('球员状况更新失败');
			}
		}
	}
}