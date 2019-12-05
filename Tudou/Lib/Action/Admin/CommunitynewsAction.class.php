<?php
class CommunitynewsAction extends CommonAction
{
    private $create_fields = array('title', 'community_id', 'details', 'intro', 'views');
    private $edit_fields = array('title', 'community_id', 'details', 'intro', 'views');
    public function index(){
        $obj = D('Communitynews');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('news_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = $communitys = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
                $communitys[$val['community_id']] = $val['community_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        $this->assign('communitys', D('Community')->itemsByIds($communitys));
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Communitynews');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('communitynews/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('sharecate', D('Sharecate')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('标题不能为空');
        }
        $data['intro'] = SecurityEditorHtml($data['intro']);
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('详细内容不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['views'] = (int) $data['views'];
        return $data;
    }
    public function edit($news_id = 0){
        if ($news_id = (int) $news_id) {
            $obj = D('Communitynews');
            if (!($detail = $obj->find($news_id))) {
                $this->tuError('请选择要编辑的小区通知');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['news_id'] = $news_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('communitynews/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('community', D('Community')->find($detail['community_id']));
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的小区通知');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('标题不能为空');
        }
        $data['intro'] = SecurityEditorHtml($data['intro']);
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('详细内容不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['views'] = (int) $data['views'];
        return $data;
    }
    public function delete($news_id = 0){
        if (is_numeric($news_id) && ($news_id = (int) $news_id)) {
            $obj = D('Communitynews');
            $obj->save(array('news_id' => $news_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('communitynews/index'));
        } else {
            $news_id = $this->_post('news_id', false);
            if (is_array($news_id)) {
                $obj = D('Communitynews');
                foreach ($news_id as $id) {
                    $obj->save(array('news_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('communitynews/index'));
            }
            $this->tuError('请选择要删除的小区通知');
        }
    }
    public function audit($news_id = 0){
        if (is_numeric($news_id) && ($news_id = (int) $news_id)) {
            $obj = D('Communitynews');
            $detail = $obj->find($news_id);
            $obj->save(array('news_id' => $news_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('communitynews/index'));
        } else {
            $news_id = $this->_post('news_id', false);
            if (is_array($news_id)) {
                $obj = D('Communitynews');
                foreach ($news_id as $id) {
                    $detail = $obj->find($id);
                    $obj->save(array('news_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('communitynews/index'));
            }
            $this->tuError('请选择要审核的小区通知');
        }
    }
}