<?php



class LifeAction extends CommonAction{
	
	
	
	public function type(){
		$cate = D('Lifecate')->getChannelMeans();
	    $arr = array();
	    foreach($cate as $k => $v){
		   $arr[$k]['id'] = $k;
		   $arr[$k]['type_name'] = $v;
		   $kk = $k ;
		   $arr[$k]['img'] = __HOST__.'/static/default/wap/image/life/life_cate_'.$kk.'.png';	
	    } 
		$arr = array_values($arr);
        $json_str = json_encode($arr);
        exit($json_str); 
		
	}
	
	public function type2(){
		$channel_id = I('id','','trim');
		$arr = D('Lifecate')->where(array('channel_id'=>$channel_id))->select();
	    foreach($arr as $k => $v){
		   $arr[$k]['id'] = $v['cate_id'];
		   $arr[$k]['name'] = $v['cate_name'];
		   $arr[$k]['money'] = round($v['price']/100,2);
	    } 
        $json_str = json_encode($arr);
        exit($json_str); 
		
	}
	
	//获取分类详情
	public function TypeInfo(){
		$cate_id = I('type2_id','','trim');
		$res = D('Lifecate')->where(array('cate_id'=>$cate_id))->getField('price');
        $json_str = json_encode(round($res/100,2));
        exit($json_str); 
		
	}
	
	//分类信息列表
	public function Lists(){
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0);
		
		if($type_id = I('type_id','','trim')){
			$Lifecate = D('Lifecate')->where(array('channel_id'=>$type_id))->select();
			foreach($Lifecate as $val) {
				$cate_ids[$val['cate_id']] = $val['cate_id'];
			}
			$map['cate_id'] = array('in', $cate_ids);
        }
		
		if($cate_id = I('type2_id','','trim')){
			$map['cate_id'] = $cate_id;
        }
		
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
			$list[$k]['img'] = $this->getListPics($val['life_id']);
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
        $json_str = json_encode($data2);
        exit($json_str); 
		
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
			$photos[$k] = config_weixin_img($val['photo']);
		}
		$Life = D('Life')->find($life_id);
		if($Life['photo']){
			$photo = config_weixin_img($Life['photo']);
			array_unshift($photos,$photo);
		}
		$res = implode(",",$photos);
		return "".$res ."";
	}
	
	
	//分类信息列表
	public function detail(){
		$life_id = I('id','','trim');
		$detail = D('Life')->find($life_id);
		
        $Users = D('Users')->find($detail['user_id']);
		$detail['id'] = $detail['life_id'];
		$detail['user'] = $Users;
		$detail['user_img'] = config_weixin_img($Users['face']);			
		$detail['user_name'] = config_user_name($Users['nickname']);
		$detail['type_name'] = $this->getListChannel($detail['cate_id']);//分类
		$detail['type2_name'] = D('Lifecate')->where(array('cate_id'=>$detail['cate_id']))->getField('cate_name');//分类
		//$detail['hb_money'] = D('LifePacket')->where(array('life_id'=>$detail['life_id'],'closed'=>0,'status'=>1))->getField('packet_surplus_money');//红包剩余多少钱	
		$detail['label'] = D('LifeCateTag')->where(array('cate_id'=>$detail['cate_id']))->select();
		$detail['time'] = $detail['create_time'];
		$detail['time2'] = $detail['create_time'];
		$detail['sh_time'] = $detail['create_time'];
		$detail['img'] = config_weixin_img($detail['photo']);
		$detail['img1'] = $this->getListPics($detail['life_id']);
		$Lifedetails = D('Lifedetails')->find($detail['life_id']);
		$detail['details'] = cleanhtml($Lifedetails['details']);
			
		$data['tz']=$detail;
	    $data['dz']='';
	    $data['pl']='';
	    $data['label']='';
	    echo json_encode($data);
	}
	
	 //领取列表
  public function HongList(){
  		$life_id = I('id','','trim');
        $list  = D('LifePacket')->where(array('life_id'=>$detail['life_id'],'closed'=>0,'status'=>1))->select();
  		echo json_encode($list);
  }
	//红包拆分
	public function Hong(){
		 function hongbao($money,$number,$ratio = 1){
			$res = array(); //结果数组
			$min = 0.01;   //最小值
			$max = ($money / $number) * (1 + $ratio);//最大值
			/*--- 第一步：分配低保 ---*/
			for($i=0;$i<$number;$i++){
				$res[$i] = $min;
			}
			$money = $money - $min * $number;
			/*--- 第二步：随机分配 ---*/
			$randRatio = 100;
			$randMax = ($max - $min) * $randRatio;
			for($i=0;$i<$number;$i++){
				//随机分钱
				$randRes = mt_rand(0,$randMax);
				$randRes = $randRes / $randRatio;
				if($money >= $randRes){ //余额充足
					$res[$i]    += $randRes;
					$money      -= $randRes;
				}
				elseif($money > 0){     //余额不足
					$res[$i]    += $money;
					$money      -= $money;
				}
				else{                   //没有余额
					break;
				}
			}
			/*--- 第三步：平均分配上一步剩余 ---*/
			if($money > 0){
				$avg = $money / $number;
				for($i=0;$i<$number;$i++){
					$res[$i] += $avg;
				}
				$money = 0;
			}
			/*--- 第四步：打乱顺序 ---*/
			shuffle($res);
			/*--- 第五步：格式化金额(可选) ---*/
			foreach($res as $k=>$v){
				//两位小数，不四舍五入
				preg_match('/^\d+(\.\d{1,2})?/',$v,$match);
				$match[0]   = number_format($match[0],2);
				$res[$k]    = $match[0];
			}
			return $res;
		}
		print_r(hongbao(1,5));
	}
	

   //置顶
    public function Top(){
		$config = D('Setting')->fetchAll();
		$res[] = array(
			'id' => 1,
			'type' => 1,
			'money' => $config['pinche']['top'],
		);
      	echo json_encode($res);
    }
	
	//查看二级分类下的标签
    public function Label(){
        $type2_id = I('type2_id','','trim,htmlspecialchars');
        $res =  D('LifeCateTag')->order(array('orderby' => 'asc'))->where(array('cate_id' =>$type2_id))->select();
		foreach($res as $k => $val){
			if($val['tag_id']){
				$res[$k]['id'] = $val['tag_id'];
				$res[$k]['click_class'] = $val['tag_id'];
			}
		}
		if($res){
			echo json_encode($res);
		}else{
			echo 1;
		}
        
   }
   
   //红包
   public function sendRandBonus($total=0, $count=3){
     $input  = range(0.01, $total, 0.01);
	 if($count>1){
		  $rand_keys = (array) array_rand($input, $count-1);
		  $last    = 0;
		  foreach($rand_keys as $i=>$key){
			$current  = $input[$key]-$last;
			$items[]  = $current;
			$last    = $input[$key];
		  }
		}
		$items[]    = $total-array_sum($items);
	  return $items;
	}
   
     //分类信息发帖
	public function Posting(){
			$data['cate_id'] = I('type2_id','','trim,htmlspecialchars');
			if(empty($data['cate_id'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'分类必须选择'));
			}
			if(!$res = D('Lifecate')->find($data['cate_id'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'分类不存在'));
			}
			$data['city_id'] = I('city_id','','trim');
			if(empty($data['city_id'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'城市不能为空'));
			}
			
			$data['contact'] = I('user_name','','trim,htmlspecialchars');//名字
			if(empty($data['contact'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'联系人不能为空'));
			}
			$data['mobile'] = I('user_tel','','trim,htmlspecialchars');
			if(empty($data['mobile'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'电话不能为空'));
				$this->tuMsg('电话不能为空');
			}
			if(!isMobile($data['mobile']) && !isPhone($data['mobile'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'电话格式不正确'));
			}
			$data['addr'] = I('address','','trim,htmlspecialchars');//地址
			
			$top_type = I('top_type','','trim,htmlspecialchars');//置顶
			if($top_type){
				$data['top_date'] = date('Y-m-d', strtotime(TODAY) + $top_type * 86400);
			}else{
				$data['top_date'] = TODAY;
			}
			$data['urgent_date'] = TODAY;
			$data['audit'] = $this->_CONFIG['site']['fabu_life_audit'];
			$data['create_time'] = NOW_TIME;
			$data['last_time'] = NOW_TIME + 86400 * 30;
			$data['create_ip'] = get_client_ip();
            $data['user_id'] = I('user_id','','trim');//会员
			if($Shop = D('Shop')->where(array('user_id' =>$data['user_id'],'closed' => 0,'audit' => 1))->find()){
				$data['is_shop'] = 1;
			}
            $details = I('details','','trim,htmlspecialchars');//内容
            if($words = D('Sensitive')->checkWords($details)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商家介绍含有敏感词：' . $words));
            }
			//标签开始
			$sz = I('sz','','trim,htmlspecialchars');//获取josn数据
			$a = json_decode(html_entity_decode($sz));//转义
      		$sz2 = json_decode(json_encode($a),true);//转化数组
			if($sz2){
				foreach($sz2 as $val) {
					$label_ids[$val['label_id']] = $val['label_id'];
				}
				$tag = implode(',', $label_ids);
				$data['tag'] = $tag;
			}
			$data['title'] = tu_msubstr($details,0,30,false);//标题
            if($life_id = D('Life')->add($data)){
				$img = I('img','','trim,htmlspecialchars');//图片
				$imgs = explode(',', $img);
				if($imgs){
					D('Life')->where(array('life_id'=>$life_id))->save(array('photo'=>$imgs['0']));
				}
				$photos = array_splice($imgs,1,9); 
				if($photos){
					D('Lifephoto')->upload($life_id, $photos);//添加更多详情图
				}
                if($data['details']){
                    D('Lifedetails')->updateDetails($life_id,$details);//添加详情
                }
				$money = I('money','','trim,htmlspecialchars');//发帖付款金额这里暂时不处理
				$hb_money = I('hb_money','','trim,htmlspecialchars');//红包金额
				$hb_num = I('hb_num','','trim,htmlspecialchars');//红包个数
				$hb_type = I('hb_type','','trim,htmlspecialchars');//红包类型1.普通 2.口令 
				$hb_keyword = I('hb_keyword','','trim,htmlspecialchars');//红包口令
				$hb_random = I('hb_random','','trim,htmlspecialchars');//随机1.是 2否
				$this->createPacket($life_id,$hb_money,$hb_num,$hb_type,$hb_keyword,$hb_random);//创建红包逻辑
				echo $life_id;
            }
			$this->ajaxReturn(array('code'=>'0','msg'=>'发布信息失败'));

	}
	
	//添加红包
	public function createPacket($life_id,$hb_money,$hb_num,$hb_type,$hb_keyword,$hb_random){
		if($hb_random == 1){
			$hong=json_encode($this->sendRandBonus($hb_money,$hb_num));//获取随机值
    		$data['hong']= $hong;
		}
		$data['life_id'] = $life_id;		
		$Life = D('Life')->find($data['life_id']);
		$LifePacket = D('LifePacket')->where(array('life_id'=>$data['life_id'],'closed'=>0,'user_id'=>$Life['user_id']))->find();
		if($LifePacket){
			return false;//如果已经创建红包
		}
		$data['user_id'] = $Life['user_id'];
		$data['packet_num'] = $hb_num;//数量
		$data['packet_money'] = (int)($hb_money*100);//红包金额
		$data['packet_total_money'] = $data['packet_money']*$data['packet_num'];
		$data['packet_surplus_money'] = $data['packet_total_money'];
		
		$data['packet_is_command'] = $hb_type;//类型
		if($data['packet_is_command'] == 1){
			$data['packet_command'] = htmlspecialchars($hb_keyword);
			if(empty($data['packet_command'])){
				return false;//口令为空
			}
			if(strlen($data['packet_command']) >= 20) {
				return false;//口令太长
			}
			if($words = D('Sensitive')->checkWords($data['packet_command'])){
				return false;//口令有违禁词
			}
		}
		$data['status'] = 0;
		$data['create_time'] = time();
		$data['create_ip'] = get_client_ip();
			
		if($packet_id = D('LifePacket')->add($data)){
			//自动置顶功能
			if($data['packet_total_money'] >= ($this->CONFIG['life']['packet_top_date']*100)){
				$day = 3;
				$top_date = date('Y-m-d', NOW_TIME + $day * 86400);
				if($Life['top_date'] > TODAY){
					$top_date = date('Y-m-d', strtotime($Life['top_date']) + $day * 86400);
				}
				D('Life')->save(array('top_date' =>$top_date,'life_id' =>$data['life_id']));
			}
			D('Weixintmpl')->pushRedPacketWeixinTmpl($data['life_id']);//推送微信模板消息红包
			return true;      
		}
		return false;
	}	
}