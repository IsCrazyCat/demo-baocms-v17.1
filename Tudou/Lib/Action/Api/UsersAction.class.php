<?php

class UsersAction extends CommonAction{
	
	
	//我的分类信息
	public function MyPost(){
		import('ORG.Util.Page');
		$user_id = I('user_id','','trim');
        $map = array('audit' => 1, 'closed' => 0,'user_id'=>$user_id);
		$count = D('Life')->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = D('Life')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$Users = D('Users')->find($val['user_id']);
			$list[$k]['id'] = $val['life_id'];
		    $list[$k]['user'] = $Users;
		    $list[$k]['user_img'] = config_weixin_img($Users['face']);			
		    $list[$k]['user_name'] = config_user_name($Users['nickname']);
			$list[$k]['type_name'] = $this->getListChannel($val['cate_id']);//分类
			$list[$k]['type2_name'] = D('Lifecate')->where(array('cate_id'=>$val['cate_id']))->getField('cate_name');//分类
			$list[$k]['hb_money'] = D('LifePacket')->where(array('life_id'=>$val['life_id'],'closed'=>0,'status'=>1))->getField('packet_surplus_money');//红包剩余多少钱	
			$list[$k]['label'] = D('LifeCateTag')->where(array('cate_id'=>$val['cate_id']))->select();
			$list[$k]['time'] = $val['create_time'];
			$list[$k]['sh_time'] = $val['create_time'];
			$list[$k]['img'] = config_weixin_img($val['photo']);
			$list[$k]['img1'] = $this->getListPics($val['life_id']);
			$Lifedetails = D('Lifedetails')->find($val['life_id']);
			$list[$k]['details'] = cleanhtml($Lifedetails['details']);
		}
		foreach($list as $k => $val){
			$data2[]=array(
			  'tz'=>$list[$k],
			  'label'=>array(),
			 );
		}
        $json_str = json_encode($list);
        exit($json_str); 
		
	}
	
	
	public function DelPost(){
		$life_id = I('id','','trim');
		$res = D('Life')->where('life_id =' . $life_id)->delete();
		exit($res); 
	}
	//获取频道
	public function getListChannel($cate_id){
		$Lifecate = D('Lifecate')->where(array('cate_id'=>$cate_id))->find();
		$this->lifechannel = D('Lifecate')->getChannelMeans();
		return $this->lifechannel[$Lifecate['channel_id']];
	}
	
	
	//获取列表图片开始
	public function getListPics($life_id){
		$list = D('Lifephoto')->getPics($life_id);
		foreach($list as $k => $val){
			$photos[$k] = $val['photo'];
		}
		$Life = D('Life')->find($article_id);
		if($Life['photo']){
			$photo = config_weixin_img($Life['photo']);
			array_unshift($photos,$photo);
		}
		$details = D('LifeDetails')->find($life_id);
		$pic = getImgs($details['details']);
		$res = implode(",",$photos);
		return "".$res ."";
	}
	
	//会员提现发送模板消息
	public function txmessage(){
		$form_id = I('form_id','','trim');
		$openid = I('openid','','trim');
		$cash_id = I('cash_id','','trim');
	}
	
	
	//会员提现
	public function TiXian(){
		
		$username = I('username','','trim,htmlspecialchars');
		$method = I('method','','trim,htmlspecialchars');//应该是状态
		$shop_id = I('store_id','','trim');
		
		$user_id = I('user_id','','trim');
		$type = I('type','','trim');
		$connect = D('Connect')->where(array('uid' =>$user_id,'type'=>'weixin'))->find();
		
		$money = I('tx_cost','','trim') * 100;//提现金额
		$data['re_user_name'] = I('name','','trim,htmlspecialchars');
		$data['user_id'] = $user_id;
		
		$sj_cost =  I('sj_cost','','trim,htmlspecialchars') * 100;//实际提现
	    $commission = $money - $sj_cost;
		
		
        $arr = array();
        $arr['user_id'] = $user_id;
		$arr['shop_id'] = $shop_id;
        $arr['money'] = $sj_cost;
		$arr['commission'] = $commission;
        $arr['type'] = 'user';
        $arr['addtime'] = NOW_TIME;
        $arr['account'] = $username;//微信账户
		$arr['re_user_name'] = $data['re_user_name'];
		$arr['code'] = 'weixin';
			
		if($commission){
			$intro = '您申请提现，扣款'.round($money/100,2).'元，其中手续费：'.round($commission/100,2).'元';
		}else{
			$intro = '您申请提现，扣款'.round($money/100,2).'元';
		}
			
		if($cash_id = D('Userscash')->add($arr)){
			if(D('Users')->addMoney($user_id, -$money,$intro)){
				D('Usersex')->save($data);
				D('Weixintmpl')->weixin_cash_user($this->uid,1);//申请提现：1会员申请，2商家同意，3商家拒绝
				exit($cash_id); //成功输出ID
			}else{
				exit(0);
			}
		}
		exit(0);
	}
	
	//我的提现
	public function MyTiXian(){
		import('ORG.Util.Page');
		$user_id = I('user_id','','trim');
        $map = array('user_id'=>$user_id);
		$count = D('Userscash')->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = D('Userscash')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['id'] = $val['cash_id'];
			$list[$k]['time'] = $val['addtime'];
			$list[$k]['tx_cost'] = round($val['money']/100,2);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	//我的抢红包记录
	public function Hbmx(){
		$obj = M('LifePacketLogs');
		import('ORG.Util.Page');
		$user_id = I('user_id','','trim');
        $map = array('user_id'=>$user_id);
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['state'] = $val['2'];
		    $list[$k]['time'] = $val['create_time'];
			$list[$k]['money'] = round($val['money']/100,2);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	
	
	//收藏的商家
	public function MyStoreCollection(){
		$user_id = I('user_id','','trim');
		$obj = D('Shopfavorites');
		import('ORG.Util.Page');
        $map = array('user_id' => $user_id,'closed' => 0);
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$Shop = D('Shop')->find($val['shop_id']);
			$list[$k]['id'] = $val['favorites_id'];
			$list[$k]['shop_name'] = $Shop['shop_name'];
			$list[$k]['addr'] = $Shop['addr'];
			$list[$k]['star'] = $Shop['star'];
			$list[$k]['views'] = $Shop['view'];
			$list[$k]['coordinates'] = $this->getBaiduChangeMap($Shop['lat'],$Shop['lng']);
			$list[$k]['details'] = D('Shopdetails')->find($Shop['shop_id']);
			$list[$k]['logo'] = config_weixin_img($Shop['photo']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	//百度地图转换为谷歌地图
	public function getBaiduChangeMap($lat,$lng){
		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $lng - 0.0065;
        $y = $lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $lng = $z * cos($theta);
        $lat = $z * sin($theta);
		return $lat.','.$lng;
	}
	
	
}