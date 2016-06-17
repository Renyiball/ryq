<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-02-19
	描述：兑换记录
**********************************/
class BarterAction extends AdminbaseAction {
	protected $Userexchanges_obj,$Shop_obj,$Itemtypes_obj,$Typeconfigs_obj,$Contacts_obj,$Users_obj,$Expressinfos_obj,$Uexchglogs_obj;
	function _initialize() {
		parent::_initialize();
		$this->Userexchanges_obj = D("Userexchanges");//订单
		$this->Shop_obj = D("Iteminfos");//商品
		$this->Itemtypes_obj = D("Itemtypes");
		$this->Typeconfigs_obj = D("Typeconfigs");//对照表
		$this->Contacts_obj = D("Contacts");//地址
		$this->Users_obj = D("Users");//用户
		$this->Expressinfos_obj = D("Expressinfos");//快递
		$this->Uexchglogs_obj = D("Uexchglogs");//详细
		$this->Itemdetails_obj = D("Itemdetails");//详细
	}
	public function num_arr(){
		$typearr  = array(
						array("id"=>"0","value"=>"xuanze","name"=> "请选择快递"),
						array("id"=>"1","value"=>"zhongtong","name"=> "中通快递"),
						array("id"=>"2","value"=>"shunfeng","name"=> "顺丰速运"),
						array("id"=>"3","value"=>"ems","name"=> "EMS"),
						array("id"=>"4","value"=>"yunda","name"=> "韵达快递"),
						array("id"=>"5","value"=>"yuantong","name"=> "圆通快递"),
						array("id"=>"6","value"=>"tiantian","name"=> "天天快递"),
					);
		return $typearr;
	}
	function ryq_array($postArray){
		$js_data = array();
		if(!function_exists('file_get_contents')) {
			$posts = file_get_contents($postArray);
		} else {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt ($ch, CURLOPT_URL, $postArray);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$post = curl_exec($ch);
			curl_close($ch);
		}
		$de_json = json_decode($post,true);
		return $de_json;
	}	
	public function index(){
    	$this->_list();
    }

	public function _list(){
		$parakey_single = array ('typeclass','start_time','end_time','userid','orderid','telephone');
		$parameters = $this->_GetRequestPara_All ( $parakey_single);
	    if($parameters){
			if($parameters['typeclass'] > 0){
				$typeclass = " && (status = ".$parameters['typeclass'].")";
				$_GET['typeclass'] = $parameters['typeclass'];
			}else{
				$typeclass = " && (status > 150 && status < 190)";
				$_GET['typeclass'] = $parameters['typeclass'];
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
			if($parameters['userid'] > 0)
			{
				$userid = " && (userid = ".$parameters['userid'].')';
				$_GET['userid'] = $parameters['userid'];
			}
			if($parameters['orderid'] > 0)
			{
				$orderid = " && (id = ".$parameters['orderid'].')';
				$_GET['orderid'] = $parameters['orderid'];
			}
			if($parameters['telephone'] > 0)
			{
				$telephone = $parameters['telephone'];
				$_GET['telephone'] = $parameters['telephone'];
				$phone = $this->Contacts_obj
				->where("relatedType = 5 && telephone = $telephone")
				->order("userid desc")
				->select();
				for($i=0;$i<count($phone);$i++){
					if($i<count($phone)-1){
					$uid = "userid =".$phone[$i]['userid'].' || ';
					$userid =$userid.$uid;
					}else{
					$uid = "userid =".$phone[$i]['userid'];
					$userid =$userid.$uid;
					}
				}
				$userid = " && (".$userid.")";
			}
			$search = $typeclass.$start_time.$end_time.$userid.$orderid;
		}
	    if($parameters['typeclass'] == ''){
			$parameters['typeID'] = $parameters['typeclass'] = 160;
			$parameters['start_time'] = date('Y-m-d',strtotime('-1 month'));
			$search = " && status = 160 && (date(created) >= '".$parameters['start_time']."')";
		}
			$type = $this->Typeconfigs_obj
			->where("typeGroup = 8 && typeID > 150 && typeID < 190")
			->order("typeID asc")
			->select();
			$typems = $this->Typeconfigs_obj
			->where("typeGroup = 4 && typeID > 0")
			->order("typeID asc")
			->select();
			$count=$this->Userexchanges_obj->where("userid > 0 $search")-> count();
			$page = $this->page($count, 20);
			$lists = $this->Userexchanges_obj
			->where("userid > 0 $search")
			->order("id DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			for($i=0;$i<count($lists);$i++){
				$uid[$i] = $lists[$i]['userid'];
			}
			$userids = array_merge(array_unique($uid));
			for($i=0;$i<count($userids);$i++){
				if($i<count($userids)-1){
					$uid = "id =".$userids[$i]." || ";
					$ucid = "relatedID =".$userids[$i]." || ";
				}else{
					$uid = "id =".$userids[$i];
					$ucid = "relatedID =".$userids[$i];
				}
				$usids =$usids.$uid;
				$ucids =$ucids.$ucid;
			}
			if($usids){
				$users = $this->Users_obj
				->where("$usids")
				->order("id desc")
				->select();
			}
			if($ucids){
				$contact = ' && ('.$ucids.')';
				$contacts = $this->Contacts_obj
				->where("relatedType = 5 $contact")
				->order("userid desc")
				->select();
			}
			$this->assign("type",$type);
			$this->assign("typems",$typems);
			$this->assign("users",$users);
			$this->assign("contacts",$contacts);
			$this->assign("formpost",$parameters);
	    	$this->assign('num',$count);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("lists",$lists);
			$this->display();
	
	}

	public function look(){
    	if(isset($_GET['id'])){
    		$id = $_GET['id'];
			$page = $this->page($count, 20);
			$info = $this->Userexchanges_obj
			->where("id = $id")
			->find();
			$uid = $info['userid'];
			$users = $this->Users_obj
			->where("id = $uid")
			->order("id desc")
			->find();
			$contacts = $this->Contacts_obj
			->where("relatedID = $uid  && relatedType = 5")
			->order("userid desc")
			->find();
			$type = $this->Typeconfigs_obj
			->where(" (typeGroup = 8 || typeGroup = 4) && typeID > 0")
			->order("typeID asc")
			->select();
			$sid = $info['itemid'];
			$shops = $this->Shop_obj
			->where("id = $sid")
			->find();
			$express = $this->Expressinfos_obj
			->where("relatedid = $id && relatedtype = 37")
			->order("id desc")
			->find();
			$uexchglogs = $this->Uexchglogs_obj
			->where("uexchangesid = $id")
			->select();
			$did = $info['detailid'];
			$itemdetails = $this->Itemdetails_obj
			->where("id = $did")
			->find();
			$number = $this->num_arr();
	    	$this->assign('number',$number);
			$this->assign("users",$users);
			$this->assign("contacts",$contacts);
			$this->assign("info",$info);
			$this->assign("type",$type);
			$this->assign("shops",$shops);
			$this->assign("express",$express);
			$this->assign("uexchglogs",$uexchglogs);
			$this->assign("itemdetails",$itemdetails);
			$this->display();
		}else{
			$this->error($this->Userexchanges_obj->getError());
		}
	}
	public function statistics(){
		$parakey_single = array ('typeID','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
	    if($parameters){
			if($parameters['typeID']){
				for($i=0;$i<count($parameters['typeID']);$i++){
					if($i<count($parameters['typeID'])-1){
						$type = '(typeID = '.$parameters['typeID'][$i].') ||';
					}else{
						$type = '(typeID = '.$parameters['typeID'][$i].')';
					}
					$typeID = $typeID.$type;
				}
				$typeID = " && ".$typeID;
				$_GET['typeID'] = $statu;
			}
			if($parameters['start_time'] != ''){
				$start_time = " && ('".$parameters['start_time']."' <= date(created))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !=''){
				$end_time = " && (date(created) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $start_time.$end_time;
		}
			if(!$search){
				$parameters['start_time'] = date("Y-m-01");
				$search = " && (date(created) >= '".$parameters['start_time']."')";
			}
			$configs = $this->Typeconfigs_obj
			->where("typeGroup = 8 && typeID > 150 && typeID < 190")
			->order("typeID asc")
			->select();
		if(!$typeID){
			$type = $this->Typeconfigs_obj
			->where("typeGroup = 8 && typeID > 150 && typeID < 190")
			->order("typeID asc")
			->select();
			for($i=0;$i<count($type);$i++){
				$status = $type[$i]['typeID'];
				$count[$i]=$this->Userexchanges_obj->where("userid > 0 && status =$status $search")->count();
			}
		}else{
			$type = $this->Typeconfigs_obj
			->where("typeGroup = 8 && typeID > 150 && typeID < 190 $typeID")
			->order("typeID asc")
			->select();
			for($i=0;$i<count($type);$i++){
				$status = $type[$i]['typeID'];
				$actions=$this->Userexchanges_obj->where("userid > 0 && status =$status $search")->select();
				$count[$i] = count($actions);
				$num = $num+$count[$i];
				$seach='';
				for($j=0;$j<count($actions);$j++){
					if($j<count($actions)-1){
						$seach = $seach." parentID = ".$actions[$j]['id']."||";
					}else{
						$seach = $seach." parentID = ".$actions[$j]['id'];
					}
				}
			}
		}
			$this->assign("configs",$configs);
			$this->assign("type",$type);
			$this->assign("count",$count);
			$this->assign("num",$num);
			$this->assign("formpost",$parameters);
			$this->display();
	}
	public function number_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if($_POST['transport'] && $_POST['num'] ){
		$number = $this->num_arr();
			for($i=0;$i<count($number);$i++){
				if($number[$i]['id'] == $_POST['transport']){
					$express['express_type']=$number[$i]['value'];
					$express['express_text']=$number[$i]['name'];
					$express['express_code']=$_POST['num'];
					$express['relatedid']= $_POST['infoid'];
					$express['relatedtype']= '37';
	        		$express['created'] = date("Y-m-d H:i:s");
	        		$express['userid'] = $userid;
					$actions['id'] =  $_POST['infoid'];
					$actions['status'] =  170;
			        $ualog['uexchangesid'] = $_POST['infoid'];
			        $ualog['description1'] = "客服(".$userid."),更新物流订单".$number[$i]['name'].":".$_POST['num']."->[已发货]";
			        $ualog['status_from'] = 165;
			        $ualog['status_to'] = 170;
			        $ualog['created'] = date("Y-m-d H:i:s");
			        $ualog['userid'] = $userid;
					$uactions = $this->Userexchanges_obj
		    		->where("id = ".$_POST['infoid'])
		    		->find();
					$extraDesc = '您兑换的['.msubstr(str_replace(' ','_',$uactions['description']),0,16).']，'.$number[$i]['name'].'已经发出（单号：'.$_POST['num'].'），请您稍后注意查看物流变化。';					
					$push = localhost_url.'index.php?g=api&m=umeng&a=index&userid='.$uactions['userid'].'&describe='.$extraDesc;
				}
			}
			if($this->Expressinfos_obj -> add($express) && $this->Userexchanges_obj -> data($actions)->save() && $this->Uexchglogs_obj -> add($ualog)){

				$banner = $this->ryq_array($push);
    			if($banner['ret'] == 'SUCCESS'){
    				$this->success('物流更新成功,已推送通知！');
    			}else{
    				$this->success('物流更新成功！');
    			}
			}else{
				$this->error("物流更新失败！");
			}
		}else{
				$this->error("请填写物流信息！");
		}
	}

    public function picking_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
    	$id=intval($_GET['id']);
    	if ($id) {
	        $data['uexchangesid'] = $id;
	        $data['description1'] = '客服('.$userid.'),已向仓库发送配货要求->[已配货]';
	        $data['status_from'] = 160;
	        $data['status_to'] = 165;
	        $data['created'] = date("Y-m-d H:i:s");
	        $data['userid'] = $userid;
    		if ($this->Uexchglogs_obj -> add($data)) {
    			$this->Userexchanges_obj->where("id=$id")->setField('status','165') ;
    			$this->success("订单配货成功！");
    		} else {
    			$this->error('订单配货失败！');
    		}
    	} else {
			$this->error($this->Userexchanges_obj->getError());
    	}
    }
	public function cancelpicking(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
    	$id=intval($_GET['id']);
    		$rst = $this->Userexchanges_obj->where("id=$id")->setField('status','160');
			        $ualog['uexchangesid'] = $id;
			        $ualog['description1'] = "客服(".$userid."),取消配货->[已付款]";
			        $ualog['status_from'] = 165;
			        $ualog['status_to'] = 160;
			        $ualog['created'] = date("Y-m-d H:i:s");
			        $ualog['userid'] = $userid;
    		if ($rst) {
    			$this->Uexchglogs_obj -> add($ualog);
    			$this->success("取消发货成功！");
    		} else {
    			$this->error('取消发货失败！');
    		}
	}
	public function cancelnumber(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
    	$id=intval($_GET['id']);
    		$rst = $this->Userexchanges_obj->where("id=$id")->setField('status','165');
			$this->Expressinfos_obj->where("relatedid = $id && relatedtype=10")->delete();
			        $ualog['uexchangesid'] = $id;
			        $ualog['description1'] = "客服(".$userid."),取消发货->[已配货]";
			        $ualog['status_from'] = 170;
			        $ualog['status_to'] = 165;
			        $ualog['created'] = date("Y-m-d H:i:s");
			        $ualog['userid'] = $userid;
    		if ($rst) {
    			$this->Uexchglogs_obj -> add($ualog);
    			$this->success("取消发货成功！");
    		} else {
    			$this->error('取消发货失败！');
    		}
	}
}