<?php
class CouponAction extends CommonAction{
	
	public function _initialize() {
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['coupon'])) {
            $this->error('优惠劵功能已关闭');
            die;
        }
    }
	
	
    public function index(){
        $Coupondownload = D('Coupondownload');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $status = (int) $this->_param('status');
        switch ($status) {
            case 1:
                break;
            case 2:
                $map['is_used'] = 0;
                break;
            case 3:
                $map['is_used'] = 1;
                break;
        }
        $this->assign('status', $status);
        $count = $Coupondownload->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Coupondownload->where($map)->order(array('download_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $coupons = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            if ($val['coupon_id']) {
                $coupons[$val['coupon_id']] = $val['coupon_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val['used_ip_area'] = $this->ipToArea($val['used_ip']);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        if ($coupons) {
            $this->assign('coupons', D('Coupon')->itemsByIds($coupons));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function delete($download_id = 0)
    {
        if (is_numeric($download_id) && ($download_id = (int) $download_id)) {
            $obj = D('Coupondownload');
            if (!($detial = $obj->find($download_id))) {
                $this->tuError('该优惠券不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->tuError('请不要操作他人的优惠券');
            }
            $obj->delete($download_id);
            $this->tuSuccess('删除成功', U('coupon/index'));
        } else {
            $this->tuError('请选择要删除的优惠券');
        }
    }
}