<?php
class LifeModel extends CommonModel{
    protected $pk = 'life_id';
    protected $tableName = 'life';
    protected $_validate = array(array(), array(), array());
	
	
    public function randTop(){
        $lifes = $this->where(array('audit' => 1, 'top_date' => array('EGT', TODAY)))->order(array('last_time' => 'desc'))->limit(0, 45)->select();
        shuffle($lifes);
        if(empty($lifes)){
            return array();
        }
        $num = count($lifes) > 9 ? 9 : count($lifes);
        $keys = array_rand($lifes, $num);
        $return = array();
        foreach($lifes as $k => $val){
            if(in_array($k, $keys)){
                $return[] = $val;
            }
        }
        return $return;
    }
	
	//在线付款分类信息订单状态回调
	public function updateLife($life_id,$price){
		D('Life')->where(array('life_id'=>$life_id))->find(array('status'=>1,'price'=>$price));
		return true;	
	}
	 
	public function get_life_list($city_id,$channel_id){
		$Lifecate = D('Lifecate')->fetchAll();
		$cates_ids = array();
            foreach ($Lifecate as $val) {
                if ($val['channel_id'] == $channel_id) {
                    $cates_ids[] = $val['cate_id'];
                }
            }
            if (!empty($cates_ids)) {
                $map['cate_id'] = array('IN', $cates_ids);
				
            }
			$order = array('top_date' => 'desc', 'last_time' => 'desc');
			$list = D('Life')->where($map)->order($order)->limit(0, 5)->select();
			foreach ($list as $k => $val) {
				$val['cate_name'] = $Lifecate[$val['cate_id']]['cate_name'];
				$list[$k] = $val;
			}
			return $list ;
	}
	
	//余额购买分类信息
	public function buyLifeDetails($life_id,$user_id){
		if(D('LifeBuy')->where(array('life_id'=>$life_id,'user_id'=>$user_id))->find()){
			$this->error = '请不要重复购买';
			return false;
		}
        if($detail = D('Life')->find($life_id)){
			if($detail['audit'] == 0){
				$this->error = '信息没有审核';
				return false;
			}elseif($detail['closed'] == 1){
				$this->error = '信息已被删除';
				return false;				
			}elseif($detail['user_id'] == $user_id){
				$this->error = '您不能购买自己的信息';
				return false;				
			}else{
				$user = D('Users')->find($user_id);
				if($user['money'] < $detail['money']){
					$this->error = '您余额不足';
					return false;
				}else{
					$data['life_id'] = $life_id;
					$data['city_id'] = $detail['city_id'];
					$data['user_id'] = $user_id;
					$data['money'] = $detail['money'];
					$data['create_time'] = NOW_TIME;
					$data['create_ip'] = get_client_ip();
					if(D('LifeBuy')->add($data)){
						D('Users')->addMoney($user_id, -$detail['money'] ,'购买分类信息：【'.$detail['title'].'】花费金额');
						D('Users')->addMoney($detail['user_id'], $detail['money'] ,$user['nickname'].'购买您分类信息：【'.$detail['title'].'】结算金额');
						D('Life')->updateCount($life_id, 'buy_num');//增加购买量
						return true;
					}else{
						$this->error = '更新数据库失败';
						return false;
					}
				}
			}
		}else{
			$this->error = '信息不存在';
			return false;
		}	
	}
	
	//积分订阅
	public function subscribeLife($life_id,$user_id){
		$detail = D('Life')->find($life_id);
		
		if(D('LifeSubscribe')->where(array('cate_id'=>$detail['cate_id'],'user_id'=>$user_id))->find()){
			$this->error = '请不要重复订阅';
			return false;
		}
		if($detail['cate_id']){
			$data = array();
			$data['city_id'] = $detail['city_id'];
			$data['area_id'] = $detail['area_id'];
			$data['business_id'] = $detail['business_id'];
			$data['user_id'] = $user_id;
			$data['cate_id'] = $detail['cate_id'];
			$data['create_time'] = NOW_TIME;
			$data['create_ip'] = get_client_ip();
			if(D('LifeSubscribe')->add($data)){
				return true;
			}else{
				$this->error = '更新数据库失败';
				return false;
			}
		}else{
			$this->error = '您订阅的分类不存在';
			return false;
		}
	}
	
	//返回当前分类信息是否还有红包
	public function getLifePacket($life_id){
		if(!$detail = D('Life')->find($life_id)){
			return 0;
		}
		$LifePacket = D('LifePacket')->where(array('life_id'=>$life_id,'closed'=>0,'status'=>1))->find();
		if(!$LifePacket){
			return 0;
		}
		if($LifePacket['packet_sold_num'] == $LifePacket['packet_num']){
			return 0;
		}
		return true;
	}		
	
	
	//获取信息详细页面轮播数据
	public function getScroll(){
		$config = D('Setting')->fetchAll();
		$limit = isset($config['life']['limit']) ? (int)$config['life']['limit'] : 6;
		$order = isset($config['life']['order']) ? (int)$config['life']['order'] : 1;
		switch ($order) {
            case '1':
                $orderby = array('create_time' =>'desc','log_id' =>'desc');
                break;
            case '2':
                $orderby = array('money' =>'desc');
                break;
            case '3':
                $orderby = array('log_id' =>'desc');
                break;
        }
		$list = D('LifePacketLogs')->order($orderby)->limit(0,$limit)->select();
		foreach($list as $k => $v){
            if($user = D('Users')->where(array('user_id'=>$v['user_id']))->find()){
                $list[$k]['user'] = $user;
            }
        }
        return $list;
    }
	
	//领取红包
	public function surplusPacket($life_id,$type_id,$packet_command = '',$user_id){
		if(!$detail = D('Life')->find($life_id)){
			$this->error = '信息不存在';
			return false;
		}
		if($detail['closed'] == 1){
			$this->error = '信息已删除';
			return false;
		}
		if($detail['audit'] == 0){
			$this->error = '信息未审核';
			return false;
		}
		if(!$LifePacket = D('LifePacket')->where(array('life_id'=>$life_id,'closed'=>0,'status'=>1))->find()){
			$this->error = '该信息作者没发布红包，或者红包状态不正常';
			return false;
		}
		if($LifePacket['packet_sold_num'] == $LifePacket['packet_num']){
			$this->error = '该信息红包已经领取完毕';
			return false;
		}
		if($LifePacket['user_id'] == $user_id){
			$this->error = '您自己发布的红包自己不能领取';
			return false;
		}
		
		
		if($type_id == 2 || $LifePacket['packet_is_command'] == 1){
			if(empty($packet_command)){
				$this->error = '必须输入口令';
				return false;
			}
			if($LifePacket['packet_command'] != $packet_command){
				$this->error = '口令不正确';
				return false;
			}
		}
		
		
		if($LifePacketLogs = D('LifePacketLogs')->where(array('life_id'=>$life_id,'user_id'=>$user_id))->find()){
			$this->error = '您已经领取过了';
			return false;
		}
		
		if($type_id == 1){
			$intro ='领取分类信息ID'.$life_id.'红包';
		}elseif($type_id == 2){
			$intro ='领取分类信息ID'.$life_id.'红包，口令【'.$packet_command.'】';
		}elseif($type_id == 3){
			$intro ='领取分类信息ID'.$life_id.'红包，分享朋友圈领取';
		}
		
		$data = array();
		$data['life_id'] = (int)$life_id;
		$data['type_id'] = (int)$type_id;
		$data['user_id'] = $user_id;
		$data['money'] = $LifePacket['packet_money'];
		$data['intro'] = $intro;
		$data['create_time'] = time();
		$data['create_ip'] = get_client_ip();
		
		if($log_id = D('LifePacketLogs')->add($data)){
			if(D('Users')->addMoney($user_id,$LifePacket['packet_money'],$intro)){
				D('LifePacket')->where(array('life_id'=>$life_id))->setInc('packet_sold_num',1);
				D('LifePacket')->where(array('life_id'=>$life_id))->setDec('packet_surplus_money',$LifePacket['packet_money']);
				return true;       
			}else{
				$this->error = '领取失败';
				return false;
			}
		}
	}	
	
	
	//分享领取积分封装
	public function getShareIntegral($life_id,$type_id,$user_id){
		if(!$detail = D('Life')->find($life_id)){
			$this->error = '信息不存在';
			return false;
		}
		$bg_time = strtotime(TODAY);
		$res = D('LifeShareLogs')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'life_id'=>$life_id,'user_id'=>$user_id))->count();
		if($res >= 3){
			$this->error = '今日分享不能在领取积分啦';
			return false;
		}
		
		$config = D('Setting')->fetchAll();
		
		if((int)$config['integral']['life'] >= 1){
			$intro ='微信分享分类信息ID'.$life_id.'领取积分';
			$data = array();
			$data['life_id'] = (int)$life_id;
			$data['type_id'] = (int)$type_id;
			$data['user_id'] = $user_id;
			$data['integral'] = (int)$config['integral']['life'];
			$data['intro'] = $intro;
			$data['create_time'] = time();
			$data['create_ip'] = get_client_ip();
			
			if($log_id = D('LifeShareLogs')->add($data)){
				if(D('Users')->addIntegral($user_id,(int)$config['integral']['life'],$intro)){
					return true;       
				}else{
					$this->error = '领取失败';
					return false;
				}
			}
		}else{
			$this->error = '后台没有配置微信分享信息领取积分';
			return false;
		}
		return true;
	}			
}