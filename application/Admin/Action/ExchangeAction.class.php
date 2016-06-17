<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-02-19
	描述：兑换管理
**********************************/
class ExchangeAction extends AdminbaseAction {
	protected $Itemtypes_obj,$Item_exchanges_obj,$Shop_obj,$Images_obj,$Property_obj,$Price_obj,$Typeconfigs_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Itemtypes_obj = D("Itemtypes");
		$this->Item_exchanges_obj = D("Item_exchanges");
		$this->Shop_obj = D("Iteminfos");
		$this->Images_obj = D("Imagedocs");
		$this->Property_obj = D("Itemdetails");
		$this->Price_obj = D("Feeinfos");
		$this->Typeconfigs_obj = D("Typeconfigs");
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('typeclass','astatus');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['typeclass'] > 0)
			{
				$typeclass = " && (itemtype = '".$parameters['typeclass']."')";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['astatus'] > 0)
			{
				if($parameters['astatus'] == 1){
					$astatus = "&&(period_from > '".date('Y-m-d H:i:s')."')";
				}
				if($parameters['astatus'] == 2){
					$astatus = "&&(period_from < '".date('Y-m-d H:i:s')."' && period_to > '".date('Y-m-d H:i:s')."')";
				}
				if($parameters['astatus'] == 3){
					$astatus = "&&(period_to < '".date('Y-m-d H:i:s')."')";
				}
				$_GET['astatus'] = $parameters['astatus'];
			}
			$search = $typeclass.$astatus;
		}
		$count=$this->Item_exchanges_obj->where("id > 0$search")->count();
		$page = $this->page($count, 20);
		$items = $this->Item_exchanges_obj
		->where("id > 0 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();

		$activity = $this->Itemtypes_obj
		->where("groupid = 'type_d'")
		->order("typeid asc")
		->select();
		$this->assign("items",$items);
		$this->assign("formpost",$parameters);
		$this->assign("activity",$activity);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
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
			$property = $this->Property_obj
			->where("itemID = $sids[0]")
			->order("id asc")
			->find();
			$pid = $property['id'];
			$price = $this->Price_obj
			->where("relatedType = 7 && relatedID = $pid")
			->order("relatedID desc")
			->find();
			if($imgids){ $imgids = ' && ('.$imgids.')';}
			$images = $this->Images_obj
			->where("relatedType = 6 $imgids")
			->order("subtype asc")
			->group('relatedID')
			->select();
			$this->assign("items",$items[0]);
			$this->assign("price",$price);
			$this->assign("images",$images);
			$this->assign("imgtituan",webroot_url);
		}
		$this->assign("sids",$sids);
		$activity = $this->Itemtypes_obj
		->where("groupid = 'type_d'")
		->order("typeid asc")
		->select();
		$typeconfigs = $this->Typeconfigs_obj
		->where("typeGroup = 25 && typeID > 25000")
		->select();
		$this->assign("activity",$activity);
	    $this->assign('typeconfigs',$typeconfigs);
    	$this->display();
	}
	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if(!$_POST['ids']){ $this -> error('请选择商品');}
		if(!$_POST['itemtype']){ $this -> error('请选择活动类型');}
		if(!$_POST['typeid']){ $this -> error('请选择兑换类型');}
		if(!$_POST['points']){ $this -> error('请填写兑换消耗');}
		if(!$_POST['cnt']){ $this -> error('请填写商品总数');}
		$point_level = $_POST['point_level'];
		$config = $this->Typeconfigs_obj
		->where("typeGroup = 25 && typeID = $point_level")
		->find();
		if(!($config['typeDesc'] <= $_POST['points'] && $config['extraDesc'] >= $_POST['points'])){
		    $this -> error('消耗球币数与所属用户组不符');
		}
		if($_POST){
			for($t=0;$t<count($_POST['ids']);$t++){
				if($t<count($_POST['ids'])-1){
					$id = $_POST['ids'][$t].',';
				}else{
					$id = $_POST['ids'][$t];
				}
				$ids = $ids.$id;
				$data[$t]['itemid'] = $_POST['ids'][$t];
				$data[$t]['itemtype'] = $_POST['itemtype'];
				$data[$t]['description'] = $_POST['description'];
				$data[$t]['typeid'] = $_POST['typeid'];
				$data[$t]['points'] = $_POST['points'];
				$data[$t]['point_level'] = $_POST['point_level'];
				$data[$t]['cnt'] = $_POST['cnt'];
				$data[$t]['status'] = $_POST['status'];
				$data[$t]['period_from'] = $_POST['period_from'];
				$data[$t]['period_to'] = $_POST['period_to'];
				$data[$t]['created'] = date("Y-m-d H:i:s");
				$data[$t]['userid'] = $userid;
			}
			$where['id'] = array('in',$ids);
			$sdata['item_type_d'] = $_POST['itemtype'];
			$this->Shop_obj->where($where)->data($sdata)->save();
			if($this->Item_exchanges_obj -> addAll($data)){
       			 $this -> success('兑换商品添加成功!', U("Exchange/index"));
			}else{
			    $this -> error('兑换商品添加失败');
			}
		} else {
			$this->error($this->Item_exchanges_obj->getError());
    	}
    }
	public function edit(){
        if(isset($_GET['id'])){
    	$gid = $_GET['id'];
    	$item = $this->Item_exchanges_obj
		->where("id=$gid")
		->find();
		$items = $this->Shop_obj
		->where(array("id"=>$item['itemid']))
		->order("id DESC")
		->find();
		if($items){ $imgids = ' && relatedID ='.$item['itemid'];}
		$images = $this->Images_obj
		->where("relatedType = 6 $imgids")
		->find();
		$this->assign("items",$items);
		$this->assign("images",$images);
		$this->assign("imgtituan",webroot_url);
		$this->assign("sids",$sids);
		$this->assign("formget",$_GET);
		$activity = $this->Itemtypes_obj
		->where("groupid = 'type_d'")
		->order("typeid asc")
		->select();
		$this->assign("activity",$activity);
		$this->assign("item",$item);
    	$this->display();
		}else{
			$this->error($this->Item_exchanges_obj->getError());
		}
    }
	
	public function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if($_POST){
		if(!$_POST['itemtype']){ $this -> error('请选择活动类型');}
		if(!$_POST['typeid']){ $this -> error('请选择兑换类型');}
		if(!$_POST['points']){ $this -> error('请填写兑换消耗');}
		if(!$_POST['cnt']){ $this -> error('请填写商品总数');}
			$data['id'] = $_POST['id'];
			$data['itemtype'] = $_POST['itemtype'];
			$data['description'] = $_POST['description'];
			$data['typeid'] = $_POST['typeid'];
			$data['points'] = $_POST['points'];
			$data['cnt'] = $_POST['cnt'];
			$data['period_from'] = $_POST['period_from'];
			$data['period_to'] = $_POST['period_to'];
			$data['created'] = date("Y-m-d H:i:s");
			$data['userid'] = $userid;
			if($this->Item_exchanges_obj -> data($data)->save()){
       			 $this -> success('兑换商品修改成功!', U("Exchange/index"));
			}else{
			    $this -> error('兑换商品修改失败');
			}
		} else {
			$this->error($this->Item_exchanges_obj->getError());
    	}
	}
	public function delete(){
        $id = intval(I("get.id"));
		$item = $this->Item_exchanges_obj->where(array("id"=>$id))->order("id DESC")->find();
		$shop = $this->Shop_obj->where(array("id"=>$item['itemid']))->setField('item_type_d','');
        if ($this->Item_exchanges_obj->where("id=$id")->delete()) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
	public function open(){
        $id=intval($_GET['id']);
    	if ($id) {
    		if ($this->Item_exchanges_obj->where("id=$id")->setField('status','200')) {
    			$this->success("开启成功！");
    		} else {
    			$this->error('开启失败！');
    		}
    	} else {
			$this->error($this->Item_exchanges_obj->getError());
    	}
    }
	public function close(){
        $id=intval($_GET['id']);
    	if ($id) {
    		if ($this->Item_exchanges_obj->where("id=$id")->setField('status','100')) {
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
    		}
    	} else {
			$this->error($this->Item_exchanges_obj->getError());
    	}
    }

}
