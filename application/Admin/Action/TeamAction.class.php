<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-27
	描述：球队管理
**********************************/
class TeamAction extends AdminbaseAction {
	protected $Matchinfos_obj,$Bets_obj,$Actioninfos_obj,$Teams_obj,$Images_obj,$Action_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Actioninfos_obj = D("Actioninfos");
		$this->Teams_obj = D("Teams");
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Bets_obj = D("Bets");
		$this->Images_obj = D("Imagedocs");
		$this->Action_obj = D("Action_teams");
		$this->Gameinfos_obj = D("Gameinfos");
	}
	public function index(){
    	$this->_list();
    }
	public function amateur(){
    	$this->_amateurlist();
    }
	public function _arr(){
		$typearr  = array(
						array("id"=>"A组",	"name"=> "A组"),
						array("id"=>"B组",	"name"=> "B组"),
						array("id"=>"C组",	"name"=> "C组"),
						array("id"=>"D组",	"name"=> "D组"),
						array("id"=>"E组",	"name"=> "E组"),
						array("id"=>"F组",	"name"=> "F组"),
						array("id"=>"G组",	"name"=> "G组"),
						array("id"=>"H组",	"name"=> "H组"),
						array("id"=>"I组",	"name"=> "I组"),
						array("id"=>"J组",	"name"=> "J组"),
						array("id"=>"K组",	"name"=> "K组"),
						array("id"=>"L组",	"name"=> "L组"),
						array("id"=>"M组",	"name"=> "M组"),
						array("id"=>"N组",	"name"=> "N组")
					);
		return $typearr;
	}
	public function _status(){
		$typearr  = array(
						array("id"=>"0",	"name"=> "已申请"),
						array("id"=>"100",	"name"=> "已通过"),
						array("id"=>"150",	"name"=> "已拒绝"),
						array("id"=>"200",	"name"=> "已退出")
					);
		return $typearr;
	}
	public function _list(){
		$parakey_single = array ('typeclass');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['typeclass'] != 0)
			{
				$search = "&& (teamtype = '".$parameters['typeclass']."' )";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
		}
		$class = $this->Teams_obj
		->where("teamdesc = '球队分类' && status = 100 && teamtype!='10100' && length(teamtype)< 8")
		->order("id desc")
		->select();
		$teamtype = ' && ';
		for($i=0;$i<count($class);$i++){
			if($i<count($class)-1){
				$actionid = "(teamtype = '".$class[$i]['teamtype']."') || ";
			}else{
				$actionid = "(teamtype = '".$class[$i]['teamtype']."')";
			}
			$teamtype = $teamtype.$actionid;
		}
		if($search == '')
		{
			$search=$teamtype;
		}
		$count=$this->Teams_obj-> where("teamdesc != '球队分类' && teamtype!='10100' $search")->count();
		$page = $this->page($count, 20);
		$teams = $this->Teams_obj
		->where("teamdesc != '球队分类' && teamtype!='10100' $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$this->assign("class",$class);
		$this->assign("teams",$teams);
		$this->assign("formpost",$parameters);
		$this->assign("Page", $page->show('Admin'));
	    $this->assign('num',$count);
		$this->display();
	}
	public function _amateurlist(){
		$parakey_single = array ('typeclass');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['typeclass'] != 0){
				$aconstid = $parameters['typeclass'];
				$_GET['typeclass'] = $parameters['typeclass'];
			}
		}
		if($aconstid){
			$search = array('action_constid'=>array('in',$aconstid));
		}
		$gameinfos = $this->Gameinfos_obj
		->where(array('is_show'=>0))
		->order("id asc")
		->select();
		$count=$this->Action_obj-> where($search)->count();
		$page = $this->page($count, 20);
		$items = $this->Action_obj
		->where($search)
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		foreach($items as$k=>$item){
			$itemid[$k] = $item['teamid'];
		}
		$teams = $this->Teams_obj
		->where(array('id'=>array('in',$itemid)))
		->order("id desc")
		->select();
		$gstatus = $this->_status();
		$this->assign("gstatus",$gstatus);
		$this->assign("gameinfos",$gameinfos);
	    $this->assign('num',$count);
		$this->assign("items",$items);
		$this->assign("teams",$teams);
		$this->assign("formpost",$parameters);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	
	public function add(){
		$proclass = $this->Teams_obj
		->where("teamtype > '20000' && status = 100 && length(teamtype)< 8")
		->order("id desc")
		->select();
		$actionclass = $this->Actioninfos_obj
		->where("parentid = 0 && status = 100 && subtype = 20000")
		->order("id asc")
		->select();
		$this->assign("proclass",$proclass);
		$this->assign("actionclass",$actionclass);
		$packetarr = $this->_arr();
		$this->assign("packet",$packetarr);
    	$this->display();
	}

	public function add_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $created = date("Y-m-d H:i:s");
		$round = $type = '';
		$typearr = $this->_arr();
    	if(isset($_POST)){
			if($_POST['type']==0)
				{ $this -> error('球队类型不能为空');}
			if($_POST['type']==1)
				{ if( $_POST['proclass']=='0'){ $this -> error('赛事类型不能为空');} $_POST['actionclass'] = '';}
			if($_POST['type']==2)
				{ if( $_POST['actionclass']=='0'){ $this -> error('赛事类型不能为空');} $_POST['proclass'] = '';}
			if($_POST['packet']=='0'){ $_POST['packet']='';}
			if($_POST['teamname']=='')
				{ $this -> error('球队名称不能为空');}
			$teamtype = $_POST['proclass'].$_POST['actionclass'];
			$countnum=$this->Teams_obj->where("teamtype = $teamtype")->order("id desc")->find();

    		if($countnum['constid']){
				$str = $countnum['constid']+1;
    		}else{
    			$str = $teamtype.'001';
    		}
			$constid=$this->Teams_obj->where("constid = '$str'")->order("id desc")->find();
			if($constid){$str = $str+1;}
		        $teams['teamname'] = $_POST['teamname'];
		        $teams['teamdesc'] = $_POST['teamdesc'];
		        $teams['extradesc'] = $_POST['packet'];
				if($_POST['type']==1){
			        $teams['teamtype'] = $teamtype;
			        $teams['constid'] = $str;
				}else{
			        $teams['teamtype'] = 10100;
				}
		        $teams['created'] = $created;
		        $teams['userid'] = $userid;
				$team = $this->Teams_obj -> add($teams);
			if($_POST['type']==2){
		        $action['action_constid'] = $teamtype;
		        $action['teamid'] = $team;
		        $action['cur_group'] = $_POST['packet'];
		        $action['teamname'] = $_POST['teamname'];
		        $action['created'] = $created;
				$this->Action_obj-> add($action);
			}
			$teamnumber = 500000+$team;
    		$this->Teams_obj->where("id=$team")->setField('teamnumber',$teamnumber);
			if($_FILES){
				$filepath = 'img/Team/'.date("Y").'/'.$teamtype.'/';
				//判断目录是否存在/不存在就创建
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				if($_FILES["teamimg"]["name"]){  
						$path1 = $_FILES["teamimg"]["name"]; 
						$path2 = time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["teamimg"]["size"]>256000){ 
				   	$this -> error('图片文件大小不得超过256KB');
				}
				if(move_uploaded_file($_FILES["teamimg"]["tmp_name"],webroot_img.$filepath.$path2)){
					$image['relatedType']=10;
					$image['relatedID']=$team;
					$image['filepath']=$filepath;
					$image['filename']=$path2;
					$image['modified'] = $created;
					$image['created'] = $created;
					$image['userid'] = $userid;
					if(!$this->Images_obj -> add($image)){
					    $this -> error('添加球队LOGO失败');
					}
				}
			}
				if($team)
				{
					$this -> success('球队添加成功!', U("team/index"));
				}else{
				    $this -> error('球队添加失败');
				}
		} else {
			$this->error("非法提交！");
		}
    	$this->display();
	}
	
    public function edit(){
		$id=$_GET['id'];
		$p=$_GET['p'];
		$t=$_GET['t'];
		$c=$_GET['c'];
		$team = $this->Teams_obj
		->where("id = $id")
		->order("id desc")
		->find();
		$image = $this->Images_obj
		->where("relatedType = 10 && relatedID=$id")
		->order("id desc")
		->find();
		$action = $this->Action_obj
		->where("teamid=$id")
		->order("id desc")
		->find();
		$this->assign("imgtituan",webroot_url);
		$this->assign("team",$team);
		$this->assign("image",$image);
		$this->assign("action",$action);
		$packetarr = $this->_arr();
		$this->assign("packet",$packetarr);
		$gstatus = $this->_status();
		$this->assign("gstatus",$gstatus);
		$this->assign("p",$p);
		$this->assign("t",$t);
		$this->assign("c",$c);
    	$this->display();
    }
	public function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
        $created = date("Y-m-d H:i:s");
    	if(isset($_POST)){
			$id = $_POST['id'];
			if($_POST['teamname']=='')
				{ $this -> error('球队名称不能为空');}
			if($_POST['packet']=='0')
				{ $_POST['packet']='';}
				if(!$_POST['teamnumber']){
					$teamnumber = 500000+$id;
				}else{
					$teamnumber = $_POST['teamnumber'];
				}
		        $teams['id'] = $id;
		        $teams['teamname'] = $_POST['teamname'];
		        $teams['teamdesc'] = $_POST['teamdesc'];
		        $teams['extradesc'] = $_POST['packet'];
		        $teams['constid'] = $_POST['constid'];
		        $teams['teamnumber'] = $teamnumber;
		        $teams['created'] = $created;
		        $teams['userid'] = $userid;
				$action = $this->Action_obj
				->where("teamid=$id")
				->order("id desc")
				->find();
				if($action){
			        $action['cur_group'] = $_POST['packet'];
			        $action['status'] = $_POST['status'];
			        $action['teamname'] = $_POST['teamname'];
			        $action['created'] = $created;
					$this->Action_obj->where("teamid=$id")->data($action)->save();
				}
			if($_FILES){
				$filepath = 'img/Team/'.date("Y").'/'.$_POST['teamtype'].'/';
				//判断目录是否存在/不存在就创建
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				if($_FILES["teamimg"]["name"]){  
						$path1 = $_FILES["teamimg"]["name"]; 
						$path2 =time().substr($path1,strpos($path1,'.'));
				}
				if($_FILES["teamimg"]["size"]>256000){ 
				   	$this -> error('图片文件大小不得超过256KB');
				}
				if(move_uploaded_file($_FILES["teamimg"]["tmp_name"],webroot_img.$filepath.$path2)){
					$image['relatedType']=10;
					$image['relatedID']=$_POST['id'];
					$image['filepath']=$filepath;
					$image['filename']=$path2;
					$image['modified'] = $created;
					$image['created'] = $created;
					$image['userid'] = $userid;
					if(!$this->Images_obj -> add($image)){
					    $this -> error('添加球队LOGO失败');
					}
				}
			}
				if($this->Teams_obj -> data($teams)->save()){
					if($_POST['c']){
						$this -> success('球队修改成功!',U("team/".$_POST['t'],array('typeclass'=>$_POST['teamtype'],'p'=>$_POST['p'])));
					}else{
						$this -> success('球队修改成功!',U("team/".$_POST['t'],array('p'=>$_POST['p'])));
					}
				}else{
				    $this -> error('球队修改失败');
				}
		} else {
			$this->error("非法提交！");
		}
    	$this->display();
    }
	public function packet(){
		if(IS_POST){
			$tids=$_GET['ids'];
			if(isset($_GET['ids']) && isset($_POST['packet'])){
				$team['cur_group']=$_POST['packet'];
				if ( $this->Action_obj->where("teamid in ($tids)")->save($team) ) {
					$this->success("分组调整成功！");
				} else {
					$this->error("分组调整失败！");
				}
			}
		}else{
			$packetarr = $this->_arr();
			$this->assign("packet",$packetarr);
		}
    	$this->display();
    }
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
			if ($this->Teams_obj->where("id=$id")->delete()) {
			$imgname = $this->Images_obj->where("relatedType = 10 && relatedID=$id")->select();
			$imgurl = webroot_img.$imgname[0]['filepath'].$imgname[0]['filename'];
			if (file_exists($imgurl))
			{
			    $delete_ok = unlink ($imgurl);
			}
				$this->Images_obj->where("relatedType = 10 && relatedID=$id")->delete();
			    $this -> success('删除成功!');
        	} else {
            	$this->error("删除失败！");
        	}
        }else{
			$this->error("非法提交！");
        }
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
		$count_json = sizeof( $de_json['return_data']);
		$js_data = $de_json['return_msg'];
		return $js_data;
	}	
	public function getArr( $file ){
        if(!file_exists( $file )){
            return false;//判断文件是否存在
        }
        $res = array();
        $arr = file($file);
        foreach($arr as $value){
            $value = trim( $value );
            if( empty($value)){
                continue;//去除空行
            }
			$value=explode(",", $value);
            $res[] = $value;//确定第二维度
        }
        return $res;
    }
	public function associate(){
		$team = $_GET['id'];
		$this->assign("team",$team);
    	$this->display();
	}
	function expChangeCode($data){
		$encode = mb_detect_encoding($data, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5')); 
		if($encode=="UTF-8"){
		        return $data;
		}else{
				$data = eval('return '.iconv('gbk','utf-8',var_export($data,true)).';');
		        return $data;
		}
    }
	public function associate_post(){
			setlocale(LC_ALL, 'en_US.UTF-8');
			$filepath1 = "data/runtime/";
			if($_FILES["associatetxt"]["name"]){  
					$path1 = $_FILES["associatetxt"]["name"]; 
					$path2 =time().substr($path1,strpos($path1,'.'));
				if(substr($path1,strpos($path1,'.'))!='.csv'){
				   	$this -> error('文件格式不正确');
				}
			}
			if($_FILES["associatetxt"]["size"]>2048000){ 
			   	$this -> error('TXT文件大小不得超过2MB');
			}
			
			move_uploaded_file($_FILES["associatetxt"]["tmp_name"],$filepath1.$path2);
			
			$file = $filepath1.$path2; 
			$arr = $this->getArr($file);
			
			$team = $_POST['id'];
			if($_SESSION['userid']>0){
				$userid = $_SESSION['userid'];
			}else{
				$userid = get_current_admin_id();
			}
			$url = webroot_url.'teams/apprequest?art=201&teamid='.$team.'&';
			while ($data = fgetcsv($file)) {
			$goods_list[] = $data;
			 }
			$ca = count($arr[0]);
			$k=0;
			for($i=2;$i<count($arr);$i++){
				$type='';$k++;
				for($j=0;$j<$ca;$j++){
					$str = '';
					$str = $this->expChangeCode($arr[$i][$j]);
					$type = $type.$arr[1][$j]."=".$str."&" ;
				}
 			fclose($file);
				$url2 = $url.'play_number='.$k.'&role_typeid=10100&func_type=8&'.$type.'loginuserid='.$userid ;
				//$url2 = $url.'play_number='.$k.'&role_typeid=10100&func_type=8&'.$type.'loginuserid=107' ;
		   		$banner = $banner.$this->ryq_array($url2);
			}
				if($banner){
		            $this->error($banner);
		        }
				if(!$banner){
					$this -> success('球员导入成功!', U("Team/amateur"));
		        }
	}
	//图片删除
    public function delete_images() {
        if (intval(I("get.id"))) {
            $id = intval(I("get.id"));
			$imgname = $this->Images_obj->where("relatedType = 10 && relatedID=$id")->select();
			$imgurl = webroot_img.$imgname[0]['filepath'].$imgname[0]['filename'];
			if (file_exists($imgurl))
			{
			    $delete_ok = unlink ($imgurl);
			}
			if ($this->Images_obj->where("relatedType = 10 && relatedID=$id")->delete()) {
			    $this -> success('删除成功!');
        	} else {
            	$this->error("删除失败！");
        	}
        }else{
			$this->error("非法提交！");
        }
    }
	
}