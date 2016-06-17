<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-12-09
	描述：文章内页
**********************************/
class ArticleAction extends HomeBaseAction {
    //文章内页
    protected $T_posts_obj;
    function _initialize() {
		parent::_initialize();
		$this->T_posts_obj = D("T_posts");
	}
    public function index() {
    	$id=intval($_GET['id']);
    	$sd=$_GET['from'];
		if(!$sd){
			$sd=strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger');
		}
		$posts = $this->T_posts_obj
		->where("id = $id")
		->order("id desc")
		->find();
    	$post_hits=sp_check_user_action("T_posts$id",1,false);
    	if($post_hits){
    		$posts_model=M("T_posts");
    		$posts_model->save(array("id"=>$id,"post_hits"=>array("exp","post_hits+1")));
    	}
		import("Jssdk",'application/Weixin',".php");
		$jssdk = new JSSDK('wx8efff750d56373d2', 'b010d032eade47ccd1627ebd36576dbc');
		$signPackage = $jssdk->GetSignPackage();
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    	$this->assign("post",$posts);
    	$this->assign("sd",$sd);
		$this->assign("imgtituan",webroot_url);
		$this->assign("url",$url);
		$this->assign("signPackage",$signPackage);
    	$tplname=sp_get_apphome_tpl($tplname, "article");
    	$this->display(":$tplname");
    }
    
//  public function do_like(){
//  	$id=intval($_GET['id']);
//  	$posts_model=M("T_posts");
//  	$can_like=sp_check_user_action("T_posts$id",1,true);
//  	
//  	if($can_like){
//  		$posts_model->save(array("id"=>$id,"post_like"=>array("exp","post_like+1")));
//  		$this->success("赞好啦！");
//  	}else{
//  		$this->error("您已赞过啦！");
//  	}
//  	
//  }
}
?>
