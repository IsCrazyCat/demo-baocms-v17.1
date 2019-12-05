<?php

class PaddlistModel extends CommonModel{
    protected $pk = 'id';
    protected $tableName = 'paddlist';
	
	
	//返回地址
	public function getIntro($id){
		$res = M('Paddlist')->where(array('id'=>$id))->find();//自己地址
		$res1 = M('Paddlist')->where(array('id'=>$res['upid']))->find();
		$res2 = M('Paddlist')->where(array('id'=>$res1['upid']))->find();
		$res3 = M('Paddlist')->where(array('id'=>$res2['upid']))->find();
		$res4 = M('Paddlist')->where(array('id'=>$res3['upid']))->find();
		return $res4['name'].'<b>&nbsp;</b>'.$res3['name'].'<b>&nbsp;</b>'.$res2['name'].'<b>&nbsp;</b>'.$res1['name'].'<b>&nbsp;</b>'.$res['name'];
	}
	
	
	//返回商城订单总价
	public function getGoodsOrderPrice($id){
		$ids = $this->getAddressIds($id);
		$need_pay = (int) M('Order')->where(array('status'=>array('in',array(1,2,3,8)),'closed'=>0,'address_id'=>array('in',$ids)))->sum('need_pay');
		return $need_pay;
	}
	
	
	
	//返回商城订单数量
	public function getGoodsOrderNum($id){
		$ids = $this->getAddressIds($id);
		$count = (int) M('Order')->where(array('status'=>array('in',array(1,2,3,8)),'closed'=>0,'address_id'=>array('in',$ids)))->count();
		return $count;
	}
	
	public function getAddressIds ($id){
		$Paddlist = M('Paddlist')->where(array('id'=>$id))->find();
		if($Paddlist['upid'] == 0){
			$list = M('Paddress')->where(array('province_id'=>$id))->select();
		}
		if($Paddlist['upid'] == 1){
			$list = M('Paddress')->where(array('city_id'=>$id))->select();
		}
		if($Paddlist['upid'] == 2){
			$list = M('Paddress')->where(array('area_id'=>$id))->select();
		}
		
		foreach ($list as $key => $val) {
            $ids[$val['id']] = $val['id'];
        }
		return $ids;
	}
}