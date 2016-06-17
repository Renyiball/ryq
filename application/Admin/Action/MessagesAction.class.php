<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-10-28
	描述：站内信
**********************************/
class MessagesAction extends AdminbaseAction {
	protected $Innermails_obj,$Users_obj;
	function _initialize() {
		parent::_initialize();
		$this->Innermails_obj = D("Innermails");
		$this->Users_obj = D("Users");//用户
	}
	public function index(){
    	$this->_list();
    }

	public function _list(){
		$parakey_single = array ('to_userid','start_time','end_time','userid');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
			if($parameters['to_userid'] !='')
			{
				$to_userid = " && (to_userid = '".$parameters['to_userid']."')";
				$_GET['to_userid'] = $parameters['to_userid'];
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
			if($parameters['userid'] !='')
			{
				$userid = " && (userid = '".$parameters['userid']."')";
				$_GET['userid'] = $parameters['userid'];
			}
			$search = $to_userid.$start_time.$end_time.$userid;
		}
		$count=$this->Innermails_obj->where("to_userid > 0 $search")->count();
		$page = $this->page($count, 20);
		$lists = $this->Innermails_obj
		->where("to_userid > 0 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$u=0;
		for($i=0;$i<count($lists);$i++){
			if($lists[$i]['to_userid']){
				$fuid[$u] = $lists[$i]['to_userid'];
				$u++;
			}
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
		$this->assign("formpost",$parameters);
		$this->display();
	}
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
            if ($this->Innermails_obj->where("id=$id")->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }else{
			$this->error($this->Innermails_obj->getError());
        }

    }
	public function send(){
		$this->display();
    }
	public function send_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if(!$_POST['userid']){ $this -> error('收信用户ID不能为空');}
		if(!$_POST['description']){ $this -> error('私信内容不能为空');}
    	if(isset($_POST)){
	        $innermails['to_userid'] = $_POST['userid'];
	        $innermails['content'] = $_POST['description'];
	        $innermails['created'] = date("Y-m-d H:i:s");
	        $innermails['userid'] = $userid;
			if($this->Innermails_obj->add($innermails)){
				$this -> success('私信发布成功!',U("Messages/index"));
			}else{
			    $this -> error('私信发布失败');
	    	}
		} else {
			$this->error($this->Innermails_obj->getError());
		}
    }
}
