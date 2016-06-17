<?php
require_once(UMENG. 'IosAction.class.php');

class IosgroupcastAction extends IosAction {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "groupcast";
		$this->data["filter"]  = NULL;
	}
}