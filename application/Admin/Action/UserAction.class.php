<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-01
	描述：本站用户
**********************************/
class UserAction extends AdminbaseAction{
	protected $users_obj,$role_obj,$Actioninfos_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->users_obj = D("T_users");
		$this->role_obj = D("T_role");
		$this->Actioninfos_obj = D("Actioninfos");
	}
	function index(){
		$count=$this->users_obj->where(array("user_type"=>1))->count();
		$page = $this->page($count, 20);
		$users=$this->users_obj
		->where(array("user_type"=>1))
		->order("user_status DESC")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$roles_src=$this->role_obj->select();
		$roles=array();
		foreach ($roles_src as $r){
			$roleid=$r['id'];
			$roles["$roleid"]=$r;
		}
		$this->assign("roles",$roles);
		$this->assign("users",$users);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	
	
	function add(){
		$roles=$this->role_obj->where("status=1")->select();
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && status = 100 && subtype = 20000")
		->order("id asc")
		->select();
		$this->assign("roles",$roles);
		$this->assign("action",$action);
		$this->display();
	}
	
	function add_post(){
		if(IS_POST){
			if ($this->users_obj->create()) {
				if ($this->users_obj->add()!==false) {
					$this->success("添加成功！", U("user/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->users_obj->getError());
			}
		}
	}
	
	
	function edit(){
		$id= intval(I("get.id"));
		$roles=$this->role_obj->where("status=1")->select();
		$this->assign("roles",$roles);
			
		$user=$this->users_obj->where(array("id"=>$id))->find();
		$action = $this->Actioninfos_obj
		->where("parentid = 0 && status = 100 && subtype = 20000")
		->order("id asc")
		->select();
		$this->assign($user);
		$this->assign("action",$action);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			if(empty($_POST['user_pass'])){
				unset($_POST['user_pass']);
			}
			if ($this->users_obj->create()) {
				$result=$this->users_obj->save();
				if ($result!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->users_obj->getError());
			}
		}
	}
	
	/**
	 *  删除
	 */
	function delete(){
		$id = intval(I("get.id"));
		if($id==1){
			$this->error("最高管理员不能删除！");
		}
		$userimg = $this->users_obj->where("id=$id")->select();
		$avatar = 'data/upload/avatar/'.$userimg[0]['avatar'];
		if (file_exists($avatar)){ unlink ($avatar);}
		if ($this->users_obj->where("id=$id")->delete()!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	
	function userinfo(){
		$userid = get_current_admin_id();
		$user=$this->users_obj->where(array("id"=>$userid))->find();
		$this->assign($user);
		$this->display();
	}
	
	function userinfo_post(){
		if (IS_POST) {
				$filepath = 'data/upload/avatar/';
				//判断目录是否存在/不存在就创建
				if(!file_exists($filepath)){
					mkdir($filepath, 0777,true);  
				}
				if($_FILES["avatar"]["name"]){  
						$path1 = $_FILES["avatar"]["name"]; 
						$path2 = time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["avatar"]["size"]>512000){ 
				   	$this -> error('图片文件大小不得超过512KB');
				}
				move_uploaded_file($_FILES["avatar"]["tmp_name"],$filepath.$path2);
				if($path1){
					$_POST['avatar'] = $path2;
				}
			if ($this->users_obj->create()) {
				if ($this->users_obj->save()!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->users_obj->getError());
			}
		}
	}
	
	
	function ban(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->users_obj->where(array("id"=>$id,"user_type"=>1))->setField('user_status','0');
    		if ($rst) {
    			$this->success("管理员停用成功！", U("user/index"));
    		} else {
    			$this->error('管理员停用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    function cancelban(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->users_obj->where(array("id"=>$id,"user_type"=>1))->setField('user_status','1');
    		if ($rst) {
    			$this->success("管理员启用成功！", U("user/index"));
    		} else {
    			$this->error('管理员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
	
	
	
}