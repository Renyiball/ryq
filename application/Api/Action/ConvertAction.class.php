<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-02-22
	描述：装备兑换
**********************************/
class ConvertAction extends AppframeAction {
	protected	$Item_exchanges_obj,
				$Userbets_obj,
				$Contacts_obj,
				$Useractions_obj,
				$Iteminfos_obj,
				$Images_obj,
				$Itemdetails_obj,
				$Feeinfos_obj,
				$Comments_obj,
				$Users_obj,
				$Userexchanges_obj,
				$Uexchglogs_obj,
				$Expressinfos_obj,
				$Bets_obj,
				$Typeconfigs_obj;
	function _initialize() {
		parent::_initialize();
		$this->Item_exchanges_obj = D("Item_exchanges");
		$this->Userbets_obj = D("Userbets");
		$this->Contacts_obj = D("Contacts");
		$this->Useractions_obj = D("Useractions");
		$this->Iteminfos_obj = D("Iteminfos");
		$this->Images_obj = D("imagedocs");
		$this->Itemdetails_obj = D("Itemdetails");
		$this->Feeinfos_obj = D("Feeinfos");
		$this->Comments_obj = D("Comments");
		$this->Users_obj = D("Users");
		$this->Userexchanges_obj = D("Userexchanges");
		$this->Uexchglogs_obj = D("Uexchglogs");
		$this->Expressinfos_obj = D("Expressinfos");
		$this->Bets_obj = D("Bets");
		$this->Typeconfigs_obj = D("Typeconfigs");
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
		$count_json = sizeof( $de_json['return_data']['data']);
		$js_data['code'] = $de_json['return_data']['express_code'];
		$js_data['text'] = $de_json['return_data']['express_text'];
		for ($i = 0; $i <$count_json; $i++)
		{
			$js_data['data'][$i] = $de_json['return_data']['data'][$i];
		}
		return $js_data;
	}	
    public function index() {
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$today = date("Y-m-d H:i:s");
		$exchanges = $this->Item_exchanges_obj
		->where("itemtype = 500200 && status = 200 && date(period_from) <= '$today' && date(period_to) >= '$today'")
		->order("id desc")
		->select();
		foreach($exchanges as$key=>$exc){
			$sid[$key] = $exc['itemid'];
		}
		$itemid =array('id'=>array("in",array_merge(array_unique($sid))));
		$imagesid =array('relatedType = 6','subtype < 100','relatedID'=>array("in",array_merge(array_unique($sid))));
		$propertys =array('itemID'=>array("in",array_merge(array_unique($sid))));
		$iteminfos = $this->Iteminfos_obj
		->where($itemid)
		->order("id desc")
		->select();
		$images = $this->Images_obj
		->where($imagesid)
		->order("relatedID asc")
		->group("relatedID")
		->select();
		$property = $this->Itemdetails_obj
		->where($propertys)
		->order("itemID asc")
		->group("itemID")
		->select();
		foreach($property as$key=>$pro){
			$pid[$key] = $pro['id'];
		}
		$prices =array('relatedType = 7','relatedID'=>array("in",array_merge(array_unique($pid))));
		$price = $this->Feeinfos_obj
		->where($prices)
		->order("relatedID desc")
		->select();
		$typeconfigs = $this->Typeconfigs_obj
		->where("typeGroup = 25 && typeID > 25000")
		->select();
	    $this->assign('exchanges',$exchanges);
	    $this->assign('iteminfos',$iteminfos);
	    $this->assign('images',$images);
	    $this->assign('property',$property);
	    $this->assign('price',$price);
	    $this->assign('typeconfigs',$typeconfigs);
	    $this->assign('userid',$url_userid);
		$this->assign("imgtituan",webroot_url);
       	$this->display();

	}
    public function detail() {
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_exchid = @$_GET['exchid'] ? $_GET['exchid'] : 0;
		$exchanges = $this->Item_exchanges_obj
		->where(array('id'=>$url_exchid))
		->order("id desc")
		->find();
		$iteminfos = $this->Iteminfos_obj
		->where(array('id'=>$exchanges['itemid']))
		->order("id desc")
		->find();
		$images = $this->Images_obj
		->where(array('relatedType = 6','relatedID'=>$exchanges['itemid']))
		->order("relatedID asc")
		->select();
		$property = $this->Itemdetails_obj
		->where(array('itemID'=>$exchanges['itemid']))
		->order("itemID asc")
		->group("itemID")
		->find();
		$price = $this->Feeinfos_obj
		->where(array('relatedType = 7','relatedID'=>$property['id']))
		->order("relatedID desc")
		->find();
		$comments = $this->Comments_obj
		->where(array('relatedType = 6','relatedID'=>$exchanges['itemid']))
		->order("id desc")
		->select();
		foreach($comments as$key=>$com){
			$uid[$key] = $com['userid'];
		}
		$users = $this->Users_obj
		->where(array('id'=>array("in",array_merge(array_unique($uid)))))
		->order("id desc")
		->select();
		$typeconfigs = $this->Typeconfigs_obj
		->where("typeGroup = 25 && typeID > 25000")
		->select();
	    $this->assign('iteminfos',$iteminfos);
	    $this->assign('image',$image);
	    $this->assign('images',$images);
	    $this->assign('property',$property);
	    $this->assign('price',$price);
	    $this->assign('comments',$comments);
	    $this->assign('users',$users);
	    $this->assign('exchanges',$exchanges);
	    $this->assign('userid',$url_userid);
	    $this->assign('exchid',$url_exchid);
	    $this->assign('typeconfigs',$typeconfigs);
		$this->assign("imgtituan",webroot_url);
       	$this->display();
	}
    public function order() {
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_exchid = @$_GET['exchid'] ? $_GET['exchid'] : 0;
		
		$nowday = date("Y-m-d");
		$today = date("Y-m-d",strtotime('monday'));
		$lastmonday = date("Y-m-d",strtotime('last monday -1 week'));
		$nextmonday = date("Y-m-d",strtotime('next monday -1 week'));
		if($today == $nowday){
			$monday = $nextmonday;
		}else{
			$monday = $lastmonday;
		}
		$sunday = date("Y-m-d",strtotime('next sunday -1 week'));	
		$exchanges = $this->Item_exchanges_obj
		->where(array('id'=>$url_exchid))
		->order("id desc")
		->find();
		$property = $this->Itemdetails_obj
		->where(array('itemID'=>$exchanges['itemid'],"originalHeld >0"))
		->order("itemID asc")
		->select();
		$prop = $this->Itemdetails_obj->where(array('itemID'=>$exchanges['itemid']))->find();
		$iteminfos = $this->Iteminfos_obj
		->where(array('id'=>$prop['itemID']))
		->find();
		$users = $this->Users_obj
		->where(array('id'=>$url_userid))
		->order("id desc")
		->find();
//  	$currencys = $this->Userbets_obj
//  	->where("userid = $url_userid")
//  	->select();
//		$consumption=$this->Useractions_obj
//		->where("parentID > 0 && (actionType=40050 || actionType=40051) && userid =$url_userid")
//		->select();
//		foreach($currencys as $bc){
//			$points = $bc['points'];
//			$odds = $bc['odds'];
//			$status = $bc['status'];
//			if($points>0 && $odds>0){
//				if($status == 160){$value = $value+($points*$odds);}
//				if($status == 150){$value = $value-($points);}
//				if($status == 140){$value = $value-($points);}
//			}
//		}
//		foreach($consumption as $ac){
//			$amount = $ac['amount']*100;
//			if($ac['actionType'] == 40050){$value = $value-$amount;}
//			if($ac['actionType'] == 40051){$value = $value+$amount;}
//		}
//		foreach($uexchanges as $ue){
//			$points = $ue['points'];
//			$value = $value-$points;
//		}
		$contacts = $this->Contacts_obj
		->where("relatedType = 5 && relatedID = $url_userid")
		->order("modified desc")
		->find();
		$typeconfigs = $this->Typeconfigs_obj
		->where("typeGroup = 25 && typeID > 25000")
		->select();
		$currency=M()->query("select fget_my15k_points($url_userid)");
	    $this->assign('currency',$currency[0]['fget_my15k_points('.$url_userid.')']);
	    $this->assign('property',$property);
	    $this->assign('exchanges',$exchanges);
	    $this->assign('contacts',$contacts);
	    $this->assign('shopid',$exchanges['itemid']);
	    $this->assign('iteminfos',$iteminfos);
	    $this->assign('users',$users);
	    $this->assign('userid',$url_userid);
	    $this->assign('exchid',$url_exchid);
	    $this->assign('typeconfigs',$typeconfigs);
       	$this->display();
	}
    public function level() {
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_exchid = @$_GET['exchid'] ? $_GET['exchid'] : 0;
		$typeconfigs = $this->Typeconfigs_obj
		->where("typeGroup = 25 && typeID > 25000")
		->select();
	    $this->assign('userid',$url_userid);
	    $this->assign('exchid',$url_exchid);
	    $this->assign('typeconfigs',$typeconfigs);
       	$this->display();
	}
    public function order_post() {
    	if(isset($_POST)){
			$num_field = $this->Bets_obj
			->where("'$monday' <= date(expiredate) && date(expiredate) <= '$sunday'")
			->order("id desc")
			->select();
			$field = count($num_field);
			for($f=0;$f<$field;$f++){
				if($f<$field-1){
					$betid = "(betid = ".$num_field[$f]['id'].") || ";
				}else{
					$betid = "(betid = ".$num_field[$f]['id'].")";
				}
				$bid = $bid.$betid;
			}
			$people = $this->Userbets_obj
			->where("userid = $url_userid && pointtype = 15000 && ($bid)")
			->order("id desc")
			->count();
			$correct = $this->Userbets_obj
			->where("userid = $url_userid && status = 160 && ($bid)")
			->order("id desc")
			->count();
	  		$percent = floor(($correct/$field)*100);
	  		$bout = $already = 0;
	  		if($percent >= 50){ $bout++;}
	  		if($people >= $field/2){ $bout++;}
			$uexchange=$this->Userexchanges_obj
			->where("userid =$url_userid")
			->select();
			$nextmonday = date("Y-m-d",strtotime('monday'));
			$nextmonday = date("Y-m-d",strtotime('next monday -1 week'));
			$sunday = date("Y-m-d",strtotime('sunday'));
			if($today == $nowday){
				$monday = $nextmonday;
			}else{
				$monday = $nextmonday;
			}
			foreach($uexchange as $uex){
				if($monday <= $uex['created'] && $uex['created'] <= $sunday){
					$already++;
				}
			}	
			if($_POST['typeID']){
				$typeid = $_POST['typeID'];
				$config = $this->Typeconfigs_obj
				->where("typeGroup = 25 && typeID = $typeid")
				->find();
				if(!($config['typeDesc'] < $_POST['currency'] && $config['extraDesc'] > $_POST['currency'])){
				    $this -> error('当前商品属于 <b>'.$config['typeName'].'</b> 专享，您不能兑换！');
				}
			}
			$typeconfigs = $this->Typeconfigs_obj
			->where("typeGroup = 24 && typeID > 24000")
			->find();
			if($bout <= $already && !$typeconfigs['typeDesc']){
			    $this -> error('您本周的兑换次数已用完');
			}
			if($_POST['currency'] < $_POST['points']){
			    $this -> error('您的可用球币不足');
			}
			$uexchanges=$this->Userexchanges_obj
			->where(array('exchid'=>$_POST['exchid'],'userid'=>$_POST['userid']))
			->find();
			if($uexchanges){
			    $this -> error('您已兑换过此商品');
			}
			if(!$_POST['colorsize']){
			    $this -> error('暂无库存');
			}
			$stock = $this->Item_exchanges_obj
			->where(array("id"=>$_POST['exchid']))
			->find();
			if($stock['uses'] >= $stock['cnt']){
			    $this -> error('商品剩余数量不足');
			}
    		$exchange['exchid']=$_POST['exchid'];
    		$exchange['itemtype']=$_POST['itemtype'];
    		$exchange['itemid']=$_POST['shopid'];
    		$exchange['detailid']=$_POST['colorsize'];
    		$exchange['points']=$_POST['points'];
    		$exchange['telephone']=$_POST['telephone'];
    		$exchange['street1']=$_POST['street1'];
    		$exchange['description']=$_POST['description'];
    		$exchange['status']=160;
    		$exchange['created']=date("Y-m-d H:i:s");
    		$exchange['userid']=$_POST['userid'];
			$userexchanges = $this->Userexchanges_obj->add($exchange);
			$ulogs['uexchangesid'] = $userexchanges;
			$ulogs['status_from'] = 145;
			$ulogs['status_to'] = 160;
			$ulogs['description1'] = "球币兑换 支付成功,更新订单状态->[支付成功]";
			$ulogs['created'] = date("Y-m-d H:i:s");
			$ulogs['userid'] = $_POST['userid'];
			$this->Uexchglogs_obj->add($ulogs);
			if($userexchanges && $ulogs){
				$this->Item_exchanges_obj
				->where(array("id"=>$_POST['exchid']))
				->setInc('uses');
				$this -> success('申请兑换成功!', U("convert/myorder",array('userid'=>$_POST['userid'])));
			}else{
			    $this -> error('申请兑换失败');
			}
    	}
	}
    public function myorder() {
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$uexchanges=$this->Userexchanges_obj
		->where(array('userid'=>$url_userid))
		->order("id desc")
		->select();
		foreach($uexchanges as$key => $ue){
			$itemid[$key] = $ue['itemid'];
			$itemdetail[$key] = $ue['detailid'];
			$commentsid[$key] = $ue['id'];
		}
		$itemid = array_merge(array_unique($itemid));
		$itemdetail = array_merge(array_unique($itemdetail));
		$images = $this->Images_obj
		->where(array('relatedType'=>6,'subtype <= 100','relatedID'=>array("in",$itemid)))
		->group("relatedID")
		->select();
		$iteminfos = $this->Iteminfos_obj
		->where(array('id'=>array("in",$itemid)))
		->select();
		$itemdetails = $this->Itemdetails_obj
		->where(array('id'=>array("in",$itemdetail)))
		->select();
		$comments = $this->Comments_obj
		->where(array('relatedType = 6','userid'=>$url_userid,'relatedID'=>array("in",$itemid),'transid'=>array("in",$commentsid)))
		->order("id desc")
		->select();
	    $this->assign('userid',$url_userid);
	    $this->assign('uexchanges',$uexchanges);
	    $this->assign('images',$images);
	    $this->assign('iteminfos',$iteminfos);
	    $this->assign('itemdetails',$itemdetails);
	    $this->assign('comments',$comments);
		$this->assign("imgtituan",webroot_url);
       	$this->display();
	}
    public function receiptorder(){
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_exchid = @$_GET['exchid'] ? $_GET['exchid'] : 0;
		$url_itemid = @$_GET['itemid'] ? $_GET['itemid'] : 0;
		$exchanges = $this->Userexchanges_obj->where("id=$url_exchid")->find();
	    $this->assign('userid',$url_userid);
	    $this->assign('exchid',$url_exchid);
	    $this->assign('itemid',$url_itemid);
	    $this->assign('exchange',$exchanges);
       	$this->display();
	}
    public function logisticsorder(){
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_exchid = @$_GET['exchid'] ? $_GET['exchid'] : 0;
		$url = webroot_url.'expressinfos/apprequest?art=30201&transid='.$url_exchid;
   		$banner = $this->ryq_array($url);
		$uexchanges=$this->Userexchanges_obj
		->where(array('id'=>$url_exchid))
		->order("id desc")
		->find();
	    $this->assign('code' ,$banner['code']);
	    $this->assign('text' ,$banner['text']);
	    $this->assign('data',$banner['data']);
	    $this->assign('userid',$url_userid);
	    $this->assign('exchid',$url_exchid);
	    $this->assign('uexchanges',$uexchanges);
       	$this->display();
	}
    public function receipt_post(){
    	if(isset($_POST)){
    		$itemid = $_POST['itemid'];
    		$exchid = $_POST['exchid'];
    		$userid = $_POST['userid'];
    		$status = $_POST['status'];
			$created = date("Y-m-d H:i:s");
			$uest = $this->Userexchanges_obj->where("id=$exchid")->find();
	    	$comment['relatedType'] = 6;
	    	$comment['relatedID'] =$itemid;
	    	$comment['transid']=$exchid;
	    	$comment['starScore']=$_POST['score'];
	    	$comment['commentText']=$_POST['comment'];
	    	$comment['created']=$created;
	    	$comment['modified']=$created;
	    	$comment['userid']=$userid;
			$comm = $this->Comments_obj->add($comment);
			$uexc = $this->Userexchanges_obj->where("id=$exchid")->setField('status','180');
			$log[0]['uexchangesid'] = $exchid;
			$log[0]['status_from'] = $status;
			$log[0]['status_to'] = 175;
			$log[0]['description1'] = "客户已收货,更新订单状态->[已收货]";
			$log[0]['created'] = $created;
			$log[0]['userid'] = $userid;
			$log[1]['uexchangesid'] = $exchid;
			$log[1]['status_from'] = 175;
			$log[1]['status_to'] = 180;
			$log[1]['description1'] = "客户已评价,更新订单状态->[已评价]";
			$log[1]['created'] = $created;
			$log[1]['userid'] = $userid;
			$ulog = $this->Uexchglogs_obj->addAll($log);
			if($uexc && $comm && $ulog){
				$this -> success('收货评论成功!', U("convert/index",array('userid'=>$_POST['userid'])));
			}else{
			    $this -> error('收货评论失败');
			}
    	}
	}
}
