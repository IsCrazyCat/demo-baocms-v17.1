<?php
class BaseAction extends Action
{
    //请求成功,并返回数据
    const TU_REQUEST_SUCCESS = 200;
    //已经登录提示
    const TU_LOGIN_ALREADY = 201;
    //登录成功提示
    const TU_LOGIN_SUCCESS = 202;
    //登录信息错误
    const TU_LOGIN_ERROR = 203;
    //登录账号不能为空
    const TU_LOGIN_ACCOUNT_ERROR = 204;
    //登录密码不能为空
    const TU_LOGIN_PSWD_ERROR = 205;
    //注册密码不一致
    const TU_REG_PSWD_ERROR = 206;
    //输入错误
    const TU_INPUT_ERROR = 301;
    //数据库错误
    const TU_DB_ERROR = 303;
    //验证码错误
    const TU_SCODE_ERROR = 304;
    //验证码或密码不匹配
    const TU_NOTSAME_ERROR = 305;
    //手机号已存在
    const TU_PHONE_EXIST_ERROR = 306;
    //错误的手机号
    const TU_PHONE_ERROR = 307;
    //优惠卷
    const TU_COUPON_ERROR = 308;
    //该优惠券已经过期
    const TU_COUPON_EXPIRES = 309;
    //该优惠券已经下载完了
    const TU_COUPON_NONUM = 310;
    //超过下载该优惠券的限制了！
    const TU_COUPON_LIMITED = 311;
    //非本人的优惠卷
    const TU_COUPON_OWNSHIP = 312;
    //优惠卷不存在
    const TU_COUPON_NO_EXSITS = 313;
    //404
    const TU_PAGE_NO_EXSITS = 404;
    //未登录或登录状态不正确！
    const TU_LOGIN_NO_REG = 314;
    //未登录或登录状态不正确！
    const TU_REG_NO_FIND = 315;
    //创建失败
    const TU_ADD_FALSE = 316;
    //修改失败
    const TU_EDIT_FALSE = 317;
    //删除失败
    const TU_DELETE_FALSE = 318;
    //无权限操作
    const TU_PERMISSION_NO_OPERATION = 319;
    //表单传值错误
    const TU_FROM_FALSE = 320;
    //数据不存在
    const TU_DETAIL_NO_EXSITS = 321;
    //没有有效的支付记录！
    const TU_LOGS_NO_PAYS = 322;
    //两次密码输入不一致！
    const TU_PWD_NO_AGREE = 323;
    //原密码输入不正确！
    const TU_PWD_NO_FALSE = 324;
    //您已经关注过该商家了！
    const TU_FAVOR_IS_TRUE = 325;
    //您未关注！
    const TU_FAVOR_IS_FALSE = 326;
    //验证码错误
    // const TU_SCODE_ERROR       = 304;
    //验证码为空
    const TU_SCODE_EMPTY = 327;
    //验证码不匹配
    const TU_SCODE_NOTSAME = 328;
    //用户名不存在
    const TU_USER_NOT_EXISTS = 329;
    //序列化数据
    protected function stringify($data = null){
        if (!$data) {
            return false;
        }
        exit(json_encode($data));
    }
    //反序列化数据
    protected function parse($data = null){
        if (!$data) {
            return false;
        }
        return json_decode($data, true);
    }
}