<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-10-08
	描述：友盟推送
**********************************/
class UmengAction extends AdminbaseAction{
	protected $Usertokens_obj;
	function _initialize() {
		parent::_initialize();
		$this->Usertokens_obj = D("Usertokens1");
		$this->T_push_obj = D("T_push1");
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
	function ryq_arrayall($url){  
	    $ch = curl_init();  
	    curl_setopt($ch, CURLOPT_URL, $url);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; 
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; 
	    $r = curl_exec($ch);  
	    curl_close($ch);  
	    return $r;  
	}  
	function index(){
		$count=$this->T_push_obj->count();
		$page = $this->page($count, 20);
		$push = $this->T_push_obj
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$this->assign("push",$push);
		$this->assign("Page", $page->show('Admin'));
        $this->display();
	}
	function single(){
        $this->display();
	}
	function many(){
        $this->display();
	}
	function single_post(){
    	if(isset($_POST)){
    		if(!$_POST['userid']){ $this -> error("请填写用户ID");}
    		if(!$_POST['description']){ $this -> error("请填写消息内容");}
			$push = localhost_url.'index.php?g=api&m=umeng&a=index&userid='.$_POST['userid'].'&describe='.$_POST['description'];
			$banner = $this->ryq_array($push);
			if($banner['ret'] == "SUCCESS"){
				$this -> success('消息已推送!', U("Umeng/index"));
			}else{
	    		$this -> error('消息推送失败!');
			}
		} else {
			$this->error("非法请求");
		}
	}
	function many_post(){
    	if(isset($_POST)){
			$title= $_POST['title'];
			$description= $_POST['description'];
			if(!$title){ $this -> error("请填写消息名称");}
			if(!$description){ $this -> error("请填写消息内容");}
			$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=".$title."&describe=".$description;
			if($this->ryq_arrayall($push)){
				$this -> success('消息已推送!', U("Umeng/index"));
			}
		} else {
			$this->error("非法请求");
		}
	}
}
