<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-08-10
	描述：赛事管理
**********************************/
class EventAction extends AdminbaseAction {
	protected $Actioninfos_obj,$Teams_obj,$Users_obj,$Moredetails_obj,$Images_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Users_obj = D("T_users");
		$this->Actioninfos_obj = D("Actioninfos");
		$this->Teams_obj = D("Teams");
		$this->Moredetails_obj = D("Moredetails");
		$this->Images_obj = D("Imagedocs");
		$this->Gameinfos_obj = D("Gameinfos");
	}
	public function _arr(){
		$typearr  = array(
						array("id"=>"1",	"name"=> "A"),
						array("id"=>"2",	"name"=> "B"),
						array("id"=>"3",	"name"=> "C"),
						array("id"=>"4",	"name"=> "D"),
						array("id"=>"5",	"name"=> "E"),
						array("id"=>"6",	"name"=> "F"),
						array("id"=>"7",	"name"=> "G"),
						array("id"=>"8",	"name"=> "H"),
						array("id"=>"9",	"name"=> "I"),
						array("id"=>"10",	"name"=> "J"),
						array("id"=>"11",	"name"=> "K"),
						array("id"=>"12",	"name"=> "L"),
						array("id"=>"13",	"name"=> "M"),
						array("id"=>"14",	"name"=> "N"),
						array("id"=>"15",	"name"=> "O"),
						array("id"=>"16",	"name"=> "P"),
						array("id"=>"17",	"name"=> "Q"),
						array("id"=>"18",	"name"=> "R"),
						array("id"=>"19",	"name"=> "S"),
						array("id"=>"20",	"name"=> "T"),
						array("id"=>"21",	"name"=> "U"),
						array("id"=>"22",	"name"=> "V"),
						array("id"=>"23",	"name"=> "W"),
						array("id"=>"24",	"name"=> "X"),
						array("id"=>"25",	"name"=> "Y"),
						array("id"=>"26",	"name"=> "Z")
					);
		return $typearr;
	}
	public function _teamarr(){
		$typearr  = array(
						array("id"=>"200",	"name"=> "中超"),
						array("id"=>"201",	"name"=> "北体大"),
						array("id"=>"300",	"name"=> "中甲"),
						array("id"=>"401",	"name"=> "英超"),
						array("id"=>"402",	"name"=> "德甲"),
						array("id"=>"403",	"name"=> "亚冠"),
						array("id"=>"404",	"name"=> "东亚男足"),
						array("id"=>"405",	"name"=> "东亚女足"),
						array("id"=>"406",	"name"=> "西甲"),
						array("id"=>"407",	"name"=> "意甲"),
						array("id"=>"408",	"name"=> "法甲"),
						array("id"=>"409",	"name"=> "欧冠"),
						array("id"=>"410",	"name"=> "英冠"),
						array("id"=>"411",	"name"=> "苏冠"),
						array("id"=>"412",	"name"=> "巴甲"),
						array("id"=>"413",	"name"=> "阿甲"),
						array("id"=>"414",	"name"=> "墨甲"),
						array("id"=>"415",	"name"=> "乌甲"),
						array("id"=>"416",	"name"=> "哥甲"),
						array("id"=>"417",	"name"=> "荷甲"),
						array("id"=>"418",	"name"=> "比甲"),
						array("id"=>"419",	"name"=> "土超"),
						array("id"=>"420",	"name"=> "俄超"),
						array("id"=>"421",	"name"=> "葡超"),
						array("id"=>"500",	"name"=> "女足世界杯"),
						array("id"=>"501",	"name"=> "美洲杯"),
						array("id"=>"502",	"name"=> "欧洲杯"),
						array("id"=>"503",	"name"=> "亚洲杯"),
						array("id"=>"504",	"name"=> "非洲杯"),
						array("id"=>"505",	"name"=> "世预赛"),
						array("id"=>"506",	"name"=> "世界杯"),
						array("id"=>"507",	"name"=> "欧联杯"),
						array("id"=>"508",	"name"=> "英联杯"),
						array("id"=>"509",	"name"=> "苏联杯"),
						array("id"=>"510",	"name"=> "U23亚洲杯")
					);
		return $typearr;
	}
	public function _extraarr(){
		$typearr  = array(
						array("id"=>"order_950",	"name"=> "order_950"),
						array("id"=>"order_900",	"name"=> "order_900"),
						array("id"=>"order_850",	"name"=> "order_850"),
						array("id"=>"order_800",	"name"=> "order_800"),
						array("id"=>"order_750",	"name"=> "order_750"),
						array("id"=>"order_700",	"name"=> "order_700"),
						array("id"=>"order_650",	"name"=> "order_650"),
						array("id"=>"order_600",	"name"=> "order_600"),
						array("id"=>"order_550",	"name"=> "order_550"),
						array("id"=>"order_500",	"name"=> "order_500"),
						array("id"=>"order_450",	"name"=> "order_450"),
						array("id"=>"order_400",	"name"=> "order_400"),
						array("id"=>"order_350",	"name"=> "order_350"),
						array("id"=>"order_300",	"name"=> "order_300"),
						array("id"=>"order_250",	"name"=> "order_250"),
						array("id"=>"order_200",	"name"=> "order_200"),
						array("id"=>"order_150",	"name"=> "order_150"),
						array("id"=>"order_100",	"name"=> "order_100")
					);
		return $typearr;
	}
	public function _player(){
		$typearr  = array(
						array("id"=>"三人制",	"name"=> "三人制"),
						array("id"=>"四人制",	"name"=> "四人制"),
						array("id"=>"五人制",	"name"=> "五人制"),
						array("id"=>"六人制",	"name"=> "六人制"),
						array("id"=>"七人制",	"name"=> "七人制"),
						array("id"=>"八人制",	"name"=> "八人制"),
						array("id"=>"九人制",	"name"=> "九人制"),
						array("id"=>"十人制",	"name"=> "十人制"),
						array("id"=>"十一人制",	"name"=> "十一人制")
					);
		return $typearr;
	}
	public function expert(){
		$count=$this->Teams_obj-> where("teamdesc = '球队分类' && length(teamtype)< 8 && teamtype > '20000'")->count();
		$page = $this->page($count, 20);
		$items = $this->Teams_obj
		->where("teamdesc = '球队分类' && length(teamtype)< 8 && teamtype > '20000'")
		->order("status desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$this->assign("lists",$items);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("users",$users);
	    $this->assign('num',$count);
		$this->display();
	}
	public function amateur(){
		$parakey_single = array ('game_name','game_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		$search = '';
	    if(isset($parameters)){
			if($parameters['game_name'])
			{
				$game_name= "&& (game_name like '%".$parameters['game_name']."%')";
				$_GET['game_name'] = $parameters['game_name'];
			}
			if($parameters['game_time'] != '')
			{
				$game_time = "&& (date(startdate) <= '".$parameters['game_time']."' ) && (date(enddate) >= '".$parameters['game_time']."')";
				$_GET['game_time'] = $parameters['game_time'];
			}
			$search = $game_name.$game_time;
		}	
		$count=$this->Gameinfos_obj->where("game_name !='' $search")->count();
		$page = $this->page($count, 20);
		$gameinfos = $this->Gameinfos_obj
		->where("game_name !='' $search")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$this->assign("gameinfos",$gameinfos);
		$this->assign("imgtituan",webroot_url);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formget",$_GET);
	    $this->assign('num',$count);
		$this->display();
	}
	public function old(){
		$count=$this->Actioninfos_obj-> where("parentid = 0 && subtype = 20000 && constid like'2015%'")->count();
		$page = $this->page($count, 20);
		$items = $this->Actioninfos_obj
		->where("parentid = 0 && subtype = 20000 && constid like'2015%'")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$images = $this->Images_obj
		->where("relatedType = 8")
		->order("relatedID asc")
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$this->assign("lists",$items);
		$this->assign("images",$images);
		$this->assign("imgtituan",webroot_url);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("users",$users);
	    $this->assign('num',$count);
		$this->display();
	}
    public function addexpert() {
		$teams = $this->_teamarr();
		$extras = $this->_extraarr();
		$datey = date("y");
		$this->assign("teams",$teams);
		$this->assign("extras",$extras);
		$this->assign("datey",$datey);
    	$this->display();
    }
    public function addexpert_post() {
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if($_POST['teamname'] == 0){$this -> error('请选择赛事类型');}
		if($_POST['extradesc'] == '0'){$this -> error('请选择排序类型');}
		if(!$_POST['fullname2']){$this -> error('赛季时间不能为空');}
		if($_POST['fullname1']){$fullname1 = $_POST['fullname1'].'-';}
		$teams = $this->_teamarr();
		$created = date("Y-m-d H:i:s");
    	if(isset($_POST)){
    		for($i=0;$i<count($teams);$i++){
    			if($_POST['teamname'] == $teams[$i]['id']){
    				$tname = $teams[$i]['name'];
    			}
    		}
			$team['teamname'] = $tname;
			$team['fullname'] = $tname.$fullname1.$_POST['fullname2'];
	        $team['teamdesc'] = '球队分类';
	        $team['extradesc'] = $_POST['extradesc'];
	        $team['status'] = 100;
	        $team['teamtype'] =$_POST['teamname'].$_POST['fullname2'];
	        $team['constid'] =0;
			$team['created'] = $created;
	        $team['userid'] = $userid;
				if($this->Teams_obj -> add($team))
				{
					$this -> success('赛事添加成功!', U("event/expert"));
				}else{
				    $this -> error('赛事添加失败');
				}
		}
    	$this->display();
    }
	public function findNum($str=''){
	$str=trim($str);
	if(empty($str)){return '';}
	$temp = $this->_arr();
	$result='';
	for($j=0;$j<sizeof($temp);$j++){
		for($i=0;$i<strlen($str);$i++){
			if(in_array($str[$i],$temp[$j])){
				$j++;
				if($j <sizeof($temp)){
					$result=$temp[$j]['name'];
				}else{
				    $this -> error('赛事ID已满');
				}
			}
		}
	}
	return $result;
	}
	public function addamateur() {
		$players = $this->_player();
		$this->assign("players",$players);
    	$this->display();
    }
    public function addamateur_post() {
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if(!I("post.game_name") ||!I("post.host_title") ||!I("post.host_field") ||!I("post.area")){
			$this->error("请填写数据");
		}
			$ym = date("ym");
			$infos = $this->Gameinfos_obj->where("constid like '$ym%'")->count();
			if($_FILES){
				$filepath = 'img/Event/'.date("Y").'/'.date("m").'/';
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				$tm = time();
				$imgwidth = 600;
				if($_FILES["game_logo"]["name"]){
					$path1 = $_FILES["game_logo"]["name"]; 
					$path2 = time().substr($path1,strpos($path1,'.'));
					$_POST['game_logo']=$filepath.'galogo'.$tm.$this->thumb_img($_FILES["game_logo"]["tmp_name"],$imgwidth,webroot_img.$filepath.'galogo'.$tm);
				}
				if($_FILES["game_adv"]["name"]){
					$path1 = $_FILES["game_adv"]["name"]; 
					$path2 = time().substr($path1,strpos($path1,'.'));
					$_POST['game_adv']=$filepath.'gaadv'.$tm.$this->thumb_img($_FILES["game_adv"]["tmp_name"],$imgwidth,webroot_img.$filepath.'gaadv'.$tm);
				}
				if($_FILES["game_bg"]["name"]){
					$path1 = $_FILES["game_bg"]["name"]; 
					$path2 = time().substr($path1,strpos($path1,'.'));
					$_POST['game_bg']=$filepath.'gabg'.$tm.$this->thumb_img($_FILES["game_bg"]["tmp_name"],$imgwidth,webroot_img.$filepath.'gabg'.$tm);
				}
			}
			$_POST['status'] = 100;
			$_POST['created'] = date("Y-m-d H:i:s");
			$_POST['userid'] = get_current_admin_id();
			$_POST['is_show'] = 0;
			$_POST['constid'] = $ym.'0001'+$infos;
			if ($this->Gameinfos_obj->add($_POST)){
				$this->success("添加成功！",U('Event/amateur'));
			} else {
				$this->error("添加失败！");
			}
    }
	public function editexpert(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$team = $this->Teams_obj
		->where("id ='$id' ")
		->order("id desc")
		->find();
		$teams = $this->_teamarr();
		$extras = $this->_extraarr();
		$this->assign("teams",$teams);
		$this->assign("extras",$extras);
		$this->assign("team",$team);
    	$this->display();
		}else{
			$this->error($this->Teams_obj->getError());
		}
	}
	public function editexpert_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
	$created = date("Y-m-d H:i:s");
    	if(isset($_POST)){
			$id = $_POST['id'];
			$team['fullname'] = $_POST['fullname'];
			$team['extradesc'] = $_POST['extradesc'];
			$team['created'] = $created;
			$team['userid'] = $userid;
			if($this->Teams_obj -> where("id = $id")->data($team)->save()){
				$this -> success('修改成功!', U("event/expert"));
			}else{
			    $this -> error('修改失败');
			}
		} else {
			$this->error($this->Teams_obj->getError());
		}
		
	}
	public function editamateur(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$gameinfos = $this->Gameinfos_obj
		->where("id ='$id'")
		->order("id desc")
		->find();
		$players = $this->_player();
		$this->assign("imgtituan",webroot_url);
		$this->assign("info",$gameinfos);
		$this->assign("players",$players);
    	$this->display();
		}else{
			$this->error($this->Gameinfos_obj->getError());
		}
	}
	public function editamateur_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		if(!I("post.game_name") ||!I("post.host_title") ||!I("post.host_field") ||!I("post.area")){
			$this->error("请填写数据");
		}
		if($_POST){
			if($_FILES){
				$filepath = 'img/Manually/actioninfo/';
				if(!file_exists(webroot_img.$filepath)){
					mkdir(webroot_img.$filepath, 0777,true);  
				}
				$infos = $this->Gameinfos_obj->where(array("id"=>intval(I("post.id"))))->find();
				$tm = time();
				$imgwidth = 600;
				if($_FILES["game_newlogo"]["name"]){
					if ($infos['game_logo']) {
						$logo = webroot_img.$infos['game_logo'];
						if (file_exists($logo)){ unlink ($logo);}
					}
					$path1 = $_FILES["game_newlogo"]["name"]; 
					$path2 = time().substr($path1,strpos($path1,'.'));
					
					$_POST['game_logo']=$filepath.'gelogo'.$tm.$this->thumb_img($_FILES["game_newlogo"]["tmp_name"],$imgwidth,webroot_img.$filepath.'gelogo'.$tm);
				}
				if($_FILES["game_newadv"]["name"]){
					if ($infos['game_adv']) {
						$adv = webroot_img.$infos['game_adv'];
						if (file_exists($logo)){ unlink ($adv);}
					}
					$path1 = $_FILES["game_newadv"]["name"]; 
					$path2 = time().substr($path1,strpos($path1,'.'));
					$_POST['game_adv']=$filepath.'geadv'.$tm.$this->thumb_img($_FILES["game_newadv"]["tmp_name"],$imgwidth,webroot_img.$filepath.'geadv'.$tm);
				}
				if($_FILES["game_newbg"]["name"]){
					if ($infos['game_bg']) {
						$bg = webroot_img.$infos['game_bg'];
						if (file_exists($logo)){ unlink ($bg);}
					}
					$path1 = $_FILES["game_newbg"]["name"]; 
					$path2 = time().substr($path1,strpos($path1,'.'));
					$_POST['game_bg']=$filepath.'gebg'.$tm.$this->thumb_img($_FILES["game_newbg"]["tmp_name"],$imgwidth,webroot_img.$filepath.'gebg'.$tm);
				}
			}
			$_POST['created'] = date("Y-m-d H:i:s");
			$_POST['userid'] = get_current_admin_id();
			if ($this->Gameinfos_obj->save($_POST)){
				$this->success("修改成功！",U('Event/amateur'));
			} else {
				$this->error("修改失败！");
			}	
		}else{
			$this->error($this->Gameinfos_obj->getError());
		}
	}
	public function openexpert(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Teams_obj->where("id=$id")->setField('status','100');
    		if ($rst) {
    			$this->success("赛事启用成功！");
    		} else {
    			$this->error('赛事启用失败！');
    		}
    	} else {
			$this->error($this->Teams_obj->getError());
    	}
	}
	public function closeexpert(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Teams_obj->where("id=$id")->setField('status','0');
    		if ($rst) {
    			$this->success("赛事停用成功！");
    		} else {
    			$this->error('赛事停用失败！');
    		}
    	} else {
			$this->error($this->Teams_obj->getError());
    	}
	}
	public function openamateur(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Gameinfos_obj->where("id=$id")->setField('is_show','1');
    		if ($rst) {
    			$this->success("赛事启用成功！");
    		} else {
    			$this->error('赛事启用失败！');
    		}
    	} else {
			$this->error($this->Gameinfos_obj->getError());
    	}
	}
	public function closeamateur(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->Gameinfos_obj->where("id=$id")->setField('is_show','0');
    		if ($rst) {
    			$this->success("赛事停用成功！");
    		} else {
    			$this->error('赛事停用失败！');
    		}
    	} else {
			$this->error($this->Gameinfos_obj->getError());
    	}
	}
	//排序
	public function showorders() {
		$pk = $this->Gameinfos_obj->getPk(); //获取主键名称
        $ids = $_POST['show_order'];
        foreach ($ids as $key => $r) {
            $data['show_order'] = $r;
            $status = $this->Gameinfos_obj->where(array($pk => $key))->save($data);
        }
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
}