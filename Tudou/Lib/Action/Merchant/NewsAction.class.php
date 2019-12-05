<?php
class NewsAction extends CommonAction{
    private $edit_fields = array('title', 'photo', 'details', 'cate_id', 'keywords', 'profiles');
    public function index(){
        $Shopnews = D('Shopnews');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id,'closed'=>0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Shopnews->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shopnews->where($map)->order(array('news_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->editCheck();
            //这里和 编辑的字段差不多
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $articles = array(
				'shop_id' => $this->shop_id, 
				'cate_id' => $data['cate_id'], 
				'city' => $data['cate_id'], 
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
                $this->tuSuccess('添加成功', U('news/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('cates', D('Articlecate')->fetchAll());
            $this->display();
        }
    }
    public function edit($news_id = 0)
    {
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
            if (false !== $obj->save($data)) {
                $this->tuSuccess('操作成功', U('news/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('cates', D('Articlecate')->fetchAll());
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $shop = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        $data['shop_id'] = $this->shop_id;
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['source'] = $shop['shop_name'];
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('分类不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('标题不能为空');
        }
        $data['keywords'] = htmlspecialchars($data['keywords']);
        $data['profiles'] = SecurityEditorHtml($data['profiles']);
        if (empty($data['profiles'])) {
            $this->tuError('简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['profiles'])) {
            $this->tuError('简介内容含有敏感词：' . $words);
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
        return $data;
    }
	
	
	 public function sms($news_id = 0) {
       		$news_id = (int) $news_id;
 			$obj = D('Shopnews');
			if (!$detail = $obj->find($news_id)) {
                $this->error('推送的文章不存在');
            }
            if ($detail['closed'] == 1) {
                $this->error('文章已被删除');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('非法操作');
            }
			$shop = D('Shop') ->where(array('shop_id'=>$this->shop_id))-> find();
			$shop_user = D('Shopfavorites') ->where(array('is_sms'=>1,'shop_id'=>$this->shop_id))-> select();
			foreach ($shop_user as $val) {
               $user_ids[$val['user_id']] = $val['user_id'];//对比shop_id
            }

			$map = array();
			$map['user_id']  = array('in',$user_ids);
			$map['mobile'] = array('neq','');
		    $users = D('Users') ->where($map)-> select();
			$time = date('Y-m-d');

			foreach($users as $k => $value){
				D('Sms')->sms_shop_news_push($detail,$value['mobile']);//批量推送短信
			}
		   $obj->where(array('news_id'=>$news_id))->save(array('is_tuisong_sms'=>1,'is_tuisong_sms_time'=>NOW_TIME,));//更新数据库
		   $this->tuSuccess('短信推送成功',U('news/index'));
    }
	
	 public function weixin($news_id = 0) {
       		$news_id = (int) $news_id;
 			$obj = D('Shopnews');
			if (!$detail = $obj->find($news_id)) {
                $this->error('推送的文章不存在');
            }
            if ($detail['closed'] == 1) {
                $this->error('文章已被删除');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('非法操作');
            }
			$shop = D('Shop') ->where(array('shop_id'=>$this->shop_id))-> find();
			$shop_user = D('Shopfavorites') ->where(array('is_weixin'=>1,'shop_id'=>$this->shop_id))-> select();
			foreach ($shop_user as $val) {
               $user_ids[$val['user_id']] = $val['user_id'];//对比shop_id
            }

			$map = array();
			$map['user_id']  = array('in',$user_ids);
		    $users = D('Users') ->where($map)-> select();
			$stringtime = date("Y-m-d H:i:s",time()); 

		    //微信批量推送
            foreach ($users as $k => $value) { 
		   		D('Weixinmsg')->weixin_shop_news_push($detail,$value['user_id']);
            } 
		   $obj->where(array('news_id'=>$news_id))->save(array('is_tuisong_weixin'=>1,'is_tuisong_weixin_time'=>NOW_TIME,));//更新数据库
		   $this->tuSuccess('微信推送成功',U('news/index'));
    }
	
	 public function msg($news_id = 0) {
				$news_id = (int) $news_id;
				$obj = D('Shopnews');
				if (!$detail = $obj->find($news_id)) {
					$this->error('推送的文章不存在');
				}
				if ($detail['closed'] == 1) {
					$this->error('文章已被删除');
				}
				if ($detail['shop_id'] != $this->shop_id) {
					$this->error('非法操作');
				}
				$shop = D('Shop') ->where(array('shop_id'=>$this->shop_id))-> find();
				$stringtime = date("Y-m-d H:i:s",time()); 	
				
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/wap/news/detail/news_id/' . $news_id . '.html';
				$arr = array();
				$arr['cate_id'] = 1;
				$arr['user_id'] = 0;
				$arr['title'] = $detail['title'];
				$arr['intro'] = $detail['intro'];
				$arr['link_url'] = $url;
				$arr['details'] = $detail['details'];
				$arr['create_time'] = time();
				$arr['create_ip'] = get_client_ip();
				$msg_id = D('Msg')->add($arr);
				
				if($msg_id){
					$obj->where(array('news_id'=>$news_id))->save(array('is_tuisong_msg'=>1,'is_tuisong_msg_time'=>NOW_TIME,));//更新数据库
					$this->tuSuccess('站内信推送成功',U('news/index'));
				}else{
					$this->tuError('操作失败');
				}
		
		   
		   
    }
}