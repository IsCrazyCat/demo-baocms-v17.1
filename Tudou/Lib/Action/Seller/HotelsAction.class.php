<?php
class HotelsAction extends CommonAction { 

	protected function _initialize(){
       parent::_initialize();
        if ($this->_CONFIG['operation']['hotels'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
	
	  private function getHotelId(){
        $res = D('Hotel')->where(array('shop_id'=>$this->shop_id))->find();
        if(!$res){
            $this->error('您还没申请酒店',U('index/index'));
        }else{
            return $res['hotel_id'];
        }
        
    }
	
    public function index(){
		$this->getHotelId();
        $st = (int) $this->_param('st');
		$this->assign('st', $st);
		$this->display(); 
    }	
	
    public function loaddata() {
		$hotel_id = $this->getHotelId();
		$obj = D('Hotelorder');
		import('ORG.Util.Page'); 
		$map = array('hotel_id' => $hotel_id); 
		$st = (int) $this->_param('st');
		if ($st == 1) {
            $map['online_pay'] = 1;
        } elseif ($st == 2) {
            $map['order_status'] = 2;
        }elseif ($st == 3) {
            $map['order_status'] = 3;
        }elseif ($st == 4) {
            $map['order_status'] = 4;
        } elseif ($st == 0) {
            $map['online_pay'] = 0;
        } else {
            $map['online_pay'] = 0;
        }
		$count = $obj->where($map)->count(); 
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
		$list = $obj->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$room_ids  = $hotel_ids = array();
        foreach ($list as $k => $val) {
            $room_ids[$val['room_id']] = $val['room_id'];
            $hotel_ids[$val['hotel_id']] = $val['hotel_id'];
        }
        if (!empty($hotel_ids)) {
            $this->assign('hotels', D('Hotel')->itemsByIds($hotel_ids));
        }
        if($room_ids){
            $this->assign('rooms', D('Hotelroom')->itemsByIds($room_ids));
        }
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}
    
    
    public function detail($order_id){
        if(!$order_id = (int)$order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Hotelorder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['hotel_id'] != $this->getHotelId()){
            $this->error('非法的订单操作');
        }else{
           $detail['night_num'] = $this->diffBetweenTwoDays($detail['stime'],$detail['ltime']); 
           $detail['room'] = D('Hotelroom')->find($detail['room_id']); 
           $detail['hotel'] = D('Hotel')->find($detail['hotel_id']);
           $this->assign('detail',$detail);
           $this->assign('roomtype',D('Hotelroom')->getRoomType());
           $this->display();
        }
    }
	
	//取消订单
	public function cancel($order_id){
        $hotel_id = $this->getHotelId();
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->tuMsg('订单不存在');
            }elseif($order['hotel_id'] != $hotel_id){
                $this->tuMsg('非法操作订单');
            }elseif($order['order_status'] == -1){
                $this->tuMsg('该订单已取消');
            }else{
                if(false !== D('Hotelorder')->cancel($order_id)){
                    $this->tuMsg('订单取消成功',U('hotels/index',array('st'=>1))); 
                }else{
                    $this->tuMsg('订单取消失败');
                }
            }
        }else{
            $this->tuError('请选择要取消的订单');
        }
    }
    
    //确认入驻
    public function complete($order_id){
        $hotel_id = $this->getHotelId();
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->tuMsg('订单不存在');
            }elseif($order['hotel_id'] != $hotel_id){
                $this->tuMsg('非法操作订单');
            }elseif(($order['online_pay'] == 1&&$order['order_status'] != 1)||($order['online_pay'] == 0&&$order['order_status'] != 0)){
                $this->tuMsg('该订单无法完成');
            }else{

                if(false !== D('Hotelorder')->complete($order_id)){
                    $this->tuMsg('订单入驻操作成功',U('hotels/index',array('st'=>2)));
                }else{
                    $this->tuMsg('订单操作失败');
                }
            }
        }else{
            $this->tuMsg('请选择要完成的订单');
        }
    }
    
    //确认删除订单
    public function delete($order_id){
        $hotel_id = $this->getHotelId();
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->tuMsg('订单不存在');
            }elseif($order['hotel_id'] != $hotel_id){
                $this->tuMsg('非法操作订单');
            }elseif($order['order_status'] != -1){
                $this->tuMsg('订单状态不正确');
            }else{
                if(false !== D('Hotelorder')->save(array('order_id'=>$order_id,'closed'=>1))){
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 6,$status = 11);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 6,$status = 11);
                    $this->tuMsg('订单删除成功',U('hotels/index',array('st'=>1)));
                }else{
                    $this->tuMsg('订单删除失败');
                }
            }
        }else{
            $this->tuMsg('请选择要删除的订单');
        }
    }
	
	
	//同意退款操作
    public function agree_refund($order_id = 0){
        $order_id = I('order_id', 0, 'trim,intval');
        $Hotelorder = D('Hotelorder');
        $hotel_order = $Hotelorder->where('order_id =' . $order_id)->find();
		if (!($detial = $Hotelorder->find($order_id))) {
                $this->tuMsg('该订单不存在');
        }elseif($hotel_order['order_status'] != 3){
				$this->tuMsg('订单状态不正确，无法退款');
		}elseif($detial['shop_id'] != $this->shop_id){
				$this->tuMsg('请不要操作其他商铺的订单');
		}else{
			if (false == $Hotelorder->hotel_refund_user($order_id)) {//退款操作
				$this->tuMsg('非法操作');
			}else{
				$this->tuMsg('已成功退款',U('hotels/index',array('st'=>4)));	
			}
		}
    }
    //拼装数组
    function diffBetweenTwoDays ($day1, $day2){
          $second1 = strtotime($day1);
          $second2 = strtotime($day2);
          if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
          }
          return ($second1 - $second2) / 86400;
    }

    
   

}
