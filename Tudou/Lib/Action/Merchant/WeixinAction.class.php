<?php
class WeixinAction extends CommonAction
{
    public function menu()
    {
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            D('Weixin')->weixinmenu($data, $this->shop_id);
            $data = serialize($data);
            D('Shopdetails')->upDetails($this->shop_id, array('menus' => $data));
            $this->tuSuccess('设置成功', U('weixin/menu'));
        } else {
            $details = D('Shopdetails')->find($this->shop_id);
            $this->assign('weixin', unserialize($details['menus']));
            $this->display();
        }
    }
    public function index()
    {
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['is_dingyue'] = (int) $data['is_dingyue'];
            $data['token'] = htmlspecialchars($data['token']);
            $data['app_id'] = htmlspecialchars($data['app_id']);
            $data['app_key'] = htmlspecialchars($data['app_key']);
            if (empty($data['token']) || empty($data['app_id']) || empty($data['app_key'])) {
                $this->tuError('请填写微信对接的相关设置');
            }
            $weixin_msg = $this->_post('weixin_msg', false);
            if ($weixin_msg) {
                foreach ($weixin_msg as $k => $val) {
                    $weixin_msg[$k] = htmlspecialchars($val);
                }
            }
            $data['weixin_msg'] = serialize($weixin_msg);
            if (false !== D('Shopdetails')->upDetails($this->shop_id, $data)) {
                $this->tuSuccess('微信配置设置成功', U('weixin/index'));
            }
            $this->tuError('请填写微信对接的相关设置');
        } else {
            $details = D('Shopdetails')->find($this->shop_id);
            $details['token'] = empty($details['token']) ? md5(NOW_TIME . rand(0, 1000)) : $details['token'];
            $details['weixin_msg'] = unserialize($details['weixin_msg']);
            $this->assign('details', $details);
            $this->display();
        }
    }
    public function fans()
    {
        $fans = D('Fans');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $fans->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $fans->where($map)->order(array('fid' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
        }
        if ($user_ids) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}