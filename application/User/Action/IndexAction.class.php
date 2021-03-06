<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-11-25
	描述：前端登录
**********************************/
class IndexAction extends HomeBaseAction {
    //登录
	public function index() {
		$this->display(":login");

    }

    //登录验证
    function dologin(){    	
    	$users_model=M("T_users");
    	$rules = array(
    			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
    			array('username', 'require', '用户名不能为空！', 1 ),
    			array('password','require','密码不能为空！',1),
    	
    	);
    	if($users_model->validate($rules)->create()===false){
    		$this->error($users_model->getError());
    	}
    	
    	extract($_POST);
    	
    	if(strpos($username,"@")>0){//邮箱登陆
    		$where['user_email']=$username;
    	}else{
    		$where['user_login']=$username;
    	}
    	$users_model=M('T_users');
    	$result = $users_model->where($where)->find();
    	$ucenter_syn=C("UCENTER_ENABLED");
    		
    	$ucenter_old_user_login=false;
    	
    	$ucenter_login_ok=false;
    	if($ucenter_syn){
    		setcookie("thinkcmf_auth","");
    		include UC_CLIENT_ROOT."client.php";
    		list($uc_uid, $username, $password, $email)=uc_user_login($username, $password);
    	
    		if($uc_uid>0){
    			if(!$result){
    				$data=array(
    						'user_login' => $username,
    						'user_email' => $email,
    						'user_pass' => sp_password($password),
    						'last_login_ip' => get_client_ip(),
    						'create_time' => time(),
    						'last_login_time' => time(),
    						'user_status' => '1',
    				);
    				$id= $users_model->add($data);
    				$data['id']=$id;
    				$result=$data;
    			}
    				
    		}else{
    	
    			switch ($uc_uid){
    				case "-1"://用户不存在，或者被删除
    					if($result){//本应用已经有这个用户
    						if($result['user_pass'] == sp_password($password)){//本应用已经有这个用户,且密码正确，同步用户
    							$uc_uid2=uc_user_register($username, $password, $result['user_email']);
    							if($uc_uid2<0){
    								$uc_register_errors=array(
    										"-1"=>"用户名不合法",
    										"-2"=>"包含不允许注册的词语",
    										"-3"=>"用户名已经存在",
    										"-4"=>"Email格式有误",
    										"-5"=>"Email不允许注册",
    										"-6"=>"该Email已经被注册",
    								);
    								$this->error("同步用户失败--".$uc_register_errors[$uc_uid2]);
    							}
    							$uc_uid=$uc_uid2;
    						}else{
    							$this->error("密码错误！");
    						}
    					}
    						
    					break;
    				case -2://密码错
    					if($result){//本应用已经有这个用户
    						if($result['user_pass'] == sp_password($password)){//本应用已经有这个用户,且密码正确，同步用户
    							$uc_user_edit_status=uc_user_edit($username,"",$password,"",1);
    							if($uc_user_edit_status<=0){
    								$this->error("登陆错误！");
    							}
    							list($uc_uid2)=uc_get_user($username);
    							$uc_uid=$uc_uid2;
    							$ucenter_old_user_login=true;
    						}else{
    							$this->error("密码错误！");
    						}
    					}else{
    						$this->error("密码错误！");
    					}
    	
    					break;
    	
    			}
    		}
    		$ucenter_login_ok=true;
    		echo uc_user_synlogin($uc_uid);
    	}
    	//exit();
    	if($result != null)
    	{
    		if($result['user_pass'] == sp_password($password)|| $ucenter_login_ok)
    		{
    			$_SESSION["user"]=$result;
    			//写入此次登录信息
    			$data = array(
    					'last_login_time' => date("Y-m-d H:i:s"),
    					'last_login_ip' => get_client_ip(),
    			);
    			$users_model->where("id=".$result["id"])->save($data);
    			$redirect=empty($_SESSION['login_http_referer'])?__ROOT__."/":$_SESSION['login_http_referer'];
    			$_SESSION['login_http_referer']="";
    			$ucenter_old_user_login_msg="";
    				
    			if($ucenter_old_user_login){
    				//$ucenter_old_user_login_msg="老用户请在跳转后，再次登陆";
    			}
    				
    			$this->success("登录成功！", U("game/index"));
    		}else{
    			$this->error("密码错误！");
    		}
    	}else{
    		$this->error("用户名不存在！");
    	}
    }
    //退出
    public function logout(){
    	$ucenter_syn=C("UCENTER_ENABLED");
    	$login_success=false;
    	if($ucenter_syn){
    		include UC_CLIENT_ROOT."client.php";
    		echo uc_user_synlogout();
    	}
    	session("user",null);//只有前台用户退出
    	redirect(__ROOT__."/");
    }
}
