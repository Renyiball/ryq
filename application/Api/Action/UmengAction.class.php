<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-10-21
	描述：推送接口
**********************************/
require_once(UMENG. 'android/AndroidunicastAction.class.php');
require_once(UMENG. 'android/AndroidbroadcastAction.class.php');
require_once(UMENG. 'ios/IosunicastAction.class.php');
require_once(UMENG. 'ios/IosbroadcastAction.class.php');
class UmindexAction{
	protected $appkey           = NULL;
	protected $appMasterSecret     = NULL;
	protected $timestamp        = NULL;
	protected $validation_token = NULL;

	function __construct($key, $secret) {
		$this->appkey = $key;
		$this->appMasterSecret = $secret;
		$this->timestamp = strval(time());
	}
	function sendAndroidUnicast($text,$tokens) {
		try {
			$unicast = new AndroidunicastAction();
			$unicast->setAppMasterSecret($this->appMasterSecret);
			$unicast->setPredefinedKeyValue("appkey",$this->appkey);
			$unicast->setPredefinedKeyValue("timestamp",$this->timestamp);
			// Set your device tokens here
			$unicast->setPredefinedKeyValue("device_tokens",$tokens); 
			$unicast->setPredefinedKeyValue("ticker","任意球 - 消息提醒");
			$unicast->setPredefinedKeyValue("title","任意球 - 消息提醒");
			$unicast->setPredefinedKeyValue("text",$text);
			$unicast->setPredefinedKeyValue("after_open","go_app");
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			$unicast->setPredefinedKeyValue("production_mode", "true");
			// Set extra fields
			$unicast->setExtraField("test", "任意球");
			//print("Sending unicast notification, please wait...\r\n");
			$result = $unicast->send();
    		$type = json_decode($result,true);
			return $type;
			//print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			return $e->getMessage();
			//print("Caught exception: " . $e->getMessage());
		}
	}

	function sendAndroidBroadcast($title,$text) {
		try {
			$brocast = new AndroidbroadcastAction();
			$brocast->setAppMasterSecret($this->appMasterSecret);
			$brocast->setPredefinedKeyValue("appkey",$this->appkey);
			$brocast->setPredefinedKeyValue("timestamp",$this->timestamp);
			$brocast->setPredefinedKeyValue("ticker",$title);
			$brocast->setPredefinedKeyValue("title",$title);
			$brocast->setPredefinedKeyValue("text",$text);
			$brocast->setPredefinedKeyValue("after_open",       "go_app");
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			$brocast->setPredefinedKeyValue("production_mode", "true");
			// [optional]Set extra fields
			$brocast->setExtraField("test", "任意球");
			//print("Sending broadcast notification, please wait...\r\n");
			$result = $brocast->send();
    		$type = json_decode($result,true);
			return $type;
			//print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			return $e->getMessage();
			//print("Caught exception: " . $e->getMessage());
		}
	}
	function sendIOSUnicast($text,$tokens) {
		try {
			$unicast = new IosunicastAction();
			$unicast->setAppMasterSecret($this->appMasterSecret);
			$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
			$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			// Set your device tokens here
			$unicast->setPredefinedKeyValue("device_tokens",$tokens); 
			$unicast->setPredefinedKeyValue("alert",$text);
			$unicast->setPredefinedKeyValue("badge", 0);
			$unicast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$unicast->setPredefinedKeyValue("production_mode", "true");
			// Set customized fields
			//print("Sending unicast notification, please wait...\r\n");
			$result = $unicast->send();
    		$type = json_decode($result,true);
			return $type;
			//print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			return $e->getMessage();
			//print("Caught exception: " . $e->getMessage());
		}
	}
	
	function sendIOSBroadcast($text) {
		try {
			$brocast = new IosbroadcastAction();
			$brocast->setAppMasterSecret($this->appMasterSecret);
			$brocast->setPredefinedKeyValue("appkey",           $this->appkey);
			$brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);

			$brocast->setPredefinedKeyValue("alert", $text);
			$brocast->setPredefinedKeyValue("badge", 1);
			$brocast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$brocast->setPredefinedKeyValue("production_mode", "true");
			// Set customized fields
			//print("Sending broadcast notification, please wait...\r\n");
			$result = $brocast->send();
    		$type = json_decode($result,true);
			return $type;
			//print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			return $e->getMessage();
			//print("Caught exception: " . $e->getMessage());
		}
	}
}

class UmengAction extends AppframeAction{
	protected $Usertokens_obj,$T_push;

	function _initialize() {
		parent::_initialize();
		$this->Usertokens_obj = D("Usertokens1");
		$this->T_push_obj = D("T_push");
	}
	function ryq_array($url){  
	    $ch = curl_init();  
	    curl_setopt($ch, CURLOPT_URL, $url);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回    
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回    
	    $r = curl_exec($ch);  
	    curl_close($ch);  
	    return $r;  
	}  
	function index(){
		//http://localhost/ryq_server/index.php?g=api&m=umeng&a=index&userid=1&describe=test
		$url_userid = @$_GET['userid'] ? $_GET['userid'] : 0;
		$url_describe = @$_GET['describe'] ? $_GET['describe'] : '';
		if(empty($url_userid)){
	    $output = array('ret'=>'No content','code'=>204,'info'=>'请传入用户ID！');
	    	exit(json_encode($output));
	 	}
		if(empty($url_describe)){
	    $output = array('ret'=>'No content','code'=>204,'info'=>'请传入推送内容！');
	    	exit(json_encode($output));
	 	}
		$user = $this->Usertokens_obj->where("relateduserid = $url_userid")->order('modified desc')->find();
		if(!$user){
	    	$output = array('ret'=>'Not found','code'=>404,'info'=>'没有找到用户！');
	    	exit(json_encode($output));
		}
			$userSource = $user['device_type'];
			$user_logon = $user['is_logon'];
			$nickname = $user['nickname'];
		if(!$user_logon){
	    	$output = array('ret'=>'Not found','code'=>404,'info'=>'用户【'.$nickname.'】当前不在线、无法推送消息！');
	    	exit(json_encode($output));
		}
			$request_uri = $user['token'];
			$push['push_title'] = '任意球 - 消息提醒';
			$push['push_describe'] = $url_describe;
			if($userSource == '200'){
				/***安卓代码调用***/
				$test = new UmindexAction('559e1bb767e58e075a000de5','wlceipctcp7tcxkijol0zx7pvusmnv4v');
				$result = $test->sendAndroidUnicast($url_describe,$request_uri);
				$push['push_type'] = 'Android';
			}
			if($userSource == '100'){
				/***IOS代码调用***/
				$ios = new UmindexAction('55a4857067e58ee3c1001fa9','okvr9rtilkwgawskl0h6ogrv6ow9n3dm');
				$result = $ios->sendIOSUnicast($url_describe,$request_uri);
				$push['push_type'] = 'Ios';
			}
			$push['push_status'] = stripslashes(json_encode($result));
			$push['push_time'] =date("Y-m-d H:i:s");
			$push['push_userid'] = $url_userid;
			$this->T_push_obj->add($push);
	}
	function bulk(){
		$url_title = @$_GET['title'] ? $_GET['title'] : '';
		$url_describe = @$_GET['describe'] ? $_GET['describe'] : '';
		if(empty($url_title)){
	    	$output = array('ret'=>'No content','code'=>204,'info'=>'请传入推送标题！');
	    	exit(json_encode($output));
	 	}
		if(empty($url_describe)){
	    	$output = array('ret'=>'No content','code'=>204,'info'=>'请传入推送内容！');
	    	exit(json_encode($output));
	 	}
		$push['push_title'] = $url_title;
		$push['push_describe'] = $url_describe;
		/***安卓代码调用***/
		$android = new UmindexAction('559e1bb767e58e075a000de5','wlceipctcp7tcxkijol0zx7pvusmnv4v');
		$androidret = $android->sendAndroidBroadcast($url_title,$url_describe);
		/***IOS代码调用***/
		$ios = new UmindexAction('55a4857067e58ee3c1001fa9','okvr9rtilkwgawskl0h6ogrv6ow9n3dm');
		$iosret = $ios->sendIOSBroadcast($url_title);
		$push['push_type'] = '群发';
		$push['push_status'] = stripslashes(json_encode($androidret)).'-'.stripslashes(json_encode($iosret));
		$push['push_time'] =date("Y-m-d H:i:s");
		$push['push_userid'] = '全部用户';
		$this->T_push_obj->add($push);
	}
	
}