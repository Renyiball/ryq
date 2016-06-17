<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-12-08
	描述：文章发布
*********************************/
class PostAction extends AdminbaseAction {
	protected $T_posts_obj,$Typeconfigs;
	
	function _initialize() {
		parent::_initialize();
		$this->T_posts_obj = D("T_posts");
		$this->Typeconfigs_obj = D("Typeconfigs");
		$this->Users_obj = D("T_users");
		$this->T_common_action_log_obj = D("T_common_action_log");
		$this->Forumsections_obj = D("Forumsections");
	}
	function ryq_array($url){  
	    $ch = curl_init();  
	    curl_setopt($ch, CURLOPT_URL, $url);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回    
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回    
	    $r = curl_exec($ch);  
	    curl_close($ch);  
	    return $r;  
	}  
	function index(){
		$this->_lists();
		$this->display();
	}
	function add(){
		$terms = $this->Typeconfigs_obj
		->where("typeGroup = 20 && typeID > 0")
		->order("id desc")
		->select();
		$this->assign("terms",$terms);
		$this->display();
	}
	function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if($_POST['post_term']==0){ $this -> error('请选择文章分类');}
		if (IS_POST) {
			$_POST['post_type']=36;
			$_POST['post_date']=date("Y-m-d H:i:s",time());
			$_POST['post_modified']=date("Y-m-d H:i:s",time());
			$_POST['userid']=$userid;
			if($_FILES["teamimg"]["name"]){
				$filepath = 'img/Post/thumb/'.date("Y").'/'.date("m").'/';
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				$path = time();
				$type = $this->thumb_img($_FILES["teamimg"]["tmp_name"],800,webroot_img.$filepath.$path);
		    	$_POST['post_image'] = $filepath.$path.$type;
			}
			if($_POST['post_push']){
				$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=".$_POST['post_title']."&describe=".$_POST['post_excerpt'];
				$this->ryq_array($push);
			}
			if ($this->T_posts_obj->add($_POST)) {
    			$this->success('添加成功！', U("post/index"));
			} else {
				$this->error("添加失败！");
			}
			 
		}
	}
	public function edit(){
		$id=  intval(I("get.id"));
		$post=$this->T_posts_obj->where("id=$id")->find();
		$terms = $this->Typeconfigs_obj
		->where("typeGroup = 20 && typeID > 0")
		->order("id desc")
		->select();
		$p=$_GET['p'];
		$c=$_GET['c'];
		$this->assign("p",$p);
		$this->assign("c",$c);
		$this->assign("terms",$terms);
		$this->assign("post",$post);
		$this->assign("imgtituan",webroot_url);
		$this->display();
	}
	public function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if (IS_POST) {
			$_POST['post_modified']=date("Y-m-d H:i:s",time());
			$_POST['userid']=$userid;
			$tpid = $_POST['id'];
			if($_FILES["teamimg"]["name"]){
				$img = $this->T_posts_obj->where("id = $tpid")->find();
				if ($img) {
					$imgurl = webroot_img.$img['post_image'];
					if (file_exists($imgurl)){ unlink ($imgurl);}
				}
				$filepath = 'img/Post/thumb/'.date("Y").'/'.date("m").'/';
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				$path = time();
				$type = $this->thumb_img($_FILES["teamimg"]["tmp_name"],800,webroot_img.$filepath.$path);
		    	$_POST['post_image'] = $filepath.$path.$type;
			}
			if($_POST['post_push']){
				$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=".$_POST['post_title']."&describe=".$_POST['post_excerpt'];
				$this->ryq_array($push);
			}
			if ($this->T_posts_obj->save($_POST)) {
    			$this->success('保存成功！', U("post/index"));
			} else {
				$this->error("保存失败！");
			}
			 
		}
	}
	public function showorder() {
		$status = parent::_showorder($this->T_posts_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	private  function _lists(){
	
		$parakey_single = array ('typeclass','keywords','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
			if($parameters['typeclass'] > "0")
			{
				$typeclass = " && (post_term = '".$parameters['typeclass']."')";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['keywords'] != '')
			{
				$keywords = " && (post_keywords like '%".$parameters['keywords']."%')";
				$_GET['keywords'] = $parameters['keywords'];
			}
			if($parameters['start_time'] != '')
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(post_date))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !='')
			{
				$end_time = " && (date(post_date) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $typeclass.$keywords.$start_time.$end_time;
		}
		$count=$this->T_posts_obj-> where("post_type = 36 $search")->count();
		$page = $this->page($count, 20);
		$posts = $this->T_posts_obj
		->where("post_type = 36 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$terms = $this->Typeconfigs_obj
		->where("typeGroup = 20 && typeID > 0")
		->order("id desc")
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$this->assign("posts",$posts);
		$this->assign("terms",$terms);
		$this->assign("users",$users);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
		$this->assign("imgtituan",webroot_url);
	}

	function celebrity(){
	
		$parakey_single = array ('typeclass','keywords','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
			if($parameters['typeclass'] > "0")
			{
				$typeclass = " && (post_term = '".$parameters['typeclass']."')";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['keywords'] != '')
			{
				$keywords = " && (post_keywords like '%".$parameters['keywords']."%')";
				$_GET['keywords'] = $parameters['keywords'];
			}
			if($parameters['start_time'] != '')
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(post_date))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !='')
			{
				$end_time = " && (date(post_date) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $typeclass.$keywords.$start_time.$end_time;
		}
		$count=$this->T_posts_obj-> where("post_type = 35 $search")->count();
		$page = $this->page($count, 20);
		$posts = $this->T_posts_obj
		->where("post_type = 35 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$forums = $this->Forumsections_obj
		->where("tags = 'mr'")
		->order("id desc")
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$this->assign("posts",$posts);
		$this->assign("forums",$forums);
		$this->assign("users",$users);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
		$this->assign("imgtituan",webroot_url);
		$this->display();
	}
	
	function addcelebrity(){
		$forums = $this->Forumsections_obj
		->where("tags = 'mr'")
		->order("id desc")
		->select();
		$this->assign("forums",$forums);
		$this->display();
	}
	function addcelebrity_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if($_POST['post_term']==0){ $this -> error('请选择文章分类');}
		if (IS_POST) {
			$_POST['post_source']='任意球APP';
			$_POST['post_type']=35;
			$_POST['post_date']=date("Y-m-d H:i:s",time());
			$_POST['post_modified']=date("Y-m-d H:i:s",time());
			$_POST['userid']=$userid;
			if($_FILES["teamimg"]["name"]){
				$filepath = 'img/Post/thumb/'.date("Y").'/'.date("m").'/';
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				$path = time();
				$type = $this->thumb_img($_FILES["teamimg"]["tmp_name"],800,webroot_img.$filepath.$path);
		    	$_POST['post_image'] = $filepath.$path.$type;
			}
			if($_POST['post_push']){
				$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=".$_POST['post_title']."&describe=".$_POST['post_excerpt'];
				$this->ryq_array($push);
			}
			if ($this->T_posts_obj->add($_POST)) {
    			$this->success('添加成功！', U("post/index"));
			} else {
				$this->error("添加失败！");
			}
			 
		}
	}

	public function editcelebrity(){
		$id=  intval(I("get.id"));
		$post=$this->T_posts_obj->where("id=$id")->find();		
		$forums = $this->Forumsections_obj
		->where("tags = 'mr'")
		->order("id desc")
		->select();
		$p=$_GET['p'];
		$c=$_GET['c'];
		$this->assign("p",$p);
		$this->assign("c",$c);
		$this->assign("forums",$forums);
		$this->assign("post",$post);
		$this->assign("imgtituan",webroot_url);
		$this->display();
	}
	public function editcelebrity_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if (IS_POST) {
			$_POST['post_modified']=date("Y-m-d H:i:s",time());
			$_POST['userid']=$userid;
			$tpid = $_POST['id'];
			if($_FILES["teamimg"]["name"]){
				$img = $this->T_posts_obj->where("id = $tpid")->find();
				if ($img) {
					$imgurl = webroot_img.$img['post_image'];
					if (file_exists($imgurl)){ unlink ($imgurl);}
				}
				$filepath = 'img/Post/thumb/'.date("Y").'/'.date("m").'/';
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				$path = time();
				$type = $this->thumb_img($_FILES["teamimg"]["tmp_name"],800,webroot_img.$filepath.$path);
		    	$_POST['post_image'] = $filepath.$path.$type;
			}
			if($_POST['post_push']){
				$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=".$_POST['post_title']."&describe=".$_POST['post_excerpt'];
				$this->ryq_array($push);
			}
			if ($this->T_posts_obj->save($_POST)) {
    			$this->success('保存成功！', U("post/index"));
			} else {
				$this->error("保存失败！");
			}
			 
		}
	}
    public function openpost() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst2 = $this->T_posts_obj->where("id=$id")->setField('post_status','1');
    		if ($rst2) {
    			$this->success("开启成功！");
    		} else {
    			$this->error('开启失败！');
    		}
    	} else {
			$this->error($this->T_posts_obj->getError());
    	}
    }
    public function closepost() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst2 = $this->T_posts_obj->where("id=$id")->setField('post_status','0');
    		if ($rst2) {
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
    		}
    	} else {
			$this->error($this->T_posts_obj->getError());
    	}
    }
	function push(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst2 = $this->T_posts_obj->where("id=$id")->setField('post_push','1');
			$post=$this->T_posts_obj->where("id=$id")->find();
			$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=".$post['post_title']."&describe=".$post['post_excerpt'];
			$this->ryq_array($push);
    		if ($rst2) {
    			$this->success("推送成功！");
    		} else {
    			$this->error('推送失败！');
    		}
    	} else {
			$this->error($this->T_posts_obj->getError());
    	}
	}
	function top(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst2 = $this->T_posts_obj->where("id=$id")->setField('post_istop','1');
    		if ($rst2) {
    			$this->success("置顶成功！");
    		} else {
    			$this->error('置顶失败！');
    		}
    	} else {
			$this->error($this->T_posts_obj->getError());
    	}
	}
	function down(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst2 = $this->T_posts_obj->where("id=$id")->setField('post_istop','0');
    		if ($rst2) {
    			$this->success("取消置顶成功！");
    		} else {
    			$this->error('取消置顶失败！');
    		}
    	} else {
			$this->error($this->T_posts_obj->getError());
    	}
	}
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
            if ($this->T_posts_obj->where("id=$id")->delete()) {
            	$object = 'T_posts'.$id;
            	$this->T_common_action_log_obj->where("object='$object'")->delete();
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }else{
			$this->error($this->T_posts_obj->getError());
        }

    }
}