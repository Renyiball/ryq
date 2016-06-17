<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-12-21
	描述：更新日志
**********************************/
class UpdateAction extends AdminbaseAction {
	
	protected $Useractions_obj;
	function _initialize() {
	}
    public function index(){
		$filename="README.md";
		$file_hwnd=fopen($filename,"r");
		$content = fread($file_hwnd, filesize($filename));
		$array_log= explode("\r\n", $content);
		fclose($file_hwnd);
		$this->assign("content",$array_log);
    	$this->display();
	}
}