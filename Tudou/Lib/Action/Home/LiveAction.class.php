<?php 
class LiveAction extends CommonAction {
	
	public function _initialize(){
        parent::_initialize();
        $this->shopcates = D('Shopcate')->fetchAll();
        $this->assign('shopcates', $this->shopcates);
        $this->assign('host', __HOST__);
    }
	
	public function index(){
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $cates = D('Shopcate')->fetchAll();
        $linkArr = array();
        $map = array('a.closed' => 0, 'a.audit' => 1, 'a.city_id' => $this->city_id,'b.live_id!=0');
        $cat = (int) $this->_param('cat');
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            if (!empty($cate_id)) {
                $map['a.cate_id'] = $cate_id;
                $this->seodatas['cate_name'] = $cates[$cate_id]['cate_name'];
                $linkArr['cat'] = $cat;
                $linkArr['cate_id'] = $cate_id;
            } else {
                $catids = D('Shopcate')->getChildren($cat);
                if (!empty($catids)) {
                    $map['a.cate_id'] = array('IN', $catids);
                }
                $this->seodatas['cate_name'] = $cates[$cat]['cate_name'];
                $linkArr['cat'] = $cat;
            }
        }
        $this->assign('cat', $cat);
        $this->assign('cate_id', $cate_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['a.shop_name|tags'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $this->assign('searchindex', 0);
        $area = (int) $this->_param('area');
        if ($area) {
            $map['a.area_id'] = $area;
            $this->seodatas['area_name'] = $this->areas[$area]['area_name'];
            $linkArr['area'] = $area;
        }
        $this->assign('area_id', $area);
        $business = (int) $this->_param('business');
        if ($business) {
            $map['a.business_id'] = $business;
            $this->seodatas['business_name'] = $this->bizs[$business]['business_name'];
            $linkArr['business'] = $business;
        }
        $this->assign('business_id', $business);
        $areas = D('Area')->fetchAll();
        $this->assign('areas', $areas);
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 't':
                $orderby = array('a.shop_id' => 'desc');
                break;
            case 'x':
                $orderby = array('a.score' => 'desc');
                break;
            case 'h':
                $orderby = array('a.view' => 'desc');
                break;
            default:
                $orderby = array('a.orderby' => 'asc');
                break;
        }
        if (empty($order)) {
            $order = 'd';
        }
        $this->assign('order', $order);
        $count = $Shop->alias('a')->where($map)->join('bao_live b on a.shop_id=b.shop_id')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Shop->alias('a')->order($orderby)->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->join('bao_live b on a.shop_id=b.shop_id')->field('a.view,a.score,b.*')->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('details', D('Shopdetails')->itemsByIds($shop_ids));
        $this->assign('total_num', $count);
        $this->assign('areas', $areas);
        $this->assign('cates', $cates);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('ctl','live');
        $this->assign('linkArr', $linkArr);
        $this->display();
    }

    public function view(){
        $live_id = I('get.live_id');
        $this->assign('live',$live = M('live')->where('live_id='.$live_id)->find());
        $this->assign('shop',$shop = M('shop')->where('shop_id='.$live['shop_id'])->find());
        $this->assign('shop_details',$shop_details = M('shop_details')->where('shop_id='.$live['shop_id'])->find());
        $this->assign('list',$list = M('live')->where('shop_id='.$live['shop_id'])->select());
        $this->assign('ctl','live');
        $url = str_replace("http://","",$live['url']);
        $url = explode("/",$url);
        $this->assign('url',json_encode($url));
		D('Live')->updateCount($live_id, 'views');
        $this->display();
    }
}