<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2016-04-18
	描述：推送管理
**********************************/
/*namespace Api\Controller;
use Common\Controller\AppframeController;*/
require_once(JPUSH.'Jpush.php');
class JpushAction extends AppframeAction{
    private $app_key = '85bef99b02d41725aa4537c3';
    private $master_secret = 'adc379dc712feaf46687c8a4';
    private $url = "https://api.jpush.cn/v3/push";
    public function __construct($app_key=null, $master_secret=null,$url=null) {
        if ($app_key) $this->app_key = $app_key;
        if ($master_secret) $this->master_secret = $master_secret;
        if ($url) $this->url = $url;
    }
	public function index(){
		//http://m1.banjisai.com/index.php?g=api&m=Jpush&a=index&uid=1,10,117&pform=1&mid=1&mtype=1&mtime=3600&content=welcome
		//http://localhost/bjs_server/api/Jpush/index/pform/1/uid/1,5,109,102/content/welcome.html
		if(I("get.uid")){
			$users=D("Usertokens1")
			->field("relateduserid, max(modified)modified,token")
			->where(array('is_logon'=>1,'relateduserid'=>array('in',explode(',',I("get.uid")))))
			->group("relateduserid")

            
			->order("modified desc")
			->select();
			if($users){
				foreach($users as$k=>$utoken){$token[$k] = $utoken['token'];}
				$receive = array('registration_id' => $token);
			}
			$pform='all';
		}else{
			$receive= 'all';
			if(I("get.pform")==1){$pform='android';}elseif(I("get.pform")==2){$pform='ios';}else{$pform='all';}
		}
		if(I("get.content")){$content=I("get.content");}
		if(I("get.mid")){$m_id=I("get.mid");}else{$m_id='0';}
		if(I("get.mtype")){$m_type=I("get.mtype");}else{$m_type='0';}
		if(I("get.mtime")){$m_time=I("get.mtime");}else{$m_time='86400';}
		$result = $this->send_pub($pform,$receive,$content,$m_id,$m_type,$m_time);
		$push['push_describe']=$content;
		$push['push_type']=$pform;
		$push['push_userid']=I("get.uid");
		$push['push_time']=date("Y-m-d H:i:s");
		$push['push_status']=$result;
		D("T_push")->add($push);
		echo $result;
		
    }
        //调用推送方法
    public function send_pub($platform,$receive,$content,$m_id,$m_type,$m_time){            
        $message="";//存储推送状态
        $result = $this->push($platform,$receive,$content,$m_id,$m_type,$m_time);
        if($result){
            $res_arr = json_decode($result, true);
            if(isset($res_arr['error'])){                      //如果返回了error则证明失败
                $error_code=$res_arr['error']['code'];             //错误码
                    switch ($error_code) {
                        case 200:$message= '发送成功！';break;
                        case 1000:$message= '失败(系统内部错误)';break;
                        case 1001:$message= '失败(只支持 HTTP Post 方法，不支持 Get 方法)';break;
                        case 1002:$message= '失败(缺少了必须的参数)';break;
                        case 1003:$message= '失败(参数值不合法)';break;
                        case 1004:$message= '失败(验证失败)';break;
                        case 1005:$message= '失败(消息体太大)';break;
                        case 1008:$message= '失败(appkey参数非法)';break;
                        case 1011:$message= '失败(没有满足条件的推送目标)';break;
                        case 1020:$message= '失败(只支持 HTTPS 请求)';break;
                        case 1030:$message= '失败(内部服务超时)';break;
                        default:$message= '失败(返回其他状态，目前不清楚，请查看推送日至！)';break;
                    }
            }else{
                $message="发送成功！";
            }
        }else{
            $message='接口调用失败或无响应';
        }
		return $message.$result;
    }
    public function push($platform='all',$receiver='all',$content='',$m_id='',$m_type='',$m_time='86400'){
        $base64=base64_encode("$this->app_key:$this->master_secret");
        $header=array("Authorization:Basic $base64","Content-Type:application/json");
        $data = array();
        $data['platform'] = $platform;          //目标用户终端手机的平台类型android,ios,winphone
        $data['audience'] = $receiver;      //目标用户
         
        $data['notification'] = array(
                //统一的模式--标准模式
                "alert"=>$content,
                 //安卓自定义
                "android"=>array(
                        "alert"=>$content,
                        "title"=>"",
                        "builder_id"=>1,
                        "extras"=>array("id"=>$m_id, "type"=>$m_type)
                ),
                //ios的自定义
                "ios"=>array(
                        "alert"=>$content,
                        "badge"=>"1",
                        "sound"=>"default",
                        "extras"=>array("id"=>$m_id, "type"=>$m_type)
                )
        );
        //苹果自定义---为了弹出值方便调测
        $data['message'] = array(
                "msg_content"=>$content,
                "extras"=>array("id"=>$m_id, "type"=>$m_type)
        );

        //附加选项
        $data['options'] = array(
                "sendno"=>time(),
                "time_to_live"=>$m_time, //保存离线时间的秒数默认为一天
                "apns_production"=>true, //布尔类型   指定 APNS 通知发送环境：0开发环境，1生产环境。或者传递false和true
        );
        $param = json_encode($data);
        $res = $this->push_curl($param,$header);
         
        if($res){       //得到返回值--成功已否后面判断
            return $res;
        }else{          //未得到返回值--返回失败
            return false;
        }
    }
    public function push_curl($param="",$header="") {
        if (empty($param)) { return false; }
        $postUrl = $this->url;
        $curlPost = $param;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$postUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}