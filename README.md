公众号信息：
#####AppID
    wxa2f870f4589a316d
#####AppSecret
    b860ab28891d59bedc977830e7d23b4f
#####登录邮箱
    505088726@qq.com
#####原始ID
    gh_f338283df545
#####配置中心 Token(guodandan  32位md5)
    15a09d29a49d45aec8283ee402c8f444
#####2019-12-20 修改微信accessToken的存储，存储到数据库
    shop_id为0 为了兼容 多商户，因为需要实现多商户多公众号
    DROP TABLE IF EXISTS `tu_shop_weixin_access`;
    CREATE TABLE `tu_shop_weixin_access`  (
      `id` int(11) NOT NULL,
      `shop_id` int(11) NULL DEFAULT 0 COMMENT '适用于多商户，单商户或主站默认为0 ',
      `access_token` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信的access_token，接口调用秘钥',
      `expir_time` datetime NULL DEFAULT NULL COMMENT '理想access_token到期时间，create_time+7000秒，小于2个小时 方式失效',
      `real_expir_time` datetime NULL DEFAULT NULL COMMENT '实际access_token时间，其实等同于下一条同shop_id的create_time',
      `status` tinyint(2) NULL DEFAULT NULL COMMENT '状态 0生效中，1已失效',
      `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
      PRIMARY KEY (`id`) USING BTREE
    ) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic; 