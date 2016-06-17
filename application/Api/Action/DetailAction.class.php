<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-03-31
	描述：装备详情
**********************************/
class DetailAction extends AppframeAction {
	protected	$Iteminfos_obj,
				$Images_obj,
				$Itemdetails_obj,
				$Feeinfos_obj,
				$Comments_obj,
				$Users_obj;
	function _initialize() {
		parent::_initialize();
		$this->Iteminfos_obj = D("Iteminfos");
		$this->Images_obj = D("imagedocs");
		$this->Itemdetails_obj = D("Itemdetails");
		$this->Feeinfos_obj = D("Feeinfos");
		$this->Comments_obj = D("Comments");
		$this->Users_obj = D("Users");
		$this->Itemtypes_obj = D("Itemtypes");
		$this->Useractions_obj = D("Useractions");
	}	
    public function index() {
    	////http://192.168.0.13/ryq_server/api/Detail/index/userid/107/itemid/609.html
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_itemid = @$_GET['itemid'] ? $_GET['itemid'] : 0;
		$url_button = @$_GET['button'] ? $_GET['button'] : 0;
		$iteminfos = $this->Iteminfos_obj
		->where(array('id'=>$url_itemid))
		->order("id desc")
		->find();
		$itemtypes = $this->Itemtypes_obj
		->where(array('typeid'=>$iteminfos['item_type_b']))
		->order("id desc")
		->find();
		$images = $this->Images_obj
		->where(array('relatedType = 6','relatedID'=>$url_itemid))
		->order("relatedID asc")
		->select();
		$property = $this->Itemdetails_obj
		->where(array('itemID'=>$url_itemid))
		->order("itemID asc")
		->group("itemID")
		->find();
		$price = $this->Feeinfos_obj
		->where(array('relatedType = 7','relatedID'=>$property['id']))
		->order("relatedID desc")
		->find();
		$comments = $this->Comments_obj
		->where(array('relatedType = 6','relatedID'=>$url_itemid))
		->order("id desc")
		->select();
		foreach($comments as$key=>$com){
			$uid[$key] = $com['userid'];
			$uaid[$key] = $com['transid'];
		}
		$users = $this->Users_obj
		->where(array('id'=>array("in",array_merge(array_unique($uid)))))
		->order("id desc")
		->select();
		$useractions = $this->Useractions_obj
		->where(array('parentID'=>array("in",array_merge(array_unique($uaid))),'actionType'=>10010))
		->order("id desc")
		->select();
		foreach($useractions as$key=>$actions){
			$udet[$key] = $actions['actionID'];
		}
		$uidetails = $this->Itemdetails_obj
		->where(array('id'=>array("in",array_merge(array_unique($udet)))))
		->order("id desc")
		->select();
		$colorsize = $this->Itemdetails_obj
		->where(array('itemID'=>$url_itemid))
		->select();
		foreach($colorsize as $k=>$v) {
       		$attributes[$v["itemColor"]]["itemColor"]= $v["itemColor"];
       		$attributes[$v["itemColor"]]["itemSize"][] = $v["itemSize"];
       		$attributes[$v["itemColor"]]["id"][] = $v["id"];
			if($v["extraClass"]){
       			$attributes[$v["itemColor"]]["extraClass"] = $v["extraClass"];
			}
		}
//		dump($attributes);
	    $this->assign('iteminfos',$iteminfos);
	    $this->assign('itemtypes',$itemtypes);
	    $this->assign('images',$images);
	    $this->assign('price',$price);
	    $this->assign('comments',$comments);
	    $this->assign('users',$users);
	    $this->assign('useractions',$useractions);
	    $this->assign('uidetails',$uidetails);
	    $this->assign('attributes',$attributes);
	    $this->assign('extraClass',$extraClass);
	    $this->assign('userid',$url_userid);
	    $this->assign('itemid',$url_itemid);
	    $this->assign('button',$url_button);
		$this->assign("imgtituan",webroot_url);
       	$this->display();
	}
	function return_data($postArray){
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
		return $js_data = $de_json['return_data'];
	}
    public function addcar_post() {
    	$message =$bannert = NULL;
		if(empty($_POST['size'])){$message['data'] = "请选择商品尺码";}
		if(empty($_POST['color'])){$message['data'] = "请选择商品颜色";}
		if(empty($_POST['userid'])){$message['data'] = "用户尚未登陆";}
		if($_POST['size'] && $_POST['color'] && $_POST['userid'] > 0){
			$urlt = webroot_url."tradedetails/apprequest?art=20101&loginuserid=".$_POST['userid']."&itemdetailid=".$_POST['size'];
	   		$bannert = $this->return_data($urlt);
			if($bannert['alert_msg']){
				$message['data'] = $bannert['alert_msg'];
			}else{
				$message['data'] = '购物车内已存在';
			}
			$message['state'] = 'success';
		}else{
			$message['state'] = 'error';
		}
		$message['url'] = U('Detail/index',array('userid'=>$_POST['userid'],'itemid'=>$_POST['itemid']));
		$this->ajaxReturn($message,'JSON');
	}
	
	public function collect_post() {
    	$message =$bannert = NULL;
		if(empty($_POST['size'])){$message['data'] = "请选择商品尺码";}
		if(empty($_POST['color'])){$message['data'] = "请选择商品颜色";}
		if(empty($_POST['userid'])){$message['data'] = "用户尚未登陆";}
		if($_POST['size'] && $_POST['color'] && $_POST['userid'] > 0){
			$urlt = webroot_url."tradecollects/apprequest?art=20101&loginuserid=".$_POST['userid']."&itemdetailid=".$_POST['size'];
	   		$bannert = $this->return_data($urlt);
			if($bannert['alert_msg']){
				$message['data'] = $bannert['alert_msg'];
			}else{
				$message['data'] = '商品已收藏';
			}
			$message['state'] = 'success';
		}else{
			$message['state'] = 'error';
		}
		$message['url'] = U('Detail/index',array('userid'=>$_POST['userid'],'itemid'=>$_POST['itemid']));
		$this->ajaxReturn($message,'JSON');
	}

}
