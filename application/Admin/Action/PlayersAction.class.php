<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-08-04
	描述：球员管理
**********************************/
class PlayersAction extends AdminbaseAction {
	
	protected $Userroles_obj,$Actioninfos_obj,$Typeconfigs_obj,$Userdetails_obj,$Teams_obj,$Users_obj,$Images_obj,$Contacts_obj;
		
    function _initialize(){
		parent::_initialize();
		$this->Userroles_obj = D("Userroles");//球员表
		$this->Actioninfos_obj = D("Actioninfos");//比赛类型
		$this->Typeconfigs_obj = D("Typeconfigs");//属性
		$this->Userdetails_obj = D("Userdetails");//人员属性
		$this->Teams_obj = D("Teams");//球队
		$this->Users_obj = D("Users");//用户
		$this->Images_obj = D("Imagedocs");//图片
		$this->Contacts_obj = D("Contacts");//地址
	}
	//验证身份证是否有效
	function validateIDCard($IDCard){
	    if (strlen($IDCard) == 18) {
	        return $this->check18IDCard($IDCard);
	    } elseif ((strlen($IDCard) == 15)) {
	        $IDCard = $this->convertIDCard15to18($IDCard);
	        return $this->check18IDCard($IDCard);
	    } else {
	        return false;
	    }
	}
	
	//计算身份证的最后一位验证码,根据国家标准GB 11643-1999
	function calcIDCardCode($IDCardBody) {
	    if (strlen($IDCardBody) != 17) {
	        return false;
	    }
	
	    //加权因子 
	    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	    //校验码对应值 
	    $code = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
	    $checksum = 0;
	
	    for ($i = 0; $i < strlen($IDCardBody); $i++) {
	        $checksum += substr($IDCardBody, $i, 1) * $factor[$i];
	    }
	
	    return $code[$checksum % 11];
	}
	
	// 将15位身份证升级到18位 
	function convertIDCard15to18($IDCard) {
	    if (strlen($IDCard) != 15) {
	        return false;
	    } else {
	        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码 
	        if (array_search(substr($IDCard, 12, 3), array('996', '997', '998', '999')) !== false) {
	            $IDCard = substr($IDCard, 0, 6) . '18' . substr($IDCard, 6, 9);
	        } else {
	            $IDCard = substr($IDCard, 0, 6) . '19' . substr($IDCard, 6, 9);
	        }
	    }
	    $IDCard = $IDCard.$this->calcIDCardCode($IDCard);
	    return $IDCard;
	}
	
	// 18位身份证校验码有效性检查 
	function check18IDCard($IDCard) {
	    if (strlen($IDCard) != 18) {
	        return false;
	    }
	
	    $IDCardBody = substr($IDCard, 0, 17); //身份证主体
	    $IDCardCode = strtoupper(substr($IDCard, 17, 1)); //身份证最后一位的验证码
	
	    if ($this->calcIDCardCode($IDCardBody) != $IDCardCode) {
	        return false;
	    } else {
	        return true;
	    }
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('typeclass','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['typeclass'] != 0)
			{
				$start_time = " && (relatedID = ".$parameters['typeclass'].")";
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
			$search = $start_time.$end_time;
		}
			$count=$this->Userroles_obj->where("entitytype != '' $search")-> count();
			$page = $this->page($count, 20);
			$roles = $this->Userroles_obj
			->where("entitytype != '' $search")
			->order("created DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			for($i=0;$i<count($roles);$i++){
				$uroles[$i] = $roles[$i]['relatedUserID'];
			}
			$uroles = array_merge(array_unique($uroles));
			for($i=0;$i<count($uroles);$i++){
				if($i<count($uroles)-1){
					$urolesid = "relatedUserID = ".$uroles[$i]." || ";
					$ucid = "relatedID =".$uroles[$i]." || ";
				}else{
					$urolesid = "relatedUserID = ".$uroles[$i];
					$ucid = "relatedID =".$uroles[$i];
				}
				$relatedUserID =$relatedUserID.$urolesid;
				$ucids =$ucids.$ucid;
			}
			$details = $this->Userdetails_obj
			->where("$relatedUserID")
			->order("created DESC")
			->select();
			$configs = $this->Typeconfigs_obj
			->where("typeGroup = 9 && typeID >0")
			->order("typeGroup DESC")
			->select();
			$infos = $this->Actioninfos_obj
			->where("subtype = 20000 && parentid = 0")
			->order("parentid ASC")
			->select();
			
			
			for($i=0;$i<count($infos);$i++){
				if($i<count($infos)-1){
					$infosid = "(teamtype = '".$infos[$i]['constid']."' || extradesc like '%[".$action[$i]['constid']."-%' ) || ";
				}else{
					$infosid = "(teamtype = '".$infos[$i]['constid']."' || extradesc like '%[".$action[$i]['constid']."-%' )";
				}
				$constid = $constid.$infosid;
			}
			$teams = $this->Teams_obj
			->where("(teamtype = '10100') || $constid")
			->order("id desc")
			->select();
			$contacts = $this->Contacts_obj
			->where("relatedType = 5 && ($ucids)")
			->order("modified desc")
			->select();
			$date = date("Y-m-d");
		    $this->assign('roles',$roles);
		    $this->assign('details',$details);
		    $this->assign('configs',$configs);
		    $this->assign('infos',$infos);
		    $this->assign('teams',$teams);
		    $this->assign('num',$count);
			$this->assign("contacts",$contacts);
		    $this->assign('date',$date);
			$this->assign("formpost",$parameters);
			$this->assign("Page", $page->show('Admin'));
			$this->display();
	}
	public function add(){
			$configs = $this->Typeconfigs_obj
			->where("typeGroup = 9 && typeID >0")
			->order("typeGroup DESC")
			->select();
			$infos = $this->Actioninfos_obj
			->where("subtype = 20000 && parentid = 0")
			->order("parentid ASC")
			->select();
			
			
			for($i=0;$i<count($infos);$i++){
				if($i<count($infos)-1){
					$infosid = "(teamtype = '".$infos[$i]['constid']."') || ";
				}else{
					$infosid = "(teamtype = '".$infos[$i]['constid']."')";
				}
				$constid = $constid.$infosid;
			}
			$teams = $this->Teams_obj
			->where("$constid")
			->order("created DESC")
			->select();
		    $this->assign('configs',$configs);
		    $this->assign('teams',$teams);
			$this->display();
    }
	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $created = date("Y-m-d H:i:s");
		if($_POST['teamid'] == 0){ $this->error("请选择球队");}
		if($_POST['typeID'] == 0){ $this->error("请选择角色");}
		if(!$_POST['play_number']){ $this->error("请输入球服号");}
		if(!$_POST['realname']){ $this->error("请输入姓名");}
	    if(isset($_POST)){
	    	$typeID = $_POST['typeID'];
			$configs = $this->Typeconfigs_obj
			->where("typeGroup = 9 && typeID >0 && typeID = $typeID ")
			->order("typeGroup DESC")
			->select();
			$config = $configs[0]['typeName'];
			$teamid = $_POST['teamid'];
			$teams = $this->Teams_obj
			->where("id = $teamid")
			->order("created DESC")
			->select();
			$team = $teams[0]['teamname'];
			$number = $_POST['play_number'];
			$role = $this->Userroles_obj
			->where("relatedID = $teamid && play_number = $number")
			->order("created DESC")
			->select();
			if($role){
				$this->error("球衣号码已存在");
			}
				$user['nickname']=$team.'_'.$config.'_'.$_POST['play_number'].'号';
				$user['regSource']=6060000;
				$user['created'] = $created;
				$user['modified'] = $created;
				$users = $this->Users_obj -> add($user);
				$contact['relatedType']=5;
				$contact['relatedID']=$users;
				$contact['telephone'] = $_POST['phone'];
				$contact['created'] = $created;
				$contact['modified'] = $created;
				$contact['userid'] = $userid;
				$this->Contacts_obj -> add($contact);
			if($_FILES){
				$filepath = 'img/Users/'.date("Y").'/'.date("m").'/';
				//判断目录是否存在/不存在就创建
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				if($_FILES["homeimg"]["name"]){  
						$path1 = $_FILES["homeimg"]["name"]; 
						$path2 = time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["teamimg"]["size"]>256000){ 
				   	$this -> error('图片文件大小不得超过256KB');
				}
				if(move_uploaded_file($_FILES["homeimg"]["tmp_name"],webroot_img.$filepath.$path2)){
					$image['relatedType']=5;
					$image['relatedID']=$users;
					$image['filepath']=$filepath;
					$image['filename']=$path2;
					$image['modified'] = $created;
					$image['created'] = $created;
					$image['userid'] = $userid;
					if(!$this->Images_obj -> add($image)){
					    $this -> error('添加球员头像失败');
					}
				}
			}
		        $details['relatedUserID'] = $users;
				$details['realname']=$_POST['realname'];
				if($_POST['dob']){ $details['dob']=$_POST['dob'];}
	    	if($_POST['personid']){
				if(strlen($_POST['personid']) == 15 || strlen($_POST['personid']) == 18){
			    	if($this->validateIDCard($_POST['personid'])){
						$details['personid'] = $_POST['personid'];
			    	}else{
						    $this -> error('身份证号码错误<a href="http://www.nciic.com.cn" target="_black">去查查</a>');
			    	}
				}else{
						$this -> error('身份证号码长度错误');
			    }
	    	}else{
						$details['personid'] = '';
	    	}
				if($_POST['height']){ $details['height'] = $_POST['height'];}
				if($_POST['weight']){ $details['weight'] = $_POST['weight'];}
				$details['created'] = $created;
				$details['modified'] = $created;
				$details['userid'] = $userid;
		        $roles['roleTypeID'] = $typeID;
				$roles['roleTypeGroup']=9;
				$roles['relatedtype']=10;
				$roles['relatedID']=$teamid;
				$roles['entitytype']=$teams[0]['teamtype'];
				$roles['relatedUserID']=$users;
				$roles['play_number']=$number;
				$roles['created'] = $created;
				$roles['userid'] = $userid;
				if($this->Userroles_obj -> add($roles) && $this->Userdetails_obj -> add($details)){
					$this -> success('球员信息添加成功!', U("Players/index"));
				}else{
				    $this -> error('球员信息添加失败');
				}
	    }else{
			$this->error($this->Userroles_obj->getError());
	    }
    }
	public function edit(){
        $id=intval($_GET['id']);
		$p=$_GET['p'];
		$c=$_GET['c'];
			$roles = $this->Userroles_obj
			->where("relatedUserID = $id")
			->order("created DESC")
			->find();
			$relatedid = $roles['relatedID'];
			$teams = $this->Teams_obj
			->where("id = $relatedid")
			->order("created DESC")
			->find();
			$configs = $this->Typeconfigs_obj
			->where("typeGroup = 9 && typeID >0")
			->order("typeGroup DESC")
			->select();
			$details = $this->Userdetails_obj
			->where("relatedUserID = $id")
			->order("created DESC")
			->find();
			$image = $this->Images_obj
			->where("relatedType = 5 && relatedID=$id")
			->order("id desc")
			->find();
			$contacts = $this->Contacts_obj
			->where("relatedType = 5 && relatedID=$id")
			->order("modified desc")
			->find();
			$this->assign("imgtituan",webroot_url);
    		$this->assign('userid',$id);
		    $this->assign('roles',$roles);
		    $this->assign('teams',$teams);
		    $this->assign('configs',$configs);
		    $this->assign('details',$details);
			$this->assign("image",$image);
			$this->assign("contacts",$contacts);
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
        $modified = date("Y-m-d H:i:s");
		if($_POST['teamid'] == 0){ $this->error("请选择球队");}
		if($_POST['typeID'] == 0){ $this->error("请选择角色");}
		if(!$_POST['play_number']){ $this->error("请输入球服号");}
		if(!$_POST['realname']){ $this->error("请输入姓名");}
	    if(isset($_POST)){
		        $relatedUserID = $_POST['userid'];
				$details['realname']=$_POST['realname'];
				if($_POST['dob']){ $details['dob']=$_POST['dob'];}
				if($_POST['height']){ $details['height'] = $_POST['height'];}
				if($_POST['weight']){ $details['weight'] = $_POST['weight'];}
				
	    	if($_POST['personid']){
				if(strlen($_POST['personid']) == 15 || strlen($_POST['personid']) == 18){
			    	if($this->validateIDCard($_POST['personid'])){
						$details['personid'] = $_POST['personid'];
			    	}else{
						    $this -> error('身份证号码错误<a href="http://www.nciic.com.cn" target="_black">去查查</a>');
			    	}
				}else{
						$this -> error('身份证号码长度错误');
			    }
	    	}else{
						$details['personid'] = '';
	    	}
				$details['modified'] = $modified;
				$details['userid'] = $userid;
				$typeid = $_POST['teamid'];
				$number = $_POST['play_number'];
					$role = $this->Userroles_obj
					->where("relatedUserID != $relatedUserID && relatedID = $typeid && play_number = $number")
					->order("created DESC")
					->select();
					if($role){
						$this->error("球衣号码已存在");
					}
		        $roles['roleTypeID'] = $_POST['typeID'];
				$roles['play_number']=$number;
				$roles['userid'] = $userid;
				$contacts['telephone'] = $_POST['phone'];
				$contacts['modified'] = $modified;
				$contacts['userid'] = $userid;
				$contact = $this->Contacts_obj 
				-> where("relatedType = 5 && relatedID = $relatedUserID")
				->order("created DESC")
				->select();
				if(!$contact){
					$contacts['relatedType']=5;
					$contacts['relatedID']=$relatedUserID;
					$contacts['created'] = $modified;
					$this->Contacts_obj -> add($contacts);
				}else{
					$this->Contacts_obj -> where("relatedType = 5 && relatedID = $relatedUserID")->data($contacts)->save();
				}
			if($_FILES){
				$filepath = 'img/Users/'.date("Y").'/'.date("m").'/';
				//判断目录是否存在/不存在就创建
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				if($_FILES["teamimg"]["name"]){  
						$path1 = $_FILES["teamimg"]["name"]; 
						$path2 = time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["teamimg"]["size"]>256000){ 
				   	$this -> error('图片文件大小不得超过256KB');
				}
				/*
					$img_path = webroot_img.$filepath.$filename;
					$thumb = $filepath.'thumb/';
					$save_path = webroot_img.$thumb.time();
					$type = $this->thumb_img($img_path,400,$save_path,true);
				 */
				if(move_uploaded_file($_FILES["teamimg"]["tmp_name"],webroot_img.$filepath.$path2)){
					$image['relatedType']=5;
					$image['relatedID']=$relatedUserID;
					$image['filepath']=$filepath;
					$image['filename']=$path2;
					$image['modified'] = $modified;
					$image['created'] = $modified;
					$image['userid'] = $userid;
					if(!$this->Images_obj -> add($image)){
					    $this -> error('添加球员头像失败');
					}
				}
			}
				$role = $this->Userroles_obj -> where("relatedUserID = $relatedUserID")->data($roles)->save();
				$detail = $this->Userdetails_obj -> where("relatedUserID = $relatedUserID")->data($details)->save();
				if($role || $detail){
					if($_POST['c']){
						$this -> success('球队修改成功!',U("Players/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
					}else{
						$this -> success('球队修改成功!',U("Players/index",array('p'=>$_POST['p'])));
					}
				}else{
				    $this -> error('球员信息修改失败');
				}
	    }else{
			$this->error("非法提交！");
	    }
    }
	public function delete(){
        if (isset($_GET['id'])) {
        	$id = intval(I("get.id"));
			if($this->Userroles_obj->where("relatedUserID=$id")->delete() && $this->Userdetails_obj->where("relatedUserID=$id")->delete() && $this->Users_obj->where("id=$id")->delete() && $this->Contacts_obj ->where("relatedID=$id")->delete()){
				$imgname = $this->Images_obj->where("relatedType = 5 && relatedID=$id")->select();
				$imgurl = webroot_img.$imgname[0]['filepath'].$imgname[0]['filename'];
				if (file_exists($imgurl))
				{
				    $delete_ok = unlink ($imgurl);
				}
				$this->Images_obj->where("relatedType = 5 && relatedID=$id")->delete();
				$this -> success('删除成功!', U("Players/index"));
			}else{
				$this->error("删除失败！");
			}
        }else{
			$this->error("非法提交！");
        }
	}
	//图片删除
    public function delete_images() {
        if (intval(I("get.id"))) {
            $id = intval(I("get.id"));
			$imgname = $this->Images_obj->where("relatedType = 5 && relatedID=$id")->select();
			$imgurl = webroot_img.$imgname[0]['filepath'].$imgname[0]['filename'];
			if (file_exists($imgurl))
			{
			    $delete_ok = unlink ($imgurl);
			}
			if ($this->Images_obj->where("relatedType = 5 && relatedID=$id")->delete()) {
			    $this -> success('删除成功!');
        	} else {
            	$this->error("删除失败！");
        	}
        }else{
			$this->error("非法提交！");
        }
    }
	
}