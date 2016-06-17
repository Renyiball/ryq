<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-01-03
	描述：商品集
**********************************/
class CollectAction extends AdminbaseAction {
	
	protected $Item_groups_obj,$Shop_obj,$Images_obj,$Property_obj,$Price_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Item_obj = D("Item_groups");
		$this->Shop_obj = D("Iteminfos");
		$this->Images_obj = D("Imagedocs");
		$this->Property_obj = D("Itemdetails");
		$this->Price_obj = D("Feeinfos");
	}

	public function index(){
    	$this->_list();
    }
	public function _list(){
		$count=$this->Item_obj->count();
		$page = $this->page($count, 20);
		$lists = $this->Item_obj
		->order("id desc")
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
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	
	public function add(){
		$sids = array_merge($_GET['sids']);
		if($sids){
			for($t=0;$t<count($sids);$t++){
				if($t<count($sids)-1){
					$id = '(id = '.$sids[$t].') ||';
					$imgid = "(relatedID = ".$sids[$t].') ||';
				}else{
					$id = '(id = '.$sids[$t].')';
					$imgid = "(relatedID = ".$sids[$t].')';
				}
				$ids = $ids.$id;
				$imgids = $imgids.$imgid;
			}
			if($ids){$ids = ' && ('.$ids.')';}
			/*******临时屏蔽某一发布日期*******/
			$search=" && date(created)>='2015-06-15'&& (status = 100)".$ids;
			/*****************************/
			$items = $this->Shop_obj
			->where("itemName = 10010 $search")
			->order("id DESC")
			->select();
			if($imgids){ $imgids = ' && ('.$imgids.')';}
			$images = $this->Images_obj
			->where("relatedType = 6 $imgids")
			->order("subtype asc")
			->group('relatedID')
			->select();
			$this->assign("images",$images);
			$this->assign("imgtituan",webroot_url);
		}
		$this->assign("sids",$sids);
		$this->display();
    }
	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if($_POST){
			$ym = date("ym");
			$item = $this->Item_obj
			->where("groupid like '$ym%'")
			->count();
			for($t=0;$t<count($_POST['ids']);$t++){
				if($t<count($_POST['ids'])-1){
					$id = $_POST['ids'][$t].',';
				}else{
					$id = $_POST['ids'][$t];
				}
				$ids = $ids.$id;
			}
			$data['groupid'] = $ym.'001'+$item;
			$data['status'] = $_POST['updown'];
			$data['itemid_list'] = $ids;
			$data['description'] = $_POST['title'];
			$data['expiredate'] = date("Y-m-d H:i:s");
			$data['created'] = date("Y-m-d H:i:s");
			$data['userid'] = $userid;
			if($this->Item_obj -> add($data)){
       			 $this -> success('商品集添加成功!', U("Collect/index"));
			}else{
			    $this -> error('商品集添加失败');
			}
		} else {
			$this->error($this->Item_obj->getError());
    	}
    }
    
	public function edit_del(){
        $id = $_GET['id'];
        $delitemid = $_GET['delitemid'];
    	$item = $this->Item_obj
		->where("id=$id")
		->find();
		$sids = explode(",",$item['itemid_list']);
		$num = array_search($delitemid,$sids);
		array_splice($sids,$num,1);
		$data['itemid_list'] = rtrim(implode(',',$sids), ","); 
        if ($this->Item_obj -> where("id = $id")->data($data)->save()) {
            $this->success("删除成功！", U("Collect/edit",array('id'=>$id)));
        } else {
            $this->error("删除失败！");
        }
    }
    
	public function edit(){
        if(isset($_GET['id'])){
    	$gid = $_GET['id'];
    	$item = $this->Item_obj
		->where("id=$gid")
		->find();
		if($_GET['sids']){
			$sids1 = array_merge($_GET['sids']);
			$sids2 = explode(",",$item['itemid_list']);
			$sids = array_merge(array_unique(array_merge($sids1,$sids2)));
			$data['itemid_list'] = rtrim(implode(',',$sids), ",");
			$this->Item_obj -> where("id = $gid")->data($data)->save();
			
		}else{
			$sids = explode(",",$item['itemid_list']);
		}
		if($sids){
			for($t=0;$t<count($sids);$t++){
					if($t<count($sids)-1){
						$id = 'id = '.$sids[$t].'||';
						$imgid = "relatedID = ".$sids[$t].'||';
					}else{
						$id = 'id = '.$sids[$t];
						$imgid = "relatedID = ".$sids[$t];
					}
					$ids = $ids.$id;
					$imgids = $imgids.$imgid;
			}
			if($ids){$ids = ' && ('.$ids.')';}
			/*******临时屏蔽某一发布日期*******/
			$search=" && date(created)>='2015-06-15'&& status = 100".$ids;
			/*****************************/
			$items = $this->Shop_obj
			->where("itemName = 10010 $search")
			->order("id DESC")
			->select();
			if($imgids){ $imgids = ' && ('.$imgids.')';}
			$images = $this->Images_obj
			->where("relatedType = 6 $imgids")
			->order("subtype asc")
			->group('relatedID')
			->select();
			$this->assign("items",$items);
			$this->assign("images",$images);
			$this->assign("imgtituan",webroot_url);
		}
		$this->assign("sids",$sids);
		$this->assign("item",$item);
		$this->assign("formget",$_GET);
    	$this->display();
		}else{
			$this->error($this->Item_obj->getError());
		}
    }
	public function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		$cid = $_POST['id'];
		if($_POST){
			for($t=0;$t<count($_POST['ids']);$t++){
				if($t<count($_POST['ids'])-1){
					$id = $_POST['ids'][$t].',';
				}else{
					$id = $_POST['ids'][$t];
				}
				$ids = $ids.$id;
			}
			$data['status'] = $_POST['updown'];
			$data['itemid_list'] = $ids;
			$data['description'] = $_POST['title'];
			$data['expiredate'] = date("Y-m-d H:i:s");
			$data['userid'] = $userid;
			if($this->Item_obj -> where("id = $cid")->data($data)->save()){
       			 $this -> success('商品集修改成功!', U("Collect/index"));
			}else{
			    $this -> error('商品集修改失败');
			}
		} else {
			$this->error($this->Item_obj->getError());
    	}
    }

	public function delete(){
        $id = intval(I("get.id"));
        if ($this->Item_obj->where("id=$id")->delete()) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
	public function open(){
        $id=intval($_GET['id']);
    	if ($id) {
    		if ($this->Item_obj->where("id=$id")->setField('status','100')) {
    			$this->success("开启成功！");
    		} else {
    			$this->error('开启失败！');
    		}
    	} else {
			$this->error($this->Item_obj->getError());
    	}
    }
	public function close(){
        $id=intval($_GET['id']);
    	if ($id) {
    		if ($this->Item_obj->where("id=$id")->setField('status','0')) {
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
    		}
    	} else {
			$this->error($this->Item_obj->getError());
    	}
    }
	public function shop(){
        $source=$_GET['source'];
        $eid=$_GET['id'];
		$parakey_single = array ('source','id','sid','sname','extra');
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
			if($parameters['id'] > 0){
				$_GET['id'] = $parameters['id'];
			}
			if($parameters['source']){
				$_GET['source'] = $parameters['source'];
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
			$page = $this->page($count, 20);
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
}