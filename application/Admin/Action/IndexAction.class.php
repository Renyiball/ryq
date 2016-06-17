<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-01
	描述：后台首页
**********************************/
class IndexAction extends AdminbaseAction {
	
	
	function _initialize() {
		parent::_initialize();
		$this->initMenu();
	}
    //后台框架首页
    public function index() {
        $this->assign("SUBMENU_CONFIG", json_encode(D("T_menu")->menu_json()));
       	$this->display();
        
    }

    

}

?>
