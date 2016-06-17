<?php
/*********************************
	作者：wzxaini9@vip.qq.com
	时间：2015-12-08
	描述：文章获取
*********************************/
function sp_sql_posts_mdate($tag,$where=array()){
	if(!is_array($where)){
		$where=array();
	}
	
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '';
	$order = !empty($tag['order']) ? $tag['order'] : 'post_date';


     $Ymdate = date("Y-m");
	//根据参数生成查询条件
	$where['post_status'] = array('eq',1);
	$where['date(post_date)'] = array('gt',$Ymdate);

	if (isset($tag['cid'])) {
		$where['term_id'] = array('in',$tag['cid']);
	}
	
	if (isset($tag['ids'])) {
		$where['object_id'] = array('in',$tag['ids']);
	}
	$rs= M("T_posts");
	$posts = $rs->where($where)->where($where)->order($order)->limit($limit)->select();
	return $posts;
}
function sp_sql_posts_7day($tag,$where=array()){
	if(!is_array($where)){
		$where=array();
	}
	
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '';
	$order = !empty($tag['order']) ? $tag['order'] : 'post_date';


     $Ymddate = date("Y-m-d H:i:s",strtotime("-7 day"));
	//根据参数生成查询条件
	$where['post_status'] = array('eq',1);
	$where['date(post_date)'] = array('gt',$Ymddate);
	if (isset($tag['cid'])) {
		$where['term_id'] = array('in',$tag['cid']);
	}
	
	if (isset($tag['ids'])) {
		$where['object_id'] = array('in',$tag['ids']);
	}
	$rs= M("T_posts");
	$posts = $rs->where($where)->where($where)->order($order)->limit($limit)->select();
	return $posts;
}
