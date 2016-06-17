<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-09-18
	描述：猜球排行
**********************************/
class GuessAction extends AdminbaseAction {
	protected $Userbets_obj,$Users_obj;
	function _initialize() {
		parent::_initialize();
		$this->Userbets_obj = D("Userbets");
		$this->Users_obj = D("Users");
	}
	public function _arr(){
		$typearr  = array(
						array("id"=>"140",	"name"=> "申请兑换"),
						array("id"=>"150",	"name"=> "已兑换")
					);
		return $typearr;
	}
	public function index(){
    	$this->_list();
    }

	function getWeekTime($date=''){
		$timestamp=empty($date)?strtotime('now'):(is_numeric($date)?$date:strtotime($date));
		$w=strftime('%w',$timestamp);
		$date=array();
		$date['start_time']=date('Y-m-d 00:00:00',$timestamp-($w-1)*86400);
		$date['end_time']=date('Y-m-d 23:59:59',$timestamp+(7-$w)*86400);
		return $date;
	}
	public function _list(){
		$parakey_single = array ('typeclass','time','other');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
			if($parameters['typeclass'] > "0")
			{
				$typeclass = " && ( status = '".$parameters['typeclass']."')";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['time'] !=''){
				$date = $this->getWeekTime($parameters['time']);
				$start_time = " && ('".$date['start_time']."' <= date(points_expire))";
				$end_time = " && (date(points_expire) <= '".$date['end_time']."')";
				$_GET['time'] = $parameters['time'];
			}
			if($parameters['end_time'] !='')
			{
				$end_time = " && (date(points_expire) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			if($parameters['other'])
			{
				$other = " && ( extra_comment !='')";
				$_GET['other'] = $parameters['other'];
			}
			$search = $typeclass.$start_time.$end_time.$other;
		}
		$count = $this->Userbets_obj
		->where("betid=-101 $search")
		->order("id desc")
		->count();
		$page = $this->page($count, 20);
		$userbets = $this->Userbets_obj
		->where("betid=-101 $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$u=0;
		for($i=0;$i<count($userbets);$i++){
			if($userbets[$i]['userid']){
				$fuid[$u] = $userbets[$i]['userid'];
				$u++;
			}
		}
		$fuid = array_merge(array_unique($fuid));
		for($i=0;$i<count($fuid);$i++){
			if($i<count($fuid)-1){
				$furid = "id = ".$fuid[$i]." || ";
			}else{
				$furid = "id = ".$fuid[$i];
			}
			$furids =$furids.$furid;
		}
		$users = $this->Users_obj
		->where("$furids")
		->order("id desc")
		->select();
		$money = $this->Userbets_obj
		->where("betid=-101 $search")
		->order("id desc")
		->select();
		$moneys=0;
		for($m=0;$m<count($money);$m++){
			$moneyall=mb_substr($money[$m]['description'],30,8);
			preg_match('/\d+/',$moneyall,$arr);
			$moneys = $moneys+$arr[0];
		}
		$type = $this->_arr();	
    	$this->assign('type',$type);
	    $this->assign('num',$count);
		$this->assign("userbets",$userbets);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formpost",$parameters);
		$this->assign("users",$users);
		$this->assign("moneys",$moneys);
	    $this->display();
	}
	public function old(){
	    	$this->display();
	}
	
	function ryq_array($postArray){
		$js_data = array();
		if(!function_exists('file_get_contents')) {
			$posts = file_get_contents($postArray);
		} else {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt ($ch, CURLOPT_URL, $postArray);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$post = curl_exec($ch);
			curl_close($ch);
		}
		$de_json = json_decode($post,true);
		return $de_json;
	}	
	public function accepted(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
			$userbets = $this->Userbets_obj
			->where("id=$id")
			->find();
	        $data['status'] = 150;
	        $data['description'] = $userbets['description'].'---操作员：'.$userid.'操作时间：'.date("Y-m-d H:i:s");
			$puserid = $userbets['userid'];
			$betoption = $userbets['betoption'];
			$moneyall=mb_substr($userbets['description'],30,8);
			preg_match('/\d+/',$moneyall,$arr);
			$moneys = $arr[0];
			$extraDesc = '您本周申请的球币兑换'.$moneys.'元话费已经充值到手机'.$betoption.'，感谢您使用任意球APP。';
			$push = localhost_url.'index.php?g=api&m=umeng&a=index&userid='.$puserid.'&describe='.$extraDesc;
	        if ($this->Userbets_obj->where("id = $id")->data($data)->save()) {
				$banner = $this->ryq_array($push);
    			if($banner['ret'] == 'SUCCESS'){
    				$this->success('受理成功,已推送通知！', U("Guess/index"));
    			}else{
    				$this->success('受理成功！', U("Guess/index"));
    			}
	        } else {
	            $this->error('兑换失败!');
	        }
		}else{
			$this->error($this->Userbets_obj->getError());
        }
	}
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
            if ($this->Userbets_obj->where("id=$id")->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }else{
			$this->error($this->Userbets_obj->getError());
        }

    }
    public function other() {
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$p=$_GET['p'];
		$info = $this->Userbets_obj
		->where("id = $id")
		->find();
    	$this->assign('info',$info);
		$this->assign("p",$p);
    	$this->display();
		}else{
			$this->error($this->Userbets_obj->getError());
		}
    }
    public function other_post() {
		
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if(!$_POST['comment']){$this->error('请填写说明');}
    	if(isset($_POST)){
        	$id=$_POST['ubets'];
			$userbets = $this->Userbets_obj
			->where("id=$id")
			->find();
	        $data['status'] = 150;
        	$data['extra_comment']=$_POST['comment'];
	        $data['description'] = $userbets['description'].'---操作员：'.$userid.'操作时间：'.date("Y-m-d H:i:s");
        	$buserid=$_POST['userid'];
	        if ($this->Userbets_obj->where("id = $id")->data($data)->save()) {
				$moneyall=mb_substr($userbets['description'],30,8);
				preg_match('/\d+/',$moneyall,$arr);
				$moneys = $arr[0];
				$extraDesc = '您本周申请的球币兑换'.$moneys.'元话费客服为您处理，感谢您使用任意球APP。';
				$push = localhost_url.'index.php?g=api&m=umeng&a=index&userid='.$buserid.'&describe='.$extraDesc;
				$banner = $this->ryq_array($push);
    			if($banner['ret'] == 'SUCCESS'){
    				$this->success('受理成功,已推送通知！', U("Guess/index"));
    			}else{
    				$this->success('受理成功！', U("Guess/index"));
    			}
	        } else {
	            $this->error('兑换失败!');
	        }
    	} else {
			$this->error($this->Userbets_obj->getError());
    	}
    }

}