<?php
class CleanAction extends CommonAction{
	
	
	
    public function cache(){
        delFileByDir(APP_PATH . 'Runtime/');
        $time = NOW_TIME - 900;
        //15分钟的会删除
        M("session")->delete(array('where' => " session_expire < '{$time}' "));
		import('ORG.Util.File');
		$File = new File();
		$res = $File->rmFiles($path = 'Tudou/Runtime/Logs');
		$this->success('请求清理缓存成功【'.$res.'】', U('index/main'));
    }
	
	
	 public function qrcode(){
		import('ORG.Util.File');
		$File = new File();
		$res = $File->rmFiles($path = 'attachs/weixin');
		$res2 = $File->rmFiles($path = 'attachs/weixinuid');
		$this->success('请求清理缓存成功【'.$res.'】【'.$res2.'】', U('index/main'));
    }
	
	//清理用户海报
	public function poster(){
		$intro = $this->delUserPoster();
		$this->success($intro);
	}
	
	
	//删除会员海报封装函数
	public function delUserPoster(){
		$res = M('users')->where(array('poster' => array('neq',''),'closed' => 0))->select();
        if($res){
           $i = 0;
           foreach($res as $k => $v) {
			  M('users')->where('user_id',$v['user_id'])->save(array('poster'=>''));
			  $i++;
           }
		   import('ORG.Util.File');
		   $File = new File();
		   $res = $File->rmFiles($path = 'attachs/weixinuid');
           return '已删除海报人数【'.$i.'】';
        }
		return '暂无会员有海报';
	}
	
	
}