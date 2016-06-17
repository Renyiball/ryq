<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-07-17
	描述：用户反馈
**********************************/
class FeedbackAction extends AdminbaseAction {
	
	protected $Forums_obj,$Users_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Forums_obj = D("Forums");
		$this->Users_obj = D("Users");//用户
	}

	public function index(){
    	$this->_list();
    }
	public function _list(){
		$search = '';
		$class = $this->Forums_obj
		->where("parentid = 2")
		->select();
		for($i=0;$i<count($class);$i++){
				if($i<count($class)-1){
					$parentid = "parentid =".$class[$i]['id'].' || ';
					$parent =$parent.$parentid;
				}else{
					$parentid = "parentid =".$class[$i]['id'];
					$parent =$parent.$parentid;
				}
		}
		$search = $search.' && ('.$parent.')';
		$count=$this->Forums_obj->where("typeid = 300  $search")-> count();
		$page = $this->page($count, 20);
		$lists = $this->Forums_obj
		->where("typeid = 300  $search")
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
    	$this->assign('num',$count);
		$this->assign("lists",$lists);
    	$this->assign('class',$class);
    	$this->assign('users',$users);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
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
		return $de_json;
	}	
	public function acceptance(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$p=$_GET['p'];
		$info = $this->Forums_obj
		->where("id = $id")
		->find();
    	$this->assign('info',$info);
		$this->assign("p",$p);
    	$this->display();
		}else{
			$this->error($this->Forums_obj->getError());
		}
	}
    public function acceptance_post() {
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if(!$_POST['extraDesc']){$this->error('请填写受理情况');}
    	if(isset($_POST)){
        	$id=$_POST['infoid'];
			$pushuserid=$_POST['userid'];
			$extraDesc=$_POST['extraDesc'];
			$forum['extraDesc'] = $extraDesc.'---操作员：'.$userid.'操作时间：'.date("Y-m-d H:i:s");
			$forum['status'] = 90;
			$push = localhost_url.'index.php?g=api&m=umeng&a=index&userid='.$pushuserid.'&describe='.$extraDesc;
   			$banner = $this->ryq_array($push);
    		if ($this->Forums_obj -> where("id = $id")->data($forum)->save()) {
    			if($banner['ret'] == 'SUCCESS'){
    				$this->success('受理成功,已推送通知！', U("Feedback/index",array('p'=>$_POST['p'])));
    			}else{
    				$this->success('受理成功！', U("Feedback/index",array('p'=>$_POST['p'])));
    			}
    		} else {
    			$this->error('受理失败！');
    		}
    	} else {
			$this->error($this->Forums_obj->getError());
    	}
    }
}