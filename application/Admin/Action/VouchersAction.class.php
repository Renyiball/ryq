<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-07-29
	描述：代金卷管理
**********************************/
class VouchersAction extends AdminbaseAction {
	protected $Shop_obj,$T_users_obj,$Users_obj,$Property_obj,$Useractions_obj;

    function _initialize() {
		parent::_initialize();
		$this->Shop_obj = D("Iteminfos");//商品
		$this->T_users_obj = D("T_users");//后台用户
		$this->Users_obj = D("Users");//用户
		$this->Property_obj = D("Itemdetails");//属性
		$this->Useractions_obj = D("Useractions");//订单
	}
	public function index(){
    	$this->_list();
    }
	
	public function _list(){
		$parakey_single = array ('start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
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
			$count=$this->Shop_obj->where("itemName = 10030 $search")-> count();
			$page = $this->page($count, 20);
			$items = $this->Shop_obj
			->where("itemName = 10030 $search")
			->order("created DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			$users = $this->T_users_obj
			->order("id desc")
			->select();
			$property = $this->Property_obj
			->order("itemID desc")
			->select();
	    	$this->assign('num',$count);
			$this->assign("lists",$items);
			$this->assign("users",$users);
			$this->assign("property",$property);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("formpost",$parameters);
			$this->display();
	}
	function up(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Shop_obj->where("id=$id")->setField('status','100');
    		if ($rst) {
    			$this->success("启此优惠卷成功！");
    		} else {
    			$this->error('启用优惠卷失败！');
    		}
    	} else {
			$this->error($this->Shop_obj->getError());
    	}
    }
    function down(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Shop_obj->where("id=$id")->setField('status','0');
    		if ($rst) {
    			$this->success("停用优惠卷成功！");
    		} else {
    			$this->error('停用优惠卷失败！');
    		}
    	} else {
			$this->error($this->Shop_obj->getError());
    	}
    }
    function add(){
			$this->display();
    }

	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $modified = $created = date("Y-m-d H:i:s");
		if(!$_POST['name']){ $this -> error('名称不能为空');}
		if(!$_POST['description']){ $this -> error('描述不能为空');}
		if($_POST['multiple']==0){ $this -> error('请选择使用倍数');}
		if(!$_POST['value']){ $this -> error('代金卷面值不能为空');}
		if(!$_POST['num']){ $this -> error('特定商品ID不能为空');}
		if(!$_POST['validity']){ $this -> error('代金卷有效期不能为空');}
	    if(isset($_POST)){			
	        $shop = $_POST;
	        $shop['itemName'] = 10030;
	        $shop['itemDesc'] = $_POST['name'];
	        $shop['status'] = $_POST['enable'];
	        $shop['showOrder'] = 0;
	        $shop['originalSale'] = 0;
	        $shop['created'] = $created;
	        $shop['modified'] = $modified;
	        $shop['userid'] = $userid;
			$shopid = $this->Shop_obj -> add($shop);
			if($shopid){
					$property['itemID']=$shopid;
					$property['originalHeld']=$_POST['num'];
					$property['itemColorID']=1;
					$property['itemColor']=$_POST['value'];
					$property['itemSizeID']=1;
					$property['itemSize']=$_POST['validity'];
    				$property['extraClass'] = $_POST['multiple'];
    				$property['extraDesc'] = $_POST['description'];
					$property['modified'] = $modified;
					$property['created'] = $created;
					$property['userid'] = $userid;
					$propertyid = $this->Property_obj -> add($property);
			}
						if($propertyid){
							$this -> success('代金卷添加成功!', U("Vouchers/index"));
						}else{
						    $this -> error('代金卷添加失败');
						}
	    }else{
			$this->error("非法提交！");
	    }
    }

    function edit(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$info = $this->Shop_obj
		->where("id = $id")
		->find();
		$property =$this->Property_obj->where("itemID=$id")->find();
		$prid = $property['id'];
		$action =$this->Useractions_obj->where("parentID >0 && actionType = 10030 && actionID = '$prid'")->select();
		if($action){
			$this->assign("action",$action);
		}
    	$this->assign('info',$info);
		$this->assign("property",$property);
    	$this->display();
		}else{
			$this->error($this->Shop_obj->getError());
		}
    }
    function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $modified = date("Y-m-d H:i:s");
	    if(isset($_POST)){
	        $itemid = $_POST['infoid'];
	        $shop['itemDesc'] = $_POST['name'];
	        $shop['modified'] = $modified;
	        $shop['userid'] = $userid;
			$property['originalHeld']=$_POST['num'];
			$property['itemColor']=$_POST['value'];
			$property['itemSize']=$_POST['validity'];
			$property['extraClass'] = $_POST['multiple'];
			$property['extraDesc'] = $_POST['description'];
			$property['modified'] = $modified;
			$property['userid'] = $userid;
			if($this->Shop_obj -> where("id = $itemid")->data($shop)->save() && $this->Property_obj -> where("itemID = $itemid")->data($property)->save()){
				$this -> success('代金卷修改成功!', U("Vouchers/index"));
			}else{
			    $this -> error('代金卷修改失败');
			}
	    }else{
			$this->error("非法提交！");
	    }
    }
	//删除
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
            if ($this->Shop_obj->where("id=$id")->delete()) {
            	$this->Property_obj->where("itemID=$id")->delete();
				$this -> success('删除优惠卷成功!');
            } else {
                $this->error("删除优惠卷失败！");
            }
        }else{
			$this->error("非法提交！");
        }
    }
	
    public function deletegrant() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
            if ($this->Useractions_obj->where("id=$id")->delete()) {
            	$this->Useractions_obj->where("parentID=$id")->delete();
				$this -> success('删除优惠卷成功!');
            } else {
                $this->error("删除优惠卷失败！");
            }
        }else{
			$this->error("非法提交！");
        }
    }
//取出接口中第$list个数组
	function ryq_array($postArray){
		$js_data = array();
		if(!function_exists('file_get_contents')) {
			$posts = file_get_contents($postArray);
		} else {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt ($ch, CURLOPT_URL, $postArray);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$post = curl_exec($ch);
			curl_close($ch);
		}
		$de_json = json_decode($post,true);
		$count_json = sizeof( $de_json['return_data']);
		for ($i = 0; $i <$count_json; $i++)
		{
			$js_data[$i] = $de_json['return_data'][$i];
		}
		return $js_data;
	}	
    function grant(){
		if($_POST['userid']){
			$userid = $_POST['userid'];
		}
		if($_GET['userid']){
			$userid = $_GET['userid'];
		}
		if(!$_POST['userid'] && !$_GET['userid']){
			$userid = 0;
		}
			$url = webroot_url.'userdetails/apprequest?art=301&loginuserid='.$userid;
	   		$banner = $this->ryq_array($url);
			$this->assign("banner",$banner);
			$this->assign("userid",$userid);
			
			$id = $_GET['id'];
			$infoid = $_POST['infoid'];
			$this->assign('info',$id);
			$this->assign("infoid",$infoid);
			$this->display();
    }
    function grant_post(){
		if(!$_POST['userid']){
			$this->error("用户ID不可为空");
		}
		$userid = $_POST['userid'];
		$infoid = $_POST['infoid'];
		$property = $this->Property_obj
		->where("itemID=$infoid")
		->select();
		$itemid = $property[0]['id'];
		$url = webroot_url.'userdetails/apprequest?art=20102&userid='.$userid.'&couponid='.$itemid;
   		$banner = $this->ryq_array($url);
		if($banner){
			$this -> success('优惠卷发放成功!', U("Vouchers/index"));
        } else {
            $this->error("用户已拥有此优惠卷！");
        }
    }
	
}