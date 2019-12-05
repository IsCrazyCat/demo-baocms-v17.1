<?php


class TuanAction extends CommonAction{
    private $create_fields = array('shop_id', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'price', 'tuan_price', 'settlement_price', 'num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook');
    private $edit_fields = array('shop_id', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'price', 'tuan_price','num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook');
    protected $tuancates = array();
    public function _initialize(){
        parent::_initialize();
		 if(empty($this->_CONFIG['operation']['tuan'])){
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
        if($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Tuan->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            $val = $Tuan->_format($val);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
	public function delete($tuan_id = 0){
        $tuan_id = (int) $tuan_id;
        $obj = D('Tuan');
        if(empty($tuan_id)){
            $this->tuError('该抢购信息不存在');
        }
        if(!($detail = D('Tuan')->find($tuan_id))){
            $this->tuError('该抢购信息不存在');
        }
        if($detail['shop_id'] != $this->shop_id){
            $this->tuError('非法操作');
        }
        D('Tuan')->save(array('tuan_id' => $tuan_id, 'closed' => 1));
        $this->tuSuccess('删除成功', U('tuan/index'));
    }
	
	
	
	
    public function history(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'end_date' => array('LT', TODAY));
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 25);
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
        $Tuanorder = D('Tuanorder');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')){
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if(isset($_GET['st']) || isset($_POST['st'])){
            $st = (int) $this->_param('st');
            if($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        }else{
            $this->assign('st', 999);
        }
        $count = $Tuanorder->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
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
	
	
    public function used(){
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            if (empty($code)) {
                $this->tuError('请输入抢购券');
            }
            $obj = D('Tuancode');
            $return = array();
            $ip = get_client_ip();
            if (count($code) > 10) {
                $this->tuError('一次最多验证10条抢购券');
            }
            $userobj = D('Users');
            foreach ($code as $key => $var){
                $var = trim(htmlspecialchars($var));
                if(!empty($var)){
                    $detail = $obj->find(array('where' => array('code' => $var)));
                    $shop = D('Shop')->find(array('where' => array('shop_id' => $detail['shop_id'])));
                    if(!empty($detail) && $detail['shop_id'] == $this->shop_id && (int) $detail['is_used'] == 0 && (int) $detail['status'] == 0) {
						$data = array();
						$data['is_used'] = 1;
						$data['worker_id'] = $this->uid;
						$data['used_time'] = NOW_TIME;
						$data['used_ip'] = get_client_ip();
             			if($obj->where(array('code_id'=>$detail['code_id']))->save($data)){
						   $res = $obj->saveShopMoney($detail,$shop);//统一更新
                           if($res == 1){
								$return[$var] = $var;
                                echo '<script>parent.used(' . $key . ',"√验证成功",1);</script>';
                            } else {
                                echo '<script>parent.used(' . $key . ',"√到店付抢购券验证成功，需要现金付款",2);</script>';
                            }
                        }
                    } else {
                        echo '<script>parent.used(' . $key . ',"X该抢购券无效",3);</script>';
                    }
                }
            }
        } else {
            $this->display();
        }
    }
	
	
    public function create(){
        if($this->isPost()){
            $data = $this->createCheck();
            $obj = D('Tuan');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if(empty($details)){
                $this->tuError('抢购详情不能为空');
            }
            if($words = D('Sensitive')->checkWords($details)){
                $this->tuError('详细内容含有敏感词：' . $words);
            }
            $instructions = $this->_post('instructions', 'SecurityEditorHtml');
            if(empty($instructions)){
                $this->tuError('购买须知不能为空');
            }
            if($words = D('Sensitive')->checkWords($instructions)){
                $this->tuError('购买须知含有敏感词：' . $words);
            }
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
            if ($tuan_id = $obj->add($data)){
                $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                //抢购类型是2
                $obj->save(array('tuan_id' => $tuan_id, 'wei_pic' => $wei_pic));
                D('Tuandetails')->add(array('tuan_id' => $tuan_id, 'details' => $details, 'instructions' => $instructions));
                $this->tuSuccess('添加成功', U('tuan/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
	
	
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if(empty($data['cate_id'])){
            $this->tuError('抢购分类不能为空');
        }
        $Tuancate = D('Tuancate')->where(array('cate_id' => $data['cate_id']))->find();
        $parent_id = $Tuancate['parent_id'];
        if ($parent_id == 0){
            $this->tuError('请选择二级分类');
        }
		
        $data['shop_id'] = $this->shop_id;
		$data['city_id'] = $this->shop['city_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['lng'] = $this->shop['lng'];
        $data['lat'] = $this->shop['lat'];
		
		
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('抢购名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('抢购简介不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传图片');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('图片格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->tuError('市场价格不能为空');
        }
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if (empty($data['tuan_price'])) {
            $this->tuError('抢购价格不能为空');
        }
		
		
		//添加
        $data['settlement_price'] = (int) ($data['tuan_price'] - $data['tuan_price'] * $this->tuancates[$data['cate_id']]['rate'] / 1000);
        $data['use_integral'] = (int) $data['use_integral'];
		//抢购检测积分合法性开始
		if (D('Tuan')->check_add_use_integral($data['use_integral'],$data['settlement_price'])) {//传2参数
            //这里暂时保留，后期增加逻辑;
        }else{
			$this->tuError(D('Tuan')->getError(), 3000, true);	  
		}
		//抢购检测积分合法性结束
		
		
		
		
		
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->tuError('库存不能为空');
        }
        $data['sold_num'] = (int) $data['sold_num'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->tuError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->tuError('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('结束时间格式不正确');
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
	
	
    public function setting($tuan_id = 0){
        if($tuan_id = (int) $tuan_id){
            $obj = D('Tuanmeal');
            if(!($detail = D('Tuan')->find($tuan_id))){
                $this->tuError('请选择要设置的抢购');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->tuError('请不要操作别人的抢购');
            }
            if($detail['closed'] != 0){
                $this->tuError('该抢购已被删除');
            }
            $tuan_details = D('Tuandetails')->getDetail($tuan_id);
            if($this->isPost()){
                $name = $this->_post('name', 'htmlspecialchars');
                if (empty($name)){
                    $this->tuError('主套餐名称不能为空');
                }
                $data = $this->_post('data', false);
                $obj->delete(array('where' => array('tuan_id' => $tuan_id)));
                $obj->add(array('tuan_id' => $tuan_id, 'id' => 0, 'name' => $name));
				$data = array_unique($data); 
                foreach ($data as $val) {
                    if (!empty($val['id']) && !empty($val['name'])) {
						if(!($Tuan = D('Tuan')->find($val['id']))) {
							$this->tuError('ID【'.$val['id'].'】错误');
						}
						if($Tuan['shop_id'] != $this->shop_id) {
							$this->tuError('ID【'.$val['id'].'】不属于您的抢购商品');
						}
                        $obj->add(array('tuan_id' => $tuan_id, 'id' => $val['id'], 'name' => $val['name']));
                    }
                }
                $this->tuSuccess('操作成功', U('tuan/setting',array('tuan_id'=>$tuan_id)));
            } else {
                $this->assign('detail', $detail);
                $this->assign('meals', $meals = $obj->where(array('tuan_id' => $tuan_id, 'id' => array('NEQ', 0)))->select());
				$this->assign('count', $count = $obj->where(array('tuan_id' => $tuan_id, 'id' => array('NEQ', 0)))->count());
                $this->assign('name', $obj->where(array('tuan_id' => $tuan_id, 'id' => 0))->find());
                $this->display();
            }
        } else {
            $this->tuError('请选择要设置的抢购');
        }
    }
	
	
    public function edit($tuan_id = 0){
        if ($tuan_id = (int) $tuan_id) {
            $obj = D('Tuan');
            if (!($detail = $obj->find($tuan_id))) {
                $this->tuError('请选择要编辑的抢购');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->tuError('请不要操作别人的抢购');
            }
            if ($detail['closed'] != 0) {
                $this->tuError('该抢购已被删除');
            }
            $tuan_details = D('Tuandetails')->getDetail($tuan_id);
            if ($this->isPost()) {
                $data = $this->editCheck();
                $details = $this->_post('details', 'SecurityEditorHtml');
                if (empty($details)) {
                    $this->tuError('抢购详情不能为空');
                }
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->tuError('详细内容含有敏感词：' . $words);
                }
                $instructions = $this->_post('instructions', 'SecurityEditorHtml');
				
                if (empty($instructions)) {
                    $this->tuError('购买须知不能为空');
                }
                if ($words = D('Sensitive')->checkWords($instructions)) {
                    $this->tuError('购买须知含有敏感词：' . $words);
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
                $data['tuan_id'] = $tuan_id;
                if (!empty($detail['wei_pic'])) {
                    if (true !== strpos($detail['wei_pic'], 'https://mp.weixin.qq.com/')) {
                        $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                        $data['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                    $data['wei_pic'] = $wei_pic;
                }
                $data['audit'] = 0;
                if(false !== $obj->save($data)){
                    D('Tuandetails')->save(array('tuan_id' => $tuan_id, 'details' => $details, 'instructions' => $instructions));
                    $this->tuSuccess('操作成功', U('tuan/index'));
                }
                $this->tuError('操作失败');
            }else{
                $this->assign('detail', $obj->_format($detail));
                $thumb = unserialize($detail['thumb']);
                $this->assign('thumb', $thumb);
				$this->assign('parent_id',D('tuancate')->getParentsId($detail['cate_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('tuan_details', $tuan_details);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的抢购');
        }
    }
	
	
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('抢购分类不能为空');
        }
		 
        $Tuancate = D('Tuancate')->where(array('cate_id' => $data['cate_id']))->find();
        $parent_id = $Tuancate['parent_id'];
        if ($parent_id == 0) {
            $this->tuError('请选择二级分类');
        }
		
        $data['shop_id'] = $this->shop_id;
		$data['city_id'] = $this->shop['city_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['lng'] = $this->shop['lng'];
        $data['lat'] = $this->shop['lat'];
		
		
		
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('商品名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('副标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if(empty($data['photo'])){
            $this->tuError('请上传图片');
        }
        if(!isImage($data['photo'])){
            $this->tuError('图片格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if(empty($data['price'])){
            $this->tuError('市场价格不能为空');
        }
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if(empty($data['tuan_price'])){
            $this->tuError('抢购价格不能为空');
        }
		if($data['tuan_price'] >= $data['price']){
            $this->tuMsg('售价不能大于或者等于市场价');
        }
		
		
		//编辑
        $data['settlement_price'] = (int) ($data['tuan_price'] - $data['tuan_price'] * $this->tuancates[$data['cate_id']]['rate'] / 1000);//编辑时候暂时不写入结算价
        $data['use_integral'] = (int) $data['use_integral'];
		//抢购检测积分合法性开始
		if (D('Tuan')->check_add_use_integral($data['use_integral'],$data['settlement_price'])) {//传2参数
        }else{
			$this->tuError(D('Tuan')->getError(), 3000, true);	  
		}
		//抢购检测积分合法性结束
		//蜂蜜源码二开结束
		
		
		
		
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->tuError('库存不能为空');
        }
        $data['sold_num'] = (int) $data['sold_num'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->tuError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->tuError('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('结束时间格式不正确');
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
	
	
	//选择分类
	public function child($parent_id=0){
        $datas = D('Tuancate')->fetchAll();
        $str = '';
        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
                foreach($datas as $var2){
                    if($var2['parent_id'] == $var['cate_id']){
                        $str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
                        foreach($datas as $var3){
                            if($var3['parent_id'] == $var2['cate_id']){
                               $str.='<option value="'.$var3['cate_id'].'">&nbsp;&nbsp;--'.$var3['cate_name'].'</option>'."\n\r"; 
                            }
                            
                        }
                    }  
                }
                             
            }           
        }
        echo $str;die;
    }
	
	  public function ajax($cate_id,$goods_id=0){
        if(!$cate_id = (int)$cate_id){
            $this->error('请选择正确的分类');
        }
        if(!$detail = D('Tuancate')->find($cate_id)){
            $this->error('请选择正确的分类');
        }
        $this->assign('cate',$detail);
        $this->assign('attrs',D('Goodscateattr')->order(array('orderby'=>'asc'))->where(array('cate_id'=>$cate_id))->select());
        if($goods_id){
            $this->assign('detail',D('Goods')->find($goods_id));
            $this->assign('maps',D('GoodsCateattr')->getAttrs($goods_id));
        }
        $this->display();
    }
	
	
	// 抢购劵列表
    public function code(){
        $Tuancode = D('Tuancode');
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
        $count = $Tuancode->where($map)->count();
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = $Tuancode->where($map)->order(array('code_id' => 'desc','used_time'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
	//验证记录
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
                $map['code_id|code'] = array('LIKE', '%' . $keyword . '%');
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
            $user_ids[$val['user_id']] = $val['user_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
        $this->display();
    }
	
	
	 public function refund($code_id = 0){
        $code_id = (int) $code_id;
        $detail = D('Tuancode')->find($code_id);
        $tuan_order = D('Tuanorder');
        $order = $tuan_order->find($detail['order_id']);
        if ($detail['status'] == 1 && (int) $detail['is_used'] === 0) {
            if ($order['status'] != 3) {
                $this->tuError('操作错误');
            }
			if ($order['shop_id'] != $this->shop_id) {
                $this->tuError('非法操作');
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
            $this->tuSuccess('退款成功', U('tuan/code'));
        } else {
            $this->tuError('当前订单状态不正确');
        }
    }
	
	
	public function detail($order_id){
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
		$this->assign('users', D('Users')->find($detail['user_id']));
        $this->assign('tuans', D('Tuan')->find($detail['tuan_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
	
}