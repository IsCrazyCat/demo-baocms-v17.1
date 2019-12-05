<?php
class CouponAction extends CommonAction {
	public function _initialize() {
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['coupon'])){
			$this->error('优惠劵功能已关闭');die;
		}
        $this->assign('shopcates',$shopcates = D('Shopcate')->fetchAll());
    }
    public function main() {
           $news= (int) $this->_param('news');
         if ($news == 1) {
            $orderby = array('coupon_id' => 'desc');
        }
        if ($news == 2) {
            $orderby = array('downloads' => 'desc');
        }
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $Coupon = D('Coupon');
        $map = array('audit' => 1,'city_id'=>$this->city_id, 'closed' => 0, 'expire_date' => array('EGT', TODAY));
        $list = $Coupon->where($map)->order($orderby)->limit(0,10)->select();
        $shop_ids = array();
        foreach ($list as $key => $v) {
            $shop_ids[$v['shop_id']] = $v['shop_id'];
        }
        $shops = D('Shop')->itemsByIds($shop_ids);
           foreach ($shops as $k => $val) {
            $shops[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('list', $list);
        $this->assign('shops', $shops);
        $this->display();
    }

    public function index() {
		
		$keyword = $this->_param('keyword', 'htmlspecialchars');
		if (!empty($keyword)) {
			$this->assign('keyword', $keyword);
		}
        $cat = (int) $this->_param('cat');
		if (!empty($cat)) {
			$this->assign('cat', $cat);
		}
		$area_id = (int) $this->_param('area_id');
		if (!empty($area_id)) {
			$this->assign('area_id', $area_id);
		}
        
		if (!empty($order)) {
			$order = (int) $this->_param('order');
			$this->assign('order', $order);
		}
        $this->assign('nextpage', LinkTo('coupon/loaddata',array('keyword'=>$keyword,'cat'=>$cat,'area_id'=>$area_id,'order'=>$order,'t' => NOW_TIME,'p' => '0000')));
		$this->assign('linkArr',$linkArr);
        $this->display(); 
    }

    public function loaddata() {
        $obj = D('Coupon');
        import('ORG.Util.Page'); 
        $map = array('audit' => 1,'city_id'=>$this->city_id, 'closed' => 0, 'expire_date' => array('EGT', TODAY));
		
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
		$this->assign('keyword', $keyword);
		
        
		
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $catids = D('Shopcate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cat;
            }
        }
		$this->assign('cat', $cat );
		
		$area_id = (int) $this->_param('area_id');
		if($area_id) {
            $map['area_id'] = $area_id;
        }
		$this->assign('area_id', $area_id );
		
        $order = (int) $this->_param('order');
        $orderby = '';
        switch ($order) {
            case '1':
                $orderby = array('views' => 'asc');
                break;
            case '2':
                $orderby = array('downloads' => 'asc');
                break;
            case '3':
                $orderby = array('num' => 'asc');
                break;
            case '4':
                $orderby = array('create_time' => 'asc');
                break;
            default:
                $orderby = array('create_time' => 'desc', 'coupon_id' => 'desc');
                break;
        }
		$this->assign('order', $order);
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }

        $list = $obj->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['end_time'] = strtotime($val['end_date']) - NOW_TIME + 86400;
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $shops = D('Shop')->itemsByIds($shop_ids);
            $ids = array();
            foreach ($shops as $k => $val) {
                $shops[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
                $d = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
                $ids[$d][] = $k; 
            }
            ksort($ids);
            $showshops = array();
            foreach ($ids as $arr1) {
                foreach ($arr1 as $val) {
                    $showshops[$val] = $shops[$val];
                }
            }
            $this->assign('shops', $showshops);
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function detail() {
        $coupon_id = (int) $this->_get('coupon_id');
        if (empty($coupon_id)) {
            $this->error('该优惠券不存在');
            die;
        }
        $Coupon = D('Coupon');
        if (!$detail = $Coupon->find($coupon_id)) {
            $this->error('该优惠券不存在');
            die;
        }
        $Coupon->updateCount($coupon_id, 'views');
        $shop = D('Shop')->find($detail['shop_id']);
        $this->assign('shop', $shop);
        $this->assign('ex', D('Shopdetails')->find($detail['shop_id']));
        $this->assign('detail', $detail);
        $this->seodatas['shop_name'] = $shop['shop_name'];
        $this->seodatas['title'] = $detail['title'];
        $this->display(); // 输出模板 
    }

    public function download() {
        if (empty($this->uid)) {
           $this->error('登录状态失效!', U('passport/login'));
        }
        if (empty($this->member['mobile'])) {
            $this->error('亲还没有验证手机号码', U('user/information/index'));
        }
        $coupon_id = (int) $this->_get('coupon_id');
		$type = (int) $this->_get('type');
        if (empty($coupon_id)) {
            $this->error('该优惠券不存在');
            die;
        }
        $Coupon = D('Coupon');
        if (!$detail = $Coupon->find($coupon_id)) {
            $this->error('该优惠券不存在');
            die;
        }

        if ($detail['expire_date'] < TODAY) {
            $this->error('该优惠券已经过期');
        }
        
         if($detail['num'] <=0){
            $this->error('该优惠券已经下载完了');
        }
        
        if($detail['limit_num']){
            $count = D('Coupondownload')->where(array( 'coupon_id' => $coupon_id,'user_id'=>  $this->uid))->count();
            if($count+1 > $detail['limit_num']){
                $this->error('您已经超过下载该优惠券的限制了');
            }
        }
        $shop = D('Shop')->find($detail['shop_id']);
        $code = D('Coupondownload')->getCode();
        $data = array(
            'user_id' => $this->uid,
            'shop_id' => $detail['shop_id'],
            'coupon_id' => $coupon_id,
            'create_time' => NOW_TIME,
            'mobile' => $this->member['mobile'],
            'create_ip' => get_client_ip(),
            'code' => $code,
        );
        if ($download_id = D('Coupondownload')->add($data)) {
            D('Coupon')->updateCount($coupon_id, 'downloads');
            D('Coupon')->updateCount($coupon_id,'num',-1);
			D('Sms')->sms_coupon_user($download_id,1);
			if($type == 1){
				$this->success('恭喜您成功下载优惠劵');
			}else{
				$this->success('恭喜您下载成功', U('user/coupon/index'));
			}
            
        }
        $this->error('下载失败');
    }

}
