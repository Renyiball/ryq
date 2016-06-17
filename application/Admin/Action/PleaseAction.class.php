<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-10-28
	描述：邀请码
**********************************/
class PleaseAction extends AdminbaseAction {
	
	protected $Usercodes_obj,$Users_obj,$Contacts_obj;
		
    function _initialize(){
		parent::_initialize();
		$this->Usercodes_obj = D("Usercodes");//邀请码
		$this->Users_obj = D("Users");//用户
	}

	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('start_time','end_time','userid','code','nickname');
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
			if($parameters['userid'] !='')
			{
				$userid = " && (relateduserid = '".$parameters['userid']."')";
				$_GET['userid'] = $parameters['userid'];
			}
			if($parameters['code'] !='')
			{
				$code = " && (invite_me_code = '".$parameters['code']."')";
				$_GET['code'] = $parameters['code'];
			}
			if($parameters['nickname'] !='')
			{
				$nickname = "nickname like '%".$parameters['nickname']."%'";
				$_GET['nickname'] = $parameters['nickname'];
			}
			if($parameters['code']){
				$jumpUrl=U('please/look',array('code'=>$parameters['code']));
		    	$this->assign('jumpUrl',$jumpUrl);
			}
			$search = $start_time.$end_time.$userid;
			$nickname=$nickname;
		}
			if($nickname){
				$nicknames = $this->Users_obj
				->where("$nickname")
				->order("created DESC")
				->select();
				for($u=0;$u<count($nicknames);$u++)
					{
						$uid = $nicknames[$u]['id'];
						if($u < count($nicknames)-1)
						{
							$twoseach = $twoseach."(relateduserid = ".$uid.") || ";
						}else{
							$twoseach = $twoseach."(relateduserid = ".$uid.")";
						}
					}
				$search = ' && '.$twoseach;
			}
			$count=$this->Usercodes_obj->where("relateduserid > 0 $search")-> count();
			$page = $this->page($count, 20);
			$lists = $this->Usercodes_obj
			->where("relateduserid > 0 $search")
			->order("created DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			for($i=0;$i<count($lists);$i++){
				if($i<count($lists)-1){
					$uid = "id = '".$lists[$i]['relateduserid']."' || ";
					$code = "invite_me_code = '".$lists[$i]['my_invite_code']."' || ";
				}else{
					$uid = "id = '".$lists[$i]['relateduserid']."'";
					$code = "invite_me_code = '".$lists[$i]['my_invite_code']."'";
				}
				$userids = $userids.$uid;
				$codes = $codes.$code;
			}
			$users = $this->Users_obj
			->where("$userids")
			->order("created DESC")
			->select();
			$nums = $this->Usercodes_obj
			->where("$codes")
			->order("created DESC")
			->select();
		    $this->assign('lists',$lists);
		    $this->assign('users',$users);
		    $this->assign('count',$count);
		    $this->assign('nums',$nums);
			$this->assign("formpost",$parameters);
			$this->assign("Page", $page->show('Admin'));
			$this->display();
	}
	public function look(){
        if (isset($_GET['code'])) {
    		$gcode = $_GET['code'];
			$lists = $this->Usercodes_obj
			->where("invite_me_code = '$gcode'")
			->order("created DESC")
			->select();
			$count = $this->Usercodes_obj
			->where("invite_me_code = '$gcode'")
			->order("created DESC")
			->count();
			for($i=0;$i<count($lists);$i++){
				if($i<count($lists)-1){
					$uid = "id = '".$lists[$i]['relateduserid']."' || ";
					$code = "invite_me_code = '".$lists[$i]['my_invite_code']."' || ";
				}else{
					$uid = "id = '".$lists[$i]['relateduserid']."'";
					$code = "invite_me_code = '".$lists[$i]['my_invite_code']."'";
				}
				$userids = $userids.$uid;
				$codes = $codes.$code;
			}
			$users = $this->Users_obj
			->where("$userids")
			->order("created desc")
			->select();
			$nums = $this->Usercodes_obj
			->where("$codes")
			->order("created DESC")
			->select();
		    $this->assign('lists',$lists);
		    $this->assign('users',$users);
		    $this->assign('count',$count);
		    $this->assign('nums',$nums);
		    $this->assign('source',$gcode);
			$this->display();
		}else{
			$this->error($this->Usercodes_obj->getError());
		}
	}
	
}