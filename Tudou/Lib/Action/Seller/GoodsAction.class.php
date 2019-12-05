<?php
class GoodsAction extends CommonAction{

    private $create_fields = array('title','photo','cate_id','guige','num','is_reight','weight','kuaidi_id','shopcate_id','price','mall_price','use_integral','end_date','details');
    private $edit_fields = array('title','photo','cate_id','guige','num','is_reight','weight','kuaidi_id','shopcate_id','price','mall_price','use_integral','end_date','details');
    public function _initialize(){
        parent::_initialize();
        $this->autocates = D('Goodsshopcate')->where(array('shop_id' => $this->shop_id))->select();
        $this->assign('autocates', $this->autocates);
		$this->GoodsCates = D('Goodscate')->fetchAll();
        $this->assign('GoodsCates', $this->GoodsCates);
    }

    public function get_select(){
        if (IS_AJAX) {
            $pid = I('pid', 0, 'intval,trim');
            $gc = D('GoodsCate');
            $list = $gc->where('parent_id =' . $pid)->select();
            if ($pid == 0) {
                $this->ajaxReturn(array('status' => 'success', 'list' => ''));
            }
            if ($list) {
                $l = '';
                foreach ($list as $k => $v) {
                    $l = $l . '<option value=' . $v['cate_id'] . ' style="color:#333333;">' . $v['cate_name'] . '</option>';
                }
                $this->ajaxReturn(array('status' => 'success', 'list' => $l));
            }
        }
    }
	
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goods');
            if ($goods_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($goods_id, 3);
                $obj->save(array('goods_id' => $goods_id, 'wei_pic' => $wei_pic));

				$photos = $this->_post('photos', false);
                if (!empty($photos)) {
                    D('Goodsphoto')->upload($goods_id, $photos);
                }
				
				$this->shuxin($goods_id);//添加规格
				$this->saveGoodsAttr($goods_id,$_POST['goods_type']); //更新商品属性
				
                $this->tuMsg('添加成功,请等待审核', U('mart/index'));
            }
            $this->tuMsg('操作失败');
        } else {
            $this->assign('cates', D('Goodscate')->fetchAll());
			$this->assign('kuaidi', D('Pkuaidi')->where(array('shop_id'=>$this->shop_id,'type'=>goods))->select());
			$this->assign('goodsInfo',D('Goods')->where('goods_id='.I('GET.id',0))->find());  // 商品详情   
            $this->assign('goodsType',M("TpGoodsType")->select());
            $this->display();
        }
    }
	
	//商品上架下架
    public function update($goods_id = 0){
        if($goods_id = (int) $goods_id){
			if(!($detail = M('Goods')->find($goods_id))){
				$this->ajaxReturn(array('code'=>'0','msg'=>'请选择要操作的商品'));
			}
			$data = array('closed' =>0,'goods_id' => $goods_id);
			$intro = '上架商品成功';
			if($detail['closed'] == 0){
				$data['closed'] = 1;
				$intro = '下架商品成功';
			}
			if(M('Goods')->save($data)){
				$this->ajaxReturn(array('code' => '1', 'msg' => $intro,'url'=>U('mart/index')));
			}
        }else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'请选择要操作的商品'));
        }
    }
	
	//编辑或者添加分销通用
	public function profit($goods_id){  
		if($this->shop['is_profit'] != 1){
            $this->error('您尚未开通分销权限');
        }
		$profit = M('GoodsProfit')->where(array('goods_id'=>$goods_id))->find();
		
		if($this->isPost()){
            $data = $this->checkFields($this->_post('data', false), array('profit_enable','profit_rate1','profit_rate2','profit_rate3'));
			$data['goods_id'] = $goods_id;
			$data['shop_id'] = $this->shop['shop_id'];
			$data['profit_enable'] = (int) $data['profit_enable'];
			$data['profit_rate1'] = $data['profit_rate1'];
			$data['profit_rate2'] = $data['profit_rate2'];
			$data['profit_rate3'] = $data['profit_rate3'];
			if(($data['profit_rate1'] + $data['profit_rate2'] + $data['profit_rate3']) >= 100){
				$this->tuMsg('分成比例相加不能大于或者等于100%');
			}
			if($profit){
				$res = M('GoodsProfit')->save($data);
			}else{
				$res = M('GoodsProfit')->add($data);
			}
            if($res){
				$this->tuMsg('操作成功', U('mart/index'));
            }
			$this->tuMsg('操作失败');
        }else{
           $this->assign('detail', M('Goods')->find($goods_id));
		   $this->assign('profit', $profit);
           $this->display();
		}
	}
	
	
	
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if(empty($data['title'])) {
            $this->tuMsg('产品名称不能为空');
        }
        $data['guige'] = htmlspecialchars($data['guige']);
		$data['num'] = (int) $data['num'];
        if(empty($data['num'])){
            $this->tuMsg('库存不能为空');
        } 
		$data['is_reight'] = (int) $data['is_reight'];
		$data['weight'] = (int) $data['weight'];
		if($data['is_reight'] == 1) {
			if (empty($data['weight'])){
             	$this->tuMsg('重量不能为空');
			}
        }
		$data['kuaidi_id'] = (int) $data['kuaidi_id'];
		if($data['is_reight'] == 1){
			if (empty($data['kuaidi_id'])) {
				$this->tuMsg('请在电脑端做好运费模版，或者选择免运费');
			}
		}	
        $data['shop_id'] = $this->shop_id;
        $shopdetail = D('Shop')->find($this->shop_id);
        $data['cate_id'] = (int) $data['cate_id'];
        if(empty($data['cate_id'])){
            $this->tuMsg('请选择分类');
        }
        $data['shopcate_id'] = (int) $data['shopcate_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
		$data['goods_type'] = I('goods_type');
        $data['photo'] = htmlspecialchars($data['photo']);
        if(empty($data['photo'])){
            $this->tuMsg('请上传缩略图');
        }
        if(!isImage($data['photo'])){
            $this->tuMsg('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if(empty($data['price'])){
            $this->tuMsg('市场价格不能为空');
        }
        $data['mall_price'] = (int) ($data['mall_price'] * 100);
        if(empty($data['mall_price'])){
            $this->tuMsg('商城价格不能为空');
        }
		$data['use_integral'] = (int) $data['use_integral'];
		if(!D('Goods')->check_add_use_integral($data['use_integral'],$data['mall_price'])) {//传2参数
            $this->tuMsg(D('Goods')->getError());
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if(empty($data['details'])){
            $this->tuMsg('商品详情不能为空');
        }
        if($words = D('Sensitive')->checkWords($data['details'])){
            $this->tuMsg('商品详情含有敏感词：' . $words);
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if(empty($data['end_date'])){
            $this->tuMsg('过期时间不能为空');
        }
        if(!isDate($data['end_date'])){
            $this->tuMsg('过期时间格式不正确');
        }
		if($res = D('Goods')->where(array('title'=>$data['title'],'details'=>$data['details'],'end_date'=>$data['end_date']))->find()){
			$this->tuMsg('请勿重复添加商品');
		}
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['sold_num'] = 0;
        $data['view'] = 0;
        $data['audit'] = 1;
        return $data;
    }
	
	
    public function edit($goods_id = 0){
        if ($goods_id = (int) $goods_id) {
            $obj = D('Goods');
            if(!($detail = $obj->find($goods_id))){
                $this->error('请选择要编辑的商品');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('请不要试图越权操作其他人的内容');
            }
            if($this->isPost()){
                $data = $this->editCheck();
                $data['goods_id'] = $goods_id;
                if(!empty($detail['wei_pic'])) {
                    if(true !== strpos($detail['wei_pic'], "https://mp.weixin.qq.com/")){
                        $wei_pic = D('Weixin')->getCode($goods_id, 3);
                        $data['wei_pic'] = $wei_pic;
                    }
                }else{
                    $wei_pic = D('Weixin')->getCode($goods_id, 3);
                    $data['wei_pic'] = $wei_pic;
                }
				
                if(false !== $obj->save($data)){
					$photos = $this->_post('photos', false);
					if(!empty($photos)){
						D('Goodsphoto')->upload($goods_id, $photos);
					}
					
					$this->shuxin($goods_id);//编辑商品属性
					$this->saveGoodsAttr($goods_id,$_POST['goods_type']); //更新商品属性
					
                    $this->tuMsg('编辑成功，请联系管理员审核', U('mart/index'));
                }
                $this->tuMsg('操作失败');
            }else{
				
				$goodsInfo=D('Goods')->where('goods_id='.I('GET.goods_id',0))->find();
				$this->assign('goodsInfo',$goodsInfo); 
				$this->assign('goodsType',M("TpGoodsType")->select());
			 
                $this->assign('detail', $obj->_format($detail));
                $this->assign('parent_id', D('Goodscate')->getParentsId($detail['cate_id']));
                $this->assign('attrs', D('Goodscateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $detail['cate_id']))->select());
                $this->assign('cates', D('Goodscate')->fetchAll());
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('Goodsphoto')->getPics($goods_id));
				$this->assign('kuaidi', D('Pkuaidi')->where(array('shop_id'=>$this->shop_id,'type'=>goods))->select());
                $this->display();
            }
        }else{
            $this->error('请选择要编辑的商品');
        }
    }
	
	
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if(empty($data['title'])){
            $this->tuMsg('产品名称不能为空');
        }
        $data['shop_id'] = (int) $this->shop_id;
        if(empty($data['shop_id'])){
            $this->tuMsg('商家不能为空');
        }
        $data['guige'] = htmlspecialchars($data['guige']);
		$data['num'] = (int) $data['num'];
        if(empty($data['num'])){
            $this->tuMsg('库存不能为空');
        } 
		
		$data['is_reight'] = (int) $data['is_reight'];
		$data['weight'] = (int) $data['weight'];
		if($data['is_reight'] == 1){
			if (empty($data['weight'])){
             	$this->tuMsg('重量不能为空');
			}
        }
		$data['kuaidi_id'] = (int) $data['kuaidi_id'];
		if($data['is_reight'] == 1){
			if(empty($data['kuaidi_id'])){
				$this->tuMsg('请在电脑端做好运费模版，或者选择免运费');
			}
		}	
        $shopdetail = D('Shop')->find($this->shop_id);
        $data['cate_id'] = (int) $data['cate_id'];
        if(empty($data['cate_id'])){
            $this->tuMsg('请选择分类');
        }
        $data['shopcate_id'] = (int) $data['shopcate_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
		$data['goods_type'] = I('goods_type');
        $data['photo'] = htmlspecialchars($data['photo']);
        if(empty($data['photo'])){
            $this->tuMsg('请上传缩略图');
        }
        if(!isImage($data['photo'])){
            $this->tuMsg('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if(empty($data['price'])){
            $this->tuMsg('市场价格不能为空');
        }
        $data['mall_price'] = (int) ($data['mall_price'] * 100);
        if(empty($data['mall_price'])){
            $this->tuMsg('商城价格不能为空');
        }
		$data['use_integral'] = (int) $data['use_integral'];
		if(!D('Goods')->check_add_use_integral($data['use_integral'],$data['mall_price'])){
            $this->tuMsg(D('Goods')->getError());
        }
		$data['details'] = SecurityEditorHtml($data['details']);
        if(empty($data['details'])){
            $this->tuMsg('商品详情不能为空');
        }
        if($words = D('Sensitive')->checkWords($data['details'])){
            $this->tuMsg('商品详情含有敏感词：' . $words);
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if(empty($data['end_date'])){
            $this->tuMsg('过期时间不能为空');
        }
        if(!isDate($data['end_date'])){
            $this->tuMsg('过期时间格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['audit'] = 1;
        return $data;
    }
	
	
	
    public function child($parent_id = 0){
        $datas = D('Goodscate')->fetchAll();
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
    public function ajax($cate_id, $goods_id = 0){
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Goodscate')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        $this->assign('cate', $detail);
        $this->assign('attrs', D('Goodscateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
        if ($goods_id) {
            $this->assign('detail', D('Goods')->find($goods_id));
            $this->assign('maps', D('GoodsCateattr')->getAttrs($goods_id));
        }
        $this->display();
    }
	
	
	 public function shuxin($goods_id){
         if($_POST['item']){
             $spec = M('TpSpec')->getField('id,name'); 
             $specItem = M('TpSpecItem')->getField('id,item');
                          
             $specGoodsPrice = M("TpSpecGoodsPrice"); 
             $specGoodsPrice->where('goods_id = '.$goods_id)->delete(); 
             foreach($_POST['item'] as $k => $v){
                   $v['price'] = trim($v['price']);
                   $store_count = $v['store_count'] = trim($v['store_count']);
                   $v['bar_code'] = trim($v['bar_code']);
                   $dataList[] = array('goods_id'=>$goods_id,'key'=>$k,'key_name'=>$v['key_name'],'price'=>$v['price'],'store_count'=>$v['store_count'],'bar_code'=>$v['bar_code']);                                      
             }             
             $specGoodsPrice->addAll($dataList);             
         }   
         refresh_stock($goods_id); 

    }
     /**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect(){
        $goods_id = $_GET['goods_id'] ? $_GET['goods_id'] : 0;
        $specList = D('TpSpec')->where("type_id = ".$_GET['spec_type'])->order('`order` desc')->select();
        foreach($specList as $k => $v)        
            $specList[$k]['spec_item'] = D('TpSpecItem')->where("spec_id = ".$v['id'])->getField('id,item'); // 获取规格项                
        $items_id = M('TpSpecGoodsPrice')->where('goods_id = '.$goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
        $items_ids = explode('_', $items_id);       
        if($goods_id){
           $specImageList = M('TpSpecImage')->where("goods_id = $goods_id")->getField('spec_image_id,src');                 
        }        
        $this->assign('specImageList',$specImageList);
        
        $this->assign('items_ids',$items_ids);
        $this->assign('specList',$specList);
        $this->display('ajax_spec_select');        
    }    

     /**
     * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
     */    
    public function ajaxGetSpecInput(){     
         
         $goods_id = $_REQUEST['goods_id'] ? $_REQUEST['goods_id'] : 0;
         $str = $this->getSpecInput($goods_id ,$_POST['spec_arr']);
         exit($str);   
    }

     /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id     
     * @param $spec_arr 笛卡尔积
     * @return string 返回表格字符串
     */
    public function getSpecInput($goods_id, $spec_arr){
        foreach ($spec_arr as $k => $v){
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);        
        foreach ($spec_arr_sort as $key =>$val){
            $spec_arr2[$key] = $spec_arr[$key];
        }
         $clo_name = array_keys($spec_arr2);         
         $spec_arr2 = combineDika($spec_arr2);            
                       
         $spec = M('TpSpec')->getField('id,name'); 
         $specItem = M('TpSpecItem')->getField('id,item,spec_id');
         $keySpecGoodsPrice = M('TpSpecGoodsPrice')->where('goods_id = '.$goods_id)->getField('key,key_name,price,store_count,bar_code');
                          
       $str = "<table class='table table-bordered' id='spec_input_tab'>";
       $str .="<tr>";       
       foreach ($clo_name as $k => $v) {
           $str .=" <td><b>{$spec[$v]}</b></td>";
       }    
        $str .="<td><b>价格</b></td>
               <td><b>库存</b></td>
               <td><b>条码</b></td>
             </tr>";
       foreach ($spec_arr2 as $k => $v) {
            $str .="<tr>";
            $item_key_name = array();
            foreach($v as $k2 => $v2)
            {
                $str .="<td>{$specItem[$v2][item]}</td>";
                $item_key_name[$v2] = $spec[$specItem[$v2]['spec_id']].':'.$specItem[$v2]['item'];
            }   
            ksort($item_key_name);            
            $item_key = implode('_', array_keys($item_key_name));
            $item_name = implode(' ', $item_key_name);
            
            $keySpecGoodsPrice[$item_key][price] ? false : $keySpecGoodsPrice[$item_key][price] = 0; // 价格默认为0
            $keySpecGoodsPrice[$item_key][store_count] ? false : $keySpecGoodsPrice[$item_key][store_count] = 0; //库存默认为0
            $str .="<td><input name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key][price]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
            $str .="<td><input name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key][store_count]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";            
            $str .="<td><input name='item[$item_key][bar_code]' value='{$keySpecGoodsPrice[$item_key][bar_code]}' />
                <input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
            $str .="</tr>";           
       }
        $str .= "</table>";
       return $str;   
    }
	
	
	
	
	//动态获取商品属性入框根据不同的数据返回不同的输入框类型
    public function ajaxGetAttrInput(){
		$goods_id = $_REQUEST['goods_id'] ? $_REQUEST['goods_id'] : 0;
		$type_id = $_REQUEST['type_id'] ? $_REQUEST['type_id'] : 0;
		$str = $this->getAttrInput($goods_id,$type_id);
        exit($str);
    }
	
	
	  /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     * @param int $goods_id 商品id
     * @param int $type_id 商品属性类型id
     */
    public function getAttrInput($goods_id,$type_id){
		
	  
		
        $attributeList = M('tpGoodsAttribute')->where(array('type_id'=>$type_id))->select();  
        foreach($attributeList as $key => $val){                                                                        
            
            $curAttrVal = $this->getGoodsAttrVal(NULL,$goods_id, $val['attr_id']);
             //促使他循环
            if(count($curAttrVal) == 0 || false == $curAttrVal)
                $curAttrVal[] = array('goods_attr_id' =>'','goods_id' => '','attr_id' => '','attr_value' => '','attr_price' => '');
            foreach($curAttrVal as $k =>$v){                                        
                            $str .= "<tr class='attr_{$val['attr_id']}'>";            
                            $addDelAttr = ''; //加减符号
                            //单选属性或者复选属性
                            if($val['attr_type'] == 1 || $val['attr_type'] == 2){
                                if($k == 0)                                
                                    $addDelAttr .= "<a onclick='addAttr(this)' href='javascript:void(0);'>[+]</a>&nbsp&nbsp";                                                                    
                                else                                
                                     $addDelAttr .= "<a onclick='delAttr(this)' href='javascript:void(0);'>[-]</a>&nbsp&nbsp";                               
                            }

                            $str .= "<td>$addDelAttr {$val['attr_name']}</td> <td>";            

                            //手工录入
                            if($val['attr_input_type'] == 0){
                                $str .= "<input type='text' size='40' value='{$v['attr_value']}' name='attr_{$val['attr_id']}[]' />";
                            }
                            //从下面的列表中选择（一行代表一个可选值）
                            if($val['attr_input_type'] == 1){
                                $str .= "<select name='attr_{$val['attr_id']}[]'>";
                                $tmp_option_val = explode(PHP_EOL, $val['attr_values']);
                                foreach($tmp_option_val as $k2=>$v2){
                                    //编辑的时候有选中值
                                    $v2 = preg_replace("/\s/","",$v2);
                                    if($v['attr_value'] == $v2)
                                        $str .= "<option selected='selected' value='{$v2}'>{$v2}</option>";
                                    else
                                        $str .= "<option value='{$v2}'>{$v2}</option>";
                                }
                                $str .= "</select>";                
                            }
                            //多行文本框
                            if($val['attr_input_type'] == 2){
                                $str .= "<textarea cols='40' rows='3' name='attr_{$val['attr_id']}[]'>{$v['attr_value']}</textarea>";
                            }                                                        
                            $str .= "</td></tr>";
            }                        
            
        }        
        return  $str;
    }
    
    /**
     * 获取 tp_goods_attr 表中指定 goods_id  指定 attr_id  或者 指定 goods_attr_id 的值 可是字符串 可是数组
     * @param int $goods_attr_id tp_goods_attr表id
     * @param int $goods_id 商品id
     * @param int $attr_id 商品属性id
     * @return array 返回数组
     */
    public function getGoodsAttrVal($goods_attr_id = 0 ,$goods_id = 0, $attr_id = 0)
    {
        if($goods_attr_id > 0)
            return M('tpGoodsAttr')->where(array('goods_attr_id'=>$goods_attr_id))->select(); 
        if($goods_id > 0 && $attr_id > 0)
            return M('tpGoodsAttr')->where(array('goods_id'=>$goods_id,'attr_id'=>$attr_id))->select();        
    }
	
	
	 /**
     *  给指定商品添加属性 或修改属性 更新到 tp_goods_attr
     * @param int $goods_id  商品id
     * @param int $goods_type  商品类型id
     */
    public function saveGoodsAttr($goods_id,$goods_type){  
     
                
         // 属性类型被更改了 就先删除以前的属性类型 或者没有属性 则删除        
        if($goods_type == 0)  {
            M('tpGoodsAttr')->where(array('goods_id'=>$goods_id))->delete(); 
            return;
        }
        
            $GoodsAttrList = M('tpGoodsAttr')->where(array('goods_id'=>$goods_id))->select();
            
            $old_goods_attr = array(); //数据库中的的属性以attr_id_和值的组合为键名
            foreach($GoodsAttrList as $k => $v){                
                $old_goods_attr[$v['attr_id'].'_'.$v['attr_value']] = $v;
            }            
                              
            // post提交的属性以attr_id_和值的组合为键名    
            $post_goods_attr = array();
	
            foreach($_POST as $k => $v){
                $attr_id = str_replace('attr_','',$k);
                if(!strstr($k, 'attr_') || strstr($k, 'attr_price_'))
                   continue;                                 
               foreach ($v as $k2 => $v2){                      
                   $v2 = str_replace('_', '', $v2); //替换特殊字符
                   $v2 = str_replace('@', '', $v2); //替换特殊字符
                   $v2 = trim($v2);
                   
                   if(empty($v2))
                       continue;
                   $tmp_key = $attr_id."_".$v2;
                   $attr_price = $_POST["attr_price_$attr_id"][$k2]; 
                   $attr_price = $attr_price ? $attr_price : 0;
                   if(array_key_exists($tmp_key , $old_goods_attr)){  
				   //如果这个属性原来就存在 
                       if($old_goods_attr[$tmp_key]['attr_price'] != $attr_price){    
					   	//并且价格不一样就做更新处理                   
                           $goods_attr_id = $old_goods_attr[$tmp_key]['goods_attr_id'];                         
                           M('tpGoodsAttr')->where(array('goods_attr_id'=>$goods_attr_id))->save(array('attr_price'=>$attr_price));                       
                       }
                   }else{
					   //否则这个属性 数据库中不存在 说明要做删除操作
                       M('tpGoodsAttr')->add(array('goods_id'=>$goods_id,'attr_id'=>$attr_id,'attr_value'=>$v2,'attr_price'=>$attr_price));                       
                   }
                   unset($old_goods_attr[$tmp_key]);
               }
            }     
            //没有被unset($old_goods_attr[$tmp_key]); 掉是说明数据库中存在表单中没有提交过来则要删除操作
            foreach($old_goods_attr as $k => $v){                
               M('tpGoodsAttr')->where(array('goods_attr_id'=>$v['goods_attr_id']))->delete();
            }                       
    }




}