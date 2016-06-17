<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-01
	描述：后台首页
**********************************/
class MainAction extends AdminbaseAction {
	
	protected $Useractions_obj;
	function _initialize() {
	}
    public function index(){
    	
		$today = date("Y-m-d 00:00:00");
    	$mysql= mysql_get_server_info();
    	$mysql=empty($mysql)?"未知":$mysql;
    	//服务器信息
    	$info = array(
    			'操作系统' => PHP_OS,
    			'运行环境' => $_SERVER["SERVER_SOFTWARE"],
    			'PHP运行方式' => php_sapi_name(),
    			'MYSQL版本' =>$mysql,
    			'程序版本' => SIMPLEWIND_CMF_VERSION,
    			'上传附件限制' => ini_get('upload_max_filesize'),
    			'执行时间限制' => ini_get('max_execution_time') . " 秒",
    			'剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . ' M',
    			'推荐浏览器' => '<a href="http://www.maxthon.cn/" target="_blank">傲游 - 让自由前所未有</a>',
    			'后台兼容BUG:' => '⇓',
    			'BUG:1' => 'Google内核浏览器下用户详情返回按钮有问题',
    			'BUG:2' => 'Firefox内核浏览器下批量图片上传有问题',
    			'BUG:3' => 'Internet Explorer 8以下内核浏览器后台左侧菜单不兼容'
    	);
	    	$this->assign('server_info', $info);
	    	$this->display();
	}
}