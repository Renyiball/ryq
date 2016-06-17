<?php
require_once(UMENG. 'IosAction.class.php');

class IosunicastAction extends IosAction {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "unicast";
		$this->data["device_tokens"] = NULL;
	}

}