<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-08
	描述：商品管理
*********************************/
class ShopAction extends AdminbaseAction {
	protected $Shop_obj,$Class_obj,$Property_obj,$Images_obj,$Price_obj,$Users_obj,$Enable_obj,$Item_exchanges_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Shop_obj = D("Iteminfos");//商品
		$this->Class_obj = D("Itemtypes");//分组
		$this->Property_obj = D("Itemdetails");//尺码颜色
		$this->Images_obj = D("Imagedocs");//图片
		$this->Price_obj = D("Feeinfos");//价格
		$this->Users_obj = D("T_users");//用户
		$this->Enable_obj = D("Enable_helds");//零时库存
		$this->Item_exchanges_obj = D("Item_exchanges");
	}

	public function index(){
    	$this->_list();
		
    }

	function ryq_array($url){
	    $ch = curl_init();  
	    curl_setopt($ch, CURLOPT_URL, $url);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; 
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; 
	    $r = curl_exec($ch);  
	    curl_close($ch);  
	    return $r;  
	}	
	public function _list(){
		$parakey_single = array ('oneclass','twoclass','noup','id','sname','extra');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['oneclass'] != '0' && $parameters['oneclass'] != ''){
				if($parameters['oneclass'] == 'b0'){
					$_GET['oneclass'] = $parameters['oneclass'];		
					$oneclassid = $parameters['oneclass'];
					$twoclassid = $this->Class_obj
					->where("groupid = 'type_b' && parent_typeid = '$oneclassid'")
					->order("typeid asc")
					->select();
					$twoseach = ' && (';$typeclass = '';
					for($i=0;$i<count($twoclassid);$i++)
					{
						$typeid = $twoclassid[$i]['typeid'];
						$twoseach = $twoseach."item_type_b = ".$typeid;
						if($i < count($twoclassid)-1)
						{
							$twoseach = $twoseach.' || ';
						}
					}
				}else if($parameters['oneclass'] == 'c0'){
					$_GET['oneclass'] = $parameters['oneclass'];		
					$oneclassid = $parameters['oneclass'];
					$twoclassid = $this->Class_obj
					->where("groupid = 'type_c' && group_text = '特许分类' && parent_typeid = '$oneclassid'")
					->order("typeid asc")
					->select();
					$twoseach = ' && (';$typeclass = '';
					for($i=0;$i<count($twoclassid);$i++)
					{
						$typeid = $twoclassid[$i]['typeid'];
						$twoseach = $twoseach."item_type_c = ".$typeid;
						if($i < count($twoclassid)-1)
						{
							$twoseach = $twoseach.' || ';
						}
					}
				}else{
					$_GET['oneclass'] = $parameters['oneclass'];		
					$oneclassid = $parameters['oneclass'];
					$twoclassid = $this->Class_obj
					->where("groupid = 'type_a' && group_text = '二级分类' && parent_typeid = $oneclassid")
					->order("typeid asc")
					->select();
					$twoseach = ' && (';$typeclass = '';
					for($i=0;$i<count($twoclassid);$i++)
					{
						$typeid = $twoclassid[$i]['typeid'];
						$twoseach = $twoseach."item_type_a = ".$typeid;
						if($i < count($twoclassid)-1)
						{
							$twoseach = $twoseach.' || ';
						}
					}
				}
				$typeclass = $twoseach.')';	
			}
			if($parameters['twoclass'] > 0)
			{
				if($parameters['oneclass'] == 'b0'){
					$typeclass = "&& (item_type_b = ".$parameters['twoclass'].')';
					$_GET['twoclass'] = $parameters['twoclass'];
				}else if($parameters['oneclass'] == 'c0'){
					$typeclass = "&& (item_type_c = ".$parameters['twoclass'].')';
					$_GET['twoclass'] = $parameters['twoclass'];
				}else{
				$typeclass = "&& (item_type_a = ".$parameters['twoclass'].')';
				$_GET['twoclass'] = $parameters['twoclass'];
				}
			}
			if($parameters['noup'] == '999' || $parameters['noup'] == ''){
				$noup = "";
				$_GET['noup'] = $parameters['noup'];
			}else{
				$noup = "&& (status = ".$parameters['noup'].")";
				$_GET['noup'] = $parameters['noup'];
			}
//			if($parameters['noup'] == '999'){
//				$noup = "";
//				$_GET['noup'] = $parameters['noup'];
//			}
			if($parameters['id']){
				$id = "&& (id = ".$parameters['id'].')';
				$_GET['id'] = $parameters['id'];
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
			$search = $typeclass.$noup.$id.$sname.$ids;
		}
			/*******临时屏蔽某一发布日期*******/
			$search_by_date=" && date(created)>='2015-06-15' ";
			$search = $search_by_date.$search;
			/************************************/

			$count=$this->Shop_obj->where("itemName = 10010 $search")-> count();
			$page = $this->page($count, 20);
			$items = $this->Shop_obj
			->where("itemName = 10010 $search")
			->order("created DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			$itemid = $items['id'];
			$images = $this->Images_obj
			->where("relatedType = 6")
			->order("relatedID asc")
			->select();
			$property = $this->Property_obj
			->where()
			->order("itemID desc")
			->select();
			$users = $this->Users_obj
			->order("id desc")
			->select();
			$oneclass = $this->Class_obj
			->where("groupid = 'type_a'")
			->order("typeid asc")
			->select();	
			$mainclass = $this->Class_obj
			->where("groupid = 'type_b' || groupid = 'type_c'")
			->group("group_text")
			->select();
			$sonclass = $this->Class_obj
			->where("groupid = 'type_b' || groupid = 'type_c'")
			->order("typeid asc")
			->select();
			$price = $this->Price_obj
			->where("relatedType = 7")
			->order("relatedID desc")
			->select();
	    	$this->assign('mainclass',$mainclass);
	    	$this->assign('sonclass',$sonclass);
	    	$this->assign('one',$oneclass);
	    	$this->assign('num',$count);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("lists",$items);
			$this->assign("price",$price);
			$this->assign("images",$images);
			$this->assign("users",$users);
			$this->assign("imgtituan",webroot_url);
			$this->assign("property",$property);
			$this->assign("formpost",$parameters);
			$this->display();
	}
	
	/*
	public function showorders() {
		$status = parent::_showorders($this->Shop_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	*/

	public function add(){
		$one = $this->Class_obj
		->where("groupid = 'type_a'")
		->order("typeid asc")
		->select();
		$brand = $this->Class_obj
		->where("groupid = 'type_b'")
		->order("typeid asc")
		->select();
		$max = $this->Class_obj
		->where("groupid = 'type_c'")
		->order("typeid asc")
		->select();
		$attr = $this->Class_obj
		->where("groupid = 'attr_a'")
		->order("typeid asc")
		->select();
    	$this->assign('one',$one);
    	$this->assign('brand',$brand);
    	$this->assign('max',$max);
    	$this->assign('attr',$attr);
    	$this->display();
	}

	public function add_post(){
		$parakey_single = array ('end');
		$parakey_multi = array ('color' => 'colorlist','xssize' => 'xssizelist','ljsize' => 'ljsizelist');
		$parameters = $this->_GetRequestPara_All ( $parakey_single, $parakey_multi );
		$colorlist=$parameters['colorlist'];
		$xssizelist=$parameters['xssizelist'][0];
		$ljsizelist=$parameters['ljsizelist'][0];
		$color = array_merge($colorlist);
		$xssize = array_merge($xssizelist);
		$ljsize = array_merge($ljsizelist);
		$filepath = 'img/Iteminfos/'.date("Y").'/'.date("m").'/';
		//判断目录是否存在/不存在就创建
		if(!file_exists(webroot_img.$filepath))  
		{
			mkdir(webroot_img.$filepath, 0777,true);  
		}
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $modified = $created = date("Y-m-d H:i:s");
		if(!$_POST['shopname']){ $this -> error('名称不能为空');}
		if($_POST['brandclass']==0){ $this -> error('品牌分类不能为空');}
		if($_POST['oneclass']==0){ $this -> error('一级分类不能为空');}
		if($_POST['twoclass']==0){ $this -> error('二级分类不能为空');}
		if($_POST['whether']=='0'){ if($_POST['maxclass']==0){ $this -> error('特许分类不能为空');}}else{ $_POST['maxclass']='';}
		if($_POST['price']==0){ $this -> error('价格不能为空');}
		if($_POST['property']==0){ $this -> error('数量不能为空');}
		if(!$color){$this -> error('颜色不能为空');}
		if(!$xssize){$this -> error('显示尺码不能为空');}
		if(!$ljsize){$this -> error('丽晶尺码不能为空');}
		if(count($xssize)!=count($ljsize)){$this -> error('显示尺码共'.count($xssize).'条，丽晶尺码共'.count($ljsize).'条无法配对，请重新选择。如无法确定建议刷新此页面重新填选！');}
		if(!$_POST['photos_url']){ $this -> error('图片不能为空');}
	    if(isset($_POST)){			
	        $shop = $_POST;
	        $shop['itemName'] = 10010;
	        $shop['itemDesc'] = $_POST['shopname'];
	        //$shop['extraDesc'] = $_POST['description'];//商品简介
	        $shop['status'] = $_POST['updown'];
			if($_POST['push']){
	        	$shop['push'] = $_POST['push'];
			}
	        $shop['item_type_a'] = $_POST['twoclass'];
	        $shop['item_type_b'] = $_POST['brandclass'];
	        $shop['item_type_c'] = $_POST['maxclass'];
	        $shop['item_type_d'] = $_POST['behavior'];
	        $shop['item_type_e'] = '';
	        $shop['showOrder'] = 0;
	        $shop['show_order_a'] = '';
	        $shop['show_order_b'] = '';
	        $shop['originalSale'] = 0;
	        $shop['modified'] = $modified;
	        $shop['userid'] = $userid;
	        $shop['created'] = $created;
			$shopid = $this->Shop_obj -> add($shop);
			$c = $s = 1;
			$w = $u = 0;
			if($shopid){
				foreach($color as $i)
				{
					for($j=0;$j<count($xssize);$j++){
						$property[$w]['itemID']=$shopid;
						$property[$w]['originalHeld']=$_POST['property'];
						$property[$w]['itemColorID']=$c;
						$property[$w]['itemColor']=$i;
						$property[$w]['itemSizeID']=$s;
						$property[$w]['itemSize']=$xssize[$j];
						$property[$w]['store_size']=$ljsize[$j];
        				$property[$w]['extraClass'] = $_POST['key'];
						$property[$w]['modified'] = $modified;
						$property[$w]['created'] = $created;
						$property[$w]['userid'] = $userid;
						$s++;$w++;
					}
					$s =1;$c++;
					
				}
			$c = 0;
			$propertyid = $this->Property_obj -> addAll($property);
			$s =$propertyid;
				if($propertyid){
					foreach($color as $i)
					{
						foreach($xssize as $j)
						{
							$price[$c]['relatedType'] = 7;
							$price[$c]['amount'] = $_POST['price'];
							$price[$c]['repeatType'] = $_POST['repeatType'];
							$price[$c]['relatedID'] = $s;
							$price[$c]['unit'] = '件';
							$price[$c]['modified'] = $modified;
							$price[$c]['created'] = $created;
							$price[$c]['userid'] = $userid;
							$c++;$s++;
						}
					}
				$priceid = $this->Price_obj -> addAll($price);
				if($priceid){
					if(isset($_POST['photos_url'])){
						foreach ($_POST['photos_url'] as $url){
							$photourl=sp_asset_relative_url($url);
							$save_path = webroot_img.$filepath;
							$save_file = time().$u;
							$img_path = 'data/upload/'.$photourl;
							$type = $this->thumb_img($img_path,800,$save_path.$save_file);
							
							$images[$u]['relatedType']=6;
							$images[$u]['subtype']=$u+10;
							$images[$u]['relatedID']=$shopid;
							$images[$u]['filepath']=$filepath;
							$images[$u]['filename']=$save_file.$type;
							$images[$u]['modified'] = $modified;
							$images[$u]['created'] = $created;
							$images[$u]['userid'] = $userid;
							if($u == 0){
								$img_path = 'data/upload/'.$photourl;
								$thumb = $filepath.'thumb/';
								$save_file = time();
								$save_path = webroot_img.$thumb.$save_file;
								$type = $this->thumb_img($img_path,400,$save_path);
								if($type){
									$imgthumb['relatedType']=6;
									$imgthumb['subtype']=100;
									$imgthumb['relatedID']=$shopid;
									$imgthumb['filepath']=$thumb;
									$imgthumb['filename']=$save_file.$type;
									$imgthumb['modified'] = $modified;
									$imgthumb['created'] = $created;
									$imgthumb['userid'] = $userid;
									$this->Images_obj -> add($imgthumb);
								}
							}
							$u++;
						}
						$imagesid = $this->Images_obj -> addAll($images);
						if($imagesid){
							if($_POST['push']){
								if(($_POST['repeatType'] < 100 && 1 < $_POST['repeatType']) && $_POST['updown'] == 100){
									$repeat = '现'.$_POST['repeatType'].'折出售';
								}
								$shopname = msubstr(str_replace(' ','_',$_POST['shopname']),0,16);
								$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=新品推荐".$shopname."&describe=".$shopname.'原价'.$_POST['price'].'元'.$repeat.'。速来抢购';
								$test = $this->ryq_array($push);
							$this -> success('添加图片成功!,已推送消息', U("shop/index"));
							}
							$this -> success('添加图片成功!', U("shop/index"));
						}else{
						    $this -> error('添加图片失败');
						}
					}
					$this -> success('添加价钱成功!', U("shop/index"));
				}else{
				    $this -> error('添加价钱失败!');
				}
				}else{
				    $this -> error('添加属性失败!');
				}	
			}else{
				$this -> error('添加信息失败!');
			}
	    }else{
			$this->error("非法提交！");
	    }
    }

    public function edit(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$info = $this->Shop_obj
		->where("id = $id")
		->find();
		$property = $this->Property_obj
		->where("itemID=$id")
		->order("itemID desc")
		->select();
		$price = $this->Price_obj
		->where("relatedType = 7")
		->select();
		$images=$this->Images_obj
		->where("relatedID=$id && relatedType = 6")
		->order("subtype asc")
		->select();
		$itemColor = $this->Class_obj
		->where("parent_type_text = '颜色'")
		->order("typeid asc")
		->select();
		$itemSize = $this->Class_obj
		->where("group_text = '二级属性' && parent_type_text like'%码'")
		->order("typeid asc")
		->select();
		$twoida = $info['item_type_a'];
		$twoidb = $info['item_type_b'];
		$twoidc = $info['item_type_c'];
		$twoide = $info['item_type_d'];
		$twoclass = $this->Class_obj
		->where("groupid = 'type_a' && group_text = '二级分类' && typeid = $twoida")
		->order("typeid asc")
		->find();
		$twoidd = $twoclass['parent_typeid'];
		$oneclass = $this->Class_obj
		->where("groupid = 'type_a' && group_text = '一级分类' && typeid = $twoidd")
		->order("typeid asc")
		->find();
		
		$brandclass = $this->Class_obj
		->where("groupid = 'type_b' && group_text = '品牌分类' && typeid = $twoidb")
		->order("typeid asc")
		->find();
		$maxclass =  $this->Class_obj
		->where("groupid = 'type_c' && group_text = '特许分类' && typeid = $twoidc")
		->order("typeid asc")
		->find();
		$activity = $this->Class_obj
		->where("typeid = $twoide")
		->order("typeid asc")
		->find();
    	$this->assign('type',$info);
		$this->assign("smeta",$images);
		$this->assign("price",$price);
    	$this->assign('itemColor',$itemColor);
    	$this->assign('itemSize',$itemSize);
    	$this->assign('oneclass',$oneclass);
    	$this->assign('twoclass',$twoclass);
    	$this->assign('maxclass',$maxclass);
    	$this->assign('brandclass',$brandclass);
		$this->assign("property",$property);
		$this->assign("imgtituan",webroot_url);
    	$this->assign('activity',$activity);
    	$this->display();
		}else{
			$this->error($this->Shop_obj->getError());
		}
    }

	public function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
    	if(isset($_POST)){
		    $modified = date("Y-m-d H:i:s");
	        $priid =  $_POST['pric_id'];
	        $price['amount'] = $_POST['pric_amount'];
	        $price['repeatType'] = $_POST['pric_repeatType'];
	        $price['modified'] = $modified;
	        $price['userid'] = $userid;
	        $proid = $_POST['prope_id'];
	        $property['originalHeld'] = $_POST['prope_existing'];
	        $property['extraClass'] = $_POST['prope_coding'];
	        $property['modified'] = $modified;
	        $property['userid'] = $userid;
			$itemid = $_POST['item_id'];
			$shop['itemDesc'] = $_POST['item_Desc'];
			if($this->Shop_obj -> where("id = $itemid")->data($shop)->save() || $this->Price_obj -> where("id = $priid")->data($price)->save() && $this->Property_obj -> where("id = $proid")->data($property)->save()){
				$this -> success('修改成功!');
			}else{
			    $this -> error('修改失败');
			}
		} else {
			$this->error("非法提交！");
		}
    }
	//删除
	/*****
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
            if ($this->Shop_obj->where("id=$id")->delete()) {
				$imgname = $this->Images_obj->where("relatedID=$id && relatedType = 6")->select();
				for($i=0;$i<count($imgname);$i++)
				{
					$ID = $imgname[$i]['id'];
					$imgurl = webroot_img.$imgname[$i]['filepath'].$imgname[$i]['filename'];
					if (file_exists($imgurl))
					{
					    $delete_ok = unlink ($imgurl);
					}
					$this->Images_obj->where("id=$ID && relatedType = 6")->delete();
					if($i == count($imgname)){
				        $this -> success('图片删除成功!');
				    }
				}
            	$itemarr = $this->Property_obj->where("itemID=$id")->order("id desc")->select();
            	$this->Property_obj->where("itemID=$id")->delete();
				if ($itemarr) {
					for($i=count($itemarr);$i>=0;--$i)
					{
						$ID = $itemarr[$i]['id'];
						if($this->Price_obj->where("relatedID=$ID")->delete()){
						}
						if($i == 0){
					        $this -> success('删除成功!');
					    }
					}
            	} else {
                	$this->error("删除属性失败！");
            	}
            } else {
                $this->error("删除信息失败！");
            }
        }else{
			$this->error("非法提交！");
        }
    }
	*/

    public function delete_property() {
        if (intval(I("get.id"))) {
            $id = intval(I("get.id"));
			if ($this->Property_obj->where("id=$id")->delete()) {
				if($this->Price_obj->where("relatedID=$id")->delete()){
			        $this -> success('删除成功!');
			    }
        	} else {
            	$this->error("删除属性失败！");
        	}
        }else{
			$this->error("非法提交！");
        }
    }

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
	
	public function push(){
		$id = $_GET['item_id'];
    	if ($id) {
    		$rst = $this->Shop_obj->where("id=$id")->setField('push','1');
			$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=".$post['push_title']."&describe=".$post['push_content'];
			$this->ryq_array($push);
    	} else {
			$this->error($this->Shop_obj->getError());
    	}
    }
	public function up(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Shop_obj->where("id=$id")->setField('status','100');
    		if ($rst) {
    			$this->success("商品上架成功！");
    		} else {
    			$this->error('商品上架失败！');
    		}
    	} else {
			$this->error($this->Shop_obj->getError());
    	}
    }

    public function down(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Shop_obj->where("id=$id")->setField('status','0');
    		if ($rst) {
    			$this->success("商品下架成功！");
    		} else {
    			$this->error('商品下架失败！');
    		}
    	} else {
			$this->error($this->Shop_obj->getError());
    	}
    }
	
    public function scrap(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Shop_obj->where("id=$id")->setField('status','10');
    		if ($rst) {
    			$this->success("商品已废弃！");
    		} else {
    			$this->error('商品废弃失败！');
    		}
    	} else {
			$this->error($this->Shop_obj->getError());
    	}
    }

    public function edit_amountrepeat(){
    	$id=$_POST['type_id'];
		$property = $this->Property_obj
		->where("itemID=$id")
		->order("itemID desc")
		->select();
		for($p=0;$p<count($property);$p++){
			$pid[$p] = $property[$p]['id'];
		}
		$pid=array_merge(array_unique($pid));
			for($i=0;$i<count($pid);$i++){
				if($i<count($pid)-1){
					$uid = "relatedID =".$pid[$i]." || ";
				}else{
					$uid = "relatedID =".$pid[$i];
				}
				$usids =$usids.$uid;
			}
		$item['amount']=$_POST['pric_amount'];
		$item['repeatType']=$_POST['pric_repeatType'];
		$price = $this->Price_obj
		->where($usids)
		->setField($item);
		if ($price) {
			$this->success("更新成功！");
		} else {
			$this->error('更新失败！');
		}

    }
    public function temporary(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Shop_obj->where("id=$id")->setField('status','20');
    		if ($rst) {
    			$this->success("商品已下架！");
    		} else {
    			$this->error('商品下架失败！');
    		}
    	} else {
			$this->error($this->Shop_obj->getError());
    	}
    }
	public function move(){
		if(IS_POST){
			if(isset($_GET['ids']) && isset($_POST['twoclass'])){
				$tids=$_GET['ids'];
				if($_POST['twoclass'] != '0'){ $team['item_type_a']=$_POST['twoclass'];}
				if($_POST['whether1'] == '0'){ $team['item_type_b']=$_POST['brandclass'];}
				if($_POST['whether2'] == '0'){ $team['item_type_c']=$_POST['charterclass'];}
				if ( $this->Shop_obj->where("id in ($tids)")->save($team) ) {
					$this->success("分类调整成功！");
				} else {
					$this->error("分类调整失败！");
				}
			}
		}else{
		$one = $this->Class_obj
		->where("groupid = 'type_a'")
		->order("typeid asc")
		->select();
		$brandclass = $this->Class_obj
		->where("groupid = 'type_b'")
		->order("typeid asc")
		->select();
		$charterclass =  $this->Class_obj
		->where("groupid = 'type_c'")
		->order("typeid asc")
		->select();
    	$this->assign('one',$one);
		$this->assign("packet",$packetarr);
    	$this->assign('brandclass',$brandclass);
    	$this->assign('charterclass',$charterclass);
		}
    	$this->display();
    }
	public function img_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if(!$_POST['photos_url']){ $this -> error('图片不能为空');}
        $modified = $created = date("Y-m-d H:i:s");
		if(isset($_POST['photos_url'])){
		$filepath = 'img/Iteminfos/'.date("Y").'/'.date("m").'/';
		//判断目录是否存在/不存在就创建
		if(!file_exists(webroot_img.$filepath))  
		{
			mkdir(webroot_img.$filepath, 0777,true);  
		}
		if(!$_POST['shoptype']){ $this->error("请选择上传图片类型！");}else{ $shoptype = $_POST['shoptype'];}
		
		if($_POST['shoptype'] == 20){ $width = 800;}
		if($_POST['shoptype'] == 300){ $width = 800;}
			$u = 0;
			foreach ($_POST['photos_url'] as $url){
				$photourl=sp_asset_relative_url($url);
				$save_path = webroot_img.$filepath;
				$save_file = time().$u;
				$img_path = 'data/upload/'.$photourl;
				$type = $this->thumb_img($img_path,$width,$save_path.$save_file);
				$images[$u]['relatedType']=6;
				$images[$u]['subtype']=$shoptype+$u;
				$images[$u]['relatedID']=$_POST['typeid'];
				$images[$u]['filepath']=$filepath;
				$images[$u]['filename']=$save_file.$type;
				$images[$u]['modified'] = $modified;
				$images[$u]['created'] = $created;
				$images[$u]['userid'] = $userid;
				$u++;
			}
				$imagesid = $this->Images_obj -> addAll($images);
				if($imagesid){
					$this -> success('添加图片成功!');
				}else{
				    $this -> error('添加图片失败');
		    	}
	    	}else {
				$this->error($this->Images_obj->getError());
	    	}
	}
	public function order_img() {
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		$modified = date("Y-m-d H:i:s");
	    $model = $this->Images_obj;
		$imgid = $_POST['cover'];
		if($imgid){
			$imgname = $this->Images_obj->where("id=$imgid")->select();
			$filepath = $imgname[0]['filepath'];
			$filename = $imgname[0]['filename'];
			$img_path = webroot_img.$filepath.$filename;
			$thumb = $filepath.'thumb/';
			$save_file = time();
			$save_path = webroot_img.$thumb.$save_file;
			$type = $this->thumb_img($img_path,400,$save_path);
			$relatedID = $imgname[0]['relatedID'];
			$imgthumb = $this->Images_obj->where("relatedType=6 && relatedID = '$relatedID' && subtype=100")->select();
			if($imgthumb){
				//删除原有缩略图
				$imgname = $this->Images_obj->where("relatedType=6 && relatedID = '$relatedID' && subtype=100")->select();
				$imgurl = webroot_img.$imgname[0]['filepath'].$imgname[0]['filename'];
				if (file_exists($imgurl))
				{
				    $delete_ok = unlink ($imgurl);
				}
				//存入新的缩略图
					$images['filepath']=$thumb;
					$images['filename']=$save_file.$type;
					$images['modified'] = $modified;
					$images['userid'] = $userid;
					$this->Images_obj -> where("relatedType=6 && relatedID = '$relatedID' && subtype=100")->data($images)->save();
			}else{
				//添加缩略图
					$images['relatedType']=6;
					$images['subtype']=100;
					$images['relatedID']=$relatedID;
					$images['filepath']=$thumb;
					$images['filename']=time().$type;
					$images['modified'] = $modified;
					$images['created'] = $modified;
					$images['userid'] = $userid;
					$this->Images_obj -> add($images);
			}
		}
        if (!is_object($model)) {
			$this->error("排序更新失败！");
        }
        $pk = $model->getPk(); //获取主键名称
        $ids = $_POST['order'];
        foreach ($ids as $key => $r) {
            $data['subtype'] = $r;
            $data['modified'] = $modified;
            $data['userid'] = $userid;
            $model->where(array($pk => $key))->save($data);
        }
			$this->success("排序更新成功！缩略图修改成功");
    }
	public function addtype_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $modified = $created = date("Y-m-d H:i:s");
		$color = $_POST['color'];
		if($color == '颜色'){ $this -> error('请选择颜色');}
		if($_POST['type']==0){ $this -> error('请选择尺码类型');}
		if($_POST['type']==1){ $xssize = $_POST['xsxsize'];}
		if($_POST['type']==2){ $xssize = $_POST['xsfsize'];}
		if($_POST['type']==3){ $xssize = $_POST['xspsize'];}
		if($_POST['type']==1){ $ljsize = $_POST['ljxsize'];}
		if($_POST['type']==2){ $ljsize = $_POST['ljfsize'];}
		if($_POST['type']==3){ $ljsize = $_POST['ljpsize'];}
		if(!$xssize){ $this -> error('请选择显示码');}
		if(!$ljsize){ $this -> error('请选择丽晶码');}
		if(!$_POST['pric_amount']){ $this -> error('请输入价格');}
		if(!$_POST['prope_existing']){ $this -> error('请输入库存');}
		$id = $_POST['item_id'];
		$property = $this->Property_obj
		->where("itemID=$id")
		->order("itemID desc")
		->select();
		$num = $this->Property_obj
		->where("itemID=$id && itemColor = '$color'")
		->select();
		$Colornum = $this->Property_obj
		->where("itemID=$id")
		->group("itemColor")
		->select();
		$Colornum=count($Colornum);
		if($num){ $c = $num[0]['itemColorID'];}else{ $c = $Colornum+1;}
		if($num){ $s = count($num)+1;}else{ $s = 1;}
		$prop['itemID']=$id;
		$prop['originalHeld']=$_POST['prope_existing'];
		$prop['itemColorID']=$c;
		$prop['itemColor']=$_POST['color'];
		$prop['itemSizeID']=$s;
		$prop['itemSize']=$xssize;
		$prop['store_size']=$ljsize;
		$prop['extraClass'] = $_POST['prope_coding'];
		$prop['modified'] = $modified;
		$prop['created'] = $created;
		$prop['userid'] = $userid;
		$propid = $this->Property_obj -> add($prop);
		$price['relatedType'] = 7;
		$price['amount'] = $_POST['pric_amount'];
		$price['relatedID'] = $propid;
		$price['unit'] = '件';
		$price['modified'] = $modified;
		$price['created'] = $created;
		$price['userid'] = $userid;
		if($this->Price_obj -> add($price)){
			$this -> success('商品新属性添加成功!');
		}else{
		    $this -> error('商品新属性添加失败!');
		}
	}
	public function getArr( $file ){
        if(!file_exists( $file )){
            return false;//判断文件是否存在
        }
        $res = array();
        $arr = file($file);
        foreach($arr as $value){
            $value = trim( $value );
            if( empty($value)){
                continue;//去除空行
            }
			$value=explode(",", $value);
            $res[] = $value;//确定第二维度
        }
        return $res;
    }
	public function logistics(){
			$this->display();
	}
	public function logistics_post(){
			if($_SESSION['userid']>0){
				$userid = $_SESSION['userid'];
			}else{
				$userid = get_current_admin_id();
			}
			$model = new Model();
			$sql = 'TRUNCATE `Enable_helds`';
			$ret = $model->query($sql);
			setlocale(LC_ALL, 'en_US.UTF-8');
			$filepath1 = "data/runtime/";
			if($_FILES["associatetxt"]["name"]){  
					$path1 = $_FILES["associatetxt"]["name"]; 
					$path2 =time().substr($path1,strpos($path1,'.'));
				if(substr($path1,strpos($path1,'.'))!='.csv'){
				   	$this -> error('文件格式不正确');
				}
			}
			if($_FILES["associatetxt"]["size"]>2048000){
			   	$this -> error('TXT文件大小不得超过2MB');
			}
			move_uploaded_file($_FILES["associatetxt"]["tmp_name"],$filepath1.$path2);
			$file = $filepath1.$path2; 
			$arr = $this->getArr($file);
			while ($data = fgetcsv($file)) {
				$goods_list[] = $data;
			}
			$e = 0;
			//从第i行开始读取
			for($i=3;$i<count($arr);$i++){
				if($arr[$i][1]!=null && $arr[$i][2]!=null && $arr[$i][3]!=null && $arr[$i][4]!=null){
					$enables[$e]['storeid']=$arr[$i][0];
					$enables[$e]['productid']=$arr[$i][1];
					$enables[$e]['store_size']=$arr[$i][2];
					$enables[$e]['enable_held']= $arr[$i][3];
					$enables[$e]['tag_price']= $arr[$i][4];
					$e++;
				}
			}
			fclose($file);
			$enable = $this->Enable_obj-> addAll($enables);
			/********更新库存********
			$enable = $this->Enable_obj
			 ->field('productid,store_size, sum(enable_held) enable_held')
			 ->group('productid,store_size')
			 ->select();
			$property = 0;
			for($i=0;$i<count($enable);$i++){
				$productid=$enable[$i]['productid'];
				$store_size=$enable[$i]['store_size'];
				$prop['originalHeld'] = $enable[$i]['enable_held'];
				$property = $this->Property_obj
				->where("extraClass='$productid' && store_size = '$store_size'")
				->data($prop)
				->save();
			}
			 */
			if($enable){
				$this->success("库存更新成功！", U("shop/index"));
			}else{
				$this->error("库存更新失败！");
			}
	}
}
