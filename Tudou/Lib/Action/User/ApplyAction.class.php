<?php
class ApplyAction extends CommonAction{
    private $create_fields = array('user_id','city_id', 'area_id', 'business_id', 'logo', 'cate_id', 'user_guide_id','tel', 'logo', 'photo', 'shop_name', 'contact', 'details', 'business_time', 'area_id', 'addr', 'lng', 'lat', 'recognition','is_pei');
	private $delivery_create_fields = array('city_id', 'user_id','photo', 'name', 'mobile', 'addr');
	
	
    public function index(){
        if(empty($this->uid)){
            header("Location:" . U('passport/login'));
            die;
        }
		
        $Shop = D('Shop')->where(array('user_id'=>$this->uid))->find();
		if(!empty($Shop)){
			if($Shop['audit'] == 0){
				$this->error('您申请的商铺审核中', U('index/index'));
			}
			if($Shop['closed'] == 1){
				$this->error('您的商铺异常', U('index/index'));
			}
			$this->error('正在为您跳转到商家管理中心', U('seller/index/index'));
		}
		
		$guide_id = I('guide_id');
		$this->assign('guide_id', $guide_id);
		
		
		$shop_apply_prrice = ((int)$this->_CONFIG['shop']['shop_apply_prrice'])*100;//首先计算入驻费用
		
		
        if($this->isPost()){
			$obj = D('Shop');
            $data = $this->createCheck();
            $details = $this->_post('details', 'htmlspecialchars');
            if($words = D('Sensitive')->checkWords($details)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商家介绍含有敏感词：' . $words));
            }
			
			if($shop_apply_prrice > 0){
				if(!($code = $_POST['code'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'请选择支付方式'));
				}
			}
			
            $ex = array('details' => $details, 'near' => $data['near'], 'price' => $data['price'], 'business_time' => $data['business_time']);
            unset($data['near'], $data['price'], $data['business_time']);
			
			
			
            if($shop_id = $obj->add($data)){
                D('Shopdetails')->upDetails($shop_id,$ex);
				D('Shopguide')->upAdd($data['user_guide_id'],$shop_id);//新增到推荐人表
				D('Shop')->buildShopQrcode($shop_id,15);//生成商家二维码
				if($shop_apply_prrice > 0){
					$arr = array(
						'type' => 'shop', 
						'user_id' => $this->uid, 
						'order_id' => $shop_id, 
						'code' => $code, 
						'need_pay' => $shop_apply_prrice, 
						'create_time' => time(), 
						'create_ip' => get_client_ip(), 
						'is_paid' => 0
					);
					if($log_id = D('Paymentlogs')->add($arr)){
						$this->ajaxReturn(array('code'=>'1','msg'=>'正在跳转为您支付','url'=>U('wap/payment/payment', array('log_id' =>$log_id))));
					}else{
						$this->ajaxReturn(array('code'=>'0','msg'=>'设置订单失败'));
					}
				}else{
					$this->ajaxReturn(array('code'=>'1','msg'=>'恭喜您申请成功','url'=>U('member/index')));
				}
            }
			$this->ajaxReturn(array('code'=>'0','msg'=>'申请失败'));
        }else{
            $lat = addslashes(cookie('lat'));
            $lng = addslashes(cookie('lng'));
            if(empty($lat) || empty($lng)) {
                $lat = $this->_CONFIG['site']['lat'];
                $lng = $this->_CONFIG['site']['lng'];
            }
            $this->assign('lat', $lat);
            $this->assign('lng', $lng);
            $this->assign('cates', D('Shopcate')->fetchAll());
			$this->assign('payment', D('Payment')->getPayments(true));
            $this->display();
        }
    }
	
	
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$detail = D('Shop')->where(array('user_id' =>$this->uid))->find();
        if(!empty($detail)){
			$this->ajaxReturn(array('code'=>'0','msg'=>'您已经是商家了'));
        }
		$data['user_id'] = $this->uid;
		$guide_ids = htmlspecialchars($data['user_guide_id']);
		$data['user_guide_id'] = explode(',',$guide_ids);
		if($guide_ids){
			if (false == D('Shopguide')->check_user_guide_id($data['user_guide_id'])){
				$this->tuMsg(D('Shopguide')->getError());
			}
		}
        $data['photo'] = htmlspecialchars($data['photo']);
        if(empty($data['photo'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'请上传商家形象图'));
        }
        if(!isImage($data['photo'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'商家形象图格式不正确'));
        }
		$data['logo'] = htmlspecialchars($data['logo']);
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if(empty($data['shop_name'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'店铺名称不能为空'));
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if(empty($data['cate_id'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'分类不能为空'));
        }
        $data['city_id'] = (int) $data['city_id'];
        if(empty($data['city_id'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'城市不能为空'));
        }
        $data['area_id'] = (int) $data['area_id'];
        $data['business_id'] = (int) $data['business_id'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['contact'] = htmlspecialchars($data['contact']);
        $data['business_time'] = htmlspecialchars($data['business_time']);
        $data['addr'] = htmlspecialchars($data['addr']);
        $data['tel'] = htmlspecialchars($data['tel']);
        if(empty($data['tel'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'手机号不能为空'));
        }
        if(!isPhone($data['tel']) && !isMobile($data['tel'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'手机号格式不正确'));
        }
        if(isMobile($data['tel'])){
            $data['mobile'] = $data['tel'];
        }
        $data['recognition'] = 1;
        $data['user_id'] = $this->uid;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	public function delivery(){
        if(empty($this->uid)){
            header("Location:" . U('passport/login'));
            die;
        }
		$obj = D('Delivery');
		$user_delivery = $obj->where(array('user_id' => $this->uid))->find();
		if($user_delivery['closed'] !=0){
			$this->error('非法错误');
		}
        if($this->isPost()){
            $data = $this->delivery_createCheck();
            if ($obj->add($data)){
                $this->tuMsg('恭喜您申请成功', U('user/member/index'));
            }else{
				$this->tuMsg('申请失败');
			}
        }else{
			$this->assign('user_delivery', $user_delivery);
            $this->display();
        }
    }
	
	 private function delivery_createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->delivery_create_fields);
		$data['user_id'] = $this->uid;
        $data['photo'] = htmlspecialchars($data['photo']);
        if(empty($data['photo'])){
            $this->tuMsg('请上传身份证');
        }
        if(!isImage($data['photo'])){
            $this->tuMsg('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if(empty($data['name'])){
            $this->tuMsg('姓名不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if(empty($data['mobile'])){
            $this->tuMsg('手机号不能为空');
        }
        if(!isPhone($data['mobile']) && !isMobile($data['mobile'])){
            $this->tuMsg('手机号格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if(empty($data['addr'])){
            $this->tuMsg('地址不能为空');
        }        
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	
	
	public function worker(){
        if(empty($this->uid)){
            header("Location:" . U('passport/login'));
            die;
        }
		$obj = D('Shopworker');
		$worker = $obj->where(array('user_id' => $this->uid))->find();
		if($worker['closed'] ==1){
			$this->error('非法错误');
		}
        if($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false),array('shop_id','name','tel','mobile','qq','weixin','work','addr'));
			$data['user_id'] = $this->uid;
			$data['shop_id'] = $data['shop_id'];
			if(empty($data['shop_id'])){
				$this->tuMsg('商家ID不能为空');
			}
			$data['name'] = htmlspecialchars($data['name']);
			if (empty($data['name'])){
				$this->tuMsg('姓名不能为空');
			}
			$data['mobile'] = $data['mobile'];
			if(empty($data['mobile'])){
				$this->tuMsg('手机号码不能为空');
			}
			$data['work'] = htmlspecialchars($data['work']);
			if(empty($data['work'])){
				$this->tuMsg('员工职务不能为空');
			}
			$data['addr'] = htmlspecialchars($data['addr']);
			if(empty($data['addr'])){
				$this->tuMsg('联系地址不能为空');
			}
			
			
			//$obj->add($data);
			//p($obj->getLastSql());die;
            if($obj->add($data)){
				
                $this->tuMsg('恭喜您申请店员成功', U('user/member/index'));
            }else{
				$this->tuMsg('申请失败');
			}
        }else{
			$this->assign('worker', $worker);
            $this->display();
        }
    }

	
}