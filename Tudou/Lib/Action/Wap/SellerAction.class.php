<?php
class SellerAction extends CommonAction {
	
	public function _initialize(){
        parent::_initialize();
       
    }
	 public function link(){ 
	 
	 	if(empty($this->uid)){
            $this->error('登录状态失效', U('passport/login'));          
        }
		$Users = D('Users')->find($this->uid);
		
		if($Users['fuid1']){
			header("Location:" . U('seller/shop',array('user_id'=>$Users['fuid1'])));
        	die;
		}else{
			header("Location:" . U('seller/shop',array('user_id'=>$this->uid)));
        	die;
		}
		
		
	 	
	 
	 }
	 
	 public function shop($user_id){
		$user_id = (int) $this->_param('user_id');
		if(!($Users = D('Users')->find($user_id))){
            $this->error('会员不存在');
        }
		$detail = D('SellerSetting')->find($user_id);
		if(!$detail){
			 $this->error('当前会员没加入小店');
		}
		
		$this->assign('users', $Users);
		$this->assign('detail', $detail);
		
		//置顶商品
		$goods_top = D('SellerGoods')->where(array('user_id'=>$user_id,'type_id' =>1,'is_top' =>1,'closed' =>0))->order(array('create_time' =>'desc'))->limit(0, 5)->select();
		foreach($goods_top as $k => $val){
            if($Goods = D('Goods')->find($val['id'])){
			   $goods_top[$k]['goods'] = $Goods;
            }
        }
        $this->assign('goods_top', $goods_top);
		
		//推荐商品
		$goods_tuijian = D('SellerGoods')->where(array('user_id'=>$user_id,'type_id' =>1,'is_tuijian' =>1,'closed' =>0))->order(array('create_time' =>'desc'))->limit(0, 5)->select();
		foreach($goods_tuijian as $k => $val){
            if($Goods = D('Goods')->find($val['id'])){
			   $goods_tuijian[$k]['goods'] = $Goods;
            }
        }
        $this->assign('goods_tuijian', $goods_tuijian);
		
		//抢购
		$tuans = D('SellerGoods')->where(array('user_id'=>$user_id,'type_id' =>2,'closed' =>0))->order(array('create_time' =>'desc'))->limit(0, 5)->select();
		foreach($tuans as $k => $val){
            if($Tuan = D('Tuan')->find($val['id'])){
			   $tuans[$k]['tuan'] = $Tuan;
            }
        }
        $this->assign('tuans', $tuans);
		//商家
		$shops = D('SellerGoods')->where(array('user_id'=>$user_id,'type_id' =>3,'closed' =>0))->order(array('create_time' =>'desc'))->limit(0, 5)->select();
		
		foreach($shops as $k => $val){
            if($Shop = D('Shop')->find($val['id'])){
			   $shops[$k]['shop'] = $Shop;
            }
        }
        $this->assign('shops', $shops);
		
        $this->display();
	 }
	 
	 
	 public function set($user_id){
		 $user_id = (int) $this->_param('user_id');
		 if(!($Users = D('Users')->find($user_id))){
            $this->error('会员不存在');
         }
		 $detail = D('SellerSetting')->find($user_id);
		 $this->assign('users', $Users);
		 $this->assign('detail', $detail);
         $this->display();
	 }
	 
	 
	  public function setting($user_id){
		 $user_id = (int) $this->_param('user_id');
		 if(!($Users = D('Users')->find($user_id))){
            $this->error('会员不存在');
         }
		 $this->assign('users', $Users);
		 
		 $detail = D('SellerSetting')->find($user_id);
		 $this->assign('detail', $detail);
		 
		 if($this->isPost()){
            $seller_name = $this->_post('seller_name');
			if(empty($seller_name)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'店铺名称不能为空'));
			}
			if($words = D('Sensitive')->checkWords($seller_name)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'店铺名称含有敏感词：' . $words));
            }
			
			$seller_intro = $this->_post('seller_intro');
			if(empty($seller_intro)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'店铺简介不能为空'));
			}
			if($words = D('Sensitive')->checkWords($seller_intro)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'店铺简介含有敏感词：' . $words));
            }
			if($detail){
				D('SellerSetting')->where(array('user_id'=>$user_id))->save(array('seller_name' => $seller_name,'seller_intro'=>$seller_intro));
				$intro = '修改成功';
			}else{
				D('SellerSetting')->where(array('user_id'=>$user_id))->add(array('user_id'=>$user_id,'seller_name' => $seller_name,'seller_intro'=>$seller_intro));
				$intro = '新增成功';
			}
            $this->ajaxReturn(array('code'=>'1','msg'=>$intro,'url'=>U('seller/set',array('user_id'=>$user_id))));
        }
		 
        $this->display();
	 }
	
	
	
	 public function sign($user_id){
		 $user_id = (int) $this->_param('user_id');
		 if(!($Users = D('Users')->find($user_id))){
            $this->error('会员不存在');
         }
		 $this->assign('users', $Users);
		 
		 $detail = D('SellerSetting')->find($user_id);
		 $this->assign('detail', $detail);
		 
		 if($this->isPost()){
            $seller_sign = $this->_post('seller_sign');
			if(empty($seller_sign)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'店铺签名不能为空'));
			}
			if($words = D('Sensitive')->checkWords($seller_sign)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'店铺签名含有敏感词：' . $words));
            }
			if($detail){
				D('SellerSetting')->where(array('user_id'=>$user_id))->save(array('seller_sign' => $seller_sign));
				$intro = '签名修改成功';
			}else{
				D('SellerSetting')->where(array('user_id'=>$user_id))->add(array('user_id'=>$user_id,'seller_sign' => $seller_sign));
				$intro = '签名新增成功';
			}
            $this->ajaxReturn(array('code'=>'1','msg'=>$intro,'url'=>U('seller/set',array('user_id'=>$user_id))));
        }
		 
        $this->display();
	 }
	 
	 
	 public function banner($user_id){
		 $user_id = (int) $this->_param('user_id');
		 if(!($Users = D('Users')->find($user_id))){
            $this->error('会员不存在');
         }
		 $this->assign('users', $Users);
		 
		 $detail = D('SellerSetting')->find($user_id);
		 $this->assign('detail', $detail);
		 
		 if($this->isPost()){
			
			$seller_photo = $this->_post('photo');
			if(empty($seller_photo)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'请上传店招'));
			}
			if(!isImage($seller_photo)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'店招图片格式不正确'));
			}
			if($detail){
				D('SellerSetting')->where(array('user_id'=>$user_id))->save(array('seller_photo' => $seller_photo));
				$intro = '店招修改成功';
			}else{
				D('SellerSetting')->where(array('user_id'=>$user_id))->add(array('user_id'=>$user_id,'seller_photo' => $seller_photo));
				$intro = '店招新增成功';
			}
            $this->ajaxReturn(array('code'=>'1','msg'=>$intro,'url'=>U('seller/set',array('user_id'=>$user_id))));
        }
        $this->display();
	 }
	
	
	 //商品首页
	 public function goods($user_id){
		$user_id = (int) $this->_param('user_id');
		if(!($Users = D('Users')->find($user_id))){
            $this->error('会员不存在');
        }
		$this->assign('users', $Users);
		$this->assign('user_id', $user_id);
		$linkArr['user_id'] = $user_id;
		 
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
		$linkArr['keyword'] = $keyword;

        $is_top = (int) $this->_param('is_top');
        $this->assign('is_top', $is_top);
		$linkArr['is_top'] = $is_top;
		
		$is_tuijian = (int) $this->_param('is_tuijian');
		$this->assign('is_tuijian', $is_tuijian);
		$linkArr['is_tuijian'] = $is_tuijian;
		
        $this->assign('nextpage', LinkTo('seller/loaddata',$linkArr, array('p' => '0000')));
        $this->display();
		 
	 }
	
	 //商品详情页
	 public function loaddata(){
        $obj = D('SellerGoods');
        import('ORG.Util.Page');
		$map['closed'] = 0;
		
		$user_id = (int) $this->_param('user_id');
        $map['user_id'] = $user_id;
		$linkArr['user_id'] = $user_id;
		
        $is_top = (int) $this->_param('is_top');
        if($is_top){
            $map['is_top'] = $is_top;
			$linkArr['is_top'] = $is_top;
        }
		
		$is_tuijian = (int) $this->_param('is_tuijian');
        if($is_tuijian){
            $map['is_tuijian'] = $is_tuijian;
			$linkArr['is_tuijian'] = $is_tuijian;
        }
		
		$count = $obj->where($map)->count();
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array('create_time' =>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val){
			
			if($val['type_id'] == 1){
				if($Goods = D('Goods')->find($val['id'])){
				   $val['goods'] = $Goods;
				   $list[$k] = $val;
				}
			}
			if($val['type_id'] == 2){
				if($Tuan = D('Tuan')->find($val['id'])){
				   $val['tuan'] = $Tuan;
				   $list[$k] = $val;
				}
			}if($val['type_id'] == 3){
				if($Shop = D('Shop')->find($val['id'])){
				   $val['shop'] = $Shop;
				   $list[$k] = $val;
				}
			}	
        }
		
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 //删除商品
	 public function delete($user_id,$goods_id){
		$user_id = (int) $this->_param('user_id');
        $goods_id = (int) $this->_param('goods_id');
		$obj = D('SellerGoods');
		if(!($res = $obj->find($goods_id))){
            $this->ajaxReturn(array('code'=>'0','msg'=>'您要选择删除的类型不存在'));
        }
		if($res['user_id'] != $this->uid){
            $this->ajaxReturn(array('code'=>'0','msg'=>'请不要非法操作'));
        }
		if($obj->where(array('goods_id'=>$goods_id))->save(array('closed'=>'1'))){
			$this->ajaxReturn(array('code'=>'1','msg'=>'删除成功','url'=>U('seller/goods',array('user_id'=>$user_id))));
		}else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'删除失败'));
		}
    }
	
	
	 //添加商品
	 public function create($user_id = 0,$type_id,$id){
		$user_id = (int) $this->_param('user_id');
        $type_id = (int) $this->_param('type_id');
		$id = (int) $this->_param('id');
		
	
		
		if(!$user_id){
			$this->ajaxReturn(array('code'=>'1','msg'=>'请登录后操作','url'=>U('passport/login')));
		}
		
		if($type_id == 1){
			if(!($Goods = D('Goods')->find($id))){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商品不存在'));
			}
			if($Goods['closed'] != 0){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商品已被删除'));
			}
			if($Goods['audit'] != 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商品未审核'));
			}
			$intro = '商品';
		}elseif($type_id == 2){
			if(!($Tuan = D('Tuan')->find($id))){
				$this->ajaxReturn(array('code'=>'0','msg'=>'抢购不存在'));
			}
			if($Tuan['closed'] != 0){
				$this->ajaxReturn(array('code'=>'0','msg'=>'抢购已被删除'));
			}
			if($Tuan['audit'] != 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>'抢购未审核'));
			}
			$intro = '抢购';
		}elseif($type_id == 3){
			if(!($Shop = D('Shop')->find($id))){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商家不存在'));
			}
			if($Shop['closed'] != 0){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商家已被删除'));
			}
			if($Shop['audit'] != 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商家未审核'));
			}
			$intro = '商城';
		}
		
		
		
		
		
		$obj = D('SellerGoods');
		
		if($res = $obj->where(array('user_id'=>$user_id,'type_id'=>$type_id,'id'=>$id))->find()){
			if($res['closed'] == 1){
				$obj->where(array('id'=>$id))->delete();
			}else{
				$this->ajaxReturn(array('code'=>'0','msg'=>'您已添加该'.$intro.'，请不要重复添加'));
			}
        }
		
		$data = array();
		$data['type_id'] = $type_id;
		$data['id'] = $id;
		$data['user_id'] = $user_id;
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		if($obj->add($data)){
			$this->ajaxReturn(array('code'=>'1','msg'=>'添加'.$intro.'成功','url'=>U('seller/goods',array('user_id'=>$user_id))));
		}else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'操作失败'));
		}
    }
	
	 //设置置顶
	 public function is_top($user_id,$goods_id){
		$user_id = (int) $this->_param('user_id');
        $goods_id = (int) $this->_param('goods_id');
		
		$obj = D('SellerGoods');
		if(!($res = $obj->find($goods_id))){
            $this->ajaxReturn(array('code'=>'0','msg'=>'您要选择置顶项目不存在'));
        }
		if($res['user_id'] != $this->uid){
            $this->ajaxReturn(array('code'=>'0','msg'=>'请不要非法操作'));
        }
		$data = array('is_top' => 0,'goods_id'=>$goods_id);
		$intro = '取消置顶成功';
        if($res['is_top'] == 0){
           $data['is_top'] = 1;
		   $intro = '添加商品置顶成功';
        }
		if($obj->save($data)){
			$this->ajaxReturn(array('code'=>'1','msg'=>$intro,'url'=>U('seller/goods',array('user_id'=>$user_id))));
		}else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'操作失败'));
		}
    }
	
	 //设置置顶
	 public function is_tuijian($user_id,$goods_id){
		$user_id = (int) $this->_param('user_id');
        $goods_id = (int) $this->_param('goods_id');
		
		$obj = D('SellerGoods');
		if(!($res = $obj->find($goods_id))){
            $this->ajaxReturn(array('code'=>'0','msg'=>'您要操作的推荐存在'));
        }
		if($res['user_id'] != $this->uid){
            $this->ajaxReturn(array('code'=>'0','msg'=>'请不要非法操作'));
        }
		
		$data = array('is_tuijian' => 0,'goods_id'=>$goods_id);
		$intro = '取消推荐成功';
        if($res['is_tuijian'] == 0){
           $data['is_tuijian'] = 1;
		   $intro = '添加推荐成功';
        }
		if($obj->save($data)){
			$this->ajaxReturn(array('code'=>'1','msg'=>$intro,'url'=>U('seller/goods',array('user_id'=>$user_id))));
		}else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'操作失败'));
		}
    }
	
	//获取分成数据
	public function separate($goods_id = 0){
        $goods_id = (int) $this->_param('goods_id');
		
		
		if(!$detail = D('SellerGoods')->find($goods_id)){
			$this->ajaxReturn(array('code'=>'0','msg'=>'不存在'));
        }
		if($detail['closed']== 1){
			$this->ajaxReturn(array('code'=>'0','msg'=>'商品已删除'));
        }
		if($detail['user_id'] != $this->uid){
            $this->ajaxReturn(array('code'=>'0','msg'=>'请不要非法操作'));
        }
		
		
		$config = D('Setting')->fetchAll();
		
		if($detail['type_id']== 1){
			if(!($Goods = D('Goods')->find($detail['id']))){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商品不存在'));
			}
			if($Goods['closed'] != 0){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商品已被删除'));
			}
			if($Goods['audit'] != 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商品未审核'));
			}
			$msg .='商品名称【'.$Goods['title'].'】<br>'; 
			$msg .='1级分成比例【'.$config['profit']['goods_profit_rate1'].'%】，预计分成【'.round($config['profit']['goods_profit_rate1'] * $Goods['mall_price']/10000,2).'】元<br>'; 
			$msg .='2级分成比例【'.$config['profit']['goods_profit_rate2'].'%】，预计分成【'.round($config['profit']['goods_profit_rate2'] * $Goods['mall_price']/10000,2).'】元<br>'; 
			$msg .='3级分成比例【'.$config['profit']['goods_profit_rate3'].'%】，预计分成【'.round($config['profit']['goods_profit_rate3'] * $Goods['mall_price']/10000,2).'】元<br>'; 
		}elseif($detail['type_id'] == 2){
			if(!($Tuan = D('Tuan')->find($detail['id']))){
				$this->ajaxReturn(array('code'=>'0','msg'=>'抢购不存在'));
			}
			if($Tuan['closed'] != 0){
				$this->ajaxReturn(array('code'=>'0','msg'=>'抢购已被删除'));
			}
			if($Tuan['audit'] != 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>'抢购未审核'));
			}
			$msg .='抢购名称【'.$Tuan['title'].'】<br>'; 
			$msg .='1级分成比例【'.$config['profit']['currency_profit_rate1'].'%】，预计分成【'.round($config['profit']['currency_profit_rate1'] * $Tuan['tuan_price']/10000,2).'】元<br>'; 
			$msg .='2级分成比例【'.$config['profit']['currency_profit_rate2'].'%】，预计分成【'.round($config['profit']['currency_profit_rate2'] * $Tuan['tuan_price']/10000,2).'】元<br>'; 
			$msg .='3级分成比例【'.$config['profit']['currency_profit_rate3'].'%】，预计分成【'.round($config['profit']['currency_profit_rate3'] * $Tuan['tuan_price']/10000,2).'】元<br>'; 
		}elseif($detail['type_id'] == 3){
			if(!($Shop = D('Shop')->find($detail['id']))){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商家不存在'));
			}
			if($Shop['closed'] != 0){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商家已被删除'));
			}
			if($Shop['audit'] != 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>'商家未审核'));
			}
			$msg .='商家名称【'.$Shop['shop_name'].'】<br>'; 
		}
        $this->ajaxReturn(array('code' => '1', 'msg' => $msg));    
	}
	
	
}