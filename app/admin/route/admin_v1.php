<?php
/**
 * Created by PhpStorm.
 * User: 万奇
 * Date: 2020/11/19 0019
 * Time: 18:22
 */

use app\middleware\CheckPhone;
use think\facade\Route;

// 规则：（路由名禁止重复） Route::post('api_test', 'admin/'.$v.'.Test/test'); 访问方式域名admin/'.$v.'.api_test；
$version = request()->header('version');
$v = $version ? $version : 'v1';
// 测试
//Route::get('index' , 'admin/'.$v.'.Index/index');

// 新增编辑企业微信
Route::post('add_wxk_config', 'admin/' . $v . '.WxkConfig/add_wxk_config');

// 企业微信列表
Route::post('wxk_config_list', 'admin/' . $v . '.WxkConfig/wxk_config_list');

// 同步企业成员部门
Route::post('synchro_department', 'admin/' . $v . '.WxkStaff/synchro_department');

// 同步企业成员
Route::post('synchro_staff', 'admin/' . $v . '.WxkStaff/synchro_staff');

// 获取成员列表
Route::post('get_user_simple_list', 'admin/' . $v . '.WxkStaff/get_user_simple_list');

// 同步企业微信客户
Route::post('synchro_customer', 'admin/' . $v . '.WxkCustomer/synchro_customer');

// 客户列表
Route::post('get_list_customer', 'admin/' . $v . '.WxkCustomer/get_list_customer');

// 客户列表渲染
Route::post('show_list_customer', 'admin/' . $v . '.WxkCustomer/show_list_customer');

// 重复客户列表
Route::post('repeat_list_customer', 'admin/' . $v . '.WxkCustomer/repeat_list_customer');

// 客户打标签回显已有的标签
Route::post('show_customer_tag', 'admin/' . $v . '.WxkCustomer/show_customer_tag');

// 客户打标签/移除标签
Route::post('customer_tagging', 'admin/' . $v . '.WxkCustomer/customer_tagging');

// 添加企业微信标签
Route::post('add_corp_tag', 'admin/' . $v . '.LiveCode/add_corp_tag');

// 活码分组列表
Route::post('live_code_group', 'admin/' . $v . '.LiveCode/live_code_group');

// 同步企业成员
Route::post('synchro_staff', 'admin/' . $v . '.WxkStaff/synchro_staff');

// 同步成员行为数据
Route::post('sync_staff_behavior', 'admin/' . $v . '.WxkStaff/sync_staff_behavior');

// 获取成员列表
Route::post('get_user_simple_list', 'admin/' . $v . '.WxkStaff/get_user_simple_list');

// 同步企业微信客户
Route::post('synchro_customer', 'admin/' . $v . '.WxkCustomer/synchro_customer');

// 添加企业微信标签
Route::post('add_corp_tag', 'admin/' . $v . '.LiveCode/add_corp_tag');

// 活码分组列表
Route::post('live_code_group', 'admin/' . $v . '.LiveCode/live_code_group');

// 新增编辑活码分组
Route::post('add_code_group', 'admin/' . $v . '.LiveCode/add_code_group');

// 新增编辑活码回显
Route::post('show_live_qr', 'admin/' . $v . '.LiveCode/show_live_qr');

// 渠道活码获取部门列表
Route::post('get_department_list', 'admin/' . $v . '.WxkStaff/get_department_list');

// 获取企业成员最后一次同步时间
Route::post('get_synchro_staff_date', 'admin/' . $v . '.WxkStaff/get_synchro_staff_date');

// 同步企业微信客户标签
Route::post('synchro_customer_tag', 'admin/' . $v . '.WxkCustomerTag/synchro_customer_tag');

// 获取客户标签组
Route::post('get_customer_tag_group', 'admin/' . $v . '.WxkCustomerTag/get_customer_tag_group');

// 编辑删除标签组
Route::post('edit_customer_tag_group', 'admin/' . $v . '.WxkCustomerTag/edit_customer_tag_group');

// 获取客户标签列表
Route::post('get_customer_tag', 'admin/' . $v . '.WxkCustomerTag/get_customer_tag');

// 获取客户标签树结构
Route::post('get_customer_tag_tree', 'admin/' . $v . '.WxkCustomerTag/get_customer_tag_tree');

// 新增客户标签
Route::post('add_customer_tag', 'admin/' . $v . '.WxkCustomerTag/add_customer_tag');

// 编辑删除客户标签
Route::post('edit_customer_tag', 'admin/' . $v . '.WxkCustomerTag/edit_customer_tag');

// 新增编辑活码
Route::post('add_live_qr' , 'admin/'.$v.'.LiveCode/add_live_qr');

// 活码列表
Route::post('get_live_qr_list' , 'admin/'.$v.'.LiveCode/get_live_qr_list');

// 活码列表预览成员
Route::post('get_live_qr_staff' , 'admin/'.$v.'.LiveCode/get_live_qr_staff');

// 活码列表客户信息
Route::post('get_live_qr_customer' , 'admin/'.$v.'.LiveCode/get_live_qr_customer');

// 活码移动分组
Route::post('live_qr_group_move' , 'admin/'.$v.'.LiveCode/live_qr_group_move');

// 删除活码
Route::post('delete_live_qr' , 'admin/'.$v.'.LiveCode/delete_live_qr');

// 新增活码获取单人成员
Route::post('get_add_live_staff' , 'admin/'.$v.'.LiveCode/get_add_live_staff');

// 新增活码获取多人成员
Route::post('get_section_tree_staff' , 'admin/'.$v.'.LiveCode/get_section_tree_staff');

// 同步配置了外部联系人权限的联系人
Route::post('synchro_follow_user' , 'admin/'.$v.'.LiveCode/synchro_follow_user');

// 批量获取活码
Route::post('batch_live_qr_list' , 'admin/'.$v.'.LiveCode/batch_live_qr_list');

// 企业微信客户管理回调
Route::rule('external_contact' , 'admin/'.$v.'.ExternalCallback/external_contact', 'get|post');

// 单个活码统计头部信息
Route::post('get_live_qr_stat' , 'admin/'.$v.'.LiveCode/get_live_qr_stat');

// 单个活码统计底部信息
Route::post('get_live_qr_stat_screen' , 'admin/'.$v.'.LiveCode/get_live_qr_stat_screen');

// 活码统计头部信息
Route::post('get_live_qr_statistics' , 'admin/'.$v.'.LiveCode/get_live_qr_statistics');

// 活码统计top10
Route::post('get_live_qr_stat_top' , 'admin/'.$v.'.LiveCode/get_live_qr_stat_top');

// 活码统计客户增长
Route::post('get_live_qr_add_stat' , 'admin/'.$v.'.LiveCode/get_live_qr_add_stat');

// 活码统计客户属性
Route::post('get_live_qr_stat_attribute' , 'admin/'.$v.'.LiveCode/get_live_qr_stat_attribute');

// 批量编辑活码成员
Route::post('edit_batch_live_qr_staff' , 'admin/'.$v.'.LiveCode/edit_batch_live_qr_staff');

// 回显批量编辑活码成员上限
Route::post('show_batch_add_limit' , 'admin/'.$v.'.LiveCode/show_batch_add_limit');

// 批量编辑活码成员上限
Route::post('edit_batch_add_limit' , 'admin/'.$v.'.LiveCode/edit_batch_add_limit');

// 项目初始化同步
Route::post('config_synchro' , 'admin/'.$v.'.SysInstall/config_synchro');

// 删除config配置
Route::post('del_config' , 'admin/'.$v.'.SysInstall/del_config');

// 回显新增编辑欢迎语
Route::post('show_add_welcome' , 'admin/'.$v.'.WxkWelcome/show_add_welcome');

// 新增欢迎语
Route::post('add_welcome' , 'admin/'.$v.'.WxkWelcome/add_welcome');

// 编辑欢迎语
Route::post('edit_welcome' , 'admin/'.$v.'.WxkWelcome/edit_welcome');

// 欢迎语列表
Route::post('get_welcome_list' , 'admin/'.$v.'.WxkWelcome/get_welcome_list');

// 删除欢迎语
Route::post('del_welcome' , 'admin/'.$v.'.WxkWelcome/del_welcome');

// 安装获取配置
Route::post('get_config_random_string' , 'admin/'.$v.'.SysInstall/get_config_random_string');

// 首页客户统计
Route::post('index_customer_total' , 'admin/'.$v.'.Index/index_customer_total');

// 首页客户增长趋势
Route::post('index_customer_trend' , 'admin/'.$v.'.Index/index_customer_trend');

// 首页客户明细
Route::post('index_customer_detailed' , 'admin/'.$v.'.Index/index_customer_detailed');

// 获取回调URL
Route::post('get_callback_url' , 'admin/'.$v.'.WxkConfig/get_callback_url');

// 域名验证文件上传
Route::post('upload_domain_verification_file' , 'admin/'.$v.'.WxkConfig/upload_domain_verification_file');

// 获取首页用户信息
Route::post('index_user_info' , 'admin/'.$v.'.Index/index_user_info');

// 添加临时预览文件
Route::post('set_content_preview' , 'admin/'.$v.'.ContentEngine/set_content_preview');

// 获取临时预览文件
Route::post('get_temporary_preview' , 'admin/'.$v.'.ContentEngine/get_temporary_preview');

// 根据 media_id 获取内容
Route::post('get_media_id_content' , 'admin/'.$v.'.ContentEngine/get_media_id_content');

// 首页统计成员top
Route::post('index_staff_top' , 'admin/'.$v.'.Index/index_staff_top');

// 首页销售排行top
Route::post('index_sale_top' , 'admin/'.$v.'.Index/index_sale_top');

// 首页引流数据统计
Route::post('index_drainage_data' , 'admin/'.$v.'.Index/index_drainage_data');

// 添加编辑拓客计划
Route::post('add_develop_custom' , 'admin/'.$v.'.CmsDevelopCustom/add_develop_custom');

// 回显下拉列表年份
Route::post('show_develop_custom_year' , 'admin/'.$v.'.CmsDevelopCustom/show_develop_custom_year');

// 拓客情况
Route::post('get_develop_custom_info' , 'admin/'.$v.'.CmsDevelopCustom/get_develop_custom_info');

// 企业计划列表
Route::post('get_business_plan_list' , 'admin/'.$v.'.CmsDevelopCustom/get_business_plan_list');

// 成员标签同步
Route::post('sync_staff_tag' , 'admin/'.$v.'.WxkStaffTag/sync_staff_tag');

// 新增编辑成员标签组
Route::post('add_staff_tag_group' , 'admin/'.$v.'.WxkStaffTag/add_staff_tag_group');

// 删除成员标签组
Route::post('del_staff_tag_group' , 'admin/'.$v.'.WxkStaffTag/del_staff_tag_group');

// 获取成员客户标签组
Route::post('get_staff_tag_group' , 'admin/'.$v.'.WxkStaffTag/get_staff_tag_group');

// 新增编辑成员标签
Route::post('add_staff_tag' , 'admin/'.$v.'.WxkStaffTag/add_staff_tag');

// 删除成员标签
Route::post('del_staff_tag' , 'admin/'.$v.'.WxkStaffTag/del_staff_tag');

// 获取成员标签列表
Route::post('get_staff_tag_list' , 'admin/'.$v.'.WxkStaffTag/get_staff_tag_list');

// 回显成员筛选列表
Route::post('show_get_staff_screen' , 'admin/'.$v.'.WxkStaff/show_get_staff_screen');

// 成员打标签回显已有的标签
Route::post('show_staff_tag' , 'admin/'.$v.'.WxkStaff/show_staff_tag');

// 成员打标签/移除标签
Route::post('staff_tagging' , 'admin/'.$v.'.WxkStaff/staff_tagging');

// 获取成员标签树结构
Route::post('get_staff_tag_tree' , 'admin/'.$v.'.WxkStaffTag/get_staff_tag_tree');

// 上传活码素材
Route::post('upload_qr_code' , 'admin/'.$v.'.LiveCode/upload_qr_code');

// 根据部门获取成员
Route::post('get_department_staff' , 'admin/'.$v.'.WxkStaff/get_department_staff');



/*-------------------------------------------------------------------*/
/*-----------------------------文章/聊天侧边栏------------------------*/
/*-------------------------------------------------------------------*/

// 新增文章阅读记录
Route::post('add_article_reading' , 'admin/'.$v.'.WxkArticleReadingLog/add_article_reading');

// 获取企业ID&成员ID
Route::post('get_code_staff_user' , 'admin/'.$v.'.WxkArticleReadingLog/get_code_staff_user');

// 获取素材
Route::post('app_get_content_engine' , 'admin/'.$v.'.WxkArticleReadingLog/app_get_content_engine');

// 微信公众号授权登录
Route::get('wechat_login' , 'admin/'.$v.'.WechatOauth/wechat_login');

// 微信公众号授权登录测试
Route::get('wechat_login_test' , 'admin/'.$v.'.WechatOauth/wechat_login_test');

// 获取JS_SDK
Route::post('get_js_sdk' , 'admin/'.$v.'.WxkArticleReadingLog/get_js_sdk');

// 素材数据统计次数
Route::post('content_data_total' , 'admin/'.$v.'.ContentEngine/content_data_total');

// 素材数据统计详情列表
Route::post('content_data_details' , 'admin/'.$v.'.ContentEngine/content_data_details');


//登录
Route::post('login', 'admin/' . $v . '.SysUser/login');

//企业微信授权登录
Route::rule('wx_login', 'admin/' . $v . '.SysUser/WxLogin');

//退出登录
Route::post('logout', 'admin/' . $v . '.SysUser/logout');

//系统设置
Route::post('app_edit', 'admin/' . $v . '.SysRbac/AppEdit');

//菜单列表及系统配置
Route::post('menu_list', 'admin/' . $v . '.SysUser/MenuList');

//修改密码
Route::post('forget_pas', 'admin/' . $v . '.SysUser/ForgetPas');

//发送短信
Route::post('send_msg', 'admin/' . $v . '.SysUser/SendMsg')->middleware(CheckPhone::class);

//注册
Route::post('register', 'admin/' . $v . '.SysUser/Register')->middleware(CheckPhone::class);

//验证短信验证码
Route::post('verify_code', 'admin/' . $v . '.SysUser/VerifyCode')->middleware(CheckPhone::class);

//子账户列表
Route::rule('user_list', 'admin/' . $v . '.SysUser/UserList', 'POST');

//子账户添加
Route::rule('user_add', 'admin/' . $v . '.SysUser/UserAdd', 'POST');

//子账户修改
Route::rule('user_edit', 'admin/' . $v . '.SysUser/UserEdit', 'POST');

//子账户删除
Route::rule('user_del', 'admin/' . $v . '.SysUser/UserDel', 'POST');

//模块列表
Route::rule('authority_list', 'admin/' . $v . '.SysRbac/AuthorityList', 'POST');

//添加模块
Route::rule('authority_add', 'admin/' . $v . '.SysRbac/AuthorityAdd', 'POST');

//修改模块
Route::rule('authority_edit', 'admin/' . $v . '.SysRbac/AuthorityEdit', 'POST');

//删除模块
Route::rule('authority_del', 'admin/' . $v . '.SysRbac/AuthorityDel', 'POST');

//角色列表
Route::rule('roles_list', 'admin/' . $v . '.SysRbac/RolesList', 'POST');

//添加角色
Route::rule('roles_add', 'admin/' . $v . '.SysRbac/RolesAdd', 'POST');

//修改角色
Route::rule('roles_edit', 'admin/' . $v . '.SysRbac/RolesEdit', 'POST');

//删除角色
Route::rule('roles_del', 'admin/' . $v . '.SysRbac/RolesDel', 'POST');

//获取角色拥有的权限
Route::rule('roles_module', 'admin/' . $v . '.SysRbac/RolesModule', 'POST');

//检测环境
Route::rule('check_environment', 'admin/' . $v . '.SysInstall/CheckEnvironment', 'POST');

//安装
Route::rule('install', 'admin/' . $v . '.SysInstall/Install', 'POST');

//检测是否安装
Route::rule('install_check', 'admin/' . $v . '.SysInstall/check', 'POST');

//发送消息
Route::rule('add_msg', 'admin/' . $v . '.ContentEngine/AddMsg', 'POST');

//内容列表
Route::rule('content_list', 'admin/' . $v . '.ContentEngine/ContentList', 'POST');

//添加内容
Route::rule('content_add', 'admin/' . $v . '.ContentEngine/ContentAdd', 'POST');

//修改内容
Route::rule('content_edit', 'admin/' . $v . '.ContentEngine/ContentEdit', 'POST');

//删除内容
Route::rule('content_del', 'admin/' . $v . '.ContentEngine/ContentDel', 'POST');

//分组列表
Route::rule('content_group_list', 'admin/' . $v . '.ContentEngine/ContentGroupList', 'POST');

//添加分组
Route::rule('content_group_add', 'admin/' . $v . '.ContentEngine/ContentGroupAdd', 'POST');

//修改分组
Route::rule('content_group_edit', 'admin/' . $v . '.ContentEngine/ContentGroupEdit', 'POST');

//删除分组
Route::rule('content_group_del', 'admin/' . $v . '.ContentEngine/ContentGroupDel', 'POST');

//上传素材
Route::rule('photo', 'admin/' . $v . '.ContentEngine/photo', 'POST');

//生成内容统计
Route::rule('content_operating', 'admin/' . $v . '.ContentEngine/ContentOperating', 'POST');

//指定分类数据
Route::rule('content_details', 'admin/' . $v . '.ContentEngine/ContentDetails', 'POST');

//内容引擎类型
Route::rule('content_type_list', 'admin/' . $v . '.ContentEngine/ContentTypeList', 'POST');

//统计次数
Route::rule('content_num', 'admin/' . $v . '.ContentEngine/ContentNum', 'POST');

//搜索，发送门，打开次数
Route::rule('content_search', 'admin/' . $v . '.ContentEngine/ContentSearch', 'POST');

//内容TOP10
Route::rule('content_top', 'admin/' . $v . '.ContentEngine/ContentTop', 'POST');

//员工TOP10
Route::rule('staff_top', 'admin/' . $v . '.ContentEngine/StaffTop', 'POST');

//搜索TOP10
Route::rule('search_top', 'admin/' . $v . '.ContentEngine/SearchTop', 'POST');

//客户跟进记录
Route::rule('custom_follow_record', 'admin/' . $v . '.WxkCustomer/CustomFollowRecord', 'POST');

//添加客户跟进记录
Route::rule('custom_follow_record_add', 'admin/' . $v . '.WxkCustomer/CustomFollowRecordAdd', 'POST');

//修改客户跟进记录
Route::rule('custom_follow_record_edit', 'admin/' . $v . '.WxkCustomer/CustomFollowRecordEdit', 'POST');

//客户互动轨迹记录
Route::rule('custom_track_record', 'admin/' . $v . '.WxkCustomer/CustomTrackRecord', 'POST');