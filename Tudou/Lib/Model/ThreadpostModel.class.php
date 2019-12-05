<?php



class ThreadpostModel extends CommonModel{
    protected $pk   = 'post_id';
    protected $tableName =  'thread_post';
	
	
	
	//短信通知
	public function noticeUserMsg($post_id,$comment_id=0){
		$config = D('Setting')->fetchAll();
		
		$post = D('Threadpost')->find($post_id);
		$comments = D('Threadpostcomments')->find($comment_id);
		
		$users = D('Users')->find($comments['user_id']);//回复人会员信息
		
		if($comments && $users){
			
			$url = $config['site']['host'].'/wap/thread/postdetail/post_id/' . $post_id . '.html';
			$arr['cate_id'] = 1;
			$arr['user_id'] = $post['user_id'];
			$arr['title'] = $users['nickname'].'回复了您的帖子【'.$post['title'].'】';
			$arr['intro'] = $comments['contents'];
			$arr['link_url'] = $url;
			$arr['details'] = $comments['contents'];
			$arr['create_time'] = time();
			$arr['create_ip'] = get_client_ip();
			
			$msg_id = D('Msg')->add($arr);//写入信息
			
			if($open_id = D('Connect')->where(array('uid'=>$post['user_id'],'type'=>'weixin'))->getField('open_id')){
				include_once 'Tudou/Lib/Net/Wxmesg.class.php';
				$data = array(
					'url' => $url, 
					'first' => '贴吧新回复通知', 
					'user_demand' => $post['title'], //客户要求
					'user_name' => $Users['nickname'] , //客户名称
					'time' => $users['nickname'],//提出时间
					'remark' => $users['nickname'].'回复了您的帖子【'.$post['title'].'】请点击下面的连接查看', 
				);
				$_data = Wxmesg::subscribe($data);
				Wxmesg::net($post['user_id'],'OPENTM207467627', $_data);
			}
		}
		return true;
	}
	
    
}