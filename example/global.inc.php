<?php
/**
 * 公共文件
 */

// 载入LeiPHP
require('../lei.php');

// 当前应用的根目录
APP::set('ROOT', dirname(__FILE__) . '/');
// 模板根目录
APP::set('TEMPLATE_ROOT', APP::get('ROOT') . 'template/');

// 输出调试信息，生成环境请去掉这行或设置为false
APP::set('DEBUG', true);

// MYSQL数据库配置，如果不定义数据库配置，则不自动连接数据库
APP::set('MYSQL_SERVER', 'localhost:3306');  // 服务器，默认为 localhost:3306，使用长连接在地址前加 p:，如：p:localhost:3306
APP::set('MYSQL_USER', 'root');            // 用户名，默认为 root
APP::set('MYSQL_PASSWD', '');                // 密码，默认为空
APP::set('MYSQL_DBNAME', 'test');            // 数据库名，默认为空
APP::set('MYSQL_PERMANENT', false);          // 使用使用永久连接，默认false

// 初始化
APP::init();
?>