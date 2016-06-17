<?php
require_once(UMENG. 'AndroidAction.class.php');

class AndroidbroadcastAction extends AndroidAction {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}