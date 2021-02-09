<p align="center">
    <img src="https://images.gitee.com/uploads/images/2021/0207/112656_da2725ac_8387330.jpeg" width="244" height="120"/>
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Edition-0.0.1-orange" />
    <img src="https://img.shields.io/badge/PHP-7.1+-green" />
    <img src="https://img.shields.io/badge/Vue-2.0+-yellow" />
    <img src="https://img.shields.io/badge/MySQL-5.7+-blueviolet" />
    <img src="https://img.shields.io/badge/Download-20M-blue" />
</p>

## 唤客猫企业微信SCRM系统

基于企业微信的私域流量裂变引流工具和数据化精细运营服务系统，帮助企业快速实现智慧、简单、友好、精细的客户运营管理。可广泛应用于教育、培训、餐饮、文娱、零售、医美、电商、旅游、自媒体、网红、房地产、金融等各类行业。官方网站 [huankemao.com](https://huankemao.com/)

## 声明

您可以 Fork 本站代码，但未经许可 **禁止** 在本产品的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于 **重新分发** 

## 技术讨论

QQ群：866828944

## 演示Demo
#### 后台测试
- [http://demo.huankemao.com](http://demo.huankemao.com)
- 用户：13910733521
- 密码：123456

#### 软件截图
<table>
    <tr>
        <td><img src="https://images.gitee.com/uploads/images/2021/0203/233545_15df4df0_8387330.jpeg"/></td>
        <td><img src="https://images.gitee.com/uploads/images/2021/0203/233557_2eac71bf_8387330.png"/></td>
    </tr>
</table>

#### 聊天工具栏测试
<p>
    <img src="https://images.gitee.com/uploads/images/2021/0204/173616_fa632253_8387330.png" width="240" height="240"/>
    <img src="https://images.gitee.com/uploads/images/2021/0207/125422_faadeac0_8387330.png" width="135" height="240"/>
</p>

## 安装教程

- 下载系统最新源码
- 部署至服务器，将运行目录设置为/public
- Vue前端使用了History模式，后端服务器需设置伪静态
    - Nginx服务器（推荐）：
    ```
    location / {
	    try_files $uri $uri/ /index.html;
    }
    ```
    - Apache服务器：
    ```
    <IfModule mod_rewrite.c>
      RewriteEngine On
      RewriteBase /
      RewriteRule ^index\.html$ - [L]
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{REQUEST_FILENAME} !-d
      RewriteRule . /index.html [L]
    </IfModule>
    ```
- 详细安装教程可参考官方文档
    - [宝塔安装](https://huankemao.com/docs/index.html)
    - [phpstudy安装](https://huankemao.com/docs/index.html)

## 软件架构
| 模块 | 框架 | 部署路径 | Git仓库 |
| :--- | :--- | :--- | :--- |
| 后端 | ThinkPHP | / | [huankemao-php](https://gitee.com/huankemao/huankemao-php)（本仓库） |
| PC前端| Vue | /public | [huankemao-web](https://gitee.com/huankemao/huankemao-web) |
| 文章预览 | H5 | /public/article-preview | [huankemao-article-preview](https://gitee.com/huankemao/huankemao-article-preview) |
| 聊天工具栏 | Vue | /public/chat-tool | [huankemao-chat-tool](https://gitee.com/huankemao/huankemao-chat-tool) |

## 开发计划
![开发计划](https://images.gitee.com/uploads/images/2021/0208/132954_bbc35ca3_8387330.png "开发计划")

## 技术选型
#### 后台框架
| 技术 | 功能 | 版本 | 官网 |
| :--- | :--- | :--- | :--- |
| ThinkPHP | 后端框架 | 6.0+ | [http://www.thinkphp.cn/](http://www.thinkphp.cn/) |
| MySQL | 数据库 | 5.7+ | [https://www.mysql.com/](https://www.mysql.com/) |

#### 前端框架

| 技术 | 功能 | 版本 | 官网 |
| :--- | :--- | :--- | :--- |
| Vue | MVVM框架 | 2.0+ | [https://cn.vuejs.org/](https://cn.vuejs.org/) |
| Axios | 数据交互 | 0.21+ | [http://www.axios-js.com/](http://www.axios-js.com/) |
| Element-UI| UI库 | 2.0+ | [https://element.eleme.cn/](https://element.eleme.cn/2.0/#/zh-CN) |

## 文档
- 用户手册 [https://huankemao.com/docs/index.html](https://huankemao.com/docs/index.html)

#### 如果对您有帮助，您可以点右上角 "Star" 支持一下，谢谢！
