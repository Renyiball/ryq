<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-06-18
	描述：专业比赛/猜球
**********************************/
class ExpertAction extends AdminbaseAction {
	protected $Matchinfos_obj,$Bets_obj,$Teams_obj,$Userbets_obj,$Users_obj,$Referbets_obj;
		
    function _initialize() {
		parent::_initialize();
		$this->Matchinfos_obj = D("Matchinfos");
		$this->Bets_obj = D("Bets");
		$this->Teams_obj = D("Teams");
		$this->Userbets_obj = D("Userbets");
		$this->Users_obj = D("T_users");//用户
		$this->Referbets_obj = D("Referbets");//用户
	}
	public function index(){
    	$this->_list();
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
	public function _arr(){
		$typearr  = array(
						array("id"=>"90",	"name"=> "小组赛"),
						array("id"=>"91",	"name"=> "淘汰赛"),
						array("id"=>"93",	"name"=> "1/32决赛"),
						array("id"=>"94",	"name"=> "1/16决赛"),
						array("id"=>"95",	"name"=> "1/8决赛"),
						array("id"=>"96",	"name"=> "1/4决赛"),
						array("id"=>"97",	"name"=> "半决赛"),
						array("id"=>"98",	"name"=> "决赛"),
						array("id"=>"99",	"name"=> "轮赛")
					);
		return $typearr;
	}
	public function _list(){
		$datetime = date('Y-m-d H:i:s');
		$parakey_single = array ('typeclass','noupdate','start_time','end_time');
		$parameters = $this->_GetRequestPara_All ( $parakey_single );
		if($parameters){
			if($parameters['typeclass'] > 0)
			{
				$typeclass = " && (matchtype like  '".$parameters['typeclass']."%' )";
				$_GET['typeclass'] = $parameters['typeclass'];
			}
			if($parameters['noupdate'] != '')
			{
				$noupdate = " && (status =".$parameters['noupdate']." && matchdatetime < '$datetime' )";
				$_GET['noupdate'] = $parameters['noupdate'];
			}

			if($parameters['start_time'] != '')
			{
				$start_time = " && ('".$parameters['start_time']."' <= date(matchdatetime))";
				$_GET['start_time'] = $parameters['start_time'];
			}
			if($parameters['end_time'] !='')
			{
				$end_time = " && (date(matchdatetime) <= '".$parameters['end_time']."')";
				$_GET['end_time'] = $parameters['end_time'];
			}
			$search = $typeclass.$noupdate.$start_time.$end_time;
		}
		$noups = $this->Matchinfos_obj
		->where("length(matchtype)=7 && status =0 && matchdatetime < '$datetime'")
		->order("id desc")
		->count();
		$team = $this->Teams_obj
		->where("teamdesc = '球队分类' && status = 100 && teamtype!='10100' && length(teamtype)< 8")
		->order("id desc")
		->select();
		for($i=0;$i<count($team);$i++){
				if($i<count($team)-1){
					$matchtype = "matchtype like '".$team[$i]['teamtype']."%' || ";
					$parent =$parent.$matchtype;
				}else{
					$matchtype = "matchtype like '".$team[$i]['teamtype']."%'";
					$parent =$parent.$matchtype;
				}
		}
		$search = '('.$parent.')'.$search;
		$count=$this->Matchinfos_obj-> where("$search && (length(matchtype)=7)")->count();
		$page = $this->page($count, 20);
		$items = $this->Matchinfos_obj
		->where("$search && (length(matchtype)=7)")
		->order("id desc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		for($i=0;$i<count($items);$i++){
			if($i<count($items)-1){
				$matchinfoid = "matchinfoid = '".$items[$i]['matchconstid']."' || ";
			}else{
				$matchinfoid = "matchinfoid = '".$items[$i]['matchconstid']."'";
			}
			$betsinfo =$betsinfo.$matchinfoid;
		}
		$bets = $this->Bets_obj
		->where("$betsinfo")
		->order("id desc")
		->select();
		$teams = $this->Teams_obj
		->where("constid != ''&& teamtype!='10100' && length(teamtype)< 8")
		->order("id desc")
		->select();
		$users = $this->Users_obj
		->order("id desc")
		->select();
		$this->assign("team",$team);
		$this->assign("noups",$noups);
		$this->assign("datetime",$datetime);
		$this->assign("lists",$items);
		$this->assign("bets",$bets);
		$this->assign("teams",$teams);
		$this->assign("formpost",$parameters);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("users",$users);
	    $this->assign('num',$count);
		$this->display();
	}
	
	public function add(){
		$teams = $this->Teams_obj
		->where("constid != ''&& teamtype!='10100' && length(teamtype)< 8")
		->order("id asc")
		->select();
		$type = $this->_arr();			
    	$this->assign('type',$type);
		$this->assign("teams",$teams);
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
			if($_POST['oneclass']==0)
				{$this -> error('赛事类型不能为空');}
			if($_POST['oneclasstype']==0)
				{$this -> error('比赛类型不能为空');}
			for($i=count($typearr);$i>0;$i--){
				if($typearr[$i]['id']==$_POST['oneclasstype']){
					$type = $typearr[$i]['name'];
					if($type != '轮赛'){
						$_POST['oneclassround'] = '';
					}
				}
			}
			if($_POST['oneclasstype']==99 && $_POST['oneclassround'] == 0)
				{$this -> error('轮次不能为空');}
			if($_POST['oneclasstype']==99 && $_POST['oneclassround'] > 0)
				{$round = '第'.$_POST['oneclassround'].'轮';}
			if($_POST['twoclass1']==0)
				{$this -> error('主队不能为空');}
			if($_POST['twoclass2']==0)
				{$this -> error('客队不能为空');}
			if($_POST['odds_w']==0 || $_POST['odds_w']=='')
				{$this -> error('主队胜赔率不能为空');}
			if($_POST['odds_d']==0 || $_POST['odds_d']=='')
				{$this -> error('两队平赔率不能为空');}
			if($_POST['odds_l']==0 || $_POST['odds_l']=='')
				{$this -> error('客队胜赔率不能为空');}
			if($_POST['start_time']==0)
				{$this -> error('开赛时间不能为空');}
			if($_POST['start_time'] < $created)
				{$this -> error('开赛时间不能晚于现在');}
			if($_POST['odds_w']>=20 || $_POST['odds_d']>=20 || $_POST['odds_l']>=20 )
				{$this -> error('赔率范围需小于20');}
			$one = $_POST['oneclass'];
			$teams = $this->Teams_obj
				->where("status = 100 && teamtype = $one")
				->order("id desc")
				->select();
				
		$oneclass = $_POST['oneclass'];
		$oneclasstype = $_POST['oneclasstype'];
		$oneclassround = $_POST['oneclassround'];
		if($oneclasstype != 99){
			$matchid = $oneclass.$oneclasstype;
		}elseif($oneclasstype == 99){
			$matchid = $oneclass.$oneclassround;
		}
		$countnum=$this->Matchinfos_obj->where("matchtype = '$matchid'")->count();
		if($countnum){
			$countnum = $countnum+1;
			if($countnum<10){
				$str='00'.$countnum;
			}else if($countnum<100){
				$str='0'.$countnum;
			}else if($countnum>=100){
				$str=''.$countnum;
			}  
		}else{
			$str='001';
		}
		$bet = $this->Bets_obj
		->where("matchinfoid like '$matchid%'")
		->order("id asc")
		->limit(1)
		->select(); 
		if($bet){
			$start = $bet[0]['effectivedate'];
		}else{
			$date = $_POST['start_time'];
			$firstday = date("Y-m-d",strtotime($date));
			$start = date("Y-m-d 06:00:00",strtotime("$firstday -7 day"));
		} 
			$team = $teams[0]['teamname'].' ';
			$full = $teams[0]['fullname'].' ';
			if($team != ' ' && $team != ''){$name = date("y").'年 '.$team;}
			if($full != ' ' && $full != ''){$name = $full;}
		        $match['teamid1'] = $_POST['twoclass1'];
		        $match['teamid2'] = $_POST['twoclass2'];
		        $match['matchtype'] = $matchid;
		        $match['matchconstid'] = $matchid.$str;
		        $match['matchdatetime'] = $_POST['start_time'];
		        $match['matchdesc'] =  $name.$type.$round;
		        $match['status'] = 0;
		        $match['showorder'] = 0;
		        $match['created'] = $created;
		        $match['userid'] = $userid;
//				$bets['action_constid'] = date("Y").'A120';
				$bets['action_constid'] = '2015A120';
				$bets['matchinfoid'] = $matchid.$str;
				$bets['effectivedate'] = $start;
				$bets['score_offset'] = 0;
				$bets['points'] = 200;
				$bets['odds_w'] = $_POST['odds_w'];
				$bets['odds_d'] = $_POST['odds_d'];
				$bets['odds_l'] = $_POST['odds_l'];
				$bets['pointstype'] = 15000;
				$bets['status'] = 100;
				$bets['showorder'] = $matchid.$str.'0';
				$bets['expiredate'] = $_POST['start_time'];
				$bets['created'] = $created;
				$bets['userid'] = $userid;
				$r=0;
				if($_POST['Opencom']){
					$refer[$r]['matchinfoid'] = $matchid.$str;
					$refer[$r]['betstype'] = $_POST['Opencom'];
					$refer[$r]['effectivedate'] = $start;
					$refer[$r]['odds_w'] = $_POST['complex_w'];
					$refer[$r]['odds_d'] = $_POST['complex_d'];
					$refer[$r]['odds_l'] = $_POST['complex_l'];
					$refer[$r]['pointstype'] = 15000;
					$refer[$r]['showorder'] = $matchid.$str.'0';
					$refer[$r]['expiredate'] = $_POST['start_time'];
					$refer[$r]['created'] = $created;
					$refer[$r]['userid'] = $userid;
					$r++;
				}
				if($_POST['Openeur']){
					$refer[$r]['matchinfoid'] = $matchid.$str;
					$refer[$r]['betstype'] = $_POST['Openeur'];
					$refer[$r]['effectivedate'] = $start;
					$refer[$r]['odds_w'] = $_POST['europe_w'];
					$refer[$r]['odds_d'] = $_POST['europe_d'];
					$refer[$r]['odds_l'] = $_POST['europe_l'];
					$refer[$r]['pointstype'] = 15000;
					$refer[$r]['showorder'] = $matchid.$str.'0';
					$refer[$r]['expiredate'] = $_POST['start_time'];
					$refer[$r]['created'] = $created;
					$refer[$r]['userid'] = $userid;
					$r++;
				}
				if($_POST['Openasi']){
					$refer[$r]['matchinfoid'] = $matchid.$str;
					$refer[$r]['betstype'] = $_POST['Openasi'];
					$refer[$r]['effectivedate'] = $start;
					$refer[$r]['odds_w'] = $_POST['asia_w'];
					$refer[$r]['odds_d'] = $_POST['asia_d'];
					$refer[$r]['odds_l'] = $_POST['asia_l'];
					$refer[$r]['pointstype'] = 15000;
					$refer[$r]['showorder'] = $matchid.$str.'0';
					$refer[$r]['expiredate'] = $_POST['start_time'];
					$refer[$r]['created'] = $created;
					$refer[$r]['userid'] = $userid;
					$r++;
				}
				if($_POST['Opencom'] || $_POST['Openeur'] || $_POST['Openasi']){
					$referid = $this->Referbets_obj -> addAll($refer);
					if(!$referid){
					    $this -> error('参考赔率添加失败');
					}
				}
				if($this->Matchinfos_obj -> add($match) && $this->Bets_obj -> add($bets))
				{
					$this -> success('球赛添加成功!', U("expert/index"));
				}else{
				    $this -> error('球赛添加失败');
				}
		} else {
			$this->error("非法提交！");
		}
	}
	
    public function edit(){
		if(isset($_GET['id'])){
    	$id = $_GET['id'];
		$p=$_GET['p'];
		$c=$_GET['c'];
		$infos = $this->Matchinfos_obj
		->where("id = $id")
		->find();
		$teams = $this->Teams_obj
		->where("teamtype ='".substr($infos['matchtype'],0,5)."' ")
		->order("id desc")
		->select();
		$bets = $this->Bets_obj
		->where("matchinfoid =".$infos['matchconstid'])
		->order("id desc")
		->find();
		$datetime = date('Y-m-d H:i:s');
		$this->assign("datetime",$datetime);
		$this->assign("teams",$teams);
    	$this->assign('infos',$infos);
    	$this->assign('bets',$bets);
		$this->assign("c",$c);
		$this->assign("p",$p);
    	$this->display();
		}else{
			$this->error($this->Matchinfos_obj->getError());
		}
    }
	public function edit_post(){
		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
	$modified = date("Y-m-d H:i:s");
    	if(isset($_POST)){
			$score1 = $_POST['score1'];
			$score2 = $_POST['score2'];
			$matchid = $_POST['info_id'];
			$betsid = $_POST['bets_id'];
			$betsmid = $_POST['bets_mid'];
				if($_POST['start_time'] < $modified){
		    		if( $score1 !='' && $score2 !=''){
				        $match['matchdatetime'] = $_POST['start_time'];
				        $match['score1'] = $score1;
				        $match['score2'] = $score2;
					    $match['status'] = 160;
				        $match['userid'] = $userid;
						$mat = $this->Matchinfos_obj -> where("id = $matchid")->data($match)->save() ;
		    		}else{
						$this->error("请填写比分！");     
		    		}
					$bets['status'] = 160;
				    $bets['expiredate'] = $_POST['start_time'];
			        $bets['userid'] = $userid;
					$bet = $this->Bets_obj -> where("matchinfoid = $betsmid")->data($bets)->save();
					$ubet=array();
					$userbet = $this->Userbets_obj->where("betid = $betsid")->order("id desc")->select();
					for($i=0;$i<count($userbet);$i++)
					{
							$ubid = $userbet[$i]['id'];
						if($score1 > $score2){
							if($userbet[$i]['betoption'] == 'w')
							{
								$ubet['status'] = 160;
							}else{
								$ubet['status'] = 150;
							}
							$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
							$ubet=array();
						}
						if($score1 == $score2){
							if($userbet[$i]['betoption'] == 'd')
							{
								$ubet['status'] = 160;
							}else{
								$ubet['status'] = 150;
							}
							$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
							$ubet=array();
						}
						if($score1 < $score2){
							if( $userbet[$i]['betoption'] == 'l')
							{
								$ubet['status'] = 160;
							}else{
								$ubet['status'] = 150;
							}
							$this->Userbets_obj -> where("id = $ubid")->data($ubet)->save();
							$ubet=array();
						}
					}
					if($_POST['c']){
						$this -> success('修改成功!',U("expert/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
					}else{
						$this -> success('修改成功!',U("expert/index",array('p'=>$_POST['p'])));
					}
				}else{
		    		if( $score1 =='' && $score2 ==''){
				        $match['matchdatetime'] = $_POST['start_time'];
					    $match['status'] = 0;
				        $match['userid'] = $userid;
						$mat = $this->Matchinfos_obj -> where("id = $matchid")->data($match)->save() ;
		    		}else{
						$this->error("未开赛不可更新比分！");     
		    		}
					if($_POST['odds_w']==0 || $_POST['odds_w']=='')
						{$this -> error('主队胜赔率不能为空');}
					if($_POST['odds_d']==0 || $_POST['odds_d']=='')
						{$this -> error('两队平赔率不能为空');}
					if($_POST['odds_l']==0 || $_POST['odds_l']=='')
						{$this -> error('客队胜赔率不能为空');}
					if($_POST['odds_w']>=20 || $_POST['odds_d']>=20 || $_POST['odds_l']>=20 )
						{$this -> error('赔率范围需小于20');}
					if($_POST['odds_w'] >0 && $_POST['odds_d'] >0 && $_POST['odds_l'] >0 ){
				        $bets['odds_w'] = $_POST['odds_w'];
				        $bets['odds_d'] = $_POST['odds_d'];
				        $bets['odds_l'] = $_POST['odds_l'];
						$bets['status'] = 100;
				        $bets['expiredate'] = $_POST['start_time'];
				        $bets['userid'] = $userid;
						$bet = $this->Bets_obj -> where("matchinfoid = $betsmid")->data($bets)->save();
					}else{
						$this->error("请填写赔率信息！");     
		    		}
					if($_POST['c']){
						$this -> success('修改成功!',U("expert/index",array('p'=>$_POST['p'],'typeclass'=>$_POST['c'])));
					}else{
						$this -> success('修改成功!',U("expert/index",array('p'=>$_POST['p'])));
					}
			}
		} else {
			$this->error("非法提交！");                              
		}
    }
    public function delete() {
        if (isset($_GET['id'])) {
            $id = intval(I("get.id"));
				$match = $this->Matchinfos_obj->where("id=$id")->order("id desc")->select();
				$matchid = $match[0]['matchconstid'];
				if($this->Matchinfos_obj->where("id=$id")->delete()){
				$this->Referbets_obj->where("matchinfoid=$matchid")->delete();
				$this->Bets_obj->where("matchinfoid=$matchid")->delete();
			        $this -> success('删除成功!');
			    }else {
                	$this->error("比赛删除失败！");
            	}
        }else{
			$this->error("非法提交！");
        }

    }
	public function push(){
		$push = localhost_url."index.php?g=api&m=umeng&a=bulk&title=本周比赛结果已更新，请您登陆APP查看&describe=本周比赛结果已更新，请您登陆APP查看球币信息";
		$this->ryq_array($push);
		$this -> success('已推送通知');
    }

}
