<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-10-21
	描述：对阵数据
**********************************/
class AgainstAction extends AppframeAction {
	function _initialize() {
		parent::_initialize();
	}
	function summary($postArray){
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
		$js_data = $de_json['return_data']['summary']['title'];
		return $js_data;
	}
	function data_list($postArray){
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
		$count_json = sizeof( $de_json['return_data']['data_list']);
		for ($i = 0; $i <$count_json; $i++)
		{
			$js_data[$i] = $de_json['return_data']['data_list'][$i];
		}
		return $js_data;
	}
	function head_data($postArray){
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
		return $js_data = $de_json['return_data']['head_data'];
	}	
    public function index() {
		$url = webroot_url.'bets/apprequest?art=10201&loginuserid=-1&page_number=1';
   		$banner = $this->data_list($url);
   		$summary = $this->summary($url);
		$this->assign("banner",$banner);
		$this->assign("summary",$summary);
		$this->assign("imgtituan",webroot_url);
		$this->display();
    }
    public function guess() {
		$id = @$_GET['id'] ? $_GET['id'] : 0;
		if(empty($id)){
	   		$out204 = array('ret'=>'No content','code'=>204,'info'=>'请传入所属比赛ID！');
			$this->assign("out204",$out204);
	 	}
        $description4 = date("Y-m-d H:i:s");
		$urlt = webroot_url.'bets/apprequest?art=30201&loginuserid=-1&=&matchid='.$id;
		$urlb = webroot_url.'forums/apprequest?art=10203&loginuserid=-1&page_number=1&max_created=0&matchid='.$id;
   		$bannert = $this->head_data($urlt);
   		$bannerb = $this->data_list($urlb);
		$this->assign("bannert",$bannert);
		$this->assign("bannerb",$bannerb);
		$this->assign("description4",$description4);
		$this->assign("imgtituan",webroot_url);
		$this->display();
    }
}