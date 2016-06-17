<?php
require_once(UMENG. 'AndroidAction.class.php');

class AndroidgroupcastAction extends AndroidAction {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "groupcast";
		$this->data["filter"]  = NULL;
	}
}