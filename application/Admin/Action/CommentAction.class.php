<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-09-30
	描述：商品评论
**********************************/
class CommentAction extends AdminbaseAction {
	
	protected $Comments_obj,$Users_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Comments_obj = D("Comments");
		$this->Users_obj = D("Users");//用户
	}

	public function index(){
    	$this->_list();
    }
	public function _list(){
		$count=$this->Comments_obj->where("relatedType = 6")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Comments_obj
		->where("relatedType = 6")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$u=0;
		for($i=0;$i<count($lists);$i++){
			if($lists[$i]['userid']){
				$fuid[$u] = $lists[$i]['userid'];
				$u++;
			}
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
		$this->assign("lists",$lists);
    	$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
}