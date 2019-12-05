<?php



class ArticleAction extends CommonAction{
	
	
	protected function _initialize(){
        parent::_initialize();
    }
	
	//新怎广告
	public function Ad(){
		$list = D('Ad')->where(array('site_id'=>'57','closed'=>'0'))->select();
		foreach ($list as $k => $val){
			$list[$k]['type'] = 3;
			$list[$k]['id'] = $val['ad_id'];
			$list[$k]['img'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	 //资讯分类
  public function Type(){
	  $res = D('ArticleCate')->where(array('parent_id'=>'0'))->limit(0,10)->select();
      echo json_encode($res);
  }

  
  //资讯列表
  public function Lists(){
		$obj = D('Article');
		import('ORG.Util.Page');
		$map = array('audit' => 1, 'closed' => 0);
		if($cate_id = I('cate_id','','trim')){
           $map['type_id'] = $type_id;
        }
		$cat = I('cate_id','','trim');       
        if($cat){
            $catids = D('Articlecate')->getChildren($cat);
            if(!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            }
        }
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['id'] = $val['article_id'];
			$list[$k]['time'] = $val['create_time'];
			$list[$k]['img'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
			$list[$k]['imgs'] = $this->getArticlePhotos($val['article_id']);
		}
		echo json_encode($list);
	}
	
	public function getArticlePhotos($article_id){
		$list = D('Articlephoto')->where(array('article_id'=>$article_id))->select();
		foreach($list as $k => $val){
			$photos[$k] = $val['photo'];
		}
		$Article = D('Article')->find($article_id);
		if($Article['photo']){
			$photo = config_weixin_img($Article['photo']);
			array_unshift($photos,$photo);
		}
		$res = implode(",",$photos);
		return "".$res ."";
	}
	
  
  //资讯详情
  public function detail(){
	  $article_id = I('id','','trim');  
	  D('Article')->updateCount($article_id,'views');
      $res = D('Article')->find($article_id);
	  $res['detail'] = cleanhtml($res['details']);
	  $res['time'] = $res['create_time'];
	  $res['cerated_time'] = formatTime($res['create_time']);
	  $res['img'] = config_weixin_img($res['photo']);
	  $res['imgs'] = $this->getArticlePhotos($article_id);
	  $res['dz']= 1;
      echo json_encode($res);
  }


  
   //评论列表
  public function comment_list(){
	  $article_id = I('zx_id','','trim');  
	  $res = D('Articlecomment')->where(array('article_id'=>$article_id,'parent_id'=>0))->limit(0,20)->select(); 
	  foreach($res as $k => $val){
		  $Users = D('Users')->find($val['user_id']);
		  $res[$k]['user'] = $Users;
		  $res[$k]['img'] = config_weixin_img($Users['face']);
		  $res[$k]['name'] = config_user_name($Users['nickname']);
		  $res[$k]['cerated_time'] = formatTime($val['create_time']);
	  }
      echo json_encode($res);
  }
  
  
  
  
  //回复
  public function reply(){
	  $data['user_id'] = I('user_id','','trim');
	  $Users = D('Users')->find($data['user_id']);
	  $data['nickname'] = $Users['nickname'];
	  $data['user_id'] = $data['user_id'];
	  $data['post_id'] = I('article_id','','trim');
	  $data['content'] = I('reply','','trim,htmlspecialchars');
	  $data['zan'] =0;
	  $data['audit'] = 1;
	  $data['create_time'] = NOW_TIME;
	  $data['create_ip'] = get_client_ip();
	  if(D('Articlecomment')->add($data)){
		echo '1';
	  }else{
		echo '2'; 
	  }
	  
  }
 
  
	
}