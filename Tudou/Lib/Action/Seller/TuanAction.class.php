<?php
class TuanAction extends CommonAction{
    private $create_fields = array('shop_id', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'price', 'tuan_price', 'settlement_price', 'num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook');
    private $edit_fields = array('shop_id', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'price', 'tuan_price','num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook');
    protected $tuancates = array();
    public function _initialize(){
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['tuan'])) {
            $this->error('抢购功能已关闭');
            die;
        }
     
        $this->tuancates = D('Tuancate')->fetchAll();
        $this->assign('tuancates', $this->tuancates);
   
    }
    public function index(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 0, 'end_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Tuan->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val = $Tuan->_format($val);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
    //上单添加开始
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Tuan');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if (empty($details)) {
                $this->tuMsg('抢购详情不能为空');
            }
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->tuMsg('详细内容含有敏感词：' . $words);
            }
            $instructions = $this->_post('instructions', 'SecurityEditorHtml');
            if (empty($instructions)) {
                $this->tuMsg('购买须知不能为空');
            }
            if ($words = D('Sensitive')->checkWords($instructions)) {
                $this->tuMsg('购买须知含有敏感词：' . $words);
            }
            $thumb = $this->_param('thumb', false);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            $data['thumb'] = serialize($thumb);
            if ($tuan_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                //抢购类型是2
                $obj->save(array('tuan_id' => $tuan_id, 'wei_pic' => $wei_pic));
                D('Tuandetails')->add(array('tuan_id' => $tuan_id, 'details' => $details, 'instructions' => $instructions));
                $this->tuMsg('添加成功，请等待网站管理员审核后即可显示', U('tuan/index'));
            }
            $this->error('操作失败');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuMsg('抢购分类不能为空');
        }
        $data['shop_id'] = $this->shop_id;
		$data['city_id'] = $this->shop['city_id'];
		$data['area_id'] = $this->shop['area_id'];
		$data['business_id'] = $this->shop['business_id'];
		
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuMsg('抢购名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuMsg('抢购副标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuMsg('请上传图片');
        }
        if (!isImage($data['photo'])) {
            $this->tuMsg('图片格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->tuMsg('市场价格不能为空');
        }
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if (empty($data['tuan_price'])) {
            $this->tuMsg('抢购价格不能为空');
        }
        $data['settlement_price'] = (int) ($data['tuan_price'] - $data['tuan_price'] * $this->tuancates[$data['cate_id']]['rate'] / 1000);
        $data['use_integral'] = (int) $data['use_integral'];
        //抢购检测积分合法性开始
		if (D('Tuan')->check_add_use_integral($data['use_integral'],$data['settlement_price'])) {//传2参数
            //这里暂时保留，后期增加逻辑;
        }else{
			$this->tuMsg(D('Tuan')->getError());	  
		}
		//抢购检测积分合法性结束
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->tuMsg('库存不能为空');
        }
        $data['sold_num'] = (int) $data['sold_num'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->tuMsg('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->tuMsg('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuMsg('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuMsg('结束时间格式不正确');
        }
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_new'] = (int) $data['is_new'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['is_multi'] = (int) $data['is_multi'];
        $data['freebook'] = (int) $data['freebook'];
        $data['is_return_cash'] = (int) $data['is_return_cash'];
        $data['fail_date'] = htmlspecialchars($data['fail_date']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		$data['audit'] = 1;
        return $data;
    }
    public function edit($tuan_id = 0){
        if ($tuan_id = (int) $tuan_id) {
            $obj = D('Tuan');
            if (!($detail = $obj->find($tuan_id))) {
                $this->error('请选择要编辑的抢购');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要操作别人的抢购');
            }
            if ($detail['closed'] != 0) {
                $this->error('该抢购已被删除');
            }
            $tuan_details = D('Tuandetails')->getDetail($tuan_id);
            if ($this->isPost()) {
                $data = $this->editCheck();
				//二开图片开始
				$thumb = $this->_param('thumb', false);
                foreach($thumb as $k => $val){
                    if(empty($val)){
                        unset($thumb[$k]);
                    }
                    if(!isImage($val)){
                        unset($thumb[$k]);
                    }
                }
                $data['thumb'] = serialize($thumb);
				//二开图片结束
				
                $data['tuan_id'] = $tuan_id;
                if(!empty($detail['wei_pic'])){
                    if (true !== strpos($detail['wei_pic'], "https://mp.weixin.qq.com/")) {
                        $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                        $data['wei_pic'] = $wei_pic;
                    }
                }else{
                    $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                    $data['wei_pic'] = $wei_pic;
                }
                $data['audit'] = 0;
                if(false !== $obj->save($data)){
                    $this->tuMsg('操作成功', U('tuan/index'));
                }
                $this->tuMsg('操作失败');
            }else{
                $this->assign('detail', $obj->_format($detail));
				$thumb = unserialize($detail['thumb']);
                $this->assign('thumb', $thumb);
				$this->assign('parent_id',D('tuancate')->getParentsId($detail['cate_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('tuan_details', $tuan_details);
                $this->display();
            }
        }else{
            $this->error('请选择要编辑的抢购');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuMsg('抢购分类不能为空');
        }
        $data['shop_id'] = $this->shop_id;
		$data['city_id'] = $this->shop['city_id'];
		$data['area_id'] = $this->shop['area_id'];
		$data['business_id'] = $this->shop['business_id'];
		
        $data['lng'] = $this->shop['lng'];
        $data['lat'] = $this->shop['lat'];
     
        $data['title'] = htmlspecialchars($data['title']);
        if(empty($data['title'])){
            $this->tuMsg('商品名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if(empty($data['intro'])){
            $this->tuMsg('副标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if(empty($data['photo'])){
            $this->tuMsg('请上传图片');
        }
        if(!isImage($data['photo'])){
            $this->tuMsg('图片格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if(empty($data['price'])){
            $this->tuMsg('市场价格不能为空');
        }
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if(empty($data['tuan_price'])){
            $this->tuMsg('抢购价格不能为空');
        }
		if($data['tuan_price'] >= $data['price']){
            $this->tuMsg('售价不能大于或者等于市场价');
        }
		
        $data['settlement_price'] = (int) ($data['tuan_price'] - $data['tuan_price'] * $this->tuancates[$data['cate_id']]['rate'] / 1000);//编辑的时候不上传结算价格
        $data['use_integral'] = (int) $data['use_integral'];
        //抢购检测积分合法性开始
		if (D('Tuan')->check_add_use_integral($data['use_integral'],$data['settlement_price'])){//传2参数
            //这里暂时保留，后期增加逻辑;
        }else{
			$this->tuMsg(D('Tuan')->getError());	  
		}
		//抢购检测积分合法性结束
		
		
		
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->tuMsg('库存不能为空');
        }
        $data['sold_num'] = (int) $data['sold_num'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if(empty($data['bg_date'])){
            $this->tuMsg('开始时间不能为空');
        }
        if(!isDate($data['bg_date'])){
            $this->tuMsg('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if(empty($data['end_date'])){
            $this->tuMsg('结束时间不能为空');
        }
        if(!isDate($data['end_date'])){
            $this->tuMsg('结束时间格式不正确');
        }
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_new'] = (int) $data['is_new'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['is_multi'] = (int) $data['is_multi'];
        $data['freebook'] = (int) $data['freebook'];
        $data['is_return_cash'] = (int) $data['is_return_cash'];
        $data['fail_date'] = htmlspecialchars($data['fail_date']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    //上单添加编辑结束
    public function history(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 1);
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Tuan->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val = $Tuan->_format($val);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function order(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
		$keyword = (int) $this->_param('keyword');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', linkto('tuan/loaddata', array('aready'=>$aready,'keyword'=>$keyword,'t' => NOW_TIME, 'p' => '0000')));
        $this->display();
    }
	
	public function loaddata(){
        $Tuanorder = D('Tuanorder');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('shop_id' => $this->shop_id);
        
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $keyword = intval($keyword);
            if (!empty($keyword)) {
                $map['order_id'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
        }
        if (isset($_GET['aready']) || isset($_POST['aready'])) {
			$aready = (int) $this->_param('aready');
			if ($aready != 999) {
				$map['status'] = $aready;
			}
			$this->assign('aready', $aready);
		} else {
			$map['status'] = 0;
			$this->assign('aready', 999);
		}
        $count = $Tuanorder->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tuanorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = $tuan_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $user_ids[$val['user_id']] = $val['user_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuan', D('Tuan')->itemsByIds($tuan_ids));

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function usedok(){
        $Tuancode = D('Tuancode');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'is_used' => '1');
        if (strtotime($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && strtotime($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            if (!empty($bg_time) && !empty($end_date)) {
                $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            }
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                if (!empty($bg_time)) {
                    $map['create_time'] = array('EGT', $bg_time);
                }
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                if (!empty($end_time)) {
                    $map['create_time'] = array('ELT', $end_time);
                }
                $this->assign('end_date', $end_date);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $keyword = intval($keyword);
            if (!empty($keyword)) {
                $map['code'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
        }
        $count = $Tuancode->where($map)->count();
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = $Tuancode->where($map)->order(array('used_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $user_ids[$val['user_id']] = $val['worker_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
        $this->display();
    }
	public function usedok_detail($code_id){
        $code_id = (int) $code_id;
        if (empty($code_id) || !($detail = D('Tuancode')->find($code_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('请不要操作他人的订单');
        }
		$detail['tuan'] = D('Tuan')->where(array('tuan_id'=>$detail['tuan_id']))->find(); 
		$detail['shopworker'] = D('Shopworker')->where(array('user_id'=>$detail['worker_id']))->find(); 
        $this->assign('detail', $detail);
        $this->display();
    }
    public function delete($tuan_id = 0){
        $tuan_id = (int) $tuan_id;
        $obj = D('Tuan');
        if (empty($tuan_id)) {
            $this->error('该抢购信息不存在');
        }
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('该抢购信息不存在');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('非法操作');
        }
        $obj->save(array('tuan_id' => $tuan_id, 'closed' => 1));
        $this->success('下单成功', U('tuan/index'));
    }
    public function shelves($tuan_id = 0)
    {
        $tuan_id = (int) $tuan_id;
        $obj = D('Tuan');
        if (empty($tuan_id)) {
            $this->error('该抢购信息不存在');
        }
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('该抢购信息不存在');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('非法操作');
        }
        $obj->save(array('tuan_id' => $tuan_id, 'closed' => 0));
        $this->success('上单成功', U('tuan/index'));
    }
    //抢购分类
    public function child($parent_id = 0)
    {
        $datas = D('Tuancate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['cate_id']) {
                                $str .= '<option value="' . $var3['cate_id'] . '">  --' . $var3['cate_name'] . '</option>' . "\n\r";
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
    public function detail($order_id)
    {
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Tuanorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('请不要操作他人的订单');
        }
        if (!($dianping = D('Tuandianping')->where(array('order_id' => $order_id, 'user_id' => $this->uid))->find())) {
            $detail['dianping'] = 0;
        } else {
            $detail['dianping'] = 1;
        }
        $this->assign('tuans', D('Tuan')->find($detail['tuan_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
	
    public function used(){
        $counts['tuan_order_code_is_used'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count();
        //未验证
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            $c = 0;
            foreach ($code as $k => $v) {
                if ($v) {
                    $c = $c + 1;
                }
            }
            if (empty($c)) {
                $this->tuMsg('请输入抢购券!');
            }
            $obj = D('Tuancode');
            $return = array();
            $ip = get_client_ip();
            if (count($code) > 10) {
                $this->tuMsg('一次最多验证10条抢购券');
            }
            $userobj = D('Users');
            foreach ($code as $key => $var) {
                $var = trim(htmlspecialchars($var));
                if (!empty($var)) {
					$detail = $obj->find(array('where' => array('code' => $var)));
                    $shop = D('Shop')->find(array('where' => array('shop_id' => $detail['shop_id'])));
                    if (!empty($detail) && $detail['shop_id'] == $this->shop_id && (int) $detail['is_used'] == 0 && (int) $detail['status'] == 0) {
						$data = array();
						$data['is_used'] = 1;
						$data['worker_id'] = $this->uid;
						$data['used_time'] = NOW_TIME;
						$data['used_ip'] = get_client_ip();
             			if($obj->where(array('code_id'=>$detail['code_id']))->save($data)){
						   $res = $obj->saveShopMoney($detail,$shop);//统一更新
                           if($res == 1){
								$return[$var] = $var;
                                $this->tuMsg($key . '验证成功!', U('tuan/used'));
                            } else {
                                 $this->tuMsg($key . '到店付抢购券验证成功!', U('tuan/used'));
                            }
                        }
                    } else {
                        $this->tuMsg($key . 'X该抢购券无效!', U('tuan/used'));
                    }
                }
            }
        } else {
            $this->assign('counts', $counts);
            $this->display();
        }
    }
	
	
	
	 //抢购劵列表
	 public function code(){
        $obj = D('Tuancode');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['code_id|code'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('code_id' => 'desc','used_time'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $tuan_ids = $user_ids = array();
        foreach ($list as $val) {
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
			$user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
		$this->assign('users', D('Users')->itemsByIds($user_ids));
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display(); 
    }
	
	//退款单个
	public function refund($code_id = 0){
        $code_id = (int) $code_id;
        $detail = D('Tuancode')->find($code_id);
        $tuan_order = D('Tuanorder');
        $order = $tuan_order->find($detail['order_id']);
        if ($detail['status'] == 1 && (int) $detail['is_used'] === 0) {
            if ($order['status'] != 3) {
                $this->tuMsg('操作错误');
            }
			if ($order['shop_id'] != $this->shop_id) {
                $this->tuMsg('非法操作');
            }
            $tuan_order->save(array('order_id' => $detail['order_id'], 'status' => 4)); //改变订单状态
            if (D('Tuancode')->save(array('code_id' => $code_id, 'status' => 2))) {//将内容变成
                $obj = D('Users');
                if ($detail['real_money'] > 0) {
                    $obj->addMoney($detail['user_id'], $detail['real_money'], '抢购券退款:' . $detail['code']);
                }
                if ($detail['real_integral'] > 0) {
                    $obj->addIntegral($detail['user_id'], $detail['real_integral'], '抢购券退款:' . $detail['code']);
                }
            }
            $where['tuan_id'] = $detail['tuan_id'];
            $tuan_num = D("Tuanorder")->where($where)->getField("num");
			D('Sms')->tuancode_refund_user($code_id);// 退款成功通知用户
            D("Tuan")->where($where)->setInc("num", $tuan_num);// 修复退款后增加库存
            $this->tuMsg('退款成功', U('tuan/code'));
        } else {
            $this->tuMsg('当前订单状态不正确');
        }
    }
	
	
}