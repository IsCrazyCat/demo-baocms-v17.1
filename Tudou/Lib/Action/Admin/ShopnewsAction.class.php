<?php
class ShopnewsAction extends CommonAction
{
    private $create_fields = array('shop_id', 'title', 'photo', 'details', 'views', 'orderby');
    private $edit_fields = array('shop_id', 'title', 'photo', 'details', 'views', 'orderby');
    public function index(){
        $Shopnews = D('Shopnews');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Shopnews->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shopnews->where($map)->order(array('news_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Shopnews');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('shopnews/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->tuError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->tuError('请选择正确的商家');
        }
        $data['cate_id'] = $shop['cate_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详细内容含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['views'] = (int) $data['views'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($news_id = 0)
    {
        if ($news_id = (int) $news_id) {
            $obj = D('Shopnews');
            if (!($detail = $obj->find($news_id))) {
                $this->tuError('请选择要编辑的商家资讯');
            }

            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['news_id'] = $news_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('shopnews/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的商家资讯');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->tuError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->tuError('请选择正确的商家');
        }
        $data['cate_id'] = $shop['cate_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('详细内容含有敏感词：' . $words);
        }
        $data['views'] = (int) $data['views'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($news_id = 0)
    {
        if (is_numeric($news_id) && ($news_id = (int) $news_id)) {
            $obj = D('Shopnews');
            $obj->delete($news_id);
            $this->tuSuccess('删除成功', U('shopnews/index'));
        } else {
            $news_id = $this->_post('news_id', false);
            if (is_array($news_id)) {
                $obj = D('Shopnews');
                foreach ($news_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('shopnews/index'));
            }
            $this->tuError('请选择要删除的商家资讯');
        }
    }
    public function audit($news_id = 0)
    {
        if (is_numeric($news_id) && ($news_id = (int) $news_id)) {
            $obj = D('Shopnews');
            $obj->save(array('news_id' => $news_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('shopnews/index'));
        } else {
            $news_id = $this->_post('news_id', false);
            if (is_array($news_id)) {
                $obj = D('Shopnews');
                foreach ($news_id as $id) {
                    $obj->save(array('news_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('shopnews/index'));
            }
            $this->tuError('请选择要审核的商家资讯');
        }
    }
}