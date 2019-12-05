<?php

class GoodsAction extends CommonAction{
	
	
	
	//商品列表
	public function StoreGoodList(){
		$shop_id = I('store_id','','trim');
		$obj = D('Goods');
		import('ORG.Util.Page');
		
		$map = array('audit' => 1, 'closed' => 0,'end_date' => array('EGT', TODAY));
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['id'] = $val['goods_id'];
			$list[$k]['lb_imgs'] = config_weixin_img($val['photo']);
			$list[$k]['price'] = round($val['mall_price']/100,2);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	//获取商品列表图片开始
	public function getGoodsPics($goods_id){
		$list = D('Goodsphoto')->getPics($goods_id);
		foreach($list as $k => $val){
			$photos[$k] = config_weixin_img($val['photo']);
		}
		$res = implode(",",$photos);
		return "".$res ."";
	}
	
	
	
	public function getSpec($goods_id){
		$list = M('TpSpecGoodsPrice')->where(array('goods_id'=>$goods_id))->select(); //获取商品规格参数     
        foreach($list as $k => $val){
			$list[$k]['spec_id'] = $val['key'];
			$list[$k]['spec_name'] = $val['key_name'];
		 }
        return $list;
   }
	
	//商品详情
	public function GoodInfo(){
		$goods_id = I('id','','trim');
		$detail = D('Goods')->find($goods_id);
		$detail['id'] = $goods_id;	
		$detail['price'] = round($detail['mall_price']/100,2);
		$detail['goods_cost'] = round($detail['mall_price']/100,2);
		$detail['lb_imgs'] = config_weixin_img($detail['photo']);			
		$detail['imgs'] = $this->getGoodsPics($detail['goods_id']);
		$detail['detail'] = cleanhtml($detail['details']);
		$res = $this->getSpec($goods_id); //获取商品规格参数     
		$data['good'] = $detail;
	    $data['spec'] = $res ;
	    echo json_encode($data);
	}
	
	
	//查看我的订单
  	public function MyOrder(){
	  	$user_id = I('user_id','','trim');
		$obj = D('Order');
		import('ORG.Util.Page');
		$map = array('user_id' => $user_id);
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
			$list[$k]['id'] = $val['order_id'];
			$list[$k]['state'] = $val['status'];
			$list[$k]['shop_name'] = $Shop['shop_name'];
			$list[$k]['complete_time'] = $val['update_time'];
			$list[$k]['money'] = round($val['total_price']/100,2);//原价
			$list[$k]['good_money'] = round($val['need_pay']/100,2);//实际付款
			$list[$k]['freight'] = round($val['express_price']/100,2);//运费
			$list[$k]['good_num'] = D('Ordergoods')->where(array('order_id'=>$val['order_id']))->sum('num');
			
			$arr = D('Ordergoods')->where(array('order_id'=>$val['order_id']))->select();
			foreach($arr as $k2 => $v2){
				$Goods = D('Goods')->find($v2['goods_id']);
				$arr[$k2]['good_name'] = $Goods['title'];
				$arr[$k2]['good_money'] = round($v2['price']/100,2);
				$arr[$k2]['good_img'] = config_weixin_img($Goods['photo']);
			}
			
			$list[$k]['goods'] = $arr;
		}
       echo json_encode($list);
  }
  
  
	  //查看商城订单详情
	  public function OrderInfo(){
		$order_id = I('order_id','','trim');
		$detail = D('Order')->find($order_id);
		  
		  
		$Shop = D('Shop')->find($detail['shop_id']);
		$detail['id'] = $detail['order_id'];
		$detail['state'] = $detail['status'];
		$detail['shop_name'] = $Shop['shop_name'];
		$detail['complete_time'] = $detail['update_time'];
		$detail['money'] = round($detail['total_price']/100,2);//原价
		$detail['good_money'] = round($detail['need_pay']/100,2);//实际付款
		$deail['freight'] = round($detail['express_price']/100,2);//运费
		$detail['good_num'] = D('Ordergoods')->where(array('order_id'=>$val['order_id']))->sum('num');
				
		$arr = D('Ordergoods')->where(array('order_id'=>$detail['order_id']))->select();
		foreach($arr as $k2 => $v2){
			$Goods = D('Goods')->find($v2['goods_id']);
			$arr[$k2]['good_name'] = $Goods['title'];
			$arr[$k2]['good_money'] = round($v2['price']/100,2);
			$arr[$k2]['good_img'] = config_weixin_img($Goods['photo']);
		}
				
		$detail['goods'] = $arr;
				
		echo json_encode($detail);
	  }
	
	//确认收货
  public function CompleteOrder(){
      global $_W, $_GPC;
      $order=pdo_get('zhtc_order',array('id'=>$_GPC['order_id']));
      $res=pdo_update('zhtc_order',array('state'=>4,'complete_time'=>time()),array('id'=>$_GPC['order_id']));
      if($res){
      	pdo_update('zhtc_store',array('wallet +='=>$order['money']));
      	$data['store_id']=$order['store_id'];
      	$data['money']=$order['money'];
      	$data['note']='商品订单';
      	$data['type']=1;
        $data['time']=date("Y-m-d H:i:s");
        pdo_insert('zhtc_store_wallet',$data);

/////////////////分销/////////////////

        $set=pdo_get('zhtc_fxset',array('uniacid'=>$_W['uniacid']));
        $order=pdo_get('zhtc_order',array('id'=>$_GPC['order_id']));
        if($set['is_open']==1){
            if($set['is_ej']==2){//不开启二级分销
       $user=pdo_get('zhtc_fxuser',array('fx_user'=>$order['user_id']));
       if($user){
            $userid=$user['user_id'];//上线id
            $money=$order['money']*($set['commission']/100);//一级佣金
            pdo_update('zhtc_user',array('commission +='=>$money),array('id'=>$userid));
            $data6['user_id']=$userid;//上线id
            $data6['son_id']=$order['user_id'];//下线id
            $data6['money']=$money;//金额
            $data6['time']=time();//时间
            $data6['uniacid']=$_W['uniacid'];
            pdo_insert('zhtc_earnings',$data6);
          }
      }else{//开启二级
       $user=pdo_get('zhtc_fxuser',array('fx_user'=>$order['user_id']));
          $user2=pdo_get('zhtc_fxuser',array('fx_user'=>$user['user_id']));//上线的上线
          if($user){
            $userid=$user['user_id'];//上线id
            $money=$order['money']*($set['commission']/100);//一级佣金
            pdo_update('zhtc_user',array('commission +='=>$money),array('id'=>$userid));
            $data6['user_id']=$userid;//上线id
            $data6['son_id']=$order['user_id'];//下线id
            $data6['money']=$money;//金额
            $data6['time']=time();//时间
            $data6['uniacid']=$_W['uniacid'];
            pdo_insert('zhtc_earnings',$data6);
          }
          if($user2){
            $userid2=$user2['user_id'];//上线的上线id
            $money=$order['money']*($set['commission2']/100);//二级佣金
            pdo_update('zhtc_user',array('commission +='=>$money),array('id'=>$userid2));
            $data7['user_id']=$userid2;//上线id
            $data7['son_id']=$order['user_id'];//下线id
            $data7['money']=$money;//金额
            $data7['time']=time();//时间
            $data7['uniacid']=$_W['uniacid'];
            pdo_insert('zhtc_earnings',$data7);
          }
        }
   }


        echo  '1';
      }else{
        echo  '2';
      }
  }
	
	//点击购买
	public function place_order(){
		$goods_id = I('id','','trim');
		$shop_id = I('store_id','','trim');
		$price = I('price','','trim');
		$num = I('num','','trim');
		$name1 = I('name1','','trim,htmlspecialchars');
		$name2 = I('name2','','trim,htmlspecialchars');
		$name3 = I('name3','','trim,htmlspecialchars');
	}
	
	//申请退款
	 public function TuOrder(){
		global $_W, $_GPC;
		$res=pdo_update('zhtc_order',array('state'=>5),array('id'=>$_GPC['order_id']));
		  if($res){
			echo  '1';
		  }else{
			echo  '2';
		  }
	 }
	 //用户删除订单
	  public function DelOrder(){
		  global $_W, $_GPC;
		  $res=pdo_update('zhtc_order',array('del'=>1),array('id'=>$_GPC['order_id']));
		  if($res){
			echo  '1';
		  }else{
			echo  '2';
		  }
	  }
	
}