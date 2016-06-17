<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-07-21
	描述：首页管理
**********************************/
class HomeAction extends AdminbaseAction {
	protected $Homelists_obj,$Shop_obj,$Images_obj,$Property_obj,$Price_obj,$Item_obj;
	function _initialize() {
		parent::_initialize();
		$this->Homelists_obj = D("Homelists");
		$this->Shop_obj = D("Iteminfos");
		$this->Images_obj = D("Imagedocs");
		$this->Property_obj = D("Itemdetails");
		$this->Price_obj = D("Feeinfos");
		$this->Item_obj =  D("Item_groups");
		$this->Typeconfigs_obj =  D("Typeconfigs");
	}
	public function _arr(){
//		$typearr  = array(
//						array("id"=>"ActivityUniformActivity",	"name"=> "球鞋、球服各类"),
//						array("id"=>"ActivityClassifyActivity",	"name"=> "足球配件分类"),
//						array("id"=>"EquipmentDetailActivity",	"name"=> "商品详情页面"),
//						array("id"=>"EquipmentListActivity",	"name"=> "商品列表、装备列表"),
//						array("id"=>"FootballFieldListActivity",	"name"=> "球场列表"),
//						array("id"=>"MatchListActivity",	"name"=> "活动"),
//						array("id"=>"MyCouponsActivity",	"name"=> "优惠券列表"),
//						array("id"=>"MyOrdersActivity",	"name"=> "物流查查")
//					);
		$typearr  = array(
						array("id"=>1,"click"=>"ActivityUniformActivity","p_art"=>"10103","p_id"=>"typeid","name"=> "商品分类"),
						array("id"=>2,"click"=>"ActivityUniformActivity","p_art"=>"10201","p_id"=>"typeid","name"=> "商品集"),
						array("id"=>3,"click"=>"EquipmentDetailActivity","p_art"=>"30101","p_id"=>"itemid","name"=> "商品详情")
					);
		return $typearr;
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
        $itemid=$_GET['itemid'];
		if($itemid)
		{
			$search = " && ( parentid = '".$itemid."')";
			$_GET['itemid'] = $itemid;
		}
		$count=$this->Homelists_obj->where("img_path != '' && img_name != '' && title !='' $search")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Homelists_obj
		->where("img_path != '' && img_name != '' && title !='' $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$class = $this->Homelists_obj
		->where("description != '' && status != ''")
		->order("id desc")
		->select();
    	$this->assign('num',$count);
		$this->assign("lists",$lists);
		$this->assign("class",$class);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formget",$_GET);
		$this->assign("imgtituan",webroot_url);
		$this->display();
	}
	public function plate(){
		$count=$this->Homelists_obj->where("style != '' && status !=''")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Homelists_obj
		->where("style != '' && status !=''")
		->order("showorder asc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$imgs = $this->Homelists_obj
		->where("img_path != '' && img_name != '' && title !=''")
		->order("showorder asc")
		->select();
		$this->assign("lists",$lists);
		$this->assign("imgs",$imgs);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("imgtituan",webroot_url);
		$this->display();
	}
    public function add() {
        $itemid=$_GET['itemid'];
		$arrs = $this->_arr();
		$this->assign("arrs",$arrs);
		$this->assign("formget",$_GET);
		$this->display();
    }
    public function add_post() {
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
	$created = date("Y-m-d H:i:s");
	if($_POST['click'] == '0'){$this -> error('板块事件不能为空');}
	if(!$_POST['shopid'] && !$_POST['groupid'] && !$_POST['typeid']){$this -> error('请先关联数据');}
	if($_POST['title'] == ''){$this -> error('板块名称不能为空');}
	if(!$_FILES){$this -> error('请上传图片');}
	    if(isset($_POST)){
			$filepath = 'img/homelists/';
			//判断目录是否存在/不存在就创建
			if(!file_exists(webroot_img.$filepath)){
				mkdir(webroot_img.$filepath, 0777,true);  
			}
			if($_FILES["homeimg"]["name"]){  
					$path1 = $_FILES["homeimg"]["name"]; 
					$path2 = time().substr($path1,strpos($path1,'.'));
			}
			if($_FILES["homeimg"]["size"]>256000){ 
			   	$this -> error('图片文件大小不得超过256KB');
			}
			if(move_uploaded_file($_FILES["homeimg"]["tmp_name"],webroot_img.$filepath.$path2)){
		    	$home['parentid'] = $_POST['id'];
		    	$home['title'] = $_POST['title'];
		    	$home['img_path'] = $filepath;
		    	$home['img_name'] = $path2;
		    	$home['showorder'] = 100;
				$arrs = $this->_arr();
for($a=0;$a<count($arrs);$a++){
	if($arrs[$a]['id'] == $_POST['clickid']){
    	$home['click'] = $arrs[$a]['click'];
		$home['p_art'] = $arrs[$a]['p_art'];
		if($_POST['shopid']){
			 $home['p_itemid'] = $_POST['shopid'];
	    	 $home['p_typeid'] = NULL;
		}
		if($_POST['groupid']){
			 $home['p_itemid'] = NULL;
			 $home['p_typeid'] = $_POST['groupid'];
		}
		if($_POST['typeid']){
			 $home['p_itemid'] = NULL;
	    	 $home['p_typeid'] = $_POST['typeid'];
		}
		$home['parameter'] = '{"art":'.$arrs[$a]['p_art'].',"'.$arrs[$a]['p_id'].'":'.$_POST['shopid'].$_POST['groupid'].$_POST['typeid'].'}';
	}
}
				$home['created'] = $created;
				$home['userid'] = $userid;
				if($this->Homelists_obj -> add($home)){
					$this -> success('模块添加成功!', U("home/index",array('itemid'=>$_POST['id'])));
				}else{
				    $this -> error('模块添加失败');
				}
			}
		}
    }
    public function addplate(){
		$style = $this->Homelists_obj
		->where("style != ''")
		->group("style")
		->select();
		$this->assign("style",$style);
		$this->display();
    }
    public function addplate_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
	$created = date("Y-m-d H:i:s");
	if($_POST['description'] == ''){$this -> error('板块描述不能为空');}
	if($_POST['style'] == '0'){
		if($_POST['styleelse'] == ''){
			$this -> error('请选择板块样式');
		}
		else{
			$_POST['style'] = $_POST['styleelse'];
		}
	}
    	if(isset($_POST)){
    	$home['title'] = $_POST['title'];
    	$home['description'] = $_POST['description'];
    	$home['style'] = $_POST['style'];
    	$home['status'] = $_POST['open'];
    	$home['showorder'] = 0;
		$home['created'] = $created;
		$home['userid'] = $userid;
		$homeid=$this->Homelists_obj -> add($home);
		$hid = $homeid+50000;
			if($this->Homelists_obj->where("id=$homeid")->setField('itemid',$hid)){
				$this -> success('板块添加成功!', U("home/plate"));
			}else{
			    $this -> error('板块添加失败');
			}
		}
    }
	public function push(){
		$this->assign("formget",$_GET);
		$this->display();
    }
	public function push_post(){
        $id=intval($_POST['id']);
		if($id){
			$title = msubstr(str_replace(' ','_',$_POST['title']),0,16);
			$describe = msubstr(str_replace(' ','_',$_POST['describe']),0,32);
			$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=".$title."&describe=".$describe;
			$this->ryq_array($push);
    		if ($this->Homelists_obj->where("id=$id")->setField('push','1')) {
				$this -> success('消息已推送!', U("home/plate"));
    		} else {
    			$this->error('消息推送失败！');
    		}
		}else{
			$this->error($this->Homelists_obj->getError());
		}
    }
    public function edit() {
        $id=intval($_GET['id']);
		$home = $this->Homelists_obj
		->where("id = $id")
		->find();
		$parentid=$home['parentid'];
		$style = $this->Homelists_obj
		->where("itemid = '$parentid'")
		->find();
		$arrs = $this->_arr();
		if(!$_GET['typeid'] && !$_GET['groupid'] && !$_GET['shopid']){
			
		if($home['parameter']){
			$parameter = $home['parameter'];
			$art=mb_substr($parameter,0,15);
			preg_match('/\d+/',$art,$arr1);
			$itemtype=mb_substr($parameter,15,-1);
			preg_match('/\d+/',$itemtype,$arr2);
		}else{
			$arr1[0] = $home['p_art'];
			if($home['p_typeid']){
				$arr2[0] = $home['p_typeid'];
			}else{
				$arr2[0] = $home['p_itemid'];
			}
		}
for($a=0;$a<count($arrs);$a++){
	if($arrs[$a]['p_art'] == $arr1[0] || $arrs[$a]['p_art'] == $home['p_art']){
		if($arrs[$a]['id'] == 1){
			if($home['p_typeid']){
				$_GET['typeid'] = $home['p_typeid'];
			}else{
				$_GET['typeid'] = $arr2[0];
			}
		}
		if($arrs[$a]['id'] == 2){
			if($home['p_typeid']){
				$_GET['groupid'] = $home['p_typeid'];
			}else{
				$_GET['groupid'] = $arr2[0];
			}
		}
		if($arrs[$a]['id'] == 3){
			if($home['p_itemid']){
				$_GET['shopid'] = $home['p_itemid'];
			}else{
				$_GET['shopid'] = $arr2[0];
			}
		}
	}
}
		}
		$arrs = $this->_arr();
		$this->assign("imgtituan",webroot_url);
		$this->assign("home",$home);
		$this->assign("style",$style);
		$this->assign("arrs",$arrs);
		$this->assign("formget",$_GET);
		$this->display();
    }
    public function edit_post() {
	$homeid = $_POST['id'];
	if($_SESSION['userid']>0){
		$userid = $_SESSION['userid'];
	}else{
		$userid = get_current_admin_id();
	}
	$created = date("Y-m-d H:i:s");
    	if(isset($_POST)){
			$home['title'] = $_POST['title'];
			if($_FILES){
				$img = $this->Homelists_obj->where("id = $homeid")->select();
				if ($img) {
					$imgurl = webroot_img.$img[0]['img_path'].$img[0]['img_name'];
					if (file_exists($imgurl)){ unlink ($imgurl);}
				}
				$filepath = 'img/homelists/';
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
					
				}
		    	$home['img_path'] = $filepath;
		    	$home['img_name'] = $path2;
			}
				$arrs = $this->_arr();
for($a=0;$a<count($arrs);$a++){
	if($arrs[$a]['id'] == $_POST['clickid']){
    	$home['click'] = $arrs[$a]['click'];
		$home['p_art'] = $arrs[$a]['p_art'];
		if($arrs[$a]['id'] == 3){
			 $home['p_itemid'] = $_POST['shopid'];
	    	 $home['p_typeid'] = NULL;
			 $home['parameter'] = '{"art":'.$arrs[$a]['p_art'].',"'.$arrs[$a]['p_id'].'":'.$_POST['shopid'].'}';
		}
		if($arrs[$a]['id'] == 2){
			 $home['p_itemid'] = NULL;
			 $home['p_typeid'] = $_POST['groupid'];
			 $home['parameter'] = '{"art":'.$arrs[$a]['p_art'].',"'.$arrs[$a]['p_id'].'":'.$_POST['groupid'].'}';
		}
		if($arrs[$a]['id'] == 1){
			 $home['p_itemid'] = NULL;
	    	 $home['p_typeid'] = $_POST['typeid'];
			 $home['parameter'] = '{"art":'.$arrs[$a]['p_art'].',"'.$arrs[$a]['p_id'].'":'.$_POST['typeid'].'}';
		}
	}
}
			$home['created'] = $created;
			$home['userid'] = $userid;
			if($this->Homelists_obj -> where("id = $homeid")->data($home)->save()){
				$this -> success('修改成功!', U("home/index",array('itemid'=>$_POST['parentid'])));
			}else{
			    $this -> error('修改失败');
			}
		} else {
			$this->error($this->Homelists_obj->getError());
		}
    }
	
    public function editplate() {
        $id=intval($_GET['id']);
		$home = $this->Homelists_obj
		->where("id = $id")
		->find();
		$style = $this->Homelists_obj
		->where("style != ''")
		->group("style")
		->select();
		$this->assign("home",$home);
		$this->assign("style",$style);
		$this->display();
    }
    public function editplate_post() {
	$homeid = $_POST['id'];
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
	$created = date("Y-m-d H:i:s");
    	if(isset($_POST)){
			$home['title'] = $_POST['mark'];
			$home['description'] = $_POST['name'];
			$home['style'] = $_POST['style'];
			$home['created'] = $created;
			$home['userid'] = $userid;
			if($this->Homelists_obj -> where("id = $homeid")->data($home)->save()){
				$this -> success('修改成功!', U("home/plate"));
			}else{
			    $this -> error('修改失败');
			}
		} else {
			$this->error($this->Homelists_obj->getError());
		}
    }
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
					$imgname = $this->Homelists_obj->where("id=$id")->find();
					$imgurl = webroot_img.$imgname['img_path'].$imgname['img_name'];
					if (file_exists($imgurl))
					{
					    $delete_ok = unlink ($imgurl);
					}
					if($this->Homelists_obj->where("id=$id")->delete()){
				        $this -> success('删除成功!');
				    }else {
                		$this->error("删除失败！");
            	}
        }else{
			$this->error($this->Homelists_obj->getError());
        }
	}
    public function delete_plate() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
					$imgname = $this->Homelists_obj->where("id=$id")->find();
					$pid = $imgname['itemid'];
					$imgnames = $this->Homelists_obj->where("parentid='$id'")->select();
					for($i=0;$i<count($imgnames);$i++){
						$imgurl = webroot_img.$imgnames[$i]['img_path'].$imgnames[$i]['img_name'];
						if (file_exists($imgurl))
						{
						    $delete_ok = unlink ($imgurl);
						}
					}
					if($this->Homelists_obj->where("id=$id")->delete()){
						$this->Homelists_obj->where("parentid='$pid'")->delete();
				        $this -> success('删除成功!');
				    }else {
                		$this->error("删除失败！");
            	}
        }else{
			$this->error($this->Homelists_obj->getError());
        }
	}
    public function openbar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		if ($this->Homelists_obj->where("id=$id")->setField('status','100')) {
    			$this->success("开启成功！");
    		} else {
    			$this->error('开启失败！');
    		}
    	} else {
			$this->error($this->Homelists_obj->getError());
    	}
    }
    public function closebar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		if ($this->Homelists_obj->where("id=$id")->setField('status','0')) {
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
    		}
    	} else {
			$this->error($this->Homelists_obj->getError());
    	}
    }
	public function showorders() {
		$status = parent::_showorder($this->Homelists_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	public function shop(){
        $itemid=$_GET['itemid'];
        $eventid=$_GET['eventid'];
        $source=$_GET['source'];
        $eid=$_GET['id'];
		$parakey_single = array ('itemid','eventid','source','id','sid','sname','extra');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['sid']){
				$sid = "&& (id = ".$parameters['sid'].')';
				$_GET['sid'] = $parameters['sid'];
			}
			if($parameters['sname']){
				$sname = "&& (itemDesc like '%".$parameters['sname']."%')";
				$_GET['sname'] = $parameters['sname'];
			}
			if($parameters['extra']){
				$extra = "extraClass like '%".$parameters['extra']."%'";
				$_GET['extra'] = $parameters['extra'];
				$propert = $this->Property_obj
				->where("$extra")
				->group("extraClass")
				->select();
				$ids=' && (';
				for($i=0;$i<count($propert);$i++){
					if($i<count($propert)-1){
						$itemiD = "id = ".$propert[$i]['itemID']." || ";
					}else{
						$itemiD = "id = ".$propert[$i]['itemID'];
					}
					$ids = $ids.$itemiD;
				}
				$ids=$ids.')';
			}
			if($parameters['itemid'] > 0){
				$_GET['itemid'] = $parameters['itemid'];
			}
			if($parameters['eventid']){
				$_GET['eventid'] = $parameters['eventid'];
			}
			$search = $sid.$sname.$ids;
		}
			/*******临时屏蔽某一发布日期*******/
			$search_by_date=" && date(created)>='2015-06-15'&& (status = 100)";
			$search = $search_by_date.$search;
			/************************************/
			$count=$this->Shop_obj
			->where("itemName = 10010 $search")
			->count();
			$page = $this->page($count, 10);
			$items = $this->Shop_obj
			->where("itemName = 10010 $search")
			->order("created DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			for($i=0;$i<count($items);$i++){
				if($i<count($items)-1){
					$itid = "itemID = ".$items[$i]['id']." || ";
					$imgid = "relatedID = ".$items[$i]['id']." || ";
				}else{
					$itid = "itemID = ".$items[$i]['id'];
					$imgid = "relatedID = ".$items[$i]['id'];
				}
				$itids = $itids.$itid;
				$imgids = $imgids.$imgid;
			}
			$property = $this->Property_obj
			->where($itids)
			->order("id asc")
			->group('itemID')
			->select();
			if($imgids){ $imgids = ' && ('.$imgids.')';}
			$images = $this->Images_obj
			->where("relatedType = 6 $imgids")
			->order("subtype asc")
			->group('relatedID')
			->select();
			for($i=0;$i<count($property);$i++){
				if($i<count($property)-1){
					$prid = "relatedID = ".$property[$i]['id']." || ";
				}else{
					$prid = "relatedID = ".$property[$i]['id'];
				}
				$prids = $prids.$prid;
			}
			if($prids){ $prids = ' && ('.$prids.')';}
			$price = $this->Price_obj
			->where("relatedType = 7 $prids")
			->order("relatedID desc")
			->select();
			$this->assign("property",$property);
			$this->assign("images",$images);
			$this->assign("price",$price);
	    	$this->assign('num',$count);
			$this->assign("lists",$items);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("imgtituan",webroot_url);
			$this->assign("formget",$parameters);
			$this->display();
	}
	public function group() {
        $itemid=$_GET['itemid'];
        $eventid=$_GET['eventid'];
        $source=$_GET['source'];
        $eid=$_GET['id'];
		$parakey_single = array ('itemid','eventid','source','id');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['itemid'] > 0){
				$_GET['itemid'] = $parameters['itemid'];
			}
			if($parameters['eventid']){
				$_GET['eventid'] = $parameters['eventid'];
			}
			$search = $sid.$sname.$ids;
		}
			$count=$this->Item_obj
			->where("status = 100")
			->count();
			$page = $this->page($count, 10);
			$lists = $this->Item_obj
			->where("status = 100")
			->order("id DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			
			for($t=0;$t<count($lists);$t++){
				if($t<count($lists)-1){
					$itemid_list = $lists[$t]['itemid_list'].',';
				}else{
					$itemid_list = $lists[$t]['itemid_list'];
				}
				$itemid_lists = $itemid_lists.$itemid_list;
			}
			$imgidarr = array_merge(array_unique(explode(",",$itemid_lists)));
			for($t=0;$t<count($imgidarr);$t++){
				if($t<count($imgidarr)-1){
					$imgid = "relatedID = ".$imgidarr[$t].' || ';
				}else{
					$imgid = "relatedID = ".$imgidarr[$t];
				}
				$imgids = $imgids.$imgid;
			}
			if($imgids){
				$imgids = ' && ('.$imgids.')';
				$images = $this->Images_obj
				->field("relatedID,filepath,filename")
				->where("relatedType = 6 $imgids")
				->order("subtype asc")
				->group('relatedID')
				->select();
				$this->assign("images",$images);
			}
			$this->assign("imgtituan",webroot_url);
			$this->assign("lists",$lists);
			$this->assign("formget",$parameters);
			$this->display();
	}

	//展示修改APP搜索框
	public function edit_app_default(){
		$this->display();
	}
	//修改默认文字
	public function edit_app_post(){
		$edit_q['extraDesc'] = I('post.default_cracter','','strip_tags');// 用strip_tags过滤$_POST['default_cracter']
		if($this->Typeconfigs_obj -> where("typeID = 90002")->data($edit_q)->save()){
        	$this -> success('默认文字修改成功!', U("Home/edit_app_default"));
		}else{
			$this -> error('默认文字修改失败!');
		}
	}
}