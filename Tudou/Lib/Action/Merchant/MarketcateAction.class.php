<?php
class MarketcateAction extends CommonAction
{
    private $create_fields = array('cate_name');
    private $edit_fields = array('cate_name');
	
	
    public function _initialize(){
        parent::_initialize();
        $getMarketCate = D('Market')->getMarketCate();
        $this->assign('getMarketCate', $getMarketCate);
        $this->market = D('Market')->find($this->shop_id);
        if(!empty($this->market) && $this->market['audit'] == 0){
            $this->error('亲，您的申请正在审核中');
        }
        if(empty($this->market) && ACTION_NAME != 'apply') {
            $this->error('您还没有入住菜市场频道', U('market/apply'));
        }
        $this->assign('market', $this->market);
    }
	
	
    public function index(){
        $obj = D('Marketcate');
        import('ORG.Util.Page');
        $map = array('closed' => '0','parent_id'=>'0');
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['cate_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($shop_id = $this->shop_id){
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count,10);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('cate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if($shop_ids){
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
    public function create($parent_id = 0){
        if ($this->isPost()){
            $data = $this->createCheck();
            $obj = D('Marketcate');
			$data['parent_id'] = $parent_id;
            if($obj->add($data)){
                $this->tuSuccess('添加成功', U('marketcate/index'));
            }
            $this->tuError('操作失败');
        }else{
			$this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
	
	
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = $this->shop_id;
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('分类名称不能为空');
        }
        return $data;
    }
	
	
    public function edit($cate_id = 0){
        if ($cate_id = (int) $cate_id) {
            $obj = D('Marketcate');
            if(!($detail = $obj->find($cate_id))){
                $this->error('请选择要编辑的商品分类');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('请不要操作其他商家的商品分类');
            }
            if($this->isPost()){
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if(false !== $obj->save($data)){
                    $this->tuSuccess('操作成功', U('marketcate/index'));
                }
                $this->tuError('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->error('请选择要编辑的商品分类');
        }
    }
	
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('分类名称不能为空');
        }
        return $data;
    }
	
    public function dmarkette($cate_id = 0){
        if(is_numeric($cate_id) && ($cate_id = (int) $cate_id)){
            $obj = D('Marketcate');
            if(!($detail = $obj->where(array('shop_id' => $this->shop_id, 'cate_id' => $cate_id))->find())) {
                $this->tuError('请选择要删除的商品分类');
            }
			
			$res = D('Marketproduct')->where(array('cate_id'=>$cate_id))->find();
			if($res){
		 		 $this->tuError('当前分类下绑定了商品【'.$res['product_name'].'】，暂时无法删除');
			}
			
			$res2 = $obj->where(array('parent_id'=>$cate_id))->find();
			if($res2 && $detail['parent_id'] != 0){
				$this->tuError('当前分类下面还有子分类【'.$res2['cate_name'].'】，暂时无法删除');
			}
			
            $obj->save(array('cate_id' => $cate_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('marketcate/index'));
        }
        $this->tuError('请选择要删除的商品分类');
    }
	
	//分类请求
	public function child($parent_id=0){
        $datas = D('Marketcate')->select();
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
	
}