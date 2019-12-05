<?php


class ThreadAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
		 if ($this->_CONFIG['operation']['thread'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
		$this->assign('cates',D('Threadcate')->fetchAll());

    }


    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
		$this->display();
	}
    
    public function loaddata(){
        $threadpost = D('Threadpost');
        import('ORG.Util.Page'); 
		
        $map = array('user_id' => $this->uid);
        $aready = (int) $this->_param('aready');
        if ($aready == 1) {
            $map['audit'] = 0;
        } elseif ($aready == 0) {
            $map['audit'] = array('IN', array(0, 1));
        } elseif ($aready == 2) {
            $map['audit'] = 1;
        }
		
        $count = $threadpost->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $threadpost->where($map)->order(array('last_time'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $thread_ids = $post_ids = $user_ids = array();
        foreach($list as $k=>$val){
            $thread_ids[$val['thread_id']] = $val['thread_id'];
            $post_ids[$val['post_id']] = $val['post_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('tribes',D('Thread')->itemsByIds($thread_ids));
        $this->assign('users',D('Users')->itemsByIds($user_ids));
        $pics = D('Threadpostphoto')->where(array('post_id'=>array('IN',$post_ids)))->select();
        foreach($list as $k=>$val){
            foreach($pics as $kk=>$v){
                if($val['post_id'] == $v['post_id']){
                    $list[$k]['pics'][] = $v['photo'];
                }
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show); 
		$this->display();
    }

    
    public function delete(){
        $post_id = (int) $this->_param('post_id');
        $obj = D('Threadpost');
        if(empty($post_id)){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '帖子不存在'));
        }
        if(!($detail = $obj->find($post_id))){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '帖子不存在'));
        }
        if($detail['user_id'] != $this->uid){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '不要操作别人的帖子'));
        }
        if($obj->delete($post_id)){
            $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您删除成功'));
        }
    }

}
