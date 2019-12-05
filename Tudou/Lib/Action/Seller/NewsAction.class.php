<?php
class NewsAction extends CommonAction{
    private $edit_fields = array('title','cate_id','city_id', 'area_id','photo','shop_id', 'source','keywords', 'keywords','profiles','details');
	
	 public function _initialize(){
        parent::_initialize();
        $this->articlecate = D('Articlecate')->fetchAll();
        $this->assign('articlecate', $this->articlecate);

    }
	
	
    public function index(){
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = D('Shopnews')->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = D('Shopnews')->where($map)->order(array('news_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('cates', D('Articlecate')->fetchAll());
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->editCheck();
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $articles = array(
				'shop_id' => $this->shop_id, 
				'cate_id' => $data['cate_id'], 
				'city_id' => $data['city_id'], 
				'area_id' => $data['area_id'], 
				'source' => $data['source'], 
				'title' => $data['title'], 
				'keywords' => $data['keywords'], 
				'profiles' => $data['profiles'], 
				'photo' => $data['photo'], 
				'details' => $data['details'], 
				'audit' => 1, 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip()
			);
            $articles['article_id'] = D('Article')->add($articles);
            $obj = D('Shopnews');
            if ($news_id = $obj->add($data)) {
                D('Shopfavorites')->save(array('last_news_id' => $news_id), array('where' => array('shop_id' => $this->shop_id)));
                $this->tuMsg('添加成功', U('news/index'));
            }
            $this->tuMsg('操作失败');
        } else {
            $this->assign('cates', D('Articlecate')->fetchAll());
            $this->display();
        }
    }
    public function edit($news_id = 0) {
        if (empty($news_id)) {
            $this->error('请选择需要编辑的内容操作');
        }
        $news_id = (int) $news_id;
        $obj = D('Shopnews');
        $detail = $obj->find($news_id);
        if (empty($detail) || $detail['shop_id'] != $this->shop_id) {
            $this->error('请选择需要编辑的内容操作');
        }
        if ($this->isPost()) {
            $data = $this->editCheck();
            $data['news_id'] = $news_id;
            if(false !== $obj->save($data)) {
                $this->tuMsg('更新文章成功', U('news/index'));
            }
            $this->tuMsg('操作失败');
        } else{
            $this->assign('cates', D('Articlecate')->fetchAll());
            $this->assign('detail', $detail);
			$this->assign('parent_id',D('Articlecate')->getParentsId($detail['cate_id']));
            $this->display();
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $shop = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        $data['shop_id'] = $this->shop_id;
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['source'] = $shop['shop_name'];
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuMsg('分类不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuMsg('标题不能为空');
        }
        $data['keywords'] = htmlspecialchars($data['keywords']);
        $data['profiles'] = SecurityEditorHtml($data['profiles']);
        if (empty($data['profiles'])) {
            $this->tuMsg('简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['profiles'])) {
            $this->tuMsg('简介内容含有敏感词：' . $words);
        }
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuMsg('正文不能为空1');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuMsg('正文含有敏感词：' . $words);
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->tuMsg('缩略图格式不正确');
        }
        return $data;
    }
    public function delete($news_id = 0){
        if (empty($news_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
        }
        $res = D('Shopnews')->find($news_id);
        if (empty($res)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
        }
        if ($res['shop_id'] != $this->shop_id) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '您没有权限访问！'));
        }
        D('Shopnews')->save(array('news_id' => $news_id, 'closed' => 1));
        $this->ajaxReturn(array('status' => 'success', 'msg' => '文章删除成功', U('worker/index')));
    }
    public function child($parent_id = 0)
    {
        $datas = D('Articlecate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['cate_id']) {
                                $str .= '<option value="' . $var3['cate_id'] . '">&nbsp;&nbsp;--' . $var3['cate_name'] . '</option>' . "\n\r";
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
}