<?php
class DeliveryAction extends CommonAction{
    protected function _initialize(){
        parent::_initialize();
    }
	
	//配送员首页
    public function delivery($id){
        $id = (int) $this->_param('id');
        if(!($detail = D('Delivery')->find($id))){
            $this->error('没有该配送员');
            die;
        }
		
		
        $obj = D('DeliveryOrder');
        $today = strtotime(date('Y-m-d'));
		$this->assign('today_p',$today_p = $obj->where('update_time >='.$today.' and delivery_id ='.$id.' and status =2'.' and closed =0')->count());
		$this->assign('today_ok',$today_ok = $obj->where('update_time >='.$today.' and delivery_id ='.$id.' and status =8'.' and closed =0')->count());
        $this->assign('all_ok',$all_ok = $obj->where('delivery_id ='.$id.' and status =8'.' and closed =0')->count());
		
		$this->assign('USERS', $Users = D('Users')->find($detail['user_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
	//配送员点评列表
    public function lists($id){
        $id = (int) $this->_param('id');
		$this->assign('id', $id);
		$linkArr['id'] = $id;
		
		$tag_id = (int) $this->_param('tag_id');
		$this->assign('tag_id', $tag_id);
		$linkArr['tag_id'] = $tag_id;
		
		$d = (int) $this->_param('d');
		$this->assign('d', $d);
		$linkArr['d'] = $d;
		
		$pic = (int) $this->_param('pic');
		$this->assign('pic', $pic);
		$linkArr['pic'] = $pic;
		
		
		$order = (int) $this->_param('order');
        $this->assign('order', $order);
        $linkArr['order'] = $order;
		
		$this->assign('nextpage', LinkTo('delivery/loaddata',$linkArr,array('t' => NOW_TIME,'p' => '0000')));
        $this->assign('detail', $detail = D('Delivery')->find($id));
		$this->assign('tags', D('DeliveryCommentTag')->order(array('orderby'=>'asc'))->where(array('closed' =>'0'))->select());
		$this->assign('linkArr',$linkArr);
        $this->display();
    }
	
	
    public function loaddata(){
        $id = (int) $this->_param('id');
        if(!($detail = D('Delivery')->find($id))){
            die('0');
        }
        $obj = D('DeliveryComment');
        import('ORG.Util.Page');
		$map = array('closed' => 0, 'delivery_id' =>$id);
		
      
		
		if($d = (int) $this->_param('d')){
			if($d == 1){
				$map['d1'] = array('IN',1,2);
			}
            if($d == 2){
				$map['d1'] = array('IN',3);
			}
			if($d == 3){
				$map['d1'] = array('IN',4,5);
			}
			$this->assign('d', $d);
			$linkArr['d'] = $d;
        }
		
		$order = $this->_param('order', 'htmlspecialchars');
        switch($order){
            case '1':
                $orderby = array('create_time' => 'asc');
                break;
			default:
                $orderby = array('comment_id' => 'desc');
                break;
        }
		$this->assign('order',$order);
		
		$tag_id = $this->_param('tag_id');
		
		$lists = $obj->order($orderby)->where($map)->select();
		foreach ($lists as $kk => $vv){
            if(!empty($tag_id)){
                if(strpos($vv['tag'],$tag_id) === false){
                    unset($lists[$kk]);
                }
            }
        }

		$count = count($lists);
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $ids = $comment_ids = array();
        foreach($list as $k => $val){
            $list[$k] = $val;
            $ids[$val['id']] = $val['delivery_id'];
            $comment_ids[$val['comment_id']] = $val['comment_id'];
        }
        if(!empty($ids)){
            $this->assign('delivery', D('Delivery')->itemsByIds($ids));
        }
        if(!empty($comment_ids)){
            $this->assign('pics', D('DeliveryCommentPics')->where(array('comment_id' => array('IN', $comment_ids)))->select());
        }
		
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
     //点评
	 public function remark($order_id,$type = 0){
		if($this->_Post()){
            $data = $this->checkFields($this->_post('data', false), array('score','d1','d2','d3','content'));
			
			if(!$res = D('DeliveryOrder')->where(array('order_id'=>$order_id))->find()){
				$this->ajaxReturn(array('code'=>'0','msg'=>'配送订单不存在或者该订单不是配送员配送'));
			}
            $data['user_id'] = $this->uid;
			$data['shop_id'] = $res['shop_id'] ? $res['shop_id'] : '1';
            $data['type_order_id'] = $res['type_order_id'];
			$data['delivery_id'] = $res['delivery_id'];
                
            $data['score'] = (int) $data['score'];
            if(empty($data['score'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'评分不能为空'));
            }
            if($data['score'] > 5 || $data['score'] < 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>'评分为1-5之间的数字'));
            }
			$config = $config = D('Setting')->fetchAll();
			$data['d1'] = (int) $data['d1'];
			if(empty($data['d1'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>$config['appoint']['d1'].'评分不能为空'));
			}
			if($data['d1'] > 5 || $data['d1'] < 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>$config['appoint']['d1'].'格式不正确'));
			}
			$data['d2'] = (int) $data['d2'];
			if(empty($data['d2'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>$config['appoint']['d2'].'评分不能为空'));
			}
			if($data['d2'] > 5 || $data['d2'] < 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>$config['appoint']['d2'].'格式不正确'));
			}
			$data['d3'] = (int) $data['d3'];
			if(empty($data['d3'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>$config['appoint']['d3'].'评分不能为空'));
			}
			if($data['d3'] > 5 || $data['d3'] < 1){
				$this->ajaxReturn(array('code'=>'0','msg'=>$config['appoint']['d3'].'格式不正确'));
			}
            $data['content'] = htmlspecialchars($data['content']);
            if(empty($data['content'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'评价内容不能为空'));
            }
            if($words = D('Sensitive')->checkWords($data['content'])){
				$this->ajaxReturn(array('code'=>'0','msg'=>'评价内容含有敏感词：' . $words));
           }
           $data['create_time'] = NOW_TIME;
           $data['create_ip'] = get_client_ip();
		   
		   $tag = $this->_post('tag', false);
           $tag = implode(',', $tag);
           $data['tag'] = $tag;
           
           if($comment_id = D('DeliveryComment')->add($data)){
			    $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val){
                   if(isImage($val))
                     $local[] = $val;
                }
                if(!empty($local)){
                    foreach($local as $k=>$val){
                      D('DeliveryCommentPics')->add(array('comment_id'=>$comment_id,'order_id'=>$order_id,'photo'=>$val));
                   }
                }
				$this->ajaxReturn(array('code'=>'1','msg'=>'恭喜您点评成功','url'=>U('user/member/index')));  
            }
			$this->ajaxReturn(array('code'=>'0','msg'=>'点评失败'));
		}
	}
	
	
	//回复
    public function reply($comment_id = 0){
        if(empty($this->uid)){
             $this->error('请先登录后操作！',U('passport/login'));
        }
        if(!$comment_id = (int)$comment_id){
            $this->error('ID不存在');
        }elseif(!$dc = D('DeliveryComment')->find($comment_id)){
            $this->error('点评不存在');
        }elseif($dc['closed'] != 0){
            $this->error('点评已被删除');
        }else{
            if($this->isPost()){
				$data['comment_id'] = $comment_id;
                $data['reply'] = htmlspecialchars($this->_param('reply'));
                if(empty($data['reply'])){
					$this->ajaxReturn(array('code'=>'0','msg'=>'回复内容不能为空'));
                }
                if($words = D('Sensitive')->checkWords($data['reply'])) {
					$this->ajaxReturn(array('code'=>'0','msg'=>'回复内容含有敏感词：' . $words));
                }
                $data['user_id'] = $this->uid;
                $data['reply_time'] = NOW_TIME;
                $data['reply_ip'] = get_client_ip();
                if(D('DeliveryComment')->save($data)){
					$this->ajaxReturn(array('code'=>'1','msg'=>'回复成功','url'=>U('delivery/lists',array('id'=>$dc['delivery_id']))));
                }else{
					$this->ajaxReturn(array('code'=>'0','msg'=>'回复失败'));
                }
            }else{
                $this->assign('comment_id',$comment_id);
                $this->display();
            }
        }
    }
	
	
	 //配送员点评
	 public function comment($order_id,$type = 0){
		if(empty($this->uid)){
            $this->error('请登录后操作', U('passport/login'));
        }
        if(!$order_id = (int) $order_id){
            $this->error('订单ID不存在');
        }
		if(!$res = D('DeliveryOrder')->where(array('type_order_id'=>$order_id,'type'=>$type))->find()){
            $this->error('配送订单不存在或者该订单不是配送员配送');
        }
		if($res['status'] != 8){
            $this->error('该配送订单未完成');
        }
		if($res['closed'] != 0){
            $this->error('该配送订单已被删除');
        }
		if($dc = D('DeliveryComment')->where(array('order_id'=>$res['order_id'],'type_order_id'=>$res['type_order_id'],'user_id'=>$this->uid,'type'=>$type))->find()){
            $this->error('该配送订单您已经点评过了');
        }
		$this->assign('res', $res);
        $this->assign('detail', $detail);
        $this->assign('delivery',D('Delivery')->find($res['delivery_id']));
		$this->assign('tags', D('DeliveryCommentTag')->order(array('orderby' => 'asc'))->where(array('closed' => '0'))->select());
        $this->display();
       
  }
	
	

}