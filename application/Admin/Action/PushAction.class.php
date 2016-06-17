<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-04-18
	描述：推送管理
**********************************/
/*namespace Admin\Controller;
use Common\Controller\AdminbaseController;*/
class PushAction extends AdminbaseAction{
	protected $push_model;
	
	function _initialize() {
		parent::_initialize();
		$this->push_model=D("T_push");
	}
	function push_url($url){ 
	    $ch = curl_init();  
	    curl_setopt($ch, CURLOPT_URL, $url);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回    
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回    
	    $r = curl_exec($ch);  
	    curl_close($ch);  
	    return $r;  
	}  
	public function index(){
		$count = $this->push_model->count();
		$page = $this->page($count, 20);
		$pushs = $this->push_model
		->order("id DESC")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$this->assign("pushs",$pushs);
		$this->assign("page", $page->show('Admin'));
		$this->display();	
	}
	public function push(){
		$this->display();
    }
	public function push_post(){
		$push = localhost_url."index.php?g=api&m=Jpush&a=index&uid=".I("post.uid")."&pform=".I("post.pform")."&mid=".I("post.mid")."&content=".I("post.content");
		if($push){
			$this->push_url($push);
			$this->success("推送消息已提交！".$push,U('Push/index'));
		} else {
			$this->error("推送消息提交失败！");
		}
    }
}