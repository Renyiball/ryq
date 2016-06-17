<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-11-24
	描述：线下用户
**********************************/
class UnderAction extends AdminbaseAction {
	protected $Usercodes_obj,$Users_obj,$Contacts_obj,$Userdetails_obj,$Images_obj,$Useractions_obj,$Forums_obj,$Userroles_obj,$Userbets_obj,$Typeconfigs_obj;
	function _initialize() {
		parent::_initialize();
		$this->Offlineusers_obj = D("Offlineusers");//线下用户
	}
	public function index(){
    	$this->_list();
    }
	public function _list(){
		$parakey_single = array ('username','address','tele_numbers');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
	    if(isset($parameters)){
			if($parameters['username'] != '')
			{
				$username = " && (username like '%".$parameters['username']."%')";
				$_GET['username'] = $parameters['username'];
			}
			if($parameters['address'] != '')
			{
				$address = " && (address like '%".$parameters['address']."%')";
				$_GET['address'] = $parameters['address'];
			}
			if($parameters['tele_numbers'] != '')
			{
				$tele_numbers = " && (tele_numbers like '%".$parameters['tele_numbers']."%')";
				$_GET['tele_numbers'] = $parameters['tele_numbers'];
			}
			$search = $username.$address.$tele_numbers;
			
		}
			$count=$this->Offlineusers_obj->where("id > 0 $search")-> count();
			$page = $this->page($count, 20);
			$items = $this->Offlineusers_obj
			->where("id > 0 $search")
			->order("id asc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
	    	$this->assign('num',$count);
			$this->assign("Page", $page->show('Admin'));
			$this->assign("lists",$items);
			$this->assign("formpost",$parameters);
			$this->display();
	}
	public function add(){
			$this->display();
    }
	public function add_post(){
		if(IS_POST){
			if ($this->Offlineusers_obj->create()) {
				if ($this->Offlineusers_obj->add()!==false) {
					$this->success("添加成功！", U("Under/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->Offlineusers_obj->getError());
			}
		}
    }
	public function edit(){
		$id= intval(I("get.id"));
		$p=$_GET['p'];
		$user=$this->Offlineusers_obj->where(array("id"=>$id))->find();
		$this->assign($user);
		$this->assign("p",$p);
		$this->display();
    }
	public function edit_post(){
		if (IS_POST) {
			if ($this->Offlineusers_obj->create()) {
				$result=$this->Offlineusers_obj->save();
				if ($result!==false) {
					$this->success("保存成功！", U("under/index",array('p'=>$_POST['p'])));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->Offlineusers_obj->getError());
			}
		}
    }
	public function delete(){
		$id = intval(I("get.id"));		
		if ($this->Offlineusers_obj->where("id=$id")->delete()!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
    }

}