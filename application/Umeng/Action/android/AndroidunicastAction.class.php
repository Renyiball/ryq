<?php
require_once(UMENG. 'AndroidAction.class.php');

class AndroidunicastAction extends AndroidAction {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "unicast";
		$this->data["device_tokens"] = NULL;
	}

}