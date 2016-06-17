<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-08-01
	描述：操作日志
**********************************/
class JournalAction extends AdminbaseAction {
	function _initialize() {
		parent::_initialize();
	}
	public function index(){
    	$this->_list();
    }

	public function _list(){
		if($_POST['time']){
			$date=$_POST['time'];
		}else{
			$date=date('Y-m-d');
		}
		$filename="data/journal/journal".$date.".log";
		if(fopen($filename,"r")){
			$num = count(file($filename));
		}else{
			$num = 0;
		}
		$file_hwnd=fopen($filename,"r");
		$content = fread($file_hwnd, filesize($filename));  // 读去文件全部内容
		fclose($file_hwnd);
		$array_log = array();
		for($i=0;$i<$num;$i++){
			$array_log[$i] = unserialize(explode("\r\n", $content)[$i]);
		}
		$this->assign("content",$array_log);
		$this->assign("formpost",$date);
		$this->assign("num",$num);
		$this->display();		
	}
}