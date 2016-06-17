<?php
/*********************************
	作者1532008314@qq.com
	时间2016-04-08
**********************************/
class GuessingAction extends AdminbaseAction {
	protected $Userbets_obj,$Users_obj;
	function _initialize() {
		parent::_initialize();
		$this->Userbets_obj = D("Userbets");
		$this->Users_obj = D("Users");
		$this->Pbdetails = D("Pbdetails");
		$this->Pbinfos = D("Pbinfos");
		$this->Matchinfos = D("Matchinfos");
		$this->Teams = D("Teams");
        $this->Cashdetails = D("Cashdetails");
	}
	/*
	 *
	 *展示数据列表
	 */
	public function index(){
		$res = $this->Pbinfos->where("pb_status <> 'deleted'")->order("desc2,pb_type")->select();
		$time = date("Y-m-d H:i:s");
/*        if('2016-05-05 18:35:00'>$time){
            echo 1;
        }*/
		$this->assign("time",$time);
    	$this->assign("data",$res);
    	$this->display();
    }
    /*
     *获取列表
     */
    public function getlist(){
    	$pb_id = intval(I("get.pb_id"));
    	$model = new model();
    	
    	$sql = "select 
				t1.id as teamid1,t1.teamname as teamname1,mt.matchdesc,
				case when mt.score1 is null or mt.score2 is null then 'VS' else CONCAT(mt.score1,':',mt.score2) end as result,
				mt.matchdatetime,
				t2.id as teamid2,t2.teamname as teamname2,
				pb.id as pb_id,mt.id as mt_id,price_amount
				from pbinfos pb 
				inner join matchinfos mt on pb.match_list like CONCAT('%|',mt.id,'|%')
				inner join teams t1 on t1.constid=mt.teamid1
				inner join teams t2 on t2.constid=mt.teamid2
				where pb.pb_type='multi' and pb.id='".$pb_id."'
				order by mt.matchdatetime";
		$res = $model->query($sql);
		$this->assign("data",$res);
    	$this->display();
    }
    /*
     *展示添加单场竞猜页面
     */
    public function add_single_pbinfos(){
    	$rtn = $this->Matchinfos->where("matchtype like '20015%' and matchdatetime > date_add(now(), interval (+1) day)")->order("matchdatetime")->limit(10)->group("matchdesc")->select();
    	$this->assign("gamelist",$rtn);
    	$this->display();
    }
    /*
     **添加单场竞猜
     */
    public function add_post(){
    	$data = I("post.");
    	$mt_id = I("post.mt_id");
    	$rtn = $this->Matchinfos->field("teamid1,teamid2,matchdatetime")->where("id=".$mt_id)->select();
    	$teamid1 = $rtn[0]['teamid1'];
    	$teamid2 = $rtn[0]['teamid2'];
		$teamname1 = $this->Teams->where("constid='$teamid1'")->field("teamname as teamname1")->find();
		$teamname2 = $this->Teams->where("constid='$teamid2'")->field("teamname as teamname2")->find();
		$data['match_list'] = $mt_id;
		unset($data['player_type']);
		$data['match_time'] = $rtn[0]['matchdatetime'];
		$data['pb_type'] = 'single';
		$data['created'] = date('Y-m-d H:i:s');
		$data['pb_name'] = $teamname1['teamname1']." VS ".$teamname2['teamname2'];
		$data['userid'] = 1;
		$ret = $this->Pbinfos->add($data);
		if($ret){
			$this -> success('单场竞猜添加成功!', U("Guessing/index"));
		}else{
			$this -> error('单场竞猜添加失败!');
		}
		
    }
    /*
     *展示添加本轮竞猜页面
     */
    public function add_multi_pbinfos(){
    	$model = new model();
    	$sql = "select * from (
				select min(matchdatetime) first_date, max(matchdatetime) last_date,matchdesc
					from matchinfos 
					where matchtype like '20015%'
					-- and matchdatetime>'2016-01-01'
					group by matchdesc
				) t
				where t.first_date>date_add(now(), interval (+1) day)
					";
    	$rtn = $model->query($sql);
    	//$rtn = $this->Matchinfos->where("matchtype like '20015%' and matchdatetime > date_add(now(), interval (+1) day)")->order("matchdatetime")->select();
    	$this->assign("gamelist",$rtn);
    	$this->display();
    }

    public function add_multi(){
    	$match = I("post.match_list");;//'中超 15年 第12轮'
    	$rtn = $this->Matchinfos->field("id,matchdatetime")->where("matchdesc like '%".$match."%'")->select();
    	$data = I("post.");
    	$data['match_time'] = $rtn[0]['matchdatetime'];
    	$num = count($rtn);
    	$mtch='';
    	$str = "0";
    	for($i=0;$i<$num;$i++){
    		unset($rtn[$i]['matchdatetime']);
    		$mtch .= "|".implode('|',$rtn[$i]);
    	}
    	$str1 = "|";
    	$match_list = $str.$mtch.$str1;
    	
    	unset($data['match_list']);
    	unset($data['ball']);
    	$data['match_list'] = $match_list;
    	$data['desc3'] = $num."场";
    	$data['pb_name'] = $match;
    	$data['pb_type'] = 'multi';
    	$data['created'] = date("Y-m-d H:i:s");
    	$ret = $this->Pbinfos->add($data);
		if($ret){
			$this -> success('本轮竞猜添加成功!', U("Guessing/index"));
		}else{
			$this -> error('本轮竞猜添加失败!');
		}
    }
    /*
     *ajax展示比赛对阵单选按钮
     */
    public function add_ajax(){
    	$matchid = I("get.mid");
    	if($matchid==0){
    		return;
    	}
    	$result = $this->Matchinfos->field("matchdesc")->where("id=".$matchid)->find();
    	$matchdesc = $result['matchdesc'];
    	$rtn = $this->Matchinfos->field("id")->where("matchdesc like '%".$matchdesc."%' and matchdatetime > date_add(now(), interval (+1) day)")->select();
    	$num = count($rtn);
    	$mtch='';
    	$str="'0";
    	for($i=0;$i<$num;$i++){
    		$mtch .= "','".implode(',',$rtn[$i]);
    	}
    	$str1="'";
    	$newstr = $str.$mtch.$str1;
    	$model = new model();
    	
    	$sql = "select t1.id as teamid1,t1.teamname as teamname1,mt.matchdesc,				
				case when mt.score1 is null or mt.score2 is null then 'VS' else CONCAT(mt.score1,':',mt.score2) end as result,mt.matchdatetime,t2.id as teamid2,t2.teamname as teamname2,
				mt.id as mt_id				
				from matchinfos mt
				inner join teams t1 on t1.constid=mt.teamid1				
				inner join teams t2 on t2.constid=mt.teamid2		
				where mt.id in(".$newstr.")
				order by mt.matchdatetime";
		$res = $model->query($sql);
		$str = "<tr id='two'><td width='100px;'>选择比赛</td><td>";
		foreach($res as $val){
			$str .= "<input type='radio' name='mt_id' value='".$val['mt_id']."' />".$val['teamname1'].'VS'.$val['teamname2']."&nbsp;&nbsp;&nbsp;";
		}
		$str .="</td></tr>";
		echo $str;
    }
    /*
     *修改竞猜状态
     */
    public function upd_ajax(){
    	$status = I("get.status");
    	$res = $this->Pbinfos->field("pb_type")->where("id=".$mid)->find();
    	if($res['pb_type']=='multi'){
			$rtn = $this->Pbinfos->where("pb_status='enable' and pb_type='multi'")->select();
			$num = count($rtn);
			if($num>=1){
    			echo 2;
    		}
    	}else{
    		$data['pb_status']= "enable";
    		$res = $this->Pbinfos->where("id=".$status)->save($data);
    		if($res){
	    		echo 1;
	    	}else{
	    		echo 0;
	    	}
    	}	
    }
    /*
     *删除竞猜
     */
    public function delete_ajax(){
    	$mid = I("get.mid");
    	$data['pb_status']= "deleted";
    	$res = $this->Pbinfos->where("id=".$mid)->save($data);
    	if($res){
    		echo 1;
    	}else{
    		echo 0;
    	}
    }

    /*
     *更新比赛结果
     */
    public function update_result(){
    	$mid = I("get.mid");
    	$res = $this->Pbinfos->field("match_list")->where("id=".$mid)->find();
        $rts = $this->Pbinfos->field("edge_goals")->where("id=".$mid)->find();
    	$len = $res['match_list'];
        $length = strlen($len);
    	if($length<=6){
    		$result = $this->Matchinfos->field("score1,score2")->where("id=".$len)->find();
            if( $result['score1'] == '' || $result['score1'] =='' ){
               $rtn=0; 
            }else{
                //单场更新结果
                if ( $result['score1'] > $result['score2'] && $rts['edge_goals'] < $result['score1']+$result['score2'] ){
                    $data['match_result']='ww';
                    $data['pb_status'] = 'updated';
                    $rtn = $this->Pbinfos->where("id=".$mid)->save($data);

                }else if ( $result['score1'] > $result['score2'] && $rts['edge_goals'] > $result['score1']+$result['score2'] ){

                    $data['match_result']='w';
                    $data['pb_status'] = 'updated';
                    $rtn = $this->Pbinfos->where("id=".$mid)->save($data);

                }else if( $result['score1'] < $result['score2'] && $rts['edge_goals'] < $result['score1']+$result['score2'] ){

                    $data['match_result']='ll';
                    $data['pb_status'] = 'updated';
                    $rtn = $this->Pbinfos->where("id=".$mid)->save($data);

                }else if( $result['score1'] < $result['score2'] && $rts['edge_goals'] > $result['score1']+$result['score2'] ){

                    $data['match_result']='l';
                    $data['pb_status'] = 'updated';
                    $rtn = $this->Pbinfos->where("id=".$mid)->save($data);

                }else if ( $result['score1'] == $result['score2'] && $rts['edge_goals'] < $result['score1']+$result['score2'] ){

                    $data['match_result']='dd';
                    $data['pb_status'] = 'updated';
                    $rtn = $this->Pbinfos->where("id=".$mid)->save($data);

                }else if ( $result['score1'] == $result['score2'] && $rts['edge_goals'] > $result['score1']+$result['score2'] ){

                    $data['match_result']='d';
                    $data['pb_status'] = 'updated';
                    $rtn = $this->Pbinfos->where("id=".$mid)->save($data);

                }
                //修改Pbdetails表  修改bet_status
                $rts = $this->Pbinfos->field("match_result")->where("id = ".$mid)->find();
                $rst = $this->Pbdetails->where("pay_status = 160 and pb_id=".$mid)->select();
                for($i=0;$i<count($rst);$i++){
                    if($rst[$i]['bet_option']==$rts['match_result']){
                        $data['bet_status'] = 'right';
                        $this->Pbdetails->where("pay_status = 160 and pb_id=".$mid)->save($data);
                    }else{
                        $data['bet_status'] = 'wrong';
                        $this->Pbdetails->where("pay_status = 160 and pb_id=".$mid)->save($data);
                    }
                }
            }
            
            if($rtn){
                echo 1;
            }else{
                echo 0;
            }
    	}else{
            //本轮更新结果
            $arr = explode('|',$res['match_list']);
            array_pop($arr);
            array_shift($arr);
            $data['match_result'] = "0|";
            for($i=0;$i<count($arr);$i++){
                $result = $this->Matchinfos->field("score1,score2")->where("id=".$arr[$i])->find();
                if($result['score1'] =='' || $result['score2'] =='' ){
                    $rtt=0;
                }else{
                    if($result['score1']>$result['score2']){
                        $data['match_result'].='w|';
                    }else if($result['score1']<$result['score2']){
                        $data['match_result'].='l|';
                    }else{
                        $data['match_result'].='d|';
                    }
                }
            }
            if($rtt != 0){
                $data['pb_status'] = 'updated';
                $rtn = $this->Pbinfos->where("id=".$mid)->save($data);
                //修改Pbdetails表  修改bet_status
                $rts = $this->Pbinfos->field("match_result")->where("id = ".$mid)->find();
                $rst = $this->Pbdetails->where("pay_status = 160 and pb_id=".$mid)->select();
                for($i=0;$i<count($rst);$i++){
                    if($rst[$i]['bet_option']==$rts['match_result']){
                        $data['bet_status'] = 'right';
                        $this->Pbdetails->where("pay_status = 160 and pb_id=".$mid)->save($data);
                    }else{
                        $data['bet_status'] = 'wrong';
                        $this->Pbdetails->where("pay_status = 160 and pb_id=".$mid)->save($data);
                    }
                }
            }else{
                $rtn=0;
            }
            if($rtn){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

    public function update_guess(){
        $mid = I("get.mid");
        $model = new model();
        //奖金池底金
        $sql = "select pb.award_amount award_amount from pbinfos pb where pb_status = 'updated' and pb.id=".$mid;
        $award = $model->query($sql);

        //计算数据（球币投注次数）
        $sql1 = "select SUM(pbd.pay_amount) pay_amount_sum from pbdetails pbd where pbd.pay_status=160 and pay_type<>'ppay' and pbd.pb_id=".$mid;
        $pay_amount_sum = $model->query($sql1);

         //计算数据（现金支付汇总）
        $sql2 = "select count(pbd.id) ppay_cnt from pbdetails pbd where pbd.pay_status=160 and pay_type='ppay' and pbd.pb_id=".$mid;
        $ppay_cnt = $model->query($sql2);

        //计算猜对的人数
        $sql3 = "select count(pbd.id) bet_true_cnt from pbdetails pbd where pbd.pay_status=160 and pbd.bet_status='right' and pbd.pb_id=".$mid;
        $bet_true_cnt = $model->query($sql3);

        $num_cnt = $award[0]['award_amount']+$pay_amount_sum[0]['pay_amount_sum']+$ppay_cnt[0]['ppay_cnt'];
        $right_count = $bet_true_cnt[0]['bet_true_cnt'];
        $average = intval($num_cnt/$right_count);
        $data['rtn_amount'] = $average;
        $data['pay_status'] = 170;
        $rtn = $this->Pbdetails->where("pay_status = 160 and bet_status='right' and pb_id=".$mid)->save($data);
        if($rtn){
            $data1['pb_status'] = 'closed';
            $this->Pbinfos->where("id=".$mid)->save($data1);
            $ret = $this->Pbdetails->where("pay_status = 170 and bet_status='right' and pb_id=".$mid)->select();
            for($j=0;$j<count($ret);$j++){
                $cash_data['relateduserid'] = $ret[$j]['userid'];
                $cash_data['pay_number'] = $ret[$j]['pbd_number'];
                $cash_data['amount'] = $ret[$j]['rtn_amount'];
                $cash_data['pay_type'] = 10;
                $cash_data['crdr'] = cr;
                $cash_data['status'] = 100;
                $cash_data['relatedtype'] = 38;
                $cash_data['relatedid'] = ret[$j]['id'];
                $cash_data['cash_type'] = 'rpay';
                $cash_data['desc1'] = '任意付 欢乐猜 奖金'.$ret[$i]['rtn_amount'].'元';
                $cash_data['created'] = date("Y-m-d H:i:s");
                $cash_data['userid'] = 1;
                $this->Cashdetails->add($cash_data);
            }
            echo 1;
        }else{
            echo 0;
        }

    }
}