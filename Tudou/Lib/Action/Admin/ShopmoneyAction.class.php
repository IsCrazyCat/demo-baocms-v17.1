<?php
class ShopmoneyAction extends CommonAction{
	
	 public function _initialize() {
        parent::_initialize(); 
		$this->assign('types', $types = D('Shopmoney')->getType());
    }
	
	
    public function index(){
        $obj = D('Shopmoney');
        import('ORG.Util.Page');
        $map = array();
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date) + 86400;
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date) + 86400;
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
		
		if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
		if(isset($_GET['type']) || isset($_POST['type'])) {
            $type = $this->_param('type', 'htmlspecialchars');
            if (!empty($type)) {
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        } else {
            $this->assign('type', 0);
        }
		
        $count = $obj->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('money_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			
			if($City = D('City')->find($val['city_id'])){
				$list[$k]['city_name'] = $City['name'];
			}
			if($Area = D('Area')->find($val['area_id'])){
				$list[$k]['area_name'] = $Area['area_name'];
			}
			$type = $obj->get_money_type($val['type']);
            $list[$k]['type'] = $type;
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('money',$money = $obj->where($map)->sum('money'));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function tjmonth(){
        $Shopmoney = D('Shopmoney');
        import('ORG.Util.Page');
        if ($month = $this->_param('month', 'htmlspecialchars')) {
            $this->assign('month', $month);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $Shopmoney->tjmonthCount($month, $shop_id);
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopmoney->tjmonth($month, $shop_id, $Page->firstRow, $Page->listRows);
        $shop_ids = array();
        foreach ($list as $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function tjyear(){
        $Shopmoney = D('Shopmoney');
        import('ORG.Util.Page');
        if ($year = $this->_param('year', 'htmlspecialchars')) {
            $this->assign('year', $year);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $Shopmoney->tjyearCount($year, $shop_id);
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopmoney->tjyear($year, $shop_id, $Page->firstRow, $Page->listRows);
        $shop_ids = array();
        foreach ($list as $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function tjday(){
        $Shopmoney = D('Shopmoney');
        import('ORG.Util.Page');
        if ($day = $this->_param('day', 'htmlspecialchars')) {
            $this->assign('day', $day);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $Shopmoney->tjdayCount($day, $shop_id);
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopmoney->tjday($day, $shop_id, $Page->firstRow, $Page->listRows);
        $shop_ids = array();
        foreach ($list as $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $add = array('create_time' => NOW_TIME, 'create_ip' => get_client_ip());
            if (!$data['shop_id']) {
                $this->tuError('请选择商家');
            }
            $add['shop_id'] = (int) $data['shop_id'];
            if (!$data['money']) {
                $this->tuError('请数据MONEY');
            }
            $add['money'] = (int) ($data['money'] * 100);
            if (!$data['type']) {
                $this->tuError('请选择类型');
            }
            $add['type'] = htmlspecialchars($data['type']);
            if (!$data['order_id']) {
                $this->tuError('请填写原始订单');
            }
            $add['order_id'] = (int) $data['order_id'];
            if (!$data['intro']) {
                $this->tuError('请填写说明');
            }
            $add['intro'] = htmlspecialchars($data['intro']);
            D('Shopmoney')->add($add);
            $shop = D('Shop')->find($add['shop_id']);
            D('Users')->addMoney($shop['user_id'], $add['money'], $add['intro']);
            $this->tuSuccess('操作成功', U('shopmoney/index'));
        } else {
            $this->display();
        }
    }
}