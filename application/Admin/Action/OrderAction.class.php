<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-11
	描述：订单管理
**********************************/
class OrderAction extends AdminbaseAction {
	protected $Shop_obj,$Typeconfigs_obj,$Useractions_obj,$Users_obj,$Contacts_obj,$Property_obj,$Expressinfos_obj,$Ualogs_obj;
	function _initialize() {
		parent::_initialize();
		$this->Shop_obj = D("Iteminfos");//商品
		$this->Typeconfigs_obj = D("Typeconfigs");//对照表
		$this->Useractions_obj = D("Useractions");//订单
		$this->Users_obj = D("Users");//用户
		$this->Contacts_obj = D("Contacts");//地址
		$this->Property_obj = D("Itemdetails");//尺寸
		$this->Expressinfos_obj = D("Expressinfos");//快递
		$this->Ualogs_obj = D("Ualogs");//详细
		$this->T_users_obj = D("T_users");//用户
	}
	public function _arr(){
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
		$parakey_multi = array ('typeID' => 'typeID');
		$parakey_single = array ('radio','typeID0','typeID1','typeID2','typeID3','typeID4','typeclass','start_time','end_time','userid','orderid','telephone');
		$parameters = $this->_GetRequestPara_All ( $parakey_single, $parakey_multi );
	    if($parameters){
	    	if($parameters['radio'] == 1){
				if($parameters['typeclass'] > 0){
					$typeclass = " && (status = ".$parameters['typeclass'].")";
					$_GET['typeclass'] = $parameters['typeclass'];
					$_GET['radio'] = $parameters['radio'];
				}else{
					$typeclass = " && (status > 110 && status != 195)";
					$_GET['typeclass'] = $parameters['typeclass'];
					$_GET['radio'] = $parameters['radio'];
				}
	    	}
	    	if($parameters['radio'] == 2){
				$types=$parameters['typeID'];
				if(!$types){
					if($parameters['typeID0']){
						$typeID0='(status = '.$parameters['typeID0'].') ||';
						$_GET["typeID0"] = $parameters['typeID0'];
					}
					if($parameters['typeID1']){
						$typeID1='(status = '.$parameters['typeID1'].') ||';
						$_GET["typeID1"] = $parameters['typeID1'];
					}
					if($parameters['typeID2']){
						$typeID2='(status = '.$parameters['typeID2'].') ||';
						$_GET["typeID2"] = $parameters['typeID2'];
					}
					if($parameters['typeID3']){
						$typeID3='(status = '.$parameters['typeID3'].') ||';
						$_GET["typeID3"] = $parameters['typeID3'];
					}
					if($parameters['typeID4']){
						$typeID4='(status = '.$parameters['typeID4'].') ||';
						$_GET["typeID4"] = $parameters['typeID4'];
					}
					$ids =$typeID0.$typeID1.$typeID2.$typeID3.$typeID4;
					$typeids = substr($ids,0,-3);
				}else{
					$typeID = array_merge($types);
					for($t=0;$t<count($typeID);$t++){
						if($t<count($typeID)-1){
							$type = '(status = '.$typeID[$t].') ||';
							$_GET["typeID".$t] = $typeID[$t];
						}else{
							$type = '(status = '.$typeID[$t].')';
							$_GET["typeID".$t] = $typeID[$t];
						}
						$typeids = $typeids.$type;
					}
				}
				$typeclass = " && (".$typeids.")";
				$_GET['radio'] = $parameters['radio'];
	    	}
	    	if(!$parameters['radio']){
				$typeclass = " && (status > 0)";
				$parameters['radio'] = '1';
				$_GET['radio'] = $parameters['radio'];
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
				->where("telephone = $telephone")
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
	    if($parameters['typeclass'] == '' && $parameters['radio'] == '1'){
			$parameters['typeID'] = $parameters['typeclass'] = 160;
			$parameters['start_time'] = date('Y-m-d',strtotime('-15 day'));
			$search = " && status = 160 && (date(created) >= '".$parameters['start_time']."')";
		}
			$type = $this->Typeconfigs_obj
			->where("typeGroup = 8 && typeID > 110 && typeID != 195")
			->order("typeID asc")
			->select();
			$typems = $this->Typeconfigs_obj
			->where("typeGroup = 4 && typeID > 0")
			->order("typeID asc")
			->select();
			$count=$this->Useractions_obj->where("userid > 0 && actionType!=10030 $search")-> count();
			$page = $this->page($count, 20);
			$lists = $this->Useractions_obj
			->where("userid > 0 && actionType!=10030 $search")
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
			
			$uaction = $this->Useractions_obj
			->where("userid > 0 && actionType!=10030 $search")
			->order("id DESC")
			->select();
			for($i=0;$i<count($uaction);$i++){
				$uaid[$i] = $uaction[$i]['id'];
			}
			$uaids = array_merge(array_unique($uaid));
			for($i=0;$i<count($uaids);$i++){
				if($i<count($uaids)-1){
					$parent = "parentID =".$uaids[$i]." || ";
				}else{
					$parent = "parentID =".$uaids[$i];
				}
				$parentid =$parentid.$parent;
			}
			if($parentid){
				$parents = ' && ('.$parentid.')';
				$pays = $this->Useractions_obj
				->where("actionType=30010 $parents")
				->order("id DESC")
				->select();
				$detailed = $this->Useractions_obj
				->where("actionType=10010 $parents")
				->order("id DESC")
				->select();
				$paymoney = $detailedmoney = 0;
				
				for($p=0;$p<count($pays);$p++){
					$pamount = $pays[$p]['amount']*$pays[$p]['unit'];
					$paymoney = $paymoney+$pamount;
				}
				for($d=0;$d<count($detailed);$d++){
					$damount = $detailed[$d]['amount']*$detailed[$d]['unit'];
					$detailedmoney = $detailedmoney+$damount;
				}
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
			$this->assign("detailed",$detailed);
			$this->assign("pays",$pays);
			$this->assign("paymoney",$paymoney);
			$this->assign("detailedmoney",$detailedmoney);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("lists",$lists);
			$this->display();
	
	}

	public function look(){
    	if(isset($_GET['id'])){
    		$id = $_GET['id'];
			$count=$this->Useractions_obj->where("parentID = $id")-> count();
			$page = $this->page($count, 20);
			$items = $this->Useractions_obj
			->where("parentID = $id")
			->order("id asc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			$uid = $items[0]['userid'];
			$users = $this->Users_obj
			->where("id = $uid")
			->order("id desc")
			->find();
			$contacts = $this->Contacts_obj
			->where("relatedID = $uid  && relatedType = 5")
			->order("userid desc")
			->find();
			$info = $this->Useractions_obj
			->where("id = $id")
			->order("id desc")
			->find();
			$type = $this->Typeconfigs_obj
			->where(" (typeGroup = 8 || typeGroup = 4) && typeID > 0")
			->order("typeID asc")
			->select();
			foreach ($items as $key=>$value){
				if($value['actionType'] == '10010'){
			    $action[$key] = intval($value['actionID']);
				}
			}
			array_multisort($action,SORT_FLAG_CASE,SORT_ASC);
			for($i=0;$i<count($action);$i++){
				if($action[$i]>0){
					$attributes[] = $action[$i];
				}
			}
			$seach = '';
			for($j=0;$j<count($attributes);$j++){
					$seach = $seach." id = ".$attributes[$j];
				if($j <count($attributes)-1){
					$seach = $seach.' || ';
				}
			}
			$property = $this->Property_obj
			->where("$seach")
			->order("itemID desc")
			->select();
			$seach = '';
			for($k=0;$k<count($property);$k++){
					$seach = $seach." id = ".$property[$k]['itemID'];
				if($k <count($attributes)-1){
					$seach = $seach.' || ';
				}
			}
			$shops = $this->Shop_obj
			->where("$seach")
			->order("id desc")
			->select();
			$express = $this->Expressinfos_obj
			->where("relatedid = $id && relatedtype = 12")
			->order("id desc")
			->find();
			$number = $this->_arr();
	    	$this->assign('number',$number);
			$this->assign("items",$items);
			$this->assign("users",$users);
			$this->assign("contacts",$contacts);
			$this->assign("info",$info);
			$this->assign("type",$type);
			$this->assign("property",$property);
			$this->assign("shops",$shops);
			$this->assign("express",$express);
			$this->assign("Page", $page->show('Admin'));
			$this->display();
		}else{
			$this->error($this->Useractions_obj->getError());
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
			->where("typeGroup = 8 && typeID > 110 && typeID != 195")
			->order("typeID asc")
			->select();
		if(!$typeID){
			$type = $this->Typeconfigs_obj
			->where("typeGroup = 8 && typeID > 110 && typeID != 195")
			->order("typeID asc")
			->select();
			for($i=0;$i<count($type);$i++){
				$status = $type[$i]['typeID'];
				$count[$i]=$this->Useractions_obj->where("userid > 0 && actionType!=10030 && status =$status $search")->count();
			}
		}else{
			$type = $this->Typeconfigs_obj
			->where("typeGroup = 8 && typeID > 110 && typeID != 195 $typeID")
			->order("typeID asc")
			->select();
			for($i=0;$i<count($type);$i++){
				$status = $type[$i]['typeID'];
				$actions=$this->Useractions_obj->where("userid > 0 && actionType!=10030 && status =$status $search")->select();
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
				$amount=$this->Useractions_obj->where("actionType=30010 && ($seach)")->select();
				$value = 0;
				for($k=0;$k<count($amount);$k++){
					$amounts = $amount[$k]['amount'];
					$units = $amount[$k]['unit'];
					$value = $value+($amounts*$units);
				}
				$values[$i]['amount'] = $value;
				$values[$i]['unit'] = count($actions);
				$values[$i]['type'] = $type[$i]['typeName'];
			}
		}
			$this->assign("configs",$configs);
			$this->assign("type",$type);
			$this->assign("count",$count);
			$this->assign("num",$num);
			$this->assign("values",$values);
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
		$number = $this->_arr();
			for($i=0;$i<count($number);$i++){
				if($number[$i]['id'] == $_POST['transport']){
					$express['express_type']=$number[$i]['value'];
					$express['express_text']=$number[$i]['name'];
					$express['express_code']=$_POST['num'];
					$express['relatedid']= $_POST['infoid'];
					$express['relatedtype']= '12';
	        		$express['created'] = date("Y-m-d H:i:s");
	        		$express['userid'] = $userid;
					$actions['id'] =  $_POST['infoid'];
					$actions['status'] =  170;
			        $ualog['transactionid'] = $_POST['infoid'];
			        $ualog['description1'] = "客服(".$userid."),更新物流订单".$number[$i]['name'].":".$_POST['num']."->[已发货]";
			        $ualog['status_from'] = 165;
			        $ualog['status_to'] = 170;
			        $ualog['created'] = date("Y-m-d H:i:s");
			        $ualog['user_type'] = 200;
			        $ualog['userid'] = $userid;
					$uactions = $this->Useractions_obj
		    		->where("id = ".$_POST['infoid'])
		    		->find();
					$extraDesc = '您购买的['.msubstr(str_replace(' ','_',$uactions['description']),0,16).']，'.$number[$i]['name'].'已经发出（单号：'.$_POST['num'].'），请您稍后注意查看物流变化。';					
					$push = localhost_url.'index.php?g=api&m=umeng&a=index&userid='.$uactions['userid'].'&describe='.$extraDesc;
				}
			}
			if($this->Expressinfos_obj -> add($express) && $this->Useractions_obj -> data($actions)->save() && $this->Ualogs_obj -> add($ualog)){

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
	public function cancelnumber(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
    	$id=intval($_GET['id']);
    		$rst = $this->Useractions_obj->where("id=$id")->setField('status','165');
			$this->Expressinfos_obj->where("relatedid = $id && relatedtype = 12")->delete();
			        $ualog['transactionid'] = $id;
			        $ualog['description1'] = "客服(".$userid."),取消发货->[已配货]";
			        $ualog['status_from'] = 170;
			        $ualog['status_to'] = 165;
			        $ualog['created'] = date("Y-m-d H:i:s");
			        $ualog['user_type'] = 200;
			        $ualog['userid'] = $userid;
    		if ($rst) {
    			$this->Ualogs_obj -> add($ualog);
    			$this->success("取消发货成功！");
    		} else {
    			$this->error('取消发货失败！');
    		}
	}
	public function detailed(){
    	if(isset($_GET['id'])){
    		$id = $_GET['id'];
    		$ualogs = $this->Ualogs_obj
    		->where("transactionid = $id")
    		->select();
			$type = $this->Typeconfigs_obj
			->where(" typeGroup = 8 && typeID > 0")
			->order("typeID asc")
			->select();
			$users = $this->Users_obj
			->order("id desc")
			->select();
			$tusers = $this->T_users_obj
			->order("id desc")
			->select();
			$detailed = $this->Useractions_obj
			->where("id = $id")
			->order("id DESC")
			->find();
			$this->assign("ualogs", $ualogs);
			$this->assign("type",$type);
			$this->assign("users",$users);
			$this->assign("tusers",$tusers);
			$this->assign("detailed",$detailed);
			$this->display();
    	}
	}
	public function exitcash(){
    	if(isset($_GET['id'])){
    		$id = $_GET['id'];
    		$ualogs = $this->Ualogs_obj
    		->where("id = $id")
    		->find();
			$nowtype = $ualogs['status_to'];
			$type = $this->Typeconfigs_obj
			->where(" typeGroup = 8 && typeID = $nowtype")
			->order("typeID asc")
			->find();
			$this->assign("ualogs", $ualogs);
			$this->assign("type",$type);
			$this->display();
    	}
	}
	public function exitcash_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if($_POST['description']){
			$transactionid = $_POST['transactionid'];
			$status_to = $_POST['status_to'];
			$description = $_POST['description'];
			
	        $ualog['transactionid'] = $transactionid;
	        $ualog['description1'] =  '客服('.$userid.'),'.$description.'->[申请退款]';
	        $ualog['status_from'] = $status_to;
	        $ualog['status_to'] = 190;
	        $ualog['created'] = date("Y-m-d H:i:s");
	        $ualog['user_type'] = 200;
	        $ualog['userid'] = $userid;
		      if($this->Ualogs_obj -> add($ualog)){
				$uactions = $this->Useractions_obj
	    		->where("id = $transactionid")
	    		->find();
				$extraDesc = '根据您的申请，客服已经为您提交了退款申请，12小时内会按照你支付的方式退回，请注意查看!';
				$push = localhost_url.'index.php?g=api&m=umeng&a=index&userid='.$uactions['userid'].'&describe='.$extraDesc;
				$banner = $this->ryq_array($push);
    			if($banner['ret'] == 'SUCCESS'){
	    			$this->Useractions_obj->where("id=$transactionid")->setField('status','190');
			        $this -> success('退款申请成功,已推送通知',U("Order/detailed",array('id'=>$transactionid)));
    			}else{
	    			$this->Useractions_obj->where("id=$transactionid")->setField('status','190');
			        $this -> success('退款申请成功!',U("Order/detailed",array('id'=>$transactionid)));
    			}
		      }else{
		        $this -> error('退款申请失败!');
		      }
		}else{
				$this->error("请填写退款原因！");
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
	        $data['transactionid'] = $id;
	        $data['description1'] = '客服('.$userid.'),已向仓库发送配货要求->[已配货]';
	        $data['status_from'] = 160;
	        $data['status_to'] = 165;
	        $data['created'] = date("Y-m-d H:i:s");
	        $data['user_type'] = 200;
	        $data['userid'] = $userid;
    		if ($this->Ualogs_obj -> add($data)) {
    			$this->Useractions_obj->where("id=$id")->setField('status','165') ;
    			$this->success("订单配货成功！");
    		} else {
    			$this->error('订单配货失败！');
    		}
    	} else {
			$this->error($this->Shop_obj->getError());
    	}
    }
	public function cancelpicking(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
    	$id=intval($_GET['id']);
    		$rst = $this->Useractions_obj->where("id=$id")->setField('status','160');
			        $ualog['transactionid'] = $id;
			        $ualog['description1'] = "客服(".$userid."),取消配货->[已付款]";
			        $ualog['status_from'] = 165;
			        $ualog['status_to'] = 160;
			        $ualog['created'] = date("Y-m-d H:i:s");
			        $ualog['user_type'] = 200;
			        $ualog['userid'] = $userid;
    		if ($rst) {
    			$this->Ualogs_obj -> add($ualog);
    			$this->success("取消发货成功！");
    		} else {
    			$this->error('取消发货失败！');
    		}
	}
	public function getArr( $file ){
        if(!file_exists( $file )){
            return false;//判断文件是否存在
        }
        $res = array();
        $arr = file($file);
        foreach($arr as $value){
            $value = trim( $value );
            if( empty($value)){
                continue;//去除空行
            }
			$value=explode(",", $value);
            $res[] = $value;//确定第二维度
        }
        return $res;
    }
	function expChangeCode($data){
		$encode = mb_detect_encoding($data, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5')); 
		if($encode=="UTF-8"){
		        return $data;
		}else{
				$data = eval('return '.iconv('gbk','utf-8',var_export($data,true)).';');
		        return $data;
		}
    }
	public function logistics(){
		$this->display();
	}
	public function logistics_post(){
			if($_SESSION['userid']>0){
				$userid = $_SESSION['userid'];
			}else{
				$userid = get_current_admin_id();
			}
			setlocale(LC_ALL, 'en_US.UTF-8');
			$filepath1 = "data/runtime/";
			if($_FILES["associatetxt"]["name"]){  
					$path1 = $_FILES["associatetxt"]["name"]; 
					$path2 =time().substr($path1,strpos($path1,'.'));
				if(substr($path1,strpos($path1,'.'))!='.csv'){
				   	$this -> error('文件格式不正确');
				}
			}
			if($_FILES["associatetxt"]["size"]>2048000){
			   	$this -> error('TXT文件大小不得超过2MB');
			}
			move_uploaded_file($_FILES["associatetxt"]["tmp_name"],$filepath1.$path2);
			$file = $filepath1.$path2; 
			$arr = $this->getArr($file);
			while ($data = fgetcsv($file)) {
				$goods_list[] = $data;
			}
			$ca = count($arr[0]);
			$number = $this->_arr();
			$name = $value = '';
			for($i=2;$i<count($arr);$i++){
				for($j=0;$j<$ca;$j++){
					$str = $this->expChangeCode($arr[$i][$j]);
					if($str){
						for($l=0;$l<count($number);$l++){
							$ustr1 = mb_substr($str,0,2,'utf-8');
							$ustr = $number[$l]['name'];
							$ustr2 = mb_substr($ustr,0,2,'utf-8');
							if($ustr1 == $ustr2){
								$name = $number[$l]['name'];
								$value = $number[$l]['value'];
							}
						}
					}else{
						break;
					}
				}
				if($str){
					$Expre = $Ualog = $User = 0;
					$num = $this->expChangeCode($arr[$i][0]);
					$infoid = $this->expChangeCode($arr[$i][2]);
					$Useractions = $this->Useractions_obj
					->where("id = $infoid")
					->find();
					$status = $Useractions['status'];
					if (is_numeric($num) && is_numeric($infoid)){
						if($status == 165){
							$express['express_type']=$value;
							$express['express_text']=$name;
							$express['express_code']=$num;
							$express['relatedid']= $infoid;
							$express['relatedtype']= '12';
			        		$express['created'] = date("Y-m-d H:i:s");
			        		$express['userid'] = $userid;
					        $ualog['transactionid'] = $infoid;
					        $ualog['description1'] = "客服(".$userid."),批量导入更新物流订单".$name.":".$num."->[已发货]";
					        $ualog['status_from'] = 165;
					        $ualog['status_to'] = 170;
					        $ualog['created'] = date("Y-m-d H:i:s");
					        $ualog['user_type'] = 200;
					        $ualog['userid'] = $userid;
							$actions['id'] =  $infoid;
							$actions['status'] =  170;
							$Expre = $this->Expressinfos_obj -> add($express);
							$Ualog = $this->Ualogs_obj -> add($ualog);
							$User = $this->Useractions_obj -> data($actions)->save();
							if(!$Expre || !$Ualog || !$User){
								$this->error("物流更新失败！");
							}
							$uactions = $this->Useractions_obj
				    		->where("id = $infoid")
				    		->find();
							$extraDesc = '您购买的['.msubstr(str_replace(' ','_',$uactions['description']),0,16).']，'.$name.'已经发出（单号：'.$num.'），请您稍后注意查看物流变化。';
							$push = localhost_url.'index.php?g=api&m=umeng&a=index&userid='.$uactions['userid'].'&describe='.$extraDesc;
							$banner = $this->ryq_array($push);
						}
					}else{
							$this->error("订单号或订单".$infoid."的物流单号".$num."不正确！");
					}
				}
			}
			fclose($file);
//			$ids = array_column($actions,'id');
//			$Ualog = $this->Ualogs_obj -> addAll($ualog);
//			$Expre = $this->Expressinfos_obj -> addAll($express);
//			$User = $this->Useractions_obj ->saveAll($ids,$actions);
			if($Expre && $Ualog && $User){
				$this->success("物流更新成功！", U("Order/index"));
			}else{
				$this->error("物流更新失败！");
			}
	}
}