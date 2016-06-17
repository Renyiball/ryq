<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-08
	描述：分类管理
**********************************/
class ClassAction extends AdminbaseAction {
	protected $Class_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Class_obj = D("Itemtypes");
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('typeclass');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
			if($parameters['typeclass'] > "0")
			{
				$typeclass = " && ( parent_typeid = '".$parameters['typeclass']."')";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			$search = $typeclass;
		}
		$count=$this->Class_obj->where("typeid !=''  $search")-> count();
		$page = $this->page($count, 20);
		$items = $this->Class_obj
		->where("typeid !=''  $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$class = $this->Class_obj
		->where("typeid !=''")
		->group("parent_type_text")
		->select();
    	$this->assign('class',$class);
    	$this->assign('num',$count);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("lists",$items);
		$this->assign("formpost",$parameters);
		$this->display();
	}
	
	public function add(){
		$class = $this->Class_obj
		->where("typeid !=''")
		->group("parent_type_text")
		->select();
    	$this->assign('class',$class);
    	$this->display();
	}

	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if($_POST['group']=='0'){
		        $this -> error('请选择分类!');
		}
	    if(isset($_POST)){
	    	$parent_type_text = $_POST['group'];
			$info = $this->Class_obj
			->where("parent_type_text = '$parent_type_text'")
			->order("id desc")
			->limit(2)
			->select();
			$num =  $info[0]['typeid'] - $info[1]['typeid'];
			$order =  $info[0]['show_order1'] - $info[1]['show_order1'];
			$typeid =  $info[0]['typeid'] + $num;
			$order1 =  $info[0]['show_order1'] + $order;
	        $data['type_text'] = $_POST['classname'];
	        $data['typeid'] = $typeid;
	        $data['groupid'] = $info[0]['groupid'];
	        $data['group_text'] = $info[0]['group_text'];
	        $data['parent_typeid'] = $info[0]['parent_typeid'];
	        $data['parent_type_text'] = $info[0]['parent_type_text'];
	        $data['show_order1'] = $order1;
	        $data['userid'] = $userid;
		      if($this->Class_obj -> add($data)){
		        $this -> success('添加分类成功!', U("class/index"));
		      }else{
		        $this -> error('添加分类失败!');
		      }
	    }else{
			$this->error($this->Class_obj->getError());
	    }
	}
	
    public function edit(){
        if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$p=$_GET['p'];
		$c=$_GET['c'];
    	$result = $this->Class_obj
		->where("id=$id")
		->find();
		$class = $this->Class_obj
		->group("group_text")
		->select();
    	$this->assign('class',$class);
        $this->assign('type',$result);
		$this->assign("p",$p);
		$this->assign("c",$c);
    	$this->display();
		}else{
			$this->error($this->Class_obj->getError());
		}
    }
	public function edit_post(){
        $id = $_POST['id'];
        unset($_POST['id']);
        $data['type_text'] = $_POST['classname'];
        if ($this->Class_obj->where("id = $id")->data($data)->save()) {
			if($_POST['c']){
				$this -> success('修改成功!',U("class/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
			}else{
				$this -> success('修改成功!',U("class/index",array('p'=>$_POST['p'])));
			}
        } else {
            $this->error('修改失败!');
        }
    }
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
            if ($this->Class_obj->where("id=$id")->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }else{
			$this->error($this->Class_obj->getError());
        }

    }

}
