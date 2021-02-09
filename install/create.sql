/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40101 SET FOREIGN_KEY_CHECKS = 0 */;


-- ----------------------------
-- Database `huankemao`
-- ----------------------------

-- --------------------------------------------------------

-- ----------------------------
-- Table structure for sys_app
-- ----------------------------
DROP TABLE IF EXISTS `sys_app`;
CREATE TABLE `sys_app`  (
  `id` varchar(36) NOT NULL COMMENT '应用配置id(主键)',
  `name` varchar(50) NOT NULL COMMENT '网站名称',
  `logo` varchar(255) NOT NULL COMMENT '网站logo',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '应用配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sys_user
-- ----------------------------
DROP TABLE IF EXISTS `sys_user`;
CREATE TABLE `sys_user`  (
  `id` varchar(36) NOT NULL COMMENT '用户id(主键)',
  `username` varchar(50)  COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '登录密码',
  `phone` varchar(11) NULL DEFAULT NULL COMMENT '手机号',
  `disable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '禁用状态：0正常 1禁用',
  `sign_up_at` timestamp NULL DEFAULT NULL COMMENT '注册时间',
  `department` varchar(255)  DEFAULT NULL COMMENT '部门',
  `token` varchar(255)  DEFAULT NULL COMMENT '用户密钥',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_sys_user_phone` (`phone`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sys_user_role
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_role`;
CREATE TABLE `sys_user_role`  (
  `user_id` varchar(36) NOT NULL COMMENT '用户id',
  `role_id` varchar(36) NOT NULL COMMENT '角色id',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`user_id`,`role_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户角色关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sys_role
-- ----------------------------
DROP TABLE IF EXISTS `sys_role`;
CREATE TABLE `sys_role`  (
  `id` varchar(36) NOT NULL COMMENT '用户id(主键)',
  `name` varchar(50) NOT NULL COMMENT '用户名',
  `disable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '禁用状态：0正常 1禁用',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sys_role_module
-- ----------------------------
DROP TABLE IF EXISTS `sys_role_module`;
CREATE TABLE `sys_role_module`  (
  `role_id` varchar(36) NOT NULL COMMENT '角色id',
  `module_id` varchar(36) NOT NULL COMMENT '模块id',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`role_id`,`module_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色模块表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sys_module
-- ----------------------------
DROP TABLE IF EXISTS `sys_module`;
CREATE TABLE `sys_module`  (
  `id` varchar(36) NOT NULL COMMENT '模块id(主键)',
  `code` int(11) NOT NULL DEFAULT 0 COMMENT '节点编号，跟节点编号1',
  `parent_code` int(11) NULL COMMENT '父节点编号',
  `tree_code` varchar(255) NULL COMMENT '节点树，英文半角逗号,隔开，根节点作为起点',
  `title` varchar(50) NULL DEFAULT NULL COMMENT '模块（菜单）标题',
  `uri` varchar(255) NULL DEFAULT NULL COMMENT '模块连接地址',
  `icon` varchar(50) NULL DEFAULT NULL COMMENT '模块图标',
  `sort` int(11) NULL DEFAULT 110 COMMENT '模块的排序',
  `is_menu` tinyint(1) NOT NULL DEFAULT 0 COMMENT '模块是否是菜单,0否 1是',
  `disable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '禁用状态：0正常 1禁用',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_sys_module_code` (`code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '模块表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_customer
-- ----------------------------
DROP TABLE IF EXISTS `wxk_customer`;
CREATE TABLE `wxk_customer`  (
  `id` varchar(36) NOT NULL COMMENT '企业微信客户id(主键)',
  `external_user_id` varchar(50) NOT NULL COMMENT '企业微信客户id',
  `name` varchar(50) NOT NULL COMMENT '企业微信客户昵称',
  `avatar` varchar(150) NOT NULL COMMENT '头像',
  `customer_type` tinyint(1) NOT NULL COMMENT '1-微信用户，2-企业微信用户',
  `gender` tinyint(1) NOT NULL COMMENT '性别 0-未知 1-男性 2-女性',
  `tag_ids` text NULL DEFAULT NULL COMMENT '客户标签，逗号分隔',
  `follow_userid` varchar(50) NOT NULL COMMENT '外部联系人的企业成员userid',
  `follow_remark` varchar(255) NULL DEFAULT NULL COMMENT '对此外部联系人的备注',
  `follow_description` varchar(255) NULL DEFAULT NULL COMMENT '对此外部联系人的描述',
  `follow_createtime` timestamp NULL DEFAULT NULL COMMENT '添加此外部联系人的时间',
  `follow_remark_mobiles` varchar(255) NULL DEFAULT NULL COMMENT '对此客户备注的手机号码（逗号间隔）',
  `follow_add_way` varchar(10) NOT NULL COMMENT '添加此客户的来源 0-未知来源 1-扫描二维码 2-搜索手机号 3-名片分享 4-群聊 5-手机通讯录 6-微信联系人 7-来自微信的添加好友申请 8-安装第三方应用时自动添加的客服人员 9-搜索邮箱 201-内部成员共享 202-管理员/负责人分配',
  `follow_oper_userid` varchar(50) NOT NULL COMMENT '发起添加的userid，如果成员主动添加，为成员的userid；如果是客户主动添加，则为客户的外部联系人userid；如果是内部成员共享/管理员分配，则为对应的成员/管理员userid',
  `follow_state` varchar(100) NULL DEFAULT NULL COMMENT '企业自定义的state参数，用于区分客户具体是通过哪个「联系我」添加',
  `follow_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-未跟进 2-跟进中 3-已拒绝 4-已成交',
  `follow_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '跟进时间',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信客户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_customer_tag
-- ----------------------------
DROP TABLE IF EXISTS `wxk_customer_tag`;
CREATE TABLE `wxk_customer_tag`  (
  `id` varchar(36) NOT NULL COMMENT '企业微信客户标签id(主键)',
  `code` int(11) NOT NULL AUTO_INCREMENT COMMENT '节点编号，跟节点编号1',
  `parent_code` int(11) NOT NULL DEFAULT 0 COMMENT '父节点编号',
  `name` varchar(50) NOT NULL COMMENT '标签名称',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_wxk_customer_tag_code` (`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信客户标签表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_config
-- ----------------------------
DROP TABLE IF EXISTS `wxk_config`;
CREATE TABLE `wxk_config`  (
  `id` varchar(36) NOT NULL COMMENT '企业微信账号配置id(主键)',
  `wxk_name` varchar(50) NULL DEFAULT NULL COMMENT '企业微信名称',
  `wxk_logo` varchar(255) NULL DEFAULT NULL COMMENT '企业微信logo',
  `wxk_id` varchar(36) NULL DEFAULT NULL COMMENT '企业微信ID',
  `wxk_app_agent_id` varchar(255) DEFAULT NULL COMMENT '应用ID',
  `wxk_app_secret` varchar(255) DEFAULT NULL COMMENT '应用密钥',
  `wxk_address_book_secret` varchar(255) NULL DEFAULT NULL COMMENT '通讯录密钥',
  `wxk_customer_admin_secret` varchar(255) NULL DEFAULT NULL COMMENT '客户管理密钥',
  `wxk_customer_callback_token` varchar(255) NULL DEFAULT NULL COMMENT '客户管理Token',
  `wxk_customer_callback_key` varchar(255) NULL DEFAULT NULL COMMENT '客户管理密钥',
  `wxk_customer_callback_url` varchar(255) DEFAULT NULL COMMENT '客户管理回调url',
  `wxk_public_app_id` varchar(255) DEFAULT NULL COMMENT '微信公众号APPID',
  `wxk_public_app_secret` varchar(255) DEFAULT NULL COMMENT '微信公众号secret',
  `domain_verification_file` varchar(255) DEFAULT NULL COMMENT '域名验证文件地址',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信账号配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_live_qr
-- ----------------------------
DROP TABLE IF EXISTS `wxk_live_qr`;
CREATE TABLE `wxk_live_qr`  (
  `id` varchar(36) NOT NULL COMMENT '活码id(主键)',
  `group_id` varchar(36) NOT NULL COMMENT '分组id',
  `name` varchar(60) NOT NULL COMMENT '活码名称',
  `qr_code` varchar(255) NOT NULL COMMENT '二维码链接',
  `tag_ids` text NULL COMMENT '客户标签ID，逗号间隔',
  `is_add_friends` tinyint(1) NOT NULL COMMENT '是否自动添加好友（2-选择时间段 1-全天开启 0-否 ）',
  `code_type` tinyint(1) NOT NULL COMMENT '活码成员类型（1-单人 2-多人 ）',
  `wxk_staff_id` varchar(255) NULL DEFAULT NULL COMMENT '使用该联系方式的成员userID列表，在type为1时为必填，且只能有一个',
  `wxk_department_id` varchar(255) NULL DEFAULT NULL COMMENT '使用该联系方式的部门id列表，只在type为2时有效',
  `is_special_period` tinyint(1) NULL DEFAULT NULL COMMENT '是否有特殊时期（1-是 0-否 ）',
  `is_add_limit` tinyint(1) NULL DEFAULT NULL COMMENT '是否有员工添加上限（1-是 0-否 ）',
  `spare_staff_id` varchar(255) DEFAULT NULL COMMENT '备用成员userid',
  `is_welcome_msg` tinyint(1) NOT NULL COMMENT '是否开启扫码推送欢迎语（1-是 0-否 ）',
  `welcome_data` text NULL COMMENT '欢迎语（json格式）',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信渠道活码表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_live_qr_group
-- ----------------------------
DROP TABLE IF EXISTS `wxk_live_qr_group`;
CREATE TABLE `wxk_live_qr_group`  (
  `id` varchar(36) NOT NULL COMMENT '活码分组id(主键)',
  `code` int(11) NOT NULL AUTO_INCREMENT COMMENT '节点编号，跟节点编号1',
  `parent_code` int(11) NULL DEFAULT 0 COMMENT '父节点编号',
  `name` varchar(60) NOT NULL COMMENT '分组名称',
  `amount` int(11) NULL DEFAULT 0 COMMENT '活码数量',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_wxk_live_qr_group_code` (`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信渠道活码分组表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_department
-- ----------------------------
DROP TABLE IF EXISTS `wxk_department`;
CREATE TABLE `wxk_department`  (
  `id` varchar(36) NOT NULL COMMENT '企业部门id(主键)',
  `code` int(11) NOT NULL COMMENT '节点编号，跟节点编号1（注：企业微信部门ID）',
  `parent_code` int(11) NOT NULL DEFAULT 0 COMMENT '父节点编号',
  `name` varchar(100) NOT NULL COMMENT '部门名称',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_wxk_department_code` (`code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信企业部门表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_staff
-- ----------------------------
DROP TABLE IF EXISTS `wxk_staff`;
CREATE TABLE `wxk_staff`  (
  `id` varchar(36) NOT NULL COMMENT '企业部门id(主键)',
  `name` varchar(255) NOT NULL COMMENT '昵称',
  `user_id` varchar(100) NOT NULL COMMENT '企业微信userid',
  `department_id` varchar(255) NOT NULL COMMENT '部门ID，逗号间隔',
  `tag_ids` text COMMENT '成员标签，逗号分隔（code）',
  `mobile` varchar(15) NOT NULL COMMENT '电话',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '激活状态: 1=已激活，2=已禁用，4=未激活，5=退出企业。',
  `gender` tinyint(1) NOT NULL DEFAULT 0 COMMENT '性别。0表示未定义，1表示男性，2表示女性',
  `avatar` varchar(255) NOT NULL COMMENT '头像url',
  `qr_code` varchar(255) NULL DEFAULT NULL COMMENT '员工个人二维码，扫描可添加为外部联系人',
  `external_authority` tinyint(1) NOT NULL DEFAULT 0 COMMENT '外部联系人权限 1-是 0-否',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信员工表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for wxk_app
-- ----------------------------
DROP TABLE IF EXISTS `wxk_app`;
CREATE TABLE `wxk_app`  (
  `id` varchar(36) NOT NULL COMMENT '员工id(主键)',
  `name` varchar(30) NOT NULL COMMENT '应用名称',
  `logo` varchar(100) NOT NULL COMMENT '应用logo',
  `trusted_domain` varchar(255) NOT NULL COMMENT '可信域名',
  `homepage_address` varchar(255) NOT NULL COMMENT '主页地址',
  `location` varchar(255) NULL DEFAULT NULL COMMENT '地理位置',
  `application_event` varchar(255) NULL DEFAULT NULL COMMENT '用户进入应用事件',
  `check_file` varchar(255) NULL DEFAULT NULL COMMENT '校验文件',
  `content_url` varchar(255) NULL DEFAULT NULL COMMENT '内容引擎页面地址',
  `client_url` varchar(255) NULL DEFAULT NULL COMMENT '客户详情页地址',
  `red_envelope_url` varchar(255) NULL DEFAULT NULL COMMENT '发红包地址',
  `message_url` varchar(255) NULL DEFAULT NULL COMMENT '应用接收消息URL',
  `token` varchar(255) NULL DEFAULT NULL COMMENT 'Token',
  `encoding_aeskey` varchar(255) NULL DEFAULT NULL COMMENT 'EncodingAESKey',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信应用配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_live_qr_statistics
-- ----------------------------
DROP TABLE IF EXISTS `wxk_live_qr_statistics`;
CREATE TABLE `wxk_live_qr_statistics`  (
  `id` varchar(36) NOT NULL COMMENT 'ID主键',
  `add_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '统计来源 1- 其他， 2 -活码',
  `live_qr_id` varchar(36) DEFAULT NULL COMMENT '活码ID',
  `user_id` varchar(100) NOT NULL COMMENT '企业微信userid',
  `external_user_id` varchar(100) NOT NULL COMMENT '企业微信客户id',
  `add_customer` tinyint(1) DEFAULT '0' COMMENT '新增客户',
  `deleted_customer` tinyint(1) DEFAULT '0' COMMENT '客户被删除',
  `deleted_staff` tinyint(1) DEFAULT '0' COMMENT '成员被删除',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '客户统计表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cms_content_engine
-- ----------------------------

DROP TABLE IF EXISTS `cms_content_engine`;
CREATE TABLE `cms_content_engine`  (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36)  COMMENT '添加子账户',
  `title` varchar(255) DEFAULT '' COMMENT '标题',
  `content` mediumtext  NULL COMMENT '内容',
  `file_name` varchar(255) NULL DEFAULT NULL COMMENT '文件名',
  `file_suffix` varchar(255) NULL DEFAULT NULL COMMENT '文件后缀',
  `explain` varchar(255)NULL DEFAULT NULL COMMENT '说明',
  `content_group_id` varchar(36)  DEFAULT '1' COMMENT '分组ID',
  `type` int(11) NULL DEFAULT NULL COMMENT '类型 1.文本 2.图片 3.图文 4.音频 5.视频 6.小程序 7.文件 8.跳转链接',
  `source` int(1) NULL DEFAULT 1 COMMENT '素材来源 1.素材库 2.正式发布',
  `user` varchar(50)  NULL DEFAULT NULL COMMENT '上传者 默认手机号',
  `cover` varchar(255) NULL DEFAULT NULL COMMENT '封面',
  `wx_cover` varchar(255)  NULL DEFAULT NULL COMMENT '企业微信封面',
  `link` varchar(255) NULL DEFAULT NULL COMMENT '原文链接',
  `summary` varchar(255)  NULL DEFAULT NULL COMMENT '摘要',
  `media_id` varchar(255)  NULL DEFAULT NULL COMMENT '微信素材ID',
  `created_at` int(11) NULL DEFAULT NULL COMMENT '微信素材创建时间',
  `applets_id` varchar(255)  NULL DEFAULT NULL COMMENT '小程序ID',
  `applets_path` varchar(255)  NULL DEFAULT NULL COMMENT '小程序路径',
  `create_date` date NULL DEFAULT NULL COMMENT '创建时间',
  `create_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for cms_content_group
-- ----------------------------
DROP TABLE IF EXISTS `cms_content_group`;
CREATE TABLE `cms_content_group` (
  `id` varchar(36) NOT NULL COMMENT '主键',
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `parent_id` varchar(36) DEFAULT NULL COMMENT '父级ID',
  `purview` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-通用 2-素材库 3-正式发布',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT='素材组表';

-- ----------------------------
-- Table structure for cms_content_operating
-- ----------------------------
DROP TABLE IF EXISTS `cms_content_operating`;
CREATE TABLE `cms_content_operating`  (
  `id` varchar(36)  NOT NULL COMMENT 'id',
  `search_name` varchar(255)  NULL DEFAULT NULL COMMENT '搜索名称',
  `search_num` tinyint(1) NULL DEFAULT 0 COMMENT '搜索次数',
  `send_num` tinyint(1) NULL DEFAULT 0 COMMENT '发送次数',
  `open_num` tinyint(1) NULL DEFAULT 0 COMMENT '打开次数',
  `content_engine_id` varchar(36)  NULL DEFAULT NULL COMMENT '内容id',
  `content_engine_group_id` varchar(36)  NULL DEFAULT NULL COMMENT '分组ID',
  `content_engine_type` int(11) NULL DEFAULT NULL COMMENT '内容类型 ',
  `content_engine_title` mediumtext COMMENT '内容标题',
  `wx_customer_id` varchar(36)  NULL DEFAULT NULL COMMENT '企业微信客户ID',
  `wx_user_id` varchar(36)  NULL DEFAULT NULL COMMENT '企业微信成员ID',
  `created_at` date NULL DEFAULT NULL COMMENT '创建时间',
  `create_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  `update_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_live_qr_add_limit
-- ----------------------------
DROP TABLE IF EXISTS `wxk_live_qr_add_limit`;
CREATE TABLE `wxk_live_qr_add_limit` (
  `id` varchar(36) NOT NULL COMMENT 'ID主键',
  `live_qr_id` varchar(36) NOT NULL COMMENT '活码ID',
  `user_id` varchar(100) NOT NULL COMMENT '企业微信userid',
  `add_limit` int(11) NOT NULL COMMENT '成员上限',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT='活码成员添加上限表';

-- ----------------------------
-- Table structure for wxk_welcome
-- ----------------------------
DROP TABLE IF EXISTS `wxk_welcome`;
CREATE TABLE `wxk_welcome` (
  `id` varchar(36) NOT NULL COMMENT '主键',
  `welcome_type` varchar(50) NOT NULL COMMENT '欢迎语类型',
  `welcome_data` text NOT NULL COMMENT '欢迎语内容',
  `user_id` text NOT NULL COMMENT '成员userid，通用类型为 0',
  `user_name` text COMMENT '成员名称',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT='欢迎语';

-- ----------------------------
-- Table structure for article_reading_log
-- ----------------------------
DROP TABLE IF EXISTS `wxk_article_reading_log`;
CREATE TABLE `wxk_article_reading_log` (
  `id` varchar(36) NOT NULL,
  `content_id` varchar(255) NOT NULL COMMENT '内容ID',
  `openid` varchar(36) NOT NULL COMMENT '微信用户的唯一标识',
  `staff_user_id` varchar(255) NOT NULL COMMENT '分享人id  (企业微信user_id)',
  `headimgurl` varchar(255) DEFAULT NULL COMMENT '用户头像，用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  `nickname` varchar(255) DEFAULT NULL COMMENT '用户昵称',
  `gender` tinyint(1) DEFAULT NULL COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `province` varchar(255) DEFAULT NULL COMMENT '用户个人资料填写的省份',
  `city` varchar(255) DEFAULT NULL COMMENT '普通用户个人资料填写的城市',
  `state` tinyint(1) DEFAULT '0' COMMENT '客户状态 0-未知',
  `reading_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '阅读时间',
  `reading_duration` int(11) NOT NULL COMMENT '阅读时长（秒）',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT='文章阅读详情表';

-- ----------------------------
-- Table structure for cms_tuoke_project
-- ----------------------------
DROP TABLE IF EXISTS `cms_tuoke_project`;
CREATE TABLE `cms_tuoke_project` (
  `id` varchar(36)  NOT NULL COMMENT '主键',
  `date_year` int(11) NOT NULL COMMENT '年份',
  `year_target` int(11) NOT NULL COMMENT '年度目标',
  `one_quarter` int(11) DEFAULT NULL COMMENT '第一季度',
  `two_quarter` int(11) DEFAULT NULL COMMENT '第二季度',
  `three_quarter` int(11) DEFAULT NULL COMMENT '第三季度',
  `four_quarter` int(11) DEFAULT NULL COMMENT '第四季度',
  `one_month` int(11) DEFAULT NULL COMMENT '一月',
  `tow_month` int(11) DEFAULT NULL COMMENT '二月',
  `three_month` int(11) DEFAULT NULL COMMENT '三月',
  `four_month` int(11) DEFAULT NULL COMMENT '四月',
  `five_month` int(11) DEFAULT NULL COMMENT '五月',
  `six_month` int(11) DEFAULT NULL COMMENT '六月',
  `seven_month` int(11) DEFAULT NULL COMMENT '七月',
  `eight_month` int(11) DEFAULT NULL COMMENT '八月',
  `nine_month` int(11) DEFAULT NULL COMMENT '九月',
  `ten_month` int(11) DEFAULT NULL COMMENT '十月',
  `eleven_month` int(11) DEFAULT NULL COMMENT '十一月',
  `twelve_month` int(11) DEFAULT NULL COMMENT '十二月',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT='拓客计划';

-- ----------------------------
-- Table structure for wxk_staff_behavior
-- ----------------------------
DROP TABLE IF EXISTS `wxk_staff_behavior`;
CREATE TABLE `wxk_staff_behavior` (
  `id` varchar(36) NOT NULL COMMENT '主键',
  `staff_user_id` varchar(255) NOT NULL COMMENT '成员user_id',
  `day_new_apply_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '发起申请数',
  `day_new_contact_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '新增客户数',
  `day_chat_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '聊天总数',
  `day_message_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '发送消息数',
  `day_reply_percentage` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已回复聊天占比',
  `day_avg_reply_time` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '平均首次回复时长，单位为分钟',
  `week_new_apply_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '发起申请数',
  `week_new_contact_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '新增客户数',
  `week_chat_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '聊天总数',
  `week_message_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '发送消息数',
  `week_reply_percentage` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已回复聊天占比',
  `week_avg_reply_time` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '平均首次回复时长，单位为分钟',
  `month_new_apply_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '发起申请数',
  `month_new_contact_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '新增客户数',
  `month_chat_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '聊天总数',
  `month_message_cnt` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '发送消息数',
  `month_reply_percentage` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已回复聊天占比',
  `month_avg_reply_time` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '平均首次回复时长，单位为分钟',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT='成员行为数据';


-- ----------------------------
-- Table structure for wxk_staff_tag
-- ----------------------------
DROP TABLE IF EXISTS `wxk_staff_tag`;
CREATE TABLE `wxk_staff_tag`  (
  `id` varchar(36) NOT NULL COMMENT '企业微信成员标签id(主键)',
  `code` int(11) NOT NULL AUTO_INCREMENT COMMENT '节点编号(对应企业微信ID)',
  `group_id` varchar(36) NOT NULL DEFAULT '0' COMMENT '组ID',
  `name` varchar(50) NOT NULL COMMENT '标签名称',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_wxk_staff_tag_code` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '企业微信成员标签表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wxk_staff_tag_group
-- ----------------------------
DROP TABLE IF EXISTS `wxk_staff_tag_group`;
CREATE TABLE `wxk_staff_tag_group` (
  `id` varchar(36) NOT NULL COMMENT '企业微信成员标签组ID(主键)',
  `name` varchar(50) NOT NULL COMMENT '标签组名称',
  `child_code` text COMMENT '子级code',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='企业微信成员标签组表';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET FOREIGN_KEY_CHECKS = 1 */;


