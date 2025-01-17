<?php
class LifeAction extends CommonAction{
    protected function _initialize(){
		
        parent::_initialize();
        if((int) $this->_CONFIG['operation']['life'] == 0){
            $this->error('此功能已关闭');
            die;
        }
    }
	
	
    public function lifetop(){
        if(!($life_id = (int) $this->_get('life_id'))){
            $this->tuError('参数错误');
        }
        if(!($detail = D('Life')->find($life_id))){
            $this->tuError('参数错误');
        }
        if($detail['user_id'] != $this->uid){
            $this->tuError('参数错误');
        }
        $day = (int) $this->_get('day');
        $mday = 0;
        switch($day){
            case 7:
                $mday = $day = 7;
                break;
            default:
                $day = 30;
                $mday = 27;
                break;
        }
        $money = $mday * $this->_CONFIG['life']['top'] * 100;
        if($this->member['money'] < $money) {
            $this->tuErrorJump('余额不足', U('members/money/money'));
        }
        $top_date = date('Y-m-d', NOW_TIME + $day * 86400);
        if($detail['top_date'] > TODAY) {
            $top_date = date('Y-m-d', strtotime($detail['top_date']) + $day * 86400);
        }
        if(D('Users')->addMoney($this->uid, -$money, '置顶信息' . $day . '天')){
            D('Life')->save(array('top_date' => $top_date, 'life_id' => $life_id));
            $this->tuSuccess('您的信息已经在同频道置顶了', U('members/life/index'));
        }
        $this->tuError('操作失败');
    }
	
	
    public function lifeflush(){
        if(!($life_id = (int) $this->_get('life_id'))){
            $this->tuError('参数错误');
        }
        if(!($detail = D('Life')->find($life_id))){
            $this->tuError('参数错误');
        }
        if($detail['user_id'] != $this->uid){
            $this->tuError('参数错误');
        }
        if(NOW_TIME - $detail['last_time'] < 86400){
            $this->tuError('您已经刷新过了');
        }
        if(NOW_TIME - $detail['last_time'] > 86400 * 30){
            $this->tuError('该信息已经超过30天了，不能在进行免费刷新');
        }
        $data = array('life_id' => $life_id, 'last_time' => NOW_TIME);
        if($detail['top_date'] < TODAY){
            $data['top_date'] = TODAY;
        }
        if(D('Life')->save($data)) {
            $this->tuSuccess('刷新成功!', U('members/life/index'));
        }
        $this->tuError('操作失败');
    }
	
	
    public function lifeurgent(){
        if(!($life_id = (int) $this->_get('life_id'))){
            $this->tuError('参数错误');
        }
        if(!($detail = D('Life')->find($life_id))){
            $this->tuError('参数错误');
        }
        if($detail['user_id'] != $this->uid){
            $this->tuError('参数错误');
        }
        $day = (int) $this->_get('day');
        $mday = 0;
        switch ($day) {
            case 7:
                $mday = $day = 7;
                break;
            default:
                $day = 30;
                $mday = 27;
                break;
        }
        $money = $mday * $this->_CONFIG['life']['urgent'] * 100;
        if($this->member['money'] < $money) {
            $this->tuErrorJump('余额不足', U('members/money/money'));
        }
        $urgent_date = date('Y-m-d', NOW_TIME + $day * 86400);
        if($detail['urgent_date'] > TODAY) {
            $urgent_date = date('Y-m-d', strtotime($detail['urgent_date']) + $day * 86400);
        }
        if(D('Users')->addMoney($this->uid, -$money, '加急信息' . $day . '天')) {
            D('Life')->save(array('urgent_date' => $urgent_date, 'life_id' => $life_id));
            $this->tuSuccess('您的信息已经加急', U('members/life/index'));
        }
        $this->tuError('操作失败');
    }
	
	
    public function index(){
        $Life = D('Life');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid,'closed'=>'0');
        $count = $Life->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Life->where($map)->order(array('last_time' => 'desc'))->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('cates', D('Lifecate')->fetchAll());
        $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
        $this->display();
    }
	
	
    public function del(){
        $life_id = I('life_id', '', 'intval,trim');
        if(!$life_id){
            $this->tuError('没有选择');
        }else{
            $l = D('Life');
            $r = $l->where('life_id =' . $life_id)->delete();
            if($r){
                $this->tuSuccess('删除成功');
            }else{
                $this->tuError('删除失败');
            }
        }
    }
	
	
    public function edit($life_id){
        if ($life_id = (int) $life_id) {
            $obj = D('Life');
            if (!($detail = $obj->find($life_id))) {
                $this->tuError('请选择要编辑的生活信息');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['life_id'] = $life_id;
                $details = $this->_post('details', 'SecurityEditorHtml');
                $data['audit'] = 0;
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->tuError('商家介绍含有敏感词：' . $words);
                }
                if (false !== $obj->save($data)) {
                    if ($details) {
                        D('Lifedetails')->updateDetails($life_id, $details);
                    }
                    $photos = $this->_post('photos', false);
                    if (!empty($photos)) {
                        D('Lifephoto')->upload($life_id, $photos);
                    }
                    $this->tuSuccess('操作成功', U('life/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('cates', D('Lifecate')->fetchAll());
                $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
                $this->assign('cate', D('Lifecate')->find($detail['cate_id']));
                $this->assign('areas', D('Area')->fetchAll());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('ex', D('Lifedetails')->find($life_id));
                $this->assign('attrs', D('Lifecateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $detail['cate_id']))->select());
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('photos', D('Lifephoto')->getPics($life_id));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的生活信息');
        }
    }
	
	
    private function editCheck(){
        $data = $this->_post('data', false);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('标题不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('分类不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        $data['business_id'] = (int) $data['business_id'];
       
        $data['user_id'] = $this->uid;
        $data['is_shop'] = (int) $data['is_shop'];
        $data['text1'] = htmlspecialchars($data['text1']);
        $data['text2'] = htmlspecialchars($data['text2']);
        $data['text3'] = htmlspecialchars($data['text3']);
        $data['num1'] = (int) $data['num1'];
        $data['num2'] = (int) $data['num2'];
        $data['select1'] = (int) $data['select1'];
        $data['select2'] = (int) $data['select2'];
        $data['select3'] = (int) $data['select3'];
        $data['select4'] = (int) $data['select4'];
        $data['select5'] = (int) $data['select5'];
        $data['urgent_date'] = htmlspecialchars($data['urgent_date']);
        $data['urgent_date'] = $data['urgent_date'] ? $data['urgent_date'] : TODAY;
        if (!empty($data['urgent_date']) && !isDate($data['urgent_date'])) {
            $this->tuError('火急日期格式不正确');
        }
        $data['top_date'] = htmlspecialchars($data['top_date']);
        $data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        $data['top_date'] = $data['top_date'] ? $data['top_date'] : TODAY;
        if (!empty($data['top_date']) && !isDate($data['top_date']) && $data['top_date'] != '0000-00-00') {
            $this->tuError('置顶日期格式不正确');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        if (empty($data['contact'])) {
            $this->tuError('联系人不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuError('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->tuError('手机格式不正确');
        }
        $data['qq'] = htmlspecialchars($data['qq']);
        $data['addr'] = htmlspecialchars($data['addr']);
        $data['views'] = (int) $data['views'];
		$data['money'] = (int) ($data['money']*100);
        return $data;
    }
    public function ajax($cate_id, $life_id = 0){
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Lifecate')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        $this->assign('cate', $detail);
        $this->assign('attrs', D('Lifecateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
        if ($life_id) {
            $this->assign('detail', D('Life')->find($life_id));
            $this->assign('maps', D('LifeCateattr')->getAttrs($life_id));
        }
        $this->display();
    }
}