<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-07-28
	描述：板块管理
**********************************/
class PlateAction extends AdminbaseAction {
	protected $Forums_obj,$Images_obj,$Forumsections_obj;
	function _initialize() {
		parent::_initialize();
		$this->Forums_obj = D("Forums");
		$this->Images_obj = D("Imagedocs");
		$this->Forumsections_obj = D("Forumsections");
		$this->Users_obj = D("T_users");
	}
	public function index(){
    	$this->_list();
    }

	public function _list(){
		$count=$this->Forumsections_obj->count();
		$page = $this->page($count, 20);
		$lists = $this->Forumsections_obj
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
    	$this->assign('num',$count);
		$this->assign("lists",$lists);
		$this->assign("users",$users);
		$this->assign("imgtituan",webroot_url);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	public function showorder() {
		$status = parent::_showorder($this->Forumsections_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	public function scrap(){
		$count=$this->Forums_obj->where("typeid = 100")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Forums_obj
		->where("typeid = 100")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$images = $this->Images_obj
		->where("relatedType = 4 && subtype = 100")
		->order("relatedID asc")
		->select();
    	$this->assign('num',$count);
		$this->assign("lists",$lists);
		$this->assign("images",$images);
		$this->assign("imgtituan",webroot_url);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	public function add(){
		$this->display();
	}
	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		$created = date("Y-m-d H:i:s");
		if($_POST['title']!=''){
		        $this -> error('板块名称不能为空!');
		}
		if($_POST['description']!=''){
		        $this -> error('板块描述不能为空!');
		}
        if (isset($_POST)) {
	        $data['sectiontitle'] = $_POST['sectiontitle'];
	        $data['description1'] = $_POST['description1'];
	        $data['description2'] = $_POST['description2'];
	        $data['status'] = $_POST['updown'];
	        $data['created'] = $created;
	        $data['userid'] = $userid;
			if($_FILES){
				$filepath = 'img/Manually/forums/';
				//判断目录是否存在/不存在就创建
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				if($_FILES["teamimg"]["name"]){  
						$path1 = $_FILES["teamimg"]["name"]; 
						$path2 = time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["teamimg"]["size"]>2048000){ 
				   	$this -> error('图片文件大小不得超过2MB');
				}
				if(move_uploaded_file($_FILES["teamimg"]["tmp_name"],webroot_img.$filepath.$path2)){
					$data['image_logo']=$filepath.$path2;
				}
			}
		      if($this->Forumsections_obj -> add($data)){
		        $this -> success('板块添加成功!', U("Plate/index"));
		      }else{
		        $this -> error('板块添加失败!');
		      }
        }else{
			$this->error($this->Forumsections_obj->getError());
	    }
	}
	public function edit(){
        $id=intval($_GET['id']);
		$class = $this->Forumsections_obj
		->where("id = $id")
		->find();
    	$this->assign('id',$id);
		$this->assign("imgtituan",webroot_url);
    	$this->assign('class',$class);
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
        if (isset($_POST)) {
	        $data['sectiontitle'] = $_POST['sectiontitle'];
	        $data['description1'] = $_POST['description1'];
	        $data['description2'] = $_POST['description2'];
	        $data['created'] = $modified;
	        $data['userid'] = $userid;
			if($_FILES){
				$img = $this->Forumsections_obj->where("id = $id")->find();
				if ($img) {
					$imgurl = webroot_img.$img['image_logo'];
					if (file_exists($imgurl)){ unlink ($imgurl);}
				}
				$filepath = 'img/Manually/forums/';
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
				if(move_uploaded_file($_FILES["teamimg"]["tmp_name"],webroot_img.$filepath.$path2)){
					$data['image_logo']=$filepath.$path2;
				}
			}
		      if($this->Forumsections_obj -> where("id = $id")->data($data)->save()){
		        $this -> success('板块修改成功!', U("Plate/index"));
		      }else{
		        $this -> error('板块修改失败!');
		      }
        }else{
			$this->error($this->Forumsections_obj->getError());
	    }		
	}
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
				$forum1 = $this->Forumsections_obj->where("id=$id")->order("id desc")->select();
				$forum2 = $this->Forums_obj->where("parentid='$id'")->order("id desc")->select();
				for($i=0;$i<count($forum2);$i++){
					if($i<count($forum2)-1){
						$pid1 = "(parentid = '".$forum2[$i]['id']."') || ";
					}else{
						$pid1 = "(parentid = '".$forum2[$i]['id']."')";
					}
					$parentid1 = $parentid1.$pid1;
				}
				$forum3 = $this->Forums_obj->where("$parentid1")->order("id desc")->select();
				for($i=0;$i<count($forum3);$i++){
					if($i<count($forum3)-1){
						$pid2 = "(parentid = '".$forum3[$i]['id']."') || ";
					}else{
						$pid2 = "(parentid = '".$forum3[$i]['id']."')";
					}
					$parentid2 = $parentid2.$pid2;
				}
			$imgname = $this->Forumsections_obj->where("relatedType = 4 && relatedID=$id")->find();
			$imgurl = webroot_img.$imgname['image_logo'];
			if (file_exists($imgurl))
			{
			    $delete_ok = unlink ($imgurl);
			}
						if ($this->Forumsections_obj->where("id=$id")->delete()) {
								$this->Forums_obj->where("parentid='$id'")->delete();
								$this->Forums_obj->where("$parentid1")->delete();
								$this->Forums_obj->where("$parentid2")->delete();
						        $this -> success('删除成功!');
		            	} else {
		                	$this->error("删除失败！");
		            	}
        }else{
			$this->error($this->Forums_obj->getError());
        }

    }
    public function enable() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forumsections_obj->where("id=$id")->setField('status','100');
    		if ($rst1) {
    			$this->success("启用成功！");
    		} else {
    			$this->error('启用失败！');
    		}
    	} else {
			$this->error($this->Forumsections_obj->getError());
    	}
    }
    public function disabled() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forumsections_obj->where("id=$id")->setField('status','0');
    		if ($rst1) {
    			$this->success("停用成功！");
    		} else {
    			$this->error('停用失败！');
    		}
    	} else {
			$this->error($this->Forumsections_obj->getError());
    	}
    }
    public function openbar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('status','200');
    		if ($rst1) {
    			$this->success("开启成功！");
    		} else {
    			$this->error('开启失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
    public function closebar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('status','90');
    		if ($rst1) {
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
    public function openshow() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('displayorder','200');
    		if ($rst1) {
    			$this->success("显示此板成功！");
    		} else {
    			$this->error('显示此板失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
    public function closeshow() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('displayorder','20');
    		if ($rst1) {
    			$this->success("取消显示成功！");
    		} else {
    			$this->error('取消显示失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
    public function upbar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('status','1000');
    		if ($rst1) {
    			$this->success("置顶成功！");
    		} else {
    			$this->error('置顶失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
    public function downbar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('status','200');
    		if ($rst1) {
    			$this->success("取消置顶成功！");
    		} else {
    			$this->error('取消置顶失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
}