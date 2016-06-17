<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-10-23
	描述：球币管理
**********************************/
class CurrencyAction extends AdminbaseAction {
	protected $Userbets_obj,$Users_obj,$Useractions_obj,$Typeconfigs_obj;
	function _initialize() {
		parent::_initialize();
		$this->Userbets_obj = D("Userbets");
		$this->Users_obj = D("Users");
		$this->Useractions_obj = D("Useractions");
		$this->Typeconfigs_obj = D("Typeconfigs");
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('start_time','end_time','userid');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
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
				$userid = " && (userid = '".$parameters['userid']."')";
				$_GET['userid'] = $parameters['userid'];
			}
			$search = $start_time.$end_time.$userid;
		}
		$count = $this->Userbets_obj
		->where("betid=0 && pointtype=15000 $search")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid=0 && pointtype=15000 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$u=0;
		for($i=0;$i<count($userbets);$i++){
			if($userbets[$i]['userid']){
				$fuid[$u] = $userbets[$i]['userid'];
				$u++;
			}
		}
		$fuid = array_merge(array_unique($fuid));
		for($i=0;$i<count($fuid);$i++){
			if($i<count($fuid)-1){
				$furid = "id = ".$fuid[$i]." || ";
			}else{
				$furid = "id = ".$fuid[$i];
			}
			$furids =$furids.$furid;
		}
		$users = $this->Users_obj
		->where("$furids")
		->order("id DESC")
		->select();
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
	    $this->display();
	}
	public function guess(){
		$parakey_single = array ('start_time','end_time','userid');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
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
				$userid = " && (userid = '".$parameters['userid']."')";
				$_GET['userid'] = $parameters['userid'];
			}
			$search = $start_time.$end_time.$userid;
		}
		$count = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 $search")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$u=0;
		for($i=0;$i<count($userbets);$i++){
			if($userbets[$i]['userid']){
				$fuid[$u] = $userbets[$i]['userid'];
				$u++;
			}
		}
		$fuid = array_merge(array_unique($fuid));
		for($i=0;$i<count($fuid);$i++){
			if($i<count($fuid)-1){
				$furid = "id = ".$fuid[$i]." || ";
			}else{
				$furid = "id = ".$fuid[$i];
			}
			$furids =$furids.$furid;
		}
		$users = $this->Users_obj
		->where("$furids")
		->order("id DESC")
		->select();
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
	    $this->display();
	}
	public function record(){
		$parakey_single = array ('start_time','end_time','userid');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
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
				$userid = " && (userid = '".$parameters['userid']."')";
				$_GET['userid'] = $parameters['userid'];
			}
			$search = $start_time.$end_time.$userid;
		}
		$count = $this->Userbets_obj
		->where("betid <= -200 && pointtype=15000 $search")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid <= -200 && pointtype=15000 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$u=0;
		for($i=0;$i<count($userbets);$i++){
			if($userbets[$i]['userid']){
				$fuid[$u] = $userbets[$i]['userid'];
				$u++;
			}
		}
		$fuid = array_merge(array_unique($fuid));
		for($i=0;$i<count($fuid);$i++){
			if($i<count($fuid)-1){
				$furid = "id = ".$fuid[$i]." || ";
			}else{
				$furid = "id = ".$fuid[$i];
			}
			$furids =$furids.$furid;
		}
		$users = $this->Users_obj
		->where("$furids")
		->order("id DESC")
		->select();
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
	    $this->display();
	}
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
            if ($this->Userbets_obj->where("id=$id")->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }else{
			$this->error($this->Userbets_obj->getError());
        }

    }
    public function grant() {
		$configs = $this->Typeconfigs_obj
		->where("typeGroup = 21 && typeID < 0")
		->order("id asc")
		->select();
		$this->assign("configs",$configs);
	    $this->display();
    }
    public function grant_post() {
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if(!$_POST['userid']){ $this -> error('用户ID不能为空');}
		if(!$_POST['shopid']){ $this -> error('订单ID不能为空');}
		if($_POST['description'] == 0){ $this -> error('请选择发放原因');}
		if($_POST['value'] <= 0){ $this -> error('发放金额不能为空');}
		$puid = $_POST['userid'];
		$psid = $_POST['shopid'];
		$users = $this->Users_obj
		->where("id = $puid")
		->order("created DESC")
		->select();
		$actions = $this->Useractions_obj
		->where("id = $psid && parentID = 0 ")
		->order("created DESC")
		->select();
		$description = $_POST['description'];
		$configs = $this->Typeconfigs_obj
		->where("typeGroup = 21 && typeID = $description")
		->order("id asc")
		->select();
		if($actions[0]['userid'] != $puid){ $this -> error('用户与订单不匹配');}
		if(!(170 < $actions[0]['status'] && $actions[0]['status']< 185)){ $this -> error('订单当前状态不可发放球币');}
		$config = $configs[0]['typeName'].'(订单：'.$psid.')'.'[award_'.abs($description) .']---操作员：'.$userid;
		$ubet = $this->Userbets_obj
		->where("betid = $description && pointtype = 15000 && status = 160 && description = '$config' ")
		->order("created DESC")
		->select();
    	if(!$ubet){
			$userbets['betid'] = $description;
	        $userbets['points'] = $_POST['value'];
	        $userbets['odds'] = $_POST['multiple'];
	        $userbets['pointtype'] = 15000;
	        $userbets['status'] = 160;
	        $userbets['description'] = $config;
	        $userbets['points_expire'] = date("Y-m-d H:i:s",strtotime("+1 year"));
	        $userbets['created'] = date("Y-m-d H:i:s");
	        $userbets['userid'] = $_POST['userid'];
			if($this->Userbets_obj->add($userbets)){
				$this -> success('球币发放成功!',U("currency/record"));
			}else{
			    $this -> error('球币发放失败');
	    	}
		} else {
			    $this -> error('该条订单已发放过球币');
		}
    }
	public function edit(){
        if(isset($_GET['id'])){
    	$id = $_GET['id'];
    	$bets = $this->Userbets_obj
		->where("id=$id")
		->find();
        $this->assign('bets',$bets);
    	$this->display();
		}else{
			$this->error($this->Userbets_obj->getError());
		}
    }
	public function edit_post(){
        $id = $_POST['id'];
        unset($_POST['id']);
        $data['betoption'] = $_POST['betoption'];
        $data['points'] = $_POST['points'];
        $data['odds'] = $_POST['odds'];
        $data['status'] = $_POST['status'];
        if ($this->Userbets_obj->where("id = $id")->data($data)->save()) {
            $this->success('修改成功!', U("Currency/guess"));
        } else {
            $this->error('修改失败!');
        }
    }
}
