<?php
require_once(UMENG. 'IosAction.class.php');

class IosbroadcastAction extends IosAction {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}