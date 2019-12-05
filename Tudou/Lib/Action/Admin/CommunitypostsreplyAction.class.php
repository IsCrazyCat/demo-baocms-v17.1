<?php

class CommunitypostsreplyAction extends CommonAction{
    private $create_fields = array('post_id','user_id','details');
    private $edit_fields = array('post_id','user_id','details');
    
     public  function index(){
       $Postreply = D('Communityreplys');
       import('ORG.Util.Page');
       $map = array();
       if ($post_id= (int) $this->_param('post_id')) {
            $map['post_id'] = $post_id;
            $this->assign('post_id', $post_id);
        }
		if ($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = $community_id;
            $community = D('Community')->find($community_id);
            $this->assign('name', $community['name']);
            $this->assign('community_id', $community_id);
        }
        if ($user_id= (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $this->assign('user_id', $user_id);
        }
        if($audit = (int)$this->_param('audit')){
            $map['audit'] = ($audit === 1 ? 1:0);
            $this->assign('audit',$audit);
        }
       $count = $Postreply->where($map)->count();
       $Page  = new Page($count,15);
       $show  = $Page->show();
       $list = $Postreply->where($map)->order(array('reply_id'=>'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
       $post_ids = $user_ids = array();
       foreach($list  as  $k=>$val){
           $post_ids[$val['post_id']] = $val['post_id'];
           $user_ids[$val['user_id']] = $val['user_id'];
		   $community_ids[$val['community_id']] = $val['community_id'];
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
       }
	   
       if(!empty($post_ids)){
           $this->assign('posts',D('Communityposts')->itemsByIds($post_ids));
       }
       if(!empty($user_ids)){
           $this->assign('users',D('Users')->itemsByIds($user_ids));
       }
	    if(!empty($community_ids)){
           $this->assign('community',D('Community')->itemsByIds($community_ids));
       }
       $this->assign('list',$list);
       $this->assign('page',$show);
	   $this->assign('user',D('Users')->find($detail['user_id']));
       $this->display(); 
    }
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Communityreplys');
        if($obj->add($data)){
            $this->tuSuccess('添加成功',U('Communitypostsreply/index'));
        }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['post_id'] = (int)$data['post_id'];
        if(empty($data['post_id'])){
            $this->tuError('帖子ID不能为空');
        }        
		$data['user_id'] = (int)$data['user_id'];
        if(empty($data['user_id'])){
            $this->tuError('用户不能为空');
        }     
		$data['details'] = SecurityEditorHtml($data['details']);
        if(empty($data['details'])){
            $this->tuError('回复内容不能为空2');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($reply_id = 0){
        if($reply_id =(int) $reply_id){
            $obj = D('Communityreplys');
            if(!$detail = $obj->find($reply_id)){
                $this->tuError('请选择要编辑的回复帖子');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['reply_id'] = $reply_id;
                if(false!==$obj->save($data)){
                    $this->tuSuccess('操作成功',U('Communitypostsreply/index'));
                }
                $this->tuError('操作失败');
                
            }else{
                $this->assign('detail',$detail);      
                $this->assign('user',D('Users')->find($detail['user_id']));
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的回复帖子');
        }
    }
     private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['post_id'] = (int)$data['post_id'];
        if(empty($data['post_id'])){
            $this->tuError('帖子ID不能为空');
        }        $data['user_id'] = (int)$data['user_id'];
        if(empty($data['user_id'])){
            $this->tuError('用户不能为空');
        }        $data['details'] = SecurityEditorHtml($data['details']);
        if(empty($data['details'])){
            $this->tuError('内容不能为空3');
        }
        return $data;  
    }

    public function delete($reply_id = 0){
         if(is_numeric($reply_id) && $reply_id = (int)$reply_id){
             $obj =D('Communityreplys');
             $obj->delete($reply_id);
             $this->tuSuccess('删除成功',U('Communitypostsreply/index'));
         }else{
            $reply_id = $this->_post('reply_id',false);
            if(is_array($reply_id)){     
                $obj = D('Communityreplys');
                foreach($reply_id as $id){
                    $obj->delete($id);
                }                
                $this->tuSuccess('删除成功', U('Communitypostsreply/index'));
            }
            $this->tuError('请选择要删除的回复帖子');
         }
         
    }
    public function audit($reply_id = 0){
         if(is_numeric($reply_id) && ($reply_id = (int)$reply_id)){
             $obj =D('Communityreplys');
              $detail = $obj->find($reply_id);
             $obj->save(array('reply_id'=>$reply_id,'audit'=>1));
              D('Users')->integral($detail['user_id'],'reply');
             $this->tuSuccess('审核成功！',U('Communitypostsreply/index'));
         }else{
            $reply_id = $this->_post('reply_id',false);
            if(is_array($reply_id)){     
                $obj = D('Communityreplys');
                foreach($reply_id as $id){
                   $detail = $obj->find($id);
                   $obj->save(array('reply_id'=>$id,'audit'=>1));
                   D('Users')->integral($detail['user_id'],'reply');
                }                
                $this->tuSuccess('审核成功', U('Communitypostsreply/index'));
            }
            $this->tuError('请选择要审核的回复帖子');
         }
         
    }
    
   
}
