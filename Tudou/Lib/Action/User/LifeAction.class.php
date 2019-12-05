<?php
class LifeAction extends CommonAction{
    protected $lifecate = array();
  
    public function _initialize(){
		
        parent::_initialize();
        if((int) $this->_CONFIG['operation']['life'] == 0){
            $this->error('此功能已关闭');
            die;
        }
        $this->lifecate = D('Lifecate')->fetchAll();
        $this->lifechannel = D('Lifecate')->getChannelMeans();
        $this->assign('lifecate', $this->lifecate);
        $this->assign('channel', $this->lifechannel);
    }
	
	
	public function index(){
        $status = (int) $this->_param('status');
		$this->assign('status', $status);
		$this->display(); 
    }
	
    public function loaddata(){
        $Life = D('Life');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if($keyword){
            $map['qq|mobile|contact|title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		$status = I('status','', 'trim,intval');
		if($status == 999){

		}elseif($status == 0 || $status == ''){
			$map['status'] = 0;
			$map['price'] = array('gt',0);
		}else{
			$map['status'] = $status;
			$map['price'] = array('gt',0);
		}
		$this->assign('status', $status);
	
		
        $count = $Life->where($map)->count();
        $Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if($Page->totalPages < $p){
            die('0');
		}
        $list = $Life->where($map)->order(array('last_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
            if($City = D('City')->find($v['city_id'])){
                $list[$k]['city'] = $City;
            }
			if($Area = D('Area')->find($v['area_id'])){
                $list[$k]['area'] = $Area;
            }
			if($Business = D('Business')->find($v['business_id'])){
                $list[$k]['business'] = $Business;
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('cates', D('Lifecate')->fetchAll());
        $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
        $this->display();
    }
	
	
    public function urgent(){
        if(!($life_id = (int) $this->_get('life_id'))){
            $this->tuMsg('参数错误');
        }
        if(!($detail = D('Life')->find($life_id))){
            $this->tuMsg('参数错误');
        }
        if($detail['user_id'] != $this->uid){
            $this->tuMsg('参数错误');
        }
        $day = (int) $this->_get('day');
        $mday = 0;
        switch ($day) {
            case 7:
                $mday = $day = 7;
                break;
            default:
                $day = 30;
                $mday = 27;
                break;
        }
		
        $money = $mday * $this->_CONFIG['life']['urgent'] * 100;
        if($this->member['money'] < $money){
            $this->tuMsg('余额不足', U('user/money/index'));
        }
        $urgent_date = date('Y-m-d', NOW_TIME + $day * 86400);
        if($detail['urgent_date'] > TODAY){
            $urgent_date = date('Y-m-d', strtotime($detail['urgent_date']) + $day * 86400);
        }
        if(D('Users')->addMoney($this->uid, -$money, '加急信息' . $day . '天')) {
            D('Life')->save(array('urgent_date' => $urgent_date, 'life_id' => $life_id));
            $this->tuMsg('您的信息已经加急', U('life/index'));
        }
        $this->tuMsg('操作失败');
    }
	
	
	
    public function top(){
        if(!($life_id = (int) $this->_get('life_id'))){
            $this->tuMsg('参数错误');
        }
        if(!($detail = D('Life')->find($life_id))){
            $this->tuMsg('参数错误');
        }
        if($detail['user_id'] != $this->uid){
            $this->tuMsg('参数错误');
        }
        $day = (int) $this->_get('day');
        $mday = 0;
        switch ($day){
            case 7:
                $mday = $day = 7;
                break;
            default:
                $day = 30;
                $mday = 27;
                break;
        }
		
        $money = $mday * $this->_CONFIG['life']['top'] * 100;
        if($this->member['money'] < $money) {
            $this->tuMsg('余额不足', U('user/money/index'));
        }
        $top_date = date('Y-m-d', NOW_TIME + $day * 86400);
        if($detail['top_date'] > TODAY) {
            $top_date = date('Y-m-d', strtotime($detail['top_date']) + $day * 86400);
        }
        if(D('Users')->addMoney($this->uid, -$money, '置顶信息' . $day . '天')){
            D('Life')->save(array('top_date' => $top_date, 'life_id' => $life_id));
            $this->tuMsg('您的信息已经在同频道置顶了', U('life/index'));
        }
        $this->tuMsg('操作失败');
    }
	
	
    public function flush(){
        if(!($life_id = (int) $this->_get('life_id'))){
            $this->tuMsg('参数错误');
        }
        if(!($detail = D('Life')->find($life_id))){
            $this->tuMsg('参数错误');
        }
        if($detail['user_id'] != $this->uid){
            $this->tuMsg('参数错误');
        }
        if(NOW_TIME - $detail['last_time'] < 86400){
            $this->tuMsg('您已经刷新过了');
        }
        if(NOW_TIME - $detail['last_time'] > 86400 * 30){
            $this->tuMsg('该信息已经超过30天了，不能在进行免费刷新');
        }
        $data = array('life_id' => $life_id, 'last_time' => NOW_TIME);
        if($detail['top_date'] < TODAY) {
            $data['top_date'] = TODAY;
        }
        if(D('Life')->save($data)){
            $this->tuMsg('刷新成功', U('life/index'));
        }
        $this->tuMsg('操作失败');
    }
	
	
	//上架下架
    public function closed($life_id= 0){
        $life_id = I('life_id', '', 'intval,trim');
		if(!$life_id){
			$this->ajaxReturn(array('code'=>'0','msg'=>'ID不存在'));
		}
		if(!$detail = D('Life')->find($life_id)){
			$this->ajaxReturn(array('code'=>'0','msg'=>'信息不存在'));
		}
		$data = array('closed' => 0, 'life_id' => $life_id);
        if ($detail['closed'] == 0) {
            $data['closed'] = 1;
        }
		if(D('Life')->save($data)){
			$this->ajaxReturn(array('code'=>'1','msg'=>'操作成功','url'=>U('life/index',array('life_id'=>$detail['life_id']))));     
		}else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'操作失败'));
		}
    }
	
    //删除信息
    public function delete(){
        $life_id = I('life_id', '', 'intval,trim');
        if(!$life_id){
			$this->ajaxReturn(array('code'=>'0','msg'=>'ID不存在'));
        }else{
            $res = D('Life')->where('life_id =' . $life_id)->delete();
            if($res){
                $this->ajaxReturn(array('code'=>'1','msg'=>'删除成功','url'=>U('life/index')));  
            }else{
                $this->ajaxReturn(array('code'=>'0','msg'=>'删除失败'));
            }
        }
    }
	
	
    //编辑
    public function edit($life_id){
        if ($life_id = (int) $life_id) {
            $obj = D('Life');
            if(!($detail = $obj->find($life_id))){
                $this->error('信息不存在');
            }
			
			if($detail['user_id'] != $this->uid){
                $this->error('非法操作');
            }
			
            if($this->isPost()){
                $data = $this->_post('data',array('title','city_id','cate_id','area_id','business_id','text1','text2','text3','text4','text5','num1','num2','select1','select2','select3','select4','select5','tag','contact','mobile','qq','addr','money','lng','lat'));
				
				
				$details = $this->_post('details', 'SecurityEditorHtml');
				if(empty($details)){
					$this->ajaxReturn(array('code'=>'0','msg'=>'说点什么吧'));
				}
			
			
				$data['cate_id'] = (int) $data['cate_id'];
				if(empty($data['cate_id'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'分类必须选择'));
				}
				
				if(!$res = D('Lifecate')->find($data['cate_id'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'分类不存在'));
				}
			
				$data['title'] = tu_msubstr($details,0,30,false);//标题截取信息内容前半部分
			
				$data['city_id'] = (int) $data['city_id'];
				if(empty($data['city_id'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'城市不能为空'));
				}
				$data['area_id'] = (int) $data['area_id'];
				$data['business_id'] = (int) $data['business_id'];
				
				$data['lng'] = htmlspecialchars(trim($data['lng']));
				$data['lat'] = htmlspecialchars(trim($data['lat']));
				$data['text1'] = htmlspecialchars($data['text1']);
				$data['text2'] = htmlspecialchars($data['text2']);
				$data['text3'] = htmlspecialchars($data['text3']);
				$data['text4'] = htmlspecialchars($data['text4']);
				$data['text5'] = htmlspecialchars($data['text5']);
				$data['num1'] = (int) $data['num1'];
				$data['num2'] = (int) $data['num2'];
				$data['select1'] = (int) $data['select1'];
				$data['select2'] = (int) $data['select2'];
				$data['select3'] = (int) $data['select3'];
				$data['select4'] = (int) $data['select4'];
				$data['select5'] = (int) $data['select5'];
				
				$tag = $this->_post('tag', false);
				$tag = implode(',', $tag);
				$data['tag'] = $tag;
				
				
				//加急信息	
				$urgent_switch_num = I('urgent_switch_num','','trim');//加急开关	
	
				if($urgent_switch_num == 1){
					$urgent_num = I('urgent_num','','trim');//加急天数
					
					$urgent_peice = ($urgent_num * $this->_CONFIG['life']['urgent'])*100;//加急费用
				}
				//置顶信息
				$top_switch_num = I('top_switch_num','','trim');//置顶开关
				if($top_switch_num == 1){
					$top_num = I('top_num','','trim');//置顶天数
					$top_peice = ($top_num * $this->_CONFIG['life']['top'])*100;//置顶费用
				}	
				
					
				$data['urgent_date'] = date('Y-m-d', NOW_TIME + $urgent_num * 86400);//最后的加急天数
				$data['top_date'] = date('Y-m-d', NOW_TIME + $top_num * 86400);//最后的置顶天数
				
				$money = $res['price'] + $top_peice + $urgent_peice;//客户应该付款总费用
			
			
				$data['contact'] = htmlspecialchars($data['contact']);
				if(empty($data['contact'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'联系人不能为空'));
				}
				$data['mobile'] = htmlspecialchars($data['mobile']);
				if(empty($data['mobile'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'电话不能为空'));
				}
				if(!isMobile($data['mobile']) && !isPhone($data['mobile'])) {
					$this->ajaxReturn(array('code'=>'0','msg'=>'电话格式不正确'));
				}
				$data['qq'] = htmlspecialchars($data['qq']);
				$data['addr'] = htmlspecialchars($data['addr']);
				$data['views'] = (int) $data['views'];
				$data['money'] = $money;
				$data['create_time'] = NOW_TIME;
				$data['last_time'] = NOW_TIME + 86400 * 30;
				$data['create_ip'] = get_client_ip();
		
                $data['life_id'] = $life_id;
                $details = $this->_post('details', 'SecurityEditorHtml');
                $data['audit'] = 1;
                if($words = D('Sensitive')->checkWords($details)){
                    $this->ajaxReturn(array('code'=>'0','msg'=>'商家介绍含有敏感词：' . $words));
                }
				
				
                if(false !== $obj->save($data)){
					
					//传图二开
					$photo = $this->_post('photos', false);
				
					if($photo){
						D('Life')->where(array('life_id'=>$life_id))->save(array('photo'=>$photo['0']));
					}
					
					$photos = array_splice($photo,1,9); 
					$arr = '';
					if($photos){
						D('Lifephoto')->upload($life_id, $photos);//更新更多详情图
						foreach($photos as $val){
							if(isImage($val) && $val != ''){
								$arr = '<img src='. config_img($val) .'>';
							}
						}
					}
					$data['details'] = $details .'<br/>'. $arr;
					if($data['details']){
						D('Lifedetails')->updateDetails($life_id, $data['details']);
					}
					
				
					if($money > 0){
						//如果用户有余额
						if($this->member['money'] >= $money){
							D('Users')->addMoney($this->uid,-$money,'发布分类信息ID【'.$life_id.'】扣除余额');
							D('Life')->save(array('life_id'=>$life_id,'price'=>$money,'status'=>1));//改变订单状态
							$this->ajaxReturn(array('code'=>'1','msg'=>'发布信息成功扣费【'.round($money/100,2).'】元','url'=>U('user/life/index')));  
						}else{
							$code = IS_WEIXIN ? 'weixin' : 'alipay'; //智能选择支付方式
							$logs = array(
								'type'=>'life',
								'user_id'=>$this->uid,
								'order_id'=>$life_id,
								'code' =>$code,
								'need_pay'=>$money,
								'create_time'=>NOW_TIME,
								'create_ip'=>get_client_ip(),
								'is_paid' =>0
							);
							$log_id = D('Paymentlogs')->add($logs);
							$this->ajaxReturn(array('code'=>'1','msg'=>'正在跳转到支付页面','url'=>U('payment/payment', array('log_id'=>$log_id))));  
						}
					}else{
						//后台没配置分类需要扣费
						$this->ajaxReturn(array('code'=>'1','msg'=>'发布信息成功，通过审核后将会显示','url'=>U('user/life/index'))); 
					}
                }
                $this->ajaxReturn(array('code'=>'0','msg'=>'编辑信息失败'));
            }else{
               
                $this->assign('cates', D('Lifecate')->fetchAll());
                $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
                $this->assign('cate',$Lifecate = D('Lifecate')->find($detail['cate_id']));
				
				$this->assign('ex', D('Lifedetails')->find($life_id));
                $this->assign('attrs',D('Lifecateattr')->order(array('orderby' =>'asc'))->where(array('cate_id' => $detail['cate_id']))->select());
				
				$this->assign('tags', D('LifeCateTag')->order(array('orderby' => 'asc'))->where(array('cate_id' =>$detail['cate_id']))->select());
				$tag = explode(',', $detail['tag']);
                $this->assign('tag', $tag);
				
				$detail['channel_id'] = $Lifecate['channel_id'];
				$this->assign('cat',$cat = D('Lifecate')->where(array('channel_id'=>$detail['channel_id']))->select());
				$this->assign('detail', $detail);
				//获取图片开始
                $Lifephoto = D('Lifephoto')->getPics($life_id);
				$arr = array();
				$arr['pic_id'] = '0';
				$arr['life_id'] = $life_id;
				$arr['photo'] = $detail['photo'];
				array_unshift($Lifephoto, $arr);
				$this->assign('photos',$Lifephoto);
                $this->display();
            }
        }else{
            $this->error('ID不存在');
        }
    }
  
	
	
	
	//前台获取商圈
    public function channels($channel_id = '0'){
		if(!$channel_id = (int)$channel_id){
			$this->ajaxReturn(array('code'=>'0','msg'=>'请选择频道'));
        }
        $datas = D('Lifecate')->order(array('orderby' => 'asc'))->where(array('channel_id' => $channel_id))->select();
        $str = '<option value="0">请选择子分类</option>';
        foreach($datas as $val){
            if($val['channel_id'] == $channel_id){
                $str .= '<option value="' . $val['cate_id'] . '">' . $val['cate_name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
	
	public function ajax($cate_id,$life_id=0){
        if(!$cate_id = (int)$cate_id){
            $this->error('请选择正确的分类');
        }
        if(!$detail = D('Lifecate')->find($cate_id)){
            $this->error('请选择正确的分类');
        }
        $this->assign('cate',$detail);
        $this->assign('attrs',D('Lifecateattr')->order(array('orderby'=>'asc'))->where(array('cate_id'=>$cate_id))->select());
        if($life_id){
            $this->assign('detail',D('Life')->find($life_id));
            $this->assign('maps',D('LifeCateattr')->getAttrs($life_id));
        }
        $this->display();
    }
	
	//根据分类获取价格
	public function getAttrPrice($cate_id){
        if(!$cate_id = (int)$cate_id){
			$this->ajaxReturn(array('code'=>'0','msg'=>'ID不存在'));
        }
        if(!$detail = D('Lifecate')->find($cate_id)){
			$this->ajaxReturn(array('code'=>'0','msg'=>'请选择正确的分类'));
        }
		$this->ajaxReturn(array('code'=>'1','msg'=>'编辑信息扣费'.round($detail['price']/100,2).'元','price'=>$detail['price'])); 
    }
	
	
	//红包页面
    public function packet($life_id = 0){
        	$life_id = I('life_id', '', 'intval,trim');
			if(!$life_id = (int)$life_id){
				$this->error('没有信息ID');
			}
			if(!$Life = D('Life')->find($life_id)){
				$this->error('信息不存在');
			}
			if($Life['user_id'] != $this->uid){
				$this->error('非法操作');
			}
			$detail = D('LifePacket')->where(array('life_id'=>$life_id,'closed'=>0,'user_id'=>$this->uid))->find();
			$this->assign('detail',$detail);
			$this->assign('life',$Life);
			$this->assign('statuss',$getStatus = D('LifePacket')->getStatus());
			$this->display();
			
    }
	
	
	//添加红包
	public function create_packet(){
			if($this->isPost()){
				$obj =  D('LifePacket');
				$data = $this->checkFields($this->_post('data', false), array('life_id','packet_num','packet_money','packet_is_command','packet_command'));
				$data['life_id'] = (int)$data['life_id'];
				
				if(!$Life = D('Life')->find($data['life_id'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'信息不存在'));
				}
				if($Life['user_id'] != $this->uid){
					$this->ajaxReturn(array('code'=>'0','msg'=>'非法操作'));
				}
				$LifePacket = D('LifePacket')->where(array('life_id'=>$data['life_id'],'closed'=>0,'user_id'=>$this->uid))->find();
				if($LifePacket){
					$this->ajaxReturn(array('code'=>'0','msg'=>'当前信息已经创建过红包，操作失败'));
				}
				$data['user_id'] = $this->uid;
				$data['packet_num'] = (int)$data['packet_num'];
				if(empty($data['packet_num'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'人数必须填写'));
				}
				if($data['packet_num'] < 1){
					$this->ajaxReturn(array('code'=>'0','msg'=>'发放人数必须大于1'));
				}
				$data['packet_money'] = (int)($data['packet_money']*100);
				if(empty($data['packet_money'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'红包金额不能为空'));
				}
				if($data['packet_money'] < 0){
					$this->ajaxReturn(array('code'=>'0','msg'=>'请输入合法的红包金额'));
				}
				
				$data['packet_total_money'] = $data['packet_money']*$data['packet_num'];
				$data['packet_surplus_money'] = $data['packet_total_money'];
				
				if($this->MEMBER['money'] < $data['packet_surplus_money']){
					$this->ajaxReturn(array('code'=>'1','msg'=>'余额不足请先充值','url'=>U('money/index')));
				}
				
				$data['packet_is_command'] = (int)$data['packet_is_command'];
				if($data['packet_is_command'] == 1){
					$data['packet_command'] = htmlspecialchars($data['packet_command']);
					if(empty($data['packet_command'])){
						$this->ajaxReturn(array('code'=>'0','msg'=>'选择口令模式后必须填写口令内容'));
					}
					if(strlen($data['packet_command']) >= 20) {
						$this->ajaxReturn(array('code'=>'0','msg'=>'您设置的口令太长，请缩短文字'));
					}
					if($words = D('Sensitive')->checkWords($data['packet_command'])){
						$this->ajaxReturn(array('code'=>'0','msg'=>'口令中不能含有敏感词汇'.$words));
					}
				}
				
				$data['status'] = 1;
				$data['create_time'] = time();
				$data['create_ip'] = get_client_ip();
	
				if($packet_id = $obj->add($data)){
					if(D('Users')->addMoney($this->uid,-$data['packet_total_money'],'分类ID'.$data['life_id'].'发布红包扣费')){
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
						$this->ajaxReturn(array('code'=>'1','msg'=>'发布红包操作成功','url'=>U('life/packet',array('life_id'=>$data['life_id']))));        
					}else{
						$this->ajaxReturn(array('code'=>'0','msg'=>'扣费失败'));
					}
				}
				$this->ajaxReturn(array('code'=>'0','msg'=>'操作失败'));
			}	
	}
	
	//红包退款
    public function refund($packet_id= 0){
        	$packet_id = I('packet_id', '', 'intval,trim');
			if(!$packet_id){
				$this->ajaxReturn(array('code'=>'0','msg'=>'退款红包ID不存在'));
			}
			if(!$detail = D('LifePacket')->find($packet_id)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'退款信息不存在'));
			}
			if($detail['closed'] == 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>'该红包已经删除无法申请退款'));
			}
			if($detail['user_id'] != $this->uid){
				$this->ajaxReturn(array('code'=>'0','msg'=>'非法操作'));
			}
			if($detail['packet_surplus_money'] ==0){
				$this->ajaxReturn(array('code'=>'0','msg'=>'该红包里面没钱了'));
			}
			if($detail['packet_sold_num'] == $detail['packet_num']){
				$this->ajaxReturn(array('code'=>'0','msg'=>'该红包已经领取完毕没有金额可退款'));
			}
			if(D('LifePacket')->where(array('packet_id'=>$packet_id))->save(array('status'=>3))){
				$this->ajaxReturn(array('code'=>'1','msg'=>'申请退款成功','url'=>U('life/packet',array('life_id'=>$detail['life_id']))));        
			}else{
				$this->ajaxReturn(array('code'=>'0','msg'=>'操作失败'));
			}
			
    }
}