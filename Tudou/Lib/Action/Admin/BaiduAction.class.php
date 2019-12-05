<?php
class ShopnewsAction extends CommonAction{


      //qq 286099981
	  public function sitemap() {
            $list = D('Article')->field('article_id,create_time')->order('Article_id desc')->limit(10000)->select();
            $sitemap = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
            foreach($list as $k=>$v){
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].''.U('news/detail',array('article_id'=>$v['article_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
          $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].'/mobile'.U('news/detail',array('article_id'=>$v['article_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
            }
            $list2 = D('shop')->field('shop_id,create_time')->order('shop_id desc')->limit(10000)->select();
            foreach($list2 as $k=>$v1){
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].''.U('shop/detail',array('shop_id'=>$v1['shop_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v1['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].'/mobile'.U('shop/detail',array('shop_id'=>$v1['shop_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v1['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
            }
		    $list3 = D('goods')->field('goods_id,create_time')->order('goods_id desc')->limit(10000)->select();
            foreach($list3 as $k=>$v2){
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].''.U('mall/detail',array('goods_id'=>$v2['goods_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v2['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].'/mobile'.U('mall/detail',array('goods_id'=>$v2['goods_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v2['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
            }
			$list4 = D('hotel')->field('hotel_id,create_time')->order('hotel_id desc')->limit(10000)->select();
			  foreach($list4 as $k=>$v3){
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].''.U('hotel/detail',array('hotel_id'=>$v3['hotel_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v3['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].'/mobile'.U('hotel/detail',array('hotel_id'=>$v3['hotel_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v3['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
            }
			$list5 = D('tuan')->field('tuan_id,create_time')->order('tuan_id desc')->limit(10000)->select();
			  foreach($list5 as $k=>$v4){
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].''.U('tuan/detail',array('tuan_id'=>$v4['tuan_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v4['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
                $sitemap .= "<url>\r\n"."<loc>".$this->_CONFIG['site']['host'].'/mobile'.U('tuan/detail',array('tuan_id'=>$v4['tuan_id']))."</loc>\r\n"."<priority>0.6</priority>\r\n<lastmod>".date('Y-m-d',$v4['create_time'])."</lastmod>\r\n<changefreq>weekly</changefreq>\r\n</url>\r\n";
            }
            $sitemap .= '</urlset>';
            $file = fopen("sitemap.xml","w");
            fwrite($file,$sitemap);
            fclose($file);
            $this->tuSuccess('生成sitemap地图成功', U('baidu/index'));
        
    }
	
   public function baidu(){
		  $list = D('Article')->field('article_id,create_time')->order('Article_id desc')->limit(10000)->select();
          foreach($list as $k=>$v){
            $sitemap .= $this->_CONFIG['site']['host'].'/'.U('news/detail',array('article_id'=>$v['article_id']))."\n";
            $sitemap .=$this->_CONFIG['site']['host'].'/wap'.U('news/detail',array('article_id'=>$v['article_id']))."\n";
          }
          $list2 = D('shop')->field('shop_id,create_time')->order('shop_id desc')->limit(10000)->select();
          foreach($list2 as $k=>$v1){
             $sitemap .= $this->_CONFIG['site']['host'].'/'.U('shop/detail',array('shop_id'=>$v1['shop_id']))."\n";
             $sitemap .= $this->_CONFIG['site']['host'].'/wap'.U('shop/detail',array('shop_id'=>$v1['shop_id']))."\n";
          }
		  $list3 = D('goods')->field('goods_id,create_time')->order('goods_id desc')->limit(10000)->select();
          foreach($list3 as $k=>$v2){
             $sitemap .= $this->_CONFIG['site']['host'].'/'.U('mall/detail',array('goods_id'=>$v2['goods_id']))."\n";
             $sitemap .=$this->_CONFIG['site']['host'].'/wap'.U('mall/detail',array('goods_id'=>$v2['goods_id']))."\n";
          }
		  $list4 = D('hotel')->field('hotel_id,create_time')->order('hotel_id desc')->limit(10000)->select();
	      foreach($list4 as $k=>$v3){
             $sitemap .=$this->_CONFIG['site']['host'].'/'.U('hotel/detail',array('hotel_id'=>$v3['hotel_id']))."\n";
             $sitemap .= $this->_CONFIG['site']['host'].'/wap'.U('hotel/detail',array('hotel_id'=>$v3['hotel_id']))."\n";
          }
		  $list5 = D('tuan')->field('tuan_id,create_time')->order('tuan_id desc')->limit(10000)->select();
		  foreach($list5 as $k=>$v4){
              $sitemap .= $this->_CONFIG['site']['host'].'/'.U('tuan/detail',array('tuan_id'=>$v4['tuan_id']))."\n";
			  $sitemap .= $this->_CONFIG['site']['host'].'/wap'.U('tuan/detail',array('tuan_id'=>$v4['tuan_id']))."\n";
          }
          $file = fopen("baidu.xml","w");
          fwrite($file,$sitemap);
          fclose($file);
          $this->tuSuccess('生成XML百度地图成功', U('baidu/index'));
 
    }
    public function update(){
        if($this->isPost()){
            $file_path = "baidu.xml";
			if(file_exists($file_path)){
				$fp = fopen($file_path,"r");
				$str = fread($fp,filesize($file_path));//指定读取大小，这里把整个文件内容读取出来
			}
			$arr = array($str);
			$api = '百度站长工具的taoke';//自己填写你的，或者后台定义
			$ch = curl_init();
			$options =  array(
				CURLOPT_URL => $api,
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => implode($arr),
				CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
			);
			curl_setopt_array($ch, $options);
			$result = curl_exec($ch);
			$this->tuSuccess('提交百度地图成功'.$result, U('baidu/index'));
        }else{
             $this->tuError('来源错误');
        }
    }
   
}