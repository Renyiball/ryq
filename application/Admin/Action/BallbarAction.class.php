<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-07-09
	描述：球吧
**********************************/
class BallbarAction extends AdminbaseAction {
	
	protected $Forums_obj,$Favorites_obj,$Users_obj,$Bets_obj,$Matchinfos_obj,$Teams_obj,$Shop_obj,$Class_obj,$Images_obj,$Property_obj,$Price_obj,$Typeconfigs_obj,$Userbets_obj,$Forumsections_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Forums_obj = D("Forums");
		$this->Favorites_obj = D("Favorites");
		$this->Users_obj = D("Users");
		$this->Bets_obj = D("Bets");
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Teams_obj = D("Teams");
		$this->Shop_obj = D("Iteminfos");
		$this->Class_obj = D("Itemtypes");
		$this->Images_obj = D("Imagedocs");
		$this->Property_obj = D("Itemdetails");
		$this->Price_obj = D("Feeinfos");
		$this->Typeconfigs_obj = D("Typeconfigs");
		$this->Userbets_obj = D("Userbets");
		$this->Forumsections_obj = D("Forumsections");
	}

	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('typeclass','seach','userid','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
			if($parameters['typeclass'] != 0)
			{
				$typeclass = " && ( parentid = '".$parameters['typeclass']."')";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['seach'])
			{
				$title= "&&(title like '%".$parameters['seach']."%' || ";
				$description= "description like '%".$parameters['seach']."%')";
				$seach = $title.$description;
				$_GET['seach'] = $parameters['seach'];
			}
			if($parameters['userid'] != 0)
			{
				$userid = " && ( userid = '".$parameters['userid']."')";
				$_GET['userid'] = $parameters['userid'];
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
			$search = $typeclass.$seach.$userid.$start_time.$end_time;
		}		
		$forumsections = $this->Forumsections_obj
		->where("showorder != '' && status = 100")
		->select();
		for($i=0;$i<count($forumsections);$i++){
				if($i<count($forumsections)-1){
					$parentid = "parentid =".$forumsections[$i]['id'].' || ';
					$parent =$parent.$parentid;
				}else{
					$parentid = "parentid =".$forumsections[$i]['id'];
					$parent =$parent.$parentid;
				}
		}
		$search = $search.' && ('.$parent.')';
		$count=$this->Forums_obj->where("typeid = 200  $search")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Forums_obj
		->where("typeid = 200  $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$f=$u=$t=0;
		for($i=0;$i<count($lists);$i++){
			if($lists[$i]['id']){
				$fid[$f] = $lists[$i]['id'];
				$f++;
			}
			if($lists[$i]['userid']){
				$fuid[$u] = $lists[$i]['userid'];
				$u++;
			}
			if($lists[$i]['itemid']){
				$itid[$t] = $lists[$i]['itemid'];
				$t++;
			}
		}
		$fid = array_merge(array_unique($fid));
		for($i=0;$i<count($fid);$i++){
			if($i<count($fid)-1){
				$fpid = "parentid = ".$fid[$i]." || ";
				$reid = "relatedid = ".$fid[$i]." || ";
				$deid = "description = '".$fid[$i]."' || ";
			}else{
				$fpid = "parentid = ".$fid[$i];
				$reid = "relatedid = ".$fid[$i];
				$deid = "description = '".$fid[$i]."'";
			}
			$fpids =$fpids.$fpid;
			$reids =$reids.$reid;
			$deids =$deids.$deid;
		}
		$fuid = array_merge(array_unique($fuid));
		for($i=0;$i<count($fuid);$i++){
			if($i<count($fuid)-1){
				$furid = "id = ".$fuid[$i]." || ";
			}else{
				$furid = "id = ".$fuid[$i];
			}
			$furids =$furids.$furid;
		}
		$users = $this->Users_obj
		->where("$furids")
		->order("id desc")
		->select();
		$parents = $this->Forums_obj
		->where("typeid = '300' && ($fpids)")
		->order("id desc")
		->select();
		$favorites = $this->Favorites_obj
		->where("typeid = 2 && status = 160 && relatedtype = 4 && ($reids)")
		->order("id desc")
		->select();
//		$bets= $this->Bets_obj
//		->where("$deids")
//		->select();
//		for($i=0;$i<count($bets);$i++){
//			$bid[$i] = $bets[$i]['matchinfoid'];
//		}
//		$bid = array_merge(array_unique($bid));
//		for($i=0;$i<count($bid);$i++){
//			if($i<count($bid)-1){
//				$maid = "matchconstid = ".$bid[$i]." || ";
//			}else{
//				$maid = "matchconstid = ".$bid[$i];
//			}
//			$maids =$maids.$maid;
//		}
//		$matchs= $this->Matchinfos_obj
//		->where("$maids")
//		->order("id desc")
//		->select();
//		$t = 0;
//		for($i=0;$i<count($matchs);$i++){
//			$teamid[$t] = $matchs[$i]['teamid1'];
//			$t++;
//			$teamid[$t] = $matchs[$i]['teamid2'];
//			$t++;
//		}
//		$teamid = array_merge(array_unique($teamid));
//		for($i=0;$i<count($teamid);$i++){
//			if($i<count($teamid)-1){
//				$teid = "constid = '".$teamid[$i]."' || teamnumber = '".$teamid[$i]."' || ";
//			}else{
//				$teid = "constid = '".$teamid[$i]."' || teamnumber = '".$teamid[$i]."'";
//			}
//			$teids =$teids.$teid;
//		}
//		$teams = $this->Teams_obj
//		->where("$teids")
//		->order("id desc")
//		->select();
//		$itid = array_merge(array_unique($itid));
//		for($i=0;$i<count($itid);$i++){
//			if($i<count($itid)-1){
//				$iteid = "id = ".$itid[$i]." || ";
//			}else{
//				$iteid = "id = ".$itid[$i];
//			}
//			$iteids =$iteids.$iteid;
//		}
//		$shops= $this->Shop_obj
//		->where("$iteids")
//		->select();
    	$this->assign('num',$count);
		$this->assign("lists",$lists);
    	$this->assign('forumsections',$forumsections);
    	$this->assign('users',$users);
    	$this->assign('parents',$parents);
    	$this->assign('favorites',$favorites);
//  	$this->assign('bets',$bets);
//  	$this->assign('matchs',$matchs);
//  	$this->assign('shops',$shops);
//  	$this->assign('teams',$teams);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
		$this->display();
	}
	public function view(){
		if($_GET['id']){
		$id = $_GET['id'];
		$count=$this->Forums_obj->where("typeid = 300 && parentid = $id")-> count();
		$page = $this->page($count, 10);
		$lists = $this->Forums_obj
		->where("typeid = 300 && parentid = $id")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$replys = $this->Forums_obj
		->where("typeid = 400")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$class = $this->Forums_obj
		->where("id = $id")
		->find();
		$favorites = $this->Favorites_obj
		->where("typeid = 2 && status = 160 && relatedtype = 4")
		->order("id desc")
		->select();
    	$this->assign('num',$count);
		$this->assign("lists",$lists);
		$this->assign("replys",$replys);
    	$this->assign('class',$class);
    	$this->assign('users',$users);
    	$this->assign('favorites',$favorites);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
		}else{
				$this->error("非法提交");
		}
    }
	public function attention(){
		if($_GET['id']){
		$id = $_GET['id'];
		$userid=$this->Favorites_obj->where("typeid = 2 && status = 160 && relatedtype = 4 && relatedid = $id")->select();
			for($j=0;$j<count($userid);$j++){
					$seach = $seach." id = ".$userid[$j]['userid'];
				if($j <count($userid)-1){
					$seach = $seach.' || ';
				}
			}
		if($userid){
			$count=$this->Users_obj->where("$seach")-> count();
			$page = $this->page($count, 20);
			$lists = $this->Users_obj
			->where("$seach")
			->order("id desc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			$users = $this->Users_obj
			->order("id desc")
			->select();
		$this->assign("lists",$lists);
    	$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
		}
		$this->display();
		}else{
				$this->error("非法提交");
		}
    }
	public function add(){
		$forumsections = $this->Forumsections_obj
		->where("showorder != '' && status = 100")
		->select();
		$configs = $this->Typeconfigs_obj
		->where("typeGroup = 13 && parentID > 100")
		->select();
    	$this->assign('forumsections',$forumsections);
    	$this->assign('configs',$configs);
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
		        $this -> error('请选择分类!');
		}
		if(!$_POST['post_title']){
		        $this -> error('请填写标题!');
		}
		if(!$_POST['post_excerpt']){
		        $this -> error('请填写内容!');
		}
		if($_POST['ptypeid'] == '1000' && !isset($_POST['photos_url']) && $_POST['typeconfigs']!=13003){
			$this -> error('请上传图片!');
		}
		if(!$_POST['ptypeid'] && !isset($_POST['photos_url'])){
			$_POST['typeconfigs'] = 13006;
		}
		if(!$_POST['ptypeid'] && isset($_POST['photos_url'])){
			$_POST['typeconfigs'] = 13004;
		}
		$filepath = 'img/Forums/'.date("Y").'/'.date("m").'/';
		//判断目录是否存在/不存在就创建
		if(!file_exists(webroot_img.$filepath))  
		{
			mkdir(webroot_img.$filepath, 0777,true);  
		}
        if (isset($_POST)) {
        $typeclass = $_POST['typeclass'];
        $forumsections = $this->Forumsections_obj
		->where("id = $typeclass")
		->find();
		if($forumsections['masteruserid']){
			$userid = $forumsections['masteruserid'];
		}
	        $data['title'] = $_POST['post_title'];
	        $data['description'] = $_POST['post_excerpt'];
	        $data['typeid'] = 200;
	        $data['parentid'] = $_POST['typeclass'];
	        $data['parenttypeid'] = $_POST['ptypeid'];
	        $data['status'] = $_POST['status'];
	        $data['displayorder'] = $_POST['typeconfigs'];
	        $data['created'] = $created;
	        $data['modified'] = $created;
	        $data['userid'] = $userid;
			$forumsid=$this->Forums_obj -> add($data);
			$u = 0;
			if(isset($_POST['photos_url'])){
				foreach ($_POST['photos_url'] as $url){
					$photourl=sp_asset_relative_url($url);
					$save_path = webroot_img.$filepath;
					$save_file = time().$u;
					$img_path = 'data/upload/'.$photourl;
					$type = $this->thumb_img($img_path,800,$save_path.$save_file);
					$images[$u]['relatedType']=4;
					$images[$u]['subtype']=$u;
					$images[$u]['relatedID']=$forumsid;
					$images[$u]['filepath']=$filepath;
					$images[$u]['filename']=$save_file.$type;
					$images[$u]['modified'] = $created;
					$images[$u]['created'] = $created;
					$images[$u]['userid'] = $userid;
					$u++;
				}
				$this->Images_obj->addAll($images);
			}
			if($forumsid){
				$this -> success('帖子发布成功!', U("ballbar/index"));
			}else{
				$this -> error('帖子发布失败!');
			}
        }else{
			$this->error($this->Forums_obj->getError());
	    }
	}
    public function openbar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('status','200');
    		$rst2 = $this->Forums_obj->where("parentid=$id")->setField('status','200');
    		if ($rst1 || $rst2) {
				$str = "description like '%[threadid_200:".$id."]'";
				$ubets = $this->Userbets_obj->where("betid = -151 && $str")->find();
				$ubid=$ubets['id'];
				$uid=abs($ubets['userid']);
				$this->Userbets_obj->where("id=$ubid")->setField("userid","$uid");
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
    		$rst2 = $this->Forums_obj->where("parentid=$id")->setField('status','90');
    		if ($rst1 || $rst2) {
				$str = "description like '%[threadid_200:".$id."]'";
				$ubets = $this->Userbets_obj->where("betid = -151 && $str")->find();
				$ubid=$ubets['id'];
				$uid='-'.$ubets['userid'];
				$this->Userbets_obj->where("id=$ubid")->setField("userid","$uid");
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
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
    public function shield() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('extraDesc','100');
    		if ($rst1) {
    			$this->success("屏蔽成功！");
    		} else {
    			$this->error('屏蔽失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
    public function restore() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('extraDesc','0');
    		if ($rst1) {
    			$this->success("取消屏蔽成功！");
    		} else {
    			$this->error('取消屏蔽失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
	public function associate(){
        $id=intval($_GET['id']);
		$now = date("Y-m-d H:i:s");
		if ($id) {
		$name = $this->Forums_obj
		->where("id=$id ")
		->find();
		$cid = $name['parentid'];
		$class= $this->Forums_obj
		->where("id=$cid ")
		->find();
		$bets= $this->Bets_obj
		->where("expiredate > '$now'")
		->select();
			for($j=0;$j<count($bets);$j++){
				$info = $bets[$j]['matchinfoid'];
						$seach = $seach." matchconstid = '".$bets[$j]['matchinfoid']."'";
					if($j <count($bets)-1){
						$seach = $seach.' || ';
					}
			}
		$match= $this->Matchinfos_obj
		->where("$seach")
		->select();
		$teams = $this->Teams_obj
		->where("constid != ''")
		->order("id desc")
		->select();
    	$this->assign('name',$name);
    	$this->assign('class',$class);
    	$this->assign('match',$match);
		$this->assign("teams",$teams);
    	$this->display();
		}else{
			$this->error($this->Forums_obj->getError());
		}
	}
	public function associate_post(){
    	if(isset($_POST)){
    		if($_POST['competition'] == 0){
			    $this -> error('请选择赛事类型');
    		}
    		if($_POST['competition'] == 1){
	        	$matchinfoid = $_POST['guess'];
    		}
    		if($_POST['competition'] == 2){
	        	$matchinfoid = $_POST['amateur'];
    		}
	        $data['description'] = $_POST['nameid'];
			if($this->Bets_obj -> where("matchinfoid = '$matchinfoid'")->data($data)->save()){
				$this -> success('关联成功!', U("ballbar/index"));
			}else{
			    $this -> error('关联失败');
			}
		}else{
			$this->error($this->Bets_obj->getError());
		}
	}
	
	
	public function shop(){
        $postid=intval($_GET['id']);
		$parakey_single = array ('postid','sid','sname','extra');
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
			if($parameters['postid'] > 0){
				$_GET['postid'] = $parameters['postid'];
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
			$this->assign("postid",$postid);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("imgtituan",webroot_url);
			$this->assign("formpost",$parameters);
			$this->display();
	}
	public function shop_post(){
		$postid = $_POST['postid'];
		$shopid = $_POST['shopid'];
    	if(isset($_POST)){
    		if($shopid == ''){
			    $this -> error('请选择商品');
    		}
	        $data['itemid'] = $shopid;
		      
			if($this->Forums_obj -> where("id = $postid")->data($data)->save()){
				$this -> success('关联成功!', U("ballbar/index"));
			}else{
			    $this -> error('关联失败');
			}
		}else{
			$this->error($this->Forums_obj->getError());
		}
	}
	public function cancel(){
		$id=intval($_GET['id']);
    	if($id){
    		$data['itemid'] = '';
			if($this->Forums_obj -> where("id = $id")->data($data)->save()){
				$this -> success('取消关联成功!', U("ballbar/index"));
			}else{
			    $this -> error('取消关联失败');
			}
		}else{
			$this->error($this->Forums_obj->getError());
		}
	}
	public function edit(){
        $id=intval($_GET['id']);
		$p=$_GET['p'];
		$c=$_GET['c'];
		$class = $this->Forums_obj
		->where("id = $id")
		->find();
		$images=$this->Images_obj
		->where("relatedID=$id && relatedType = 4")
		->order("subtype asc")
		->select();
    	$this->assign('id',$id);
		$this->assign("smeta",$images);
    	$this->assign('class',$class);
		$this->assign("imgtituan",webroot_url);
		$this->assign("p",$p);
		$this->assign("c",$c);
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
	        $data['title'] = $_POST['post_title'];
	        $data['description'] = $_POST['post_excerpt'];
	        $data['modified'] = $modified;
			if($_POST['order']){
		    	$model = $this->Images_obj;
		        if (!is_object($model)) {
					$this->error("排序更新失败！");
		        }
		        $pk = $model->getPk(); //获取主键名称
		        $ids = $_POST['order'];
		        foreach ($ids as $key => $r) {
		            $data['subtype'] = $r;
		            $data['modified'] = $modified;
		            $sub = $model->where(array($pk => $key))->save($data);
		        }
			}
		      if($this->Forums_obj -> where("id = $id")->data($data)->save()){
				if($_POST['c']){
					$this -> success('帖子修改成功!',U("ballbar/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
				}else{
					$this -> success('帖子修改成功!',U("ballbar/index",array('p'=>$_POST['p'])));
				}
		      }else{
		        $this -> error('帖子修改失败!');
		      }
        }else{
			$this->error($this->Forums_obj->getError());
	    }		
	}
	
    public function refresh() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('modified',date("Y-m-d H:i:s"));
    		if ($rst1) {
    			$this->success("刷新发布时间成功！");
    		} else {
    			$this->error('刷新发布时间失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
    public function hotbar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('status','10000');
    		if ($rst1) {
    			$this->success("设为热帖成功！");
    		} else {
    			$this->error('设为热帖失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
    public function nothotbar() {
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst1 = $this->Forums_obj->where("id=$id")->setField('status','200');
    		if ($rst1) {
    			$this->success("取消热帖成功！");
    		} else {
    			$this->error('取消热帖失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
	
	//图片删除
    public function delete_images() {
        if (intval(I("get.id"))) {
            $id = intval(I("get.id"));
			$imgname = $this->Images_obj->where("id=$id")->select();
			$imgurl = webroot_img.$imgname[0]['filepath'].$imgname[0]['filename'];
			if (file_exists($imgurl))
			{
			    $delete_ok = unlink ($imgurl);
			}
			if ($this->Images_obj->where("id=$id")->delete()) {
			    $this -> success('删除成功!');
        	} else {
            	$this->error("删除失败！");
        	}
        }else{
			$this->error("非法提交！");
        }
	}
	public function reply(){
        $id=intval($_GET['id']);
		$p=$_GET['p'];
		$c=$_GET['c'];
		$class = $this->Forums_obj
		->where("id = $id")
		->find();
		$images=$this->Images_obj
		->where("relatedID=$id && relatedType = 4")
		->order("subtype asc")
		->select();
    	$this->assign('id',$id);
    	$this->assign('class',$class);
		$this->assign("smeta",$images);
		$this->assign("imgtituan",webroot_url);
		$this->assign("p",$p);
		$this->assign("c",$c);
		$this->display();
    }
	public function reply_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        if (isset($_POST)) {
		    $id = $_POST['id'];
			$modified = date("Y-m-d H:i:s");
	        $data['description'] = $_POST['post_excerpt'];
	        $data['extraDesc'] = 0;
	        $data['typeid'] = 300;
	        $data['parentid'] = $_POST['id'];
	        $data['parenttypeid'] = 200;
	        $data['status'] = 200;
	        $data['created'] = $modified;
	        $data['modified'] = $modified;
	        $data['userid'] = $userid;
		      if($this->Forums_obj ->add($data)){
				if($_POST['c']){
					$this -> success('帖子回复成功!',U("ballbar/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
				}else{
					$this -> success('帖子回复成功!',U("ballbar/index",array('p'=>$_POST['p'])));
				}
		      }else{
		        $this -> error('帖子回复失败!');
		      }
        }else{
			$this->error($this->Forums_obj->getError());
	    }		
	}
    public function thumbs() {
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $id=intval($_GET['id']);
		$modified = date("Y-m-d H:i:s");
		$userids=$this->Favorites_obj
		->where("typeid = 2 && status = 160 && relatedtype = 4 && relatedid = $id")
		->select();
		for($j=0;$j<count($userids);$j++){
			$seach = $seach." id != ".$userids[$j]['userid'];
			if($j <count($userids)-1){
				$seach = $seach.' && ';
			}
		}
		if($seach){
			$seach = ' && '.$seach;
		}
		$user=$this->Users_obj
		->where("regsource='4020008' && created>='2015-07-15' && created<='2015-07-25' $seach")
		->count();
		$num = rand(10,$user);
		$users=$this->Users_obj
		->where("regsource='4020008' && created>='2015-07-15' && created<='2015-07-25' $seach")
		->limit($num,10)
		->select();
		for($j=0;$j<count($users);$j++){
			
		    $data[$j]['typeid'] ='2';
	        $data[$j]['status'] = '160';
	        $data[$j]['relatedtype'] = 4;
	        $data[$j]['description'] = '通过后台加赞---操作员：'.$userid;
	        $data[$j]['relatedid'] = $id;
	        $data[$j]['modified'] = $modified;
	        $data[$j]['created'] = $modified;
	        $data[$j]['userid'] = $users[$j]['id'];
		}
		if($this->Favorites_obj -> addall($data)){
	        $this -> success('帖子加赞成功!');
	    }else{
	    	$this -> error('帖子加赞失败!');
	    }
		
    }
}