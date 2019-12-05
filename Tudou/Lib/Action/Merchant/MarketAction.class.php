<?php
class MarketAction extends CommonAction{
    private $create_fields = array('shop_id', 'distribution', 'is_open', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money', 'logistics', 'since_money', 'sold_num', 'month_num', 'intro', 'orderby');
    protected $market;
    public function _initialize(){
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['market'])) {
            $this->error('菜市场功能已关闭');
            die;
        }
        $getMarketCate = D('Market')->getMarketCate();
        $this->assign('getMarketCate', $getMarketCate);
        $this->market = D('Market')->find($this->shop_id);
        if (empty($this->market) && ACTION_NAME != 'apply') {
            $this->error('您还没有入住菜市场频道', U('market/apply'));
        }
        if (!empty($this->market) && $this->market['audit'] == 0) {
            $this->error('亲，您的申请正在审核中');
        }
        $this->assign('market', $this->market);
    }
    public function index(){
		$file['small_file'] = D('Market')->get_file_Code($this->shop_id,8);//生成二维码
		$file['middle_file'] = D('Market')->get_file_Code($this->shop_id,15);//生成二维码
		$file['big_file'] = D('Market')->get_file_Code($this->shop_id,100);//生成二维码
        $this->assign('file', $file);
        $this->display();
    }
    public function open(){
        $is_open = (int) $_POST['is_open'];
		$is_pay = (int) $_POST['is_pay'];
		$is_daofu = (int) $_POST['is_daofu'];
		$is_coupon = (int) $_POST['is_coupon'];
		$is_new = (int) $_POST['is_new'];
		$full_money = (int) ($_POST['full_money']*100);
		$new_money = (int) ($_POST['new_money']*100);
		
		$is_full = (int) $_POST['is_full'];
		$order_price_full_1 = (int) ($_POST['order_price_full_1']*100);
		$order_price_reduce_1 = (int) ($_POST['order_price_reduce_1']*100);
		$order_price_full_2 = (int) ($_POST['order_price_full_2']*100);
		$order_price_reduce_2 = (int) ($_POST['order_price_reduce_2']*100);
		if($is_full){
			if($order_price_full_1 == 0 || $order_price_reduce_1 == 0){
				$this->tuError('满多少1或者减多少1必填或者填写错误');
			}
			if($order_price_reduce_1 >= $order_price_full_1){
				$this->tuError('减去多少1不能大于满多少1');
			}
			if($order_price_full_2){
				if($order_price_full_2 == 0 || $order_price_reduce_2 == 0){
					$this->tuError('满多少1或者减多少1必填或者填写错误');
				}
				if($order_price_reduce_2 >= $order_price_full_2){
					$this->tuError('减去多少1不能大于满多少2');
				}
				if($order_price_full_1 >= $order_price_full_2){
					$this->tuError('满多少1不能大于满多少2');
				}
			}
		}
		
		$logistics = (int) ($_POST['logistics']*100);
		$logistics_full = (int) ($_POST['logistics_full']*100);
		$busihour = $_POST['busihour'];
        $is_radius = $_POST['is_radius'];
		$given_distribution = $_POST['given_distribution'];
		if($given_distribution !=0){
			if (!($Deliver = D('Delivery')->where(array('id'=>$given_distribution))->find())) {
				$this->tuError('不存在配送员ID');
			}
	    }
		$is_voice = (int) $_POST['is_voice'];
		$is_refresh = (int) $_POST['is_refresh'];
		$is_refresh_second = $_POST['is_refresh_second'];
		$tags = $_POST['tags'];
        D('Market')->save(array(
			'shop_id' => $this->shop_id, 
			'is_open' => $is_open, 
			'is_pay' => $is_pay, 
			'is_daofu' => $is_daofu, 
			'is_coupon' => $is_coupon, 
			'is_new' => $is_new, 
			'full_money' => $full_money, 
			'new_money' => $new_money, 
			'is_full' => $is_full, 
			'order_price_full_1' => $order_price_full_1, 
			'order_price_reduce_1' => $order_price_reduce_1, 
			'order_price_full_2' => $order_price_full_2, 
			'order_price_reduce_2' => $order_price_reduce_2, 
			'logistics' => $logistics, 
			'logistics_full' => $logistics_full, 
			'busihour' => $busihour, 
			'is_radius' => $is_radius,
			'given_distribution' => $given_distribution,
			'is_print_deliver' => $is_print_deliver,
			'is_voice' => $is_voice,
			'is_refresh' => $is_refresh,
			'is_refresh_second' => $is_refresh_second,
			'tags' => $tags
		));
        $this->tuSuccess('操作成功', U('market/index'));
    }
    public function apply(){
        $this->assign('area', D('Area')->fetchAll());
        $this->assign('city', D('City')->fetchAll());
        if($this->isPost()){
            $data = $this->applyCheck();
            $obj = D('Market');
            $cate = $this->_post('cate', false);
            $cate = implode(',', $cate);
            $data['cate'] = $cate;
            if($obj->add($data)){
                $this->tuSuccess('添加成功', U('market/index'));
            }
            $this->tuError('操作失败');
        }else{
            $this->display();
        }
    }
	
    private function applyCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = $this->shop_id;
        if (empty($data['shop_id'])) {
            $this->tuError('ID不能为空');
        }
        if (!($shop = D('Shop')->find($data['shop_id']))) {
            $this->tuError('商家不存在');
        }
        $data['shop_name'] = $shop['shop_name'];
        $data['lng'] = $shop['lng'];
        $data['lat'] = $shop['lat'];
        $data['area_id'] = $shop['area_id'];
        $data['city_id'] = $shop['city_id'];
        $data['is_open'] = (int) $data['is_open'];
        $data['is_pay'] = (int) $data['is_pay'];
        $data['is_fan'] = (int) $data['is_fan'];
        $data['fan_money'] = (int) ($data['fan_money'] * 100);
        $data['is_new'] = (int) $data['is_new'];
        $data['full_money'] = (int) ($data['full_money'] * 100);
        $data['new_money'] = (int) ($data['new_money'] * 100);
        $data['logistics'] = (int) ($data['logistics'] * 100);
        $data['since_money'] = (int) ($data['since_money'] * 100);
        $data['distribution'] = (int) $data['distribution'];
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('说明不能为空');
        }
        return $data;
    }
}