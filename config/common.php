<?php
/**
 * Created by Shy
 * Date 2020/12/1
 * Time 15:18
 */
return [
    'wx_access_token'=>'https://qyapi.weixin.qq.com/cgi-bin/gettoken?',//微信获取access_token
    'wx_login_url'=>'https://open.work.weixin.qq.com/wwopen/sso/qrConnect?',//微信授权地址
    'wx_get_user_info'=>'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?',//微信获取访问用户身份
    'wx_add_msg_template'=>'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/add_msg_template?',//添加企业群发消息任务
    'wx_upload'=>'https://qyapi.weixin.qq.com/cgi-bin/media/upload?',//上传临时素材
    'wx_jsapi_ticket'=>'https://qyapi.weixin.qq.com/cgi-bin/ticket/get?',

    // 忘记密码短信配置
    'AccessKeyId' => "",  // AccessKeyId
    'Secret' => "", // Secret
    'SignName' => "", //短信签名名称
    'TemplateCode' => "", //模板CODE

    //验证码类型
    'MsgCode' => [
        '1' => 'res_pas_code_',//修改密码
        '2' => 'register_code_',//注册
    ],

    //内容类型
    'content_type_list'=>[
        1=>'文本',
        2=>'图片',
        3=>'图文',
        4=>'音频',
        5=>'视频',
        6=>'小程序',
        7=>'文件',
    ],


    //文件类型
    'content_file_type'=>[
        'DOC'=>1,
        'DOCX'=>1,
        'XLS'=>2,
        'XLSX'=>2,
        'PPT'=>3,
        'PPTX'=>3,
        'PDF'=>4,
        'PNG'=>5,
        'JPG'=>5,
        'JPEG'=>5,
        'GIF'=>5,
        'MP3'=>6,
        'WMA'=>6,
        'MP4'=>7,
        'TXT'=>8,
        'RAR'=>9,
        'ZIP'=>9,
        'CAB'=>9,
        'TAR'=>9,
        'GZIP'=>9,
        'JAR'=>9,
    ],


    //文件类型对应名称
    'content_file_name'=>[
        1=>'word',
        2=>'excel',
        3=>'ppt',
        4=>'pdf',
        5=>'图片类',
        6=>'音频',
        7=>'视频',
        8=>'文本',
        9=>'压缩包',
        10=>'其它',
    ],

    //客户跟进状态
    'customer_track_status'=>[
       1=>'未跟进',
       2=>'跟进中',
       3=>'已拒绝',
       4=>'已成交'
    ],



];