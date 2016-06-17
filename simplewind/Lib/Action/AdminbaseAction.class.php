<?php

/**
 * 后台Action
 */
//定义是后台
define('IN_ADMIN', true);

class AdminbaseAction extends AppframeAction {

	public function __construct() {
		$admintpl_path=C("SP_ADMIN_TMPL_PATH").C("SP_ADMIN_DEFAULT_THEME")."/";
		C("TMPL_ACTION_SUCCESS",$admintpl_path.C("SP_ADMIN_TMPL_ACTION_SUCCESS"));
					C("TMPL_ACTION_ERROR",$admintpl_path.C("SP_ADMIN_TMPL_ACTION_ERROR"));
					parent::__construct();
		$time=time();
		$this->assign("js_debug",APP_DEBUG?"?v=$time":"");

		if($_SESSION['userid']>0){
			$userid = $_SESSION['userid'];
		}else{
			$userid = get_current_admin_id();
		}
		$array_log = array(__SELF__,$userid,date('Y-m-d H:i:s'),get_client_ip());
		$filename="data/journal/journal".date('Y-m-d').".log";
		$file_hwnd=fopen($filename,"a+");
		fwrite($file_hwnd,serialize($array_log)."\r\n"); //输入序列化的数据
		fclose($file_hwnd);

	}

    function _initialize() {
       parent::_initialize();
    	if(isset($_SESSION['ADMIN_ID'])){
    		$users_obj= D("T_users");
    		$id=$_SESSION['ADMIN_ID'];
    		$user=$users_obj->where("id=$id")->find();
    		if(!$this->check_access($user['role_id'])){
    			$this->error("您没有访问权限！");
    			exit();
    		}
			$_SESSION['userid'] = $user['userid'];
			$this->assign('roleid',$user['role_id']);
    		$this->assign("admin",$user);
    	}else{
    		//$this->error("您还没有登录！",U("admin/public/login"));
    		if(IS_AJAX){
    			$this->error("您还没有登录！",U("admin/public/login"));
    		}else{
    			header("Location:".U("admin/public/login"));
    			exit();
    		}
    		
    	}
    }

    /**
     * 消息提示
     * @param type $message
     * @param type $jumpUrl
     * @param type $ajax 
     */
    public function success($message = '', $jumpUrl = '', $ajax = false) {
        parent::success($message, $jumpUrl, $ajax);
    }

    /**
     * 模板显示
     * @param type $templateFile 指定要调用的模板文件
     * @param type $charset 输出编码
     * @param type $contentType 输出类型
     * @param string $content 输出内容
     * 此方法作用在于实现后台模板直接存放在各自项目目录下。例如Admin项目的后台模板，直接存放在Admin/Tpl/目录下
     */
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        parent::display($this->parseTemplate($templateFile), $charset, $contentType);
    }
    
    
    /**
     * 自动定位模板文件
     * @access protected
     * @param string $template 模板文件规则
     * @return string
     */
    public function parseTemplate($template='') {
    	$tmpl_path=C("SP_ADMIN_TMPL_PATH");
    	$app_name=APP_NAME==basename(dirname($_SERVER['SCRIPT_FILENAME'])) && ''==__APP__?'':APP_NAME.'/';
    	// 获取当前主题名称
    	$theme      =    C('SP_ADMIN_DEFAULT_THEME');
    	if(is_file($template)) {
    		$group  =   defined('GROUP_NAME')?GROUP_NAME.'/':'';
    		
    		// 获取当前主题的模版路径
    		if(1==C('APP_GROUP_MODE')){ // 独立分组模式
    			define('THEME_PATH',   dirname(BASE_LIB_PATH).'/'.$group.basename($tmpl_path).'/'.$theme."/");
    			define('APP_TMPL_PATH',__ROOT__.'/'.$app_name.C('APP_GROUP_PATH').'/'.$group.basename($tmpl_path).'/'.$theme."/");
    		}else{
    			define('THEME_PATH',   $tmpl_path.$group.$theme."/");
    			define('APP_TMPL_PATH',__ROOT__.'/'.$app_name.basename($tmpl_path).'/'.$group.$theme."/");
    		}
    		return $template;
    	}
    	$depr       =   C('TMPL_FILE_DEPR');
    	$template   =   str_replace(':', $depr, $template);
    	
    	// 获取当前模版分组
    	$group      =   defined('GROUP_NAME')?GROUP_NAME.'/':'';
    	//$group      =   '';
    	if(defined('GROUP_NAME') && strpos($template,'@')){ // 跨分组调用模版文件
    		list($group,$template)  =   explode('@',$template);
    		$group  .=   '/';
    	}
    	// 获取当前主题的模版路径
    	if(1==C('APP_GROUP_MODE')){ // 独立分组模式
    		/* define('THEME_PATH',   $tmpl_path.$group.$theme."/");
    			define('APP_TMPL_PATH',__ROOT__.'/'.basename($tmpl_path).'/'.$group.$theme."/"); */
    		define('THEME_PATH',   $tmpl_path.$theme."/");
    		define('APP_TMPL_PATH',__ROOT__.'/'.basename($tmpl_path).'/'.$theme."/");
    	}else{
    		/* define('THEME_PATH',   $tmpl_path.$group.$theme."/");
    			define('APP_TMPL_PATH',__ROOT__.'/'.$app_name.basename($tmpl_path).'/'.$group.$theme."/"); */
    		define('THEME_PATH',   $tmpl_path.$theme."/");
    		define('APP_TMPL_PATH',__ROOT__.'/'.$app_name.basename($tmpl_path).'/'.$theme."/");
    	}
    	// 分析模板文件规则
    	if('' == $template) {
    		// 如果模板文件名为空 按照默认规则定位
    		$template = MODULE_NAME . $depr . ACTION_NAME;
    	}elseif(false === strpos($template, '/')){
    		$template = MODULE_NAME . $depr . $template;
    	}
    	$templateFile=THEME_PATH.$group.$template.C('TMPL_TEMPLATE_SUFFIX');
    	if(!is_file($templateFile))
    		throw_exception(L('_TEMPLATE_NOT_EXIST_').'['.$templateFile.']');
    	return $templateFile;
    }

    //扩展方法，当用户没有权限操作，用于记录日志的扩展方法
    public function _ErrorLog() {
        
    }

    /**
     * 初始化后台菜单
     */
    public function initMenu() {
        $Menu = F("T_menu");
        if (!$Menu) {
            $Menu=D("T_menu")->menu_cache();
        }
        return $Menu;
    }

    /**
     *  排序 排序字段为listorders数组 POST 排序字段为：listorder
     */
    protected function _listorders($model) {
        if (!is_object($model)) {
            return false;
        }
        $pk = $model->getPk(); //获取主键名称
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            $model->where(array($pk => $key))->save($data);
        }
        return true;
    }

    /**
     *  排序 排序字段为showorders数组 POST 排序字段为：showOrder
     */
    protected function _showorders($model) {
        if (!is_object($model)) {
            return false;
        }
        $pk = $model->getPk(); //获取主键名称
        $ids = $_POST['showOrder'];
        foreach ($ids as $key => $r) {
            $data['showOrder'] = $r;
            $model->where(array($pk => $key))->save($data);
        }
        return true;
    }
    /**
     *  排序 排序字段为showorders数组 POST 排序字段为：showorder
     */
    protected function _showorder($model) {
        if (!is_object($model)) {
            return false;
        }
        $pk = $model->getPk(); //获取主键名称
        $ids = $_POST['showorder'];
        foreach ($ids as $key => $r) {
            $data['showorder'] = $r;
            $model->where(array($pk => $key))->save($data);
        }
        return true;
    }
    protected function page($Total_Size = 1, $Page_Size = 0, $Current_Page = 1, $listRows = 6, $PageParam = '', $PageLink = '', $Static = FALSE) {
        import('Page');
        if ($Page_Size == 0) {
            $Page_Size = C("PAGE_LISTROWS");
        }
        if (empty($PageParam)) {
            $PageParam = C("VAR_PAGE");
        }
        $Page = new Page($Total_Size, $Page_Size, $Current_Page, $listRows, $PageParam, $PageLink, $Static);
        $Page->SetPager('Admin', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
        return $Page;
    }

    /**
     * 获取菜单导航
     * @param type $app
     * @param type $model
     * @param type $action
     */
    public static function getMenu() {

        $menuid = (int) $_GET['menuid'];
        $menuid = $menuid ? $menuid : cookie("menuid", "", array("prefix" => ""));
        //cookie("menuid",$menuid);

        $db = D("T_menu");
        $info = $db->cache(true, 60)->where(array("id" => $menuid))->getField("id,action,app,model,parentid,data,type,name");
        $find = $db->cache(true, 60)->where(array("parentid" => $menuid, "status" => 1))->getField("id,action,app,model,parentid,data,type,name");

        if ($find) {
            array_unshift($find, $info[$menuid]);
        } else {
            $find = $info;
        }
        foreach ($find as $k => $v) {
            $find[$k]['data'] = $find[$k]['data']."&menuid=$menuid" ;
        }

        return $find;
    }

    /**
     * 当前位置
     * @param $id 菜单id
     */
    final public static function current_pos($id) {
        $menudb = M("T_menu");
        $r = $menudb->where(array('id' => $id))->find();
        $str = '';
        if ($r['parentid']) {
            $str = self::current_pos($r['parentid']);
        }
        return $str . $r['name'] . ' > ';
    }
    
    private function check_access($roleid){
    	
    		//如果用户角色是1，则无需判断
    		if($roleid == 1){
    			return true;
    		}
    		$role_obj= D("T_role");
    		$role=$role_obj->field("status")->where("id=$roleid")->find();
    		if(!empty($role) && $role['status']==1){
    			$group=GROUP_NAME;
    			$model=MODULE_NAME;
    			$action=ACTION_NAME;
    			if(GROUP_NAME.MODULE_NAME.ACTION_NAME!="AdminIndexindex"){
    				$access_obj = M("T_access");
    				$count = $access_obj->where ( "role_id=$roleid and g='$group' and m='$model' and a='$action'")->count();
    				return $count;
    			}else{
    				return true;
    			}
    		}else{
    			return false;
    		}
    		
    		
    		
    }
	
	
	
	
	// ***************************************************************************
	// **** app request functions ************************************************
	public function _GetRequestPara_All($parakey_single, $parakey_multi = null) {
		$parameters = array ();
		
		if (isset ( $parakey_single )) {
			$array_key_s = array_keys ( $parakey_single );
			$array_val_s = array_values ( $parakey_single );
			for($s = 0; $s < count ( $array_key_s ); $s ++) {
				$key_s_from = $array_key_s [$s];
				$key_s_to = $array_val_s [$s];
				if (is_numeric ( $key_s_from ) == false) {
					$parameters [$key_s_to] = $this->_GetRequestPara ( $key_s_from );
				} else {
					$parameters [$key_s_to] = $this->_GetRequestPara ( $key_s_to );
				}
			}
		}
		if (isset ( $parakey_multi )) {
			$array_key_m = array_keys ( $parakey_multi );
			$array_val_m = array_values ( $parakey_multi );
			for($m = 0; $m < count ( $array_key_m ); $m ++) {
				$key_m_from = $array_key_m [$m];
				$key_m_to = $array_val_m [$m];
				if (is_numeric ( $key_m_from ) == false) {
					$parameters [$key_m_to] = $this->_GetRequestParaArray ( $key_m_from );
				} else {
					$parameters [$key_m_to] = $this->_GetRequestParaArray ( $key_m_to );
				}
			}
		}
		return $parameters;
	}
	
	/*
	 * get APP submitted parameters by a given key : $paraName
	 * if not existed, return default value : $defVal
	 */
	public function _GetRequestPara($paraName, $defVal = '') {
		$rtn = $defVal;
		if (isset ( $_GET [$paraName] )) {
			$rtn = $_GET [$paraName];
		} else if (isset ( $_POST [$paraName] )) {
			$rtn = $_POST [$paraName];
		}
		if ($rtn != $defVal && $paraName == 'password') {
			$rtn = md5 ( $rtn );
		}
		// pr($paraName.'--------'.$rtn);
		return $rtn;
	}
	/*
	 * get APP submitted parameters array, such as "fi0, fi1, fi2 ..... fi9"
	 * array length is $paraCnt, defaulet value : 10
	 */
	public function _GetRequestParaArray($paraName, $paraCnt = 50) {
		$rtnArray = array ();
		$cnt = 0;
		for($i = 0; $i <= $paraCnt; $i ++) {
			$key = $paraName . $i;
			if (isset ( $_POST [$key] )) {
				$rtnArray [$i] = $this->_GetRequestPara ( $key );
				$cnt = $cnt + 1;
			} else {
				///break;
			}
		}
		if ($cnt == 0) {
			// if no such parameter array, try return single parameter, such as "fi"
			$tmpval = $this->_GetRequestPara ( $paraName, '0' );
			if ($tmpval != '0') {
				$rtnArray [0] = $this->_GetRequestPara ( $paraName );
			}
		}
		return $rtnArray;
	}
	/*
	* $img_path 被压缩的图片的路径
	* $thumb_w 压缩的宽
	* $save_path 压缩后图片的存储路径
	* $is_del 是否删除原文件，默认删除
	*/
	public function thumb_img($img_path, $thumb_w, $save_path, $is_del = false){
		import('ThinkImage'); 
		$image = new  ThinkImage(); 
		$image->open($img_path);
		$width = $image->width(); // 返回图片的宽度
		if($width > $thumb_w){
			$width = $width/$thumb_w; //取得图片的长宽比
			$height = $image->height();
			$thumb_h = ceil($height/$width);
		}
		//如果文件路径不存在则创建
		$save_path_info = pathinfo($save_path);
		if(!is_dir($save_path_info['dirname'])){
			mkdir ($save_path_info['dirname'], 0777);
		}
		$type = '.'.$image->type($img_path);
		$image->thumb($thumb_w, $thumb_h)->save($save_path.$type);
		if($is_del) @unlink($img_path); //删除源文件
		return $type;
	}
}
?>
