<?php
/**
 * 示例程序：配置文件
 */

// 应用目录
define('APP_ROOT', dirname(__FILE__ ).'/');
// 模板目录
define('APP_TEMPLATE_ROOT', APP_ROOT.'template/');

// 输出调试信息，生成环境请去掉这行
define('APP_DEBUG', true);

// MYSQL数据库配置，若不配置，则默认不创建数据库连接
define('CONF_MYSQL_SERVER', '127.0.0.1:3306');    // 服务器
define('CONF_MYSQL_USER',   'root');              // 用户名
define('CONF_MYSQL_PASSWD', '');                  // 密码
define('CONF_MYSQL_DBNAME', 'test');              // 数据库名
define('CONF_MYSQL_CHARSET', 'utf8');             // 数据库编码


// 载入leiphp并初始化
require(APP_ROOT.'../leiphp.php');
APP::init();

