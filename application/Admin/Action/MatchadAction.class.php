<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-08-21
	描述：赛事广告
**********************************/
class MatchadAction extends AdminbaseAction {
	protected $Moredetails_obj,$Actioninfos_obj;
	function _initialize() {
		parent::_initialize();
		$this->Moredetails_obj = D("Moredetails");
		$this->Actioninfos_obj = D("Actioninfos");
	}
	public function index(){
    	$this->_list();
    }
	public function showorder() {
		$status = parent::_showorder($this->Moredetails_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	public function _list(){
		$count=$this->Moredetails_obj-> where("parentid = 'action_adv' && relatedtype ='8'")->count();
		$page = $this->page($count, 20);
		$more = $this->Moredetails_obj
		->where("parentid = 'action_adv' && relatedtype ='8'")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && status = 100 && subtype = 20000")
		->order("id asc")
		->select();
		$this->assign("action",$action);
		$this->assign("Page", $page->show('Admin'));
	    $this->assign('num',$count);
		$this->assign("imgtituan",webroot_url);
		$this->assign("more",$more);
    	$this->display();
	}
	public function add(){
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && status = 100 && subtype = 20000")
		->order("id asc")
		->select();
		$this->assign("action",$action);
    	$this->display();
	}
	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		$created = date("Y-m-d H:i:s");
		if($_POST['typeclass']=='0'){
		        $this -> error('请选择赛事!');
		}
		if(!$_FILES){
		        $this -> error('请选择图片!');
		}
		$sname = $_POST['typeclass'];
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && constid = '$sname' && subtype = 20000")
		->order("id asc")
		->select();
		$detail_key = $action[0]['actionName'];
        if (isset($_POST)) {
				$filepath = 'img/Manually/actioninfo/';
				//判断目录是否存在/不存在就创建
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				if($_FILES["teamimg"]["name"]){  
						$path1 = $_FILES["teamimg"]["name"]; 
						$path2 = time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["teamimg"]["size"]>512000){ 
				   	$this -> error('图片文件大小不得超过512KB');
				}
				if(move_uploaded_file($_FILES["teamimg"]["tmp_name"],webroot_img.$filepath.$path2)){
			        $data['parentid'] ='action_adv';
			        $data['relatedtype'] = '8';
			        $data['relatedid'] =$_POST['typeclass'];
			        $data['detail_key'] = $detail_key;
			        $data['detail_value'] = $filepath.$path2;
			        $data['showorder'] = 100;
			        $data['created'] = $created;
			        $data['userid'] = $userid;
					if($this->Moredetails_obj -> add($data)){
		       			 $this -> success('广告添加成功!', U("Ad/index"));
					}else{
					    $this -> error('广告添加失败');
					}
				}
        }else{
			$this->error($this->Moredetails_obj->getError());
	    }
	}
	public function edit(){
        $id=intval($_GET['id']);
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && status = 100 && subtype = 20000")
		->order("id asc")
		->select();
    	$result = $this->Moredetails_obj
		->where("id = $id")
		->find();
		$this->assign("action",$action);
		$this->assign("result",$result);
		$this->assign("imgtituan",webroot_url);
    	$this->display();
	}
	public function edit_post(){
	     $id = $_POST['id'];
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		$modified = date("Y-m-d H:i:s");
		if($_POST['typeclass']=='0'){
		        $this -> error('请选择赛事!');
		}
		$sname = $_POST['typeclass'];
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && constid = '$sname' && subtype = 20000")
		->order("id asc")
		->select();
		$detail_key = $action[0]['actionName'];
        if (isset($_POST)) {
        	if($_FILES){
				$moredetails = $this->Moredetails_obj
				->where("parentid = 'action_adv' && relatedtype = '8' && relatedid = '$sname'")
				->order("id asc")
				->select();
				$imgurl = webroot_img.$moredetails[0]['detail_value'];
				if (file_exists($imgurl)){ unlink ($imgurl);}
				$filepath = 'img/Manually/actioninfo/';
				//判断目录是否存在/不存在就创建
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				if($_FILES["teamimg"]["name"]){  
						$path1 = $_FILES["teamimg"]["name"]; 
						$path2 = time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["teamimg"]["size"]>512000){ 
				   	$this -> error('图片文件大小不得超过512KB');
				}
				move_uploaded_file($_FILES["teamimg"]["tmp_name"],webroot_img.$filepath.$path2);
			    $data['detail_value'] = $filepath.$path2;
        	}
			        $data['relatedid'] =$_POST['typeclass'];
			        $data['detail_key'] = $detail_key;
			        $data['created'] = $modified;
			        $data['userid'] = $userid;
					if($this->Moredetails_obj ->where("id = $id")->data($data)->save()){
		       			 $this -> success('广告添加成功!', U("Ad/index"));
					}else{
					    $this -> error('广告添加失败');
					}
        }else{
			$this->error($this->Moredetails_obj->getError());
	    }	
	}
	
	public function delete(){
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
			$imgname = $this->Moredetails_obj->where("id=$id")->select();
			if ($imgname) {
			$imgurl = webroot_img.$imgname[0]['detail_value'];
			if (file_exists($imgurl))
			{
			    $delete_ok = unlink ($imgurl);
			}
				$this->Moredetails_obj->where("id=$id")->delete();
			    $this -> success('删除成功!');
        	} else {
            	$this->error("删除失败！");
        	}
        }else{
			$this->error("非法提交！");
        }
	}
}