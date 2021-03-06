<?php
/**
 * 示例程序
 */

// 载入配置文件
require('global.inc.php');


// 公共处理部分在这里写
echo '公共处理部分<hr>';

APP::load('require_file');


// 处理GET请求
function method_get()
{
    echo 'GET请求';
    APP::dump(APP::$config);
    APP::dump(APP::get('ROOT'));
    APP::dump($_GET);
    APP::dump(SQL::find_all('show tables'));
    APP::dump(SQL::find_all('show databases'));
    APP::dump(SQL::find_all('select 1+1 as a'));
    // 渲染模板
    TPL::set_val('title', '标题');
    TPL::render('test.html');
}

// 处理POST请求
function method_post()
{
    echo 'POST请求';
    APP::dump($_POST);
}

// 以下函数用于处理任意请求（当没有定义method_get或method_post时）
function method_all()
{
    echo '处理所有请求';
}
