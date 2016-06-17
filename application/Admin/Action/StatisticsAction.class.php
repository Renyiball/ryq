<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-16
	描述：注册统计
**********************************/
class StatisticsAction extends AdminbaseAction {
	protected $Usercodes_obj,$Users_obj,$Contacts_obj,$Userdetails_obj,$Images_obj,$Useractions_obj,$Forums_obj,$Userroles_obj,$Userbets_obj,$Typeconfigs_obj,$Favorites_obj,$Teams_obj,$Bets_obj,$Matchinfos_obj;
	function _initialize() {
		parent::_initialize();
		$this->Usercodes_obj = D("Usercodes");//邀请码
		$this->Users_obj = D("Users");//用户
		$this->Contacts_obj = D("Contacts");//地址
		$this->Userdetails_obj = D("Userdetails");
		$this->Images_obj = D("Imagedocs");//图片
		$this->Useractions_obj = D("Useractions");//订单
		$this->Forums_obj = D("Forums");//帖子
		$this->Userroles_obj = D("Userroles");//球队
		$this->Userbets_obj = D("Userbets");//猜球
		$this->Typeconfigs_obj = D("Typeconfigs");//对照表
		$this->Favorites_obj = D("Favorites");
		$this->Teams_obj = D("Teams");//球队
		$this->Bets_obj = D("Bets");//猜球
		$this->Matchinfos_obj = D("Matchinfos");//比赛
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
		$count_json = sizeof( $de_json['return_data']);
		for ($i = 0; $i <$count_json; $i++)
		{
			$js_data[$i] = $de_json['return_data'][$i];
		}
		return $js_data;
	}	
	public function index(){
    	$this->_list();
    }
	public function _arr(){
		$typearr  = array(
						array("id"=>"1010","name"=> "安卓客户端  —  手机号码"),
						array("id"=>"2010","name"=> "苹果客户端  —  手机号码"),
						array("id"=>"3010","name"=> "h5页面  —  手机号码"),
						array("id"=>"4010","name"=> "微信公众平台  —  手机号码"),
						array("id"=>"5010","name"=> "pc网站  —  手机号码"),
						array("id"=>"1020","name"=> "安卓客户端  —  微信登陆"),
						array("id"=>"2020","name"=> "苹果客户端  —  微信登陆"),
						array("id"=>"3020","name"=> "h5页面  —  微信登陆"),
						array("id"=>"4020","name"=> "微信公众平台  —  微信登陆"),
						array("id"=>"5020","name"=> "pc网站  —  微信登陆"),
						array("id"=>"1030","name"=> "安卓客户端  —  QQ登陆"),
						array("id"=>"2030","name"=> "苹果客户端  —  QQ登陆"),
						array("id"=>"3030","name"=> "h5页面  —  QQ登陆"),
						array("id"=>"4030","name"=> "微信公众平台  —  QQ登陆"),
						array("id"=>"5030","name"=> "pc网站  —  QQ登陆"),
						array("id"=>"6060","name"=> "客户端  —  球队报名")
					);
		return $typearr;
	}

	public function _arr2(){
		$typearr  = array(
						array("id"=>"100",		"name"=> "android手机号"),
						array("id"=>"110",		"name"=> "iphone手机号"),
						array("id"=>"10008",	"name"=> "赛事活动注册"),
						array("id"=>"10021",	"name"=> "颠球引入注册"),
						array("id"=>"20100",	"name"=> "微信用户登录"),
						array("id"=>"20110",	"name"=> "QQ用户登录"),
						array("id"=>"10000",	"name"=> "异常数据")
					);
		return $typearr;
	}
	public function _list(){
		$parakey_single = array ('typeclass','old','userid','nickname','code','telephone','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$length = '&& (length(regSource)=7)';
	    if(isset($parameters)){
			if($parameters['typeclass'] > 0)
			{
				$typeclass = " && (regSource like '".$parameters['typeclass']."%')";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['old'] == '0')
			{
				$length = " && (length(regSource)<6)";
				$_GET['old'] = $parameters['old'];
			}
			if($parameters['userid'] != '')
			{
				$userid = " id=".$parameters['userid'];
				$_GET['userid'] = $parameters['userid'];
			}
			if($parameters['nickname'] != '')
			{
				$nickname = "(nickname like '%".$parameters['nickname']."%')";
				$_GET['nickname'] = $parameters['nickname'];
			}
			if($parameters['code'] != '')
			{
				$ucode = "my_invite_code = '".$parameters['code']."'";
				$_GET['code'] = $parameters['code'];
				$usercode = $this->Usercodes_obj
				->where("$ucode")
				->order("created DESC")
				->find();
				$ucodeid = "id=".$usercode['relateduserid'];
			}
			if($parameters['telephone'] != '')
			{
				$telephone = "telephone = '".$parameters['telephone']."'";
				$_GET['telephone'] = $parameters['telephone'];
				$contacts = $this->Contacts_obj
				->where("$telephone")
				->order("created DESC")
				->find();
				$uphoneid = "id=".$contacts['userid'];
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
			if($parameters['userid'] && $usercode && $contacts){
				$uid = " && (".$userid."||".$ucodeid."||".$uphoneid.")";
			}
			
			if($parameters['userid'] && $usercode && !$contacts){
				$uid = " && (".$userid."||".$ucodeid.")";
			}
			if($parameters['userid'] && !$usercode && $contacts){
				$uid = " && (".$userid."||".$uphoneid.")";
			}
			if(!$parameters['userid'] && $usercode && $contacts){
				$uid = " && (".$ucodeid."||".$uphoneid.")";
			}
			
			if($parameters['userid'] && !$usercode && !$contacts){
				$uid = " && (".$userid.")";
			}
			if(!$parameters['userid'] && $usercode && !$contacts){
				$uid = " && (".$ucodeid.")";
			}
			if(!$parameters['userid'] && !$usercode && $contacts){
				$uid = " && (".$uphoneid.")";
			}
			if($uid && $parameters['nickname']){
				$nickname = " || ".$nickname;
			}
			if(!$uid && $parameters['nickname'] != ''){
				$nickname = " && ".$nickname;
			}
			if($uid){
				$search = $typeclass.$uid.$nickname.$start_time.$end_time;
			}else{
				$search = $length.$typeclass.$nickname.$start_time.$end_time;
			}
			
		}
			$count=$this->Users_obj->where("id > 0 $search")-> count();
			$page = $this->page($count, 20);
			$items = $this->Users_obj
			->where("id > 0 $search")
			->order("id DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			for($i=0;$i<count($items);$i++){
				if($i<count($items)-1){
					$uid = "relatedID = '".$items[$i]['id']."' || ";
					$deta = "relatedUserID = '".$items[$i]['id']."' || ";
					$code = "relateduserid = '".$items[$i]['id']."' || ";
				}else{
					$uid = "relatedID = '".$items[$i]['id']."'";
					$deta = "relatedUserID = '".$items[$i]['id']."'";
					$code = "relateduserid = '".$items[$i]['id']."'";
				}
				$users = $users.$uid;
				$detais = $detais.$deta;
				$codes = $codes.$code;
			}
			$userinfo = $this->Userdetails_obj
			->where("$detais")
			->order("id DESC")
			->select();
			$contacts = $this->Contacts_obj
			->where("relatedType = 5 &&($users)")
			->order("userid desc")
			->select();
			$usercodes = $this->Usercodes_obj
			->where("$codes")
			->order("created DESC")
			->select();
			$type = $this->_arr();
			foreach ($type as $key=>$value){
			    $id[$key] = $value['id'];
			    $name[$key] = $value['name'];
			}
			array_multisort($name,SORT_NUMERIC,SORT_DESC,$id,SORT_STRING,SORT_ASC,$type);
			if($parameters['old'] == '0'){
				$type = $this->_arr2();	
			}
	    	$this->assign('type',$type);
	    	$this->assign('uinfo',$userinfo);
	    	$this->assign('num',$count);
			$this->assign("contacts",$contacts);
			$this->assign("usercodes",$usercodes);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("lists",$items);
			$this->assign("formpost",$parameters);
			$this->display();
	}
		public function source(){
			$type = $this->_arr();
			$count = array();
			for($i=0;$i<count($type);$i++)
			{
				$num=$type[$i]['id'];
				$count[$i]=$this->Users_obj->field("id,regSource")->where("id > 0  && (length(regSource)=7) && regSource like '$num%'")-> count();
			}
			$this->assign('num',$count);
			$this->display();
	}
		public function month(){
			
			$count = array();$now='';
			$tim = date('Y-m',strtotime('-6 month'));
			$p_date=$this->Users_obj->field("id,created")->where("id > 0 && (created > '$tim')")-> select();
			for($j=0;$j<6;$j++)
			{
				$num=0;
				$now = date('Y-m',strtotime('-'.$j.' month'));
				$num=$this->Users_obj->field("id,created")->where("id > 0 && date_format(created,'%Y-%m')='$now'")->count();
				$count[$j]['mon'] = $now;
				$count[$j]['num'] = $num;
			}
			$this->assign('num',$count);
			$this->display();
	}
		public function day(){
			$parakey_single = array ('start_time','end_time');
			$parameters = $this->_GetRequestPara_All ( $parakey_single );
			if($parameters){
				if($parameters['start_time'] != "")
				{
					$start_time = " && ('".$parameters['start_time']."' <= date(created))";
					$_GET['start_time'] = $parameters['start_time'];
				}
				if($parameters['end_time'] !="")
				{
					$end_time = " && (date(created) <= '".$parameters['end_time']."')";
					$_GET['end_time'] = $parameters['end_time'];
				}
				$search = $start_time.$end_time;
			}
				$count = array();
			if(!$search){
				for($j=0;$j<30;$j++)
				{
					$num=0;
					$now = date('Y-m-d',strtotime('-'.$j.' day'));
					$num=$this->Users_obj->field("id,created")->where("id > 0 && date(created) = '$now'")->count();
					$count[$j]['mon'] = date('m-d',strtotime('-'.$j.' day'));
					$count[$j]['num'] = $num;
				}
			}else{
				$end_time=$_GET['end_time'];
				$start_time=$_GET['start_time'];
				if(!$start_time){$this->error("请填入开始时间");}
				if(!$end_time){$end_time=date('Y-m-d');}
				$numday = floor((strtotime($end_time)-strtotime($start_time))/86400)+1;
				for($j=0;$j<$numday;$j++){
					$num=0;
					$now = date('Y-m-d',strtotime($end_time.'-'.$j.' day'));
					$num =$this->Users_obj->field("id,created")->where("id > 0 && date(created) = '$now'")->count();
					$count[$j]['mon'] = date('m-d',strtotime($end_time.'-'.$j.' day'));
					$count[$j]['num'] = $num;
				}
			}
			$this->assign('num',$count);
			$this->assign("formpost",$parameters);
			$this->display();
	}
	public function move(){
		if(IS_POST){
			if(isset($_GET['ids']) && isset($_POST['types'])){
				$tids=$_GET['ids'];
				$entrance = $_POST['entrance'];
				$source = $_POST['source'];
				$types = $_POST['types'];
				$usertype = $entrance.$source.$types;
				$user['regSource'] = $usertype;
				if ( $this->Users_obj->where("id in ($tids)")->save($user) ) {
					$this->success("来源调整成功！");
				} else {
					$this->error("来源调整失败！");
				}
			}
		}else{
    		$this->display();
		}
	}
	public function edit(){
        $id=intval($_GET['id']);
		$p=$_GET['p'];
		$c=$_GET['c'];
		$image = $this->Images_obj
		->where("relatedType = 5 && relatedID=$id")
		->order("id desc")
		->find();
		$users = $this->Users_obj
		->where("id = $id")
		->order("id DESC")
		->find();
		$contacts = $this->Contacts_obj
		->where("relatedID = $id  && relatedType = 5")
		->order("userid desc")
		->find();
		$usercodes = $this->Usercodes_obj
		->where("relateduserid = $id")
		->order("created DESC")
		->find();
		$userdetails = $this->Userdetails_obj
		->where("relatedUserID = $id")
		->order("id DESC")
		->find();
		$this->assign("image",$image);
		$this->assign("users",$users);
		$this->assign("contacts",$contacts);
		$this->assign("usercodes",$usercodes);
		$this->assign("userdetails",$userdetails);
		$this->assign("imgtituan",webroot_url);
		$this->assign("p",$p);
		$this->assign("c",$c);
    	$this->display();
	}
	public function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $created = date("Y-m-d H:i:s");
    	if(isset($_POST)){
    		$id = $_POST['userid'];
			$user['username'] = $_POST['username'];
			$user['nickname'] = $_POST['nickname'];
			$user['modified'] = $created;
			if($_POST['cid']){
				$contacts['street1'] = $_POST['street1'];
				$contacts['telephone'] = $_POST['telephone'];
				$contacts['modified'] = $created;
			}
			if($_POST['uid']){
				$userdetails['realname'] = $_POST['realname'];
				$userdetails['gender'] = $_POST['gender'];
				$userdetails['dob'] = $_POST['dob'];
				$userdetails['age'] = $_POST['age'];
				$userdetails['personid'] = $_POST['personid'];
				$userdetails['height'] = $_POST['height'];
				$userdetails['weight'] = $_POST['weight'];
				$userdetails['modified'] = $created;
				$userdetails['userid'] = $userid;
			}
			if($_FILES){
				$filepath = 'img/Users/'.date("Y").'/'.date("m").'/';
				//判断目录是否存在/不存在就创建
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				if($_FILES["teamimg"]["name"]){  
						$path1 = $_FILES["teamimg"]["name"]; 
						$path2 =time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["teamimg"]["size"]>256000){ 
				   	$this -> error('图片文件大小不得超过256KB');
				}
				if(move_uploaded_file($_FILES["teamimg"]["tmp_name"],webroot_img.$filepath.$path2)){
					$image['relatedType']=5;
					$image['relatedID']=$id;
					$image['filepath']=$filepath;
					$image['filename']=$path2;
					$image['modified'] = $created;
					$image['created'] = $created;
					$image['userid'] = $userid;
					if(!$this->Images_obj -> add($image)){
					    $this -> error('添加用户头像失败');
					}
				}
			}
			if($this->Users_obj->where("id = $id")->data($user)->save()){
				if($_POST['cid']){
					$this->Contacts_obj->where("relatedID = $id && relatedType = 5")->data($contacts)->save();
				}
				if($_POST['uid']){
					$this->Userdetails_obj->where("relatedUserID = $id")->data($userdetails)->save();
				}
				if($_POST['c']){
					$this -> success('修改成功!',U("statistics/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
				}else{
					$this -> success('修改成功!',U("statistics/index",array('p'=>$_POST['p'])));
				}
			} else {
				$this->error("用户资料修改失败！");
			}
    	}
	}
	public function detailed(){
        $id=intval($_GET['id']);
		$users = $this->Users_obj
		->where("id = $id")
		->order("id DESC")
		->find();
		$contacts = $this->Contacts_obj
		->where("relatedID = $id  && relatedType = 5")
		->order("userid desc")
		->find();
		$class = $this->Forums_obj
		->where("parentid = 1 && displayorder > 100")
		->select();
		for($i=0;$i<count($class);$i++){
				if($i<count($class)-1){
					$parentid = "parentid =".$class[$i]['id'].' || ';
					$parent =$parent.$parentid;
				}else{
					$parentid = "parentid =".$class[$i]['id'];
					$parent =$parent.$parentid;
				}
		}
			$search = ' && ('.$parent.')';
		$actionscount=$this->Useractions_obj->where("parentID = 0 && actionType!=10030 && status > 0 && userid =$id")-> count();
		$forumsrelease=$this->Forums_obj->where("typeid = 200 && userid =$id $search")-> count();
		$forumsreply=$this->Forums_obj->where("typeid > 200 && userid = $id && parentid > 200")->count();
		//$this->error(json_encode($forumsreply));
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID > 0 && relatedUserID = $id")
		->order("id desc")
		->group("relatedID")
		->select();
		$userrolescount = count($userroles);
		$guesscount = $this->Userbets_obj
		->where("betid > 0 && pointtype=15000 && userid =$id")
		->order("id desc")
		->count();
		$winningcount = $this->Userbets_obj
		->where("betid = -101 && pointtype=15000 && userid =$id")
		->order("id desc")
		->count();
		$correctcount = $this->Userbets_obj
		->where("betid > 0 && pointtype=15000 && status=160 && userid =$id")
		->order("id desc")
		->count();
		$errorcount = $this->Userbets_obj
		->where("betid > 0 && pointtype=15000 && status=150 && userid =$id")
		->order("id desc")
		->count();
		$usersid = $users['id'];
		$url = webroot_url.'userdetails/apprequest?art=301&loginuserid='.$usersid;
   		$banner = $this->ryq_array($url);
   		$bannercount = count($banner);
		$usercodes = $this->Usercodes_obj
		->where("relateduserid = $usersid")
		->order("created DESC")
		->find();
		$favorites = $this->Favorites_obj
		->where("typeid = 3 && status = 160 && relatedid=$usersid")
		->order("id desc")
		->count();
		$currency=M()->query("select fget_my15k_points($usersid)");
		$this->assign("users",$users);
		$this->assign("contacts",$contacts);
		$this->assign("actionscount",$actionscount);
		$this->assign("forumsrelease",$forumsrelease);
		$this->assign("forumsreply",$forumsreply);
		$this->assign("userrolescount",$userrolescount);
		$this->assign("guesscount",$guesscount);
		$this->assign("winningcount",$winningcount);
		$this->assign("correctcount",$correctcount);
		$this->assign("errorcount",$errorcount);
	    $this->assign('currency',$currency[0]['fget_my15k_points('.$usersid.')']);
		$this->assign("bannercount",$bannercount);
		$this->assign("usercodes",$usercodes);
		$this->assign("favorites",$favorites);
    	$this->display();
	}

	public function order(){
        $id=intval($_GET['id']);
		$count=$this->Useractions_obj->where("parentID = 0 && actionType!=10030 && status > 0 && userid =$id")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Useractions_obj
		->where("parentID = 0 && actionType!=10030 && status > 0 && userid =$id")
		->order("id DESC")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$users = $this->Users_obj
		->where("id = $id")
		->order("id desc")
		->select();
		$type = $this->Typeconfigs_obj
		->where("typeGroup = 8 && typeID > 0")
		->order("typeID asc")
		->select();
		$detailed = $this->Useractions_obj
		->where("parentID != 0 && actionType=10010 && userid = $id")
		->order("id DESC")
		->select();
		$pays = $this->Useractions_obj
		->where("parentID != 0 && actionType=30010 && userid = $id")
		->order("id DESC")
		->select();
		//$this->error(json_encode($count));
    	$this->assign('num',$count);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("lists",$lists);
		$this->assign("users",$users);
		$this->assign("type",$type);
		$this->assign("detailed",$detailed);
		$this->assign("pays",$pays);
    	$this->display();
	}
	public function ballbar(){
        $id=intval($_GET['id']);
		$class = $this->Forums_obj
		->where("parentid = 1 && displayorder > 100")
		->select();
		for($i=0;$i<count($class);$i++){
				if($i<count($class)-1){
					$parentid = "parentid =".$class[$i]['id'].' || ';
					$parent =$parent.$parentid;
				}else{
					$parentid = "parentid =".$class[$i]['id'];
					$parent =$parent.$parentid;
				}
		}
			$search = ' && ('.$parent.')';
		$count=$this->Forums_obj->where("typeid = 200 && userid = $id $search")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Forums_obj
		->where("typeid = 200 && userid = $id $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$class = $this->Forums_obj
		->where("typeid = 100 && displayorder > 100")
		->select();
		$users = $this->Users_obj
		->where("id = $id")
		->order("id desc")
		->select();
		$forumid = $favoritesid = ' && ';
		for($i=0;$i<count($lists);$i++){
			if($i<count($lists)-1){
				$parentid = "(parentid = '".$lists[$i]['id']."') || ";
				$relatedid = "(relatedid = '".$lists[$i]['id']."') || ";
			}else{
				$parentid = "(parentid = '".$lists[$i]['id']."')";
				$relatedid = "(relatedid = '".$lists[$i]['id']."')";
			}
			$forumid = $forumid.$parentid;
			$favoritesid = $favoritesid.$relatedid;
		}
		$parents = $this->Forums_obj
		->where("typeid = '300' $forumid")
		->order("id desc")
		->select();
		$favorites = $this->Favorites_obj
		->where("typeid = 2 && status = 160 && relatedtype = 4 $favoritesid")
		->order("id desc")
		->select();
    	$this->assign('num',$count);
		$this->assign("lists",$lists);
    	$this->assign('class',$class);
    	$this->assign('users',$users);
    	$this->assign('parents',$parents);
    	$this->assign('favorites',$favorites);
		$this->assign("Page", $page->show('Admin'));
    	$this->display();
	}
	public function reply(){
    	if($_GET['id']){
		$id = $_GET['id'];
		$count=$this->Forums_obj->where("typeid > 200 && userid = $id && parentid > 200")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Forums_obj
		->where("typeid > 200 && userid = $id && parentid > 200")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$replys = $this->Forums_obj
		->where("typeid = 400")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$users = $this->Users_obj
		->where("id = $id")
		->order("id desc")
		->select();
		$class = $this->Forums_obj
		->where("parentid = 1 && displayorder > 100")
		->select();
		for($i=0;$i<count($class);$i++){
				if($i<count($class)-1){
					$parentid = "parentid =".$class[$i]['id'].' || ';
					$parent =$parent.$parentid;
				}else{
					$parentid = "parentid =".$class[$i]['id'];
					$parent =$parent.$parentid;
				}
		}
			$search = ' && ('.$parent.')';
		$class = $this->Forums_obj
		->where("typeid = 200 $search")
		->select();
    	$this->assign('num',$count);
		$this->assign("lists",$lists);
		$this->assign("replys",$replys);
    	$this->assign('class',$class);
    	$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
		}else{
				$this->error("非法提交");
		}
	}
	public function team(){
        $id=intval($_GET['id']);
		$userroles = $this->Userroles_obj
		->where("relatedtype = 10 && relatedID > 0 && relatedUserID = $id")
		->order("id desc")
		->group("relatedID")
		->select();
		for($i=0;$i<count($userroles);$i++){
			if($i<count($userroles)-1){
				$teamid = "id =".$userroles[$i]['relatedID']." || ";
				$rolesid = "relatedID =".$userroles[$i]['relatedID']." || ";
			}else{
				$teamid = "id =".$userroles[$i]['relatedID'];
				$rolesid = "relatedID =".$userroles[$i]['relatedID'];
			}
			$team =$team.$teamid;
			$role = $role.$rolesid;
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
		->where("id = $id")
		->order("id desc")
		->select();
		$roles = $this->Userroles_obj
		->where("relatedtype = 10 && $role")
		->order("id desc")
		->select();
		$this->assign("userroles",$userroles);
		$this->assign("users",$users);
		$this->assign("roles",$roles);
		$this->assign("id",$id);
		$this->assign("teams",$teams);
	    $this->assign('count',$count);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	public function guess(){
        $id=intval($_GET['id']);
		$count = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 && userid = $id")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 && userid = $id")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($userbets);$i++){
			if($i<count($userbets)-1){
				$betid = "id =".$userbets[$i]['betid']." || ";
			}else{
				$betid = "id =".$userbets[$i]['betid'];
			}
			$bet =$bet.$betid;
		}
		$bets = $this->Bets_obj
		->where("$bet")
		->order("id desc")
		->select();
		for($i=0;$i<count($bets);$i++){
			if($i<count($bets)-1){
				$matchid = "matchconstid ='".$bets[$i]['matchinfoid']."' || ";
			}else{
				$matchid = "matchconstid ='".$bets[$i]['matchinfoid']."'";
			}
			$match =$match.$matchid;
		}
		$matchinfos = $this->Matchinfos_obj
		->where("$match")
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
		$users = $this->Users_obj
		->where("id = $id")
		->order("id DESC")
		->select();
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign('bets',$bets);
		$this->assign('matchinfos',$matchinfos);
		$this->assign('teams',$teams);
		$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
	    $this->display();
	}
	public function exchange(){
        $id=intval($_GET['id']);
		$count = $this->Userbets_obj
		->where("betid=-101 && userid =$id ")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid=-101 && userid =$id ")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$users = $this->Users_obj
		->where("id = $id")
		->order("id desc")
		->select();
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("users",$users);
	    $this->display();
	}
	public function correct(){
        $id=intval($_GET['id']);
		$count = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 && status = 160 && userid = $id")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 && status = 160 &&  userid = $id")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($userbets);$i++){
			if($i<count($userbets)-1){
				$betid = "id =".$userbets[$i]['betid']." || ";
			}else{
				$betid = "id =".$userbets[$i]['betid'];
			}
			$bet =$bet.$betid;
		}
		$bets = $this->Bets_obj
		->where("$bet")
		->order("id desc")
		->select();
		for($i=0;$i<count($bets);$i++){
			if($i<count($bets)-1){
				$matchid = "matchconstid ='".$bets[$i]['matchinfoid']."' || ";
			}else{
				$matchid = "matchconstid ='".$bets[$i]['matchinfoid']."'";
			}
			$match =$match.$matchid;
		}
		$matchinfos = $this->Matchinfos_obj
		->where("$match")
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
		$users = $this->Users_obj
		->where("id = $id")
		->order("id DESC")
		->select();
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign('bets',$bets);
		$this->assign('matchinfos',$matchinfos);
		$this->assign('teams',$teams);
		$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
	    $this->display();
	}
	public function wrong(){
        $id=intval($_GET['id']);
		$count = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 && status = 150 && userid = $id")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid>0 && pointtype=15000 && status = 150 &&  userid = $id")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($userbets);$i++){
			if($i<count($userbets)-1){
				$betid = "id =".$userbets[$i]['betid']." || ";
			}else{
				$betid = "id =".$userbets[$i]['betid'];
			}
			$bet =$bet.$betid;
		}
		$bets = $this->Bets_obj
		->where("$bet")
		->order("id desc")
		->select();
		for($i=0;$i<count($bets);$i++){
			if($i<count($bets)-1){
				$matchid = "matchconstid ='".$bets[$i]['matchinfoid']."' || ";
			}else{
				$matchid = "matchconstid ='".$bets[$i]['matchinfoid']."'";
			}
			$match =$match.$matchid;
		}
		$matchinfos = $this->Matchinfos_obj
		->where("$match")
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
		$users = $this->Users_obj
		->where("id = $id")
		->order("id DESC")
		->select();
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign('bets',$bets);
		$this->assign('matchinfos',$matchinfos);
		$this->assign('teams',$teams);
		$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
	    $this->display();
	}
	public function money(){
        $id=intval($_GET['id']);
		$count = $this->Userbets_obj
		->where("betid>=0 && pointtype=15000 && userid = $id")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid>=0 && pointtype=15000 && userid = $id")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($userbets);$i++){
			if($i<count($userbets)-1){
				$betid = "id =".$userbets[$i]['betid']." || ";
			}else{
				$betid = "id =".$userbets[$i]['betid'];
			}
			$bet =$bet.$betid;
		}
		$bets = $this->Bets_obj
		->where("$bet")
		->order("id desc")
		->select();
		for($i=0;$i<count($bets);$i++){
			if($i<count($bets)-1){
				$matchid = "matchconstid ='".$bets[$i]['matchinfoid']."' || ";
			}else{
				$matchid = "matchconstid ='".$bets[$i]['matchinfoid']."'";
			}
			$match =$match.$matchid;
		}
		$matchinfos = $this->Matchinfos_obj
		->where("$match")
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
		$users = $this->Users_obj
		->where("id = $id")
		->order("id DESC")
		->select();
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign('bets',$bets);
		$this->assign('matchinfos',$matchinfos);
		$this->assign('teams',$teams);
		$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
	    $this->display();
	}
}