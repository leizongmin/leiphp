# LeiPHP 轻量级的 PHP MVC 框架

此框架仅有一个文件，其中包含了MySQL数据库、上传文件、调试信息、导入依赖文件、模板和REST路由等一系列常用操作。适合用来快速写一些对性能要求不高的程序。

## 初始化

首先新建一个`config.inc.php`文件，所有程序通过加载该文件来进行配置及初始化：

```php
<?php
/**
 * 配置文件
 */

// 当前应用的根目录
define('APP_ROOT', dirname(__FILE__ ).'/');
// 模板根目录
define('APP_TEMPLATE_ROOT', APP_ROOT.'template/');

// 输出调试信息，生成环境请去掉这行或设置为false
define('APP_DEBUG', true);

// MYSQL数据库配置，如果不定义数据库配置，则不自动连接数据库
define('CONF_MYSQL_SERVER', 'localhost:3306');  // 服务器，默认为 localhost:3306
define('CONF_MYSQL_USER',   'root');            // 用户名，默认为 root
define('CONF_MYSQL_PASSWD', '123456');          // 密码，默认为空
define('CONF_MYSQL_DBNAME', 'test');            // 数据库名，默认为空
define('CONF_MYSQL_PERMANENT', false);          // 使用使用永久连接，默认false

// 载入LeiPHP并初始化
require(APP_ROOT.'leiphp.php');
APP::init();
?>
```

在所有php程序中，均可载入`config.inc.php`文件唉实现初始化LeiPHP：

```php
<?php
require('config.inc.php');
// ...
?>
```

## REST路由

LeiPHP可以根据不同的请求方法来调用相应的处理函数完成请求，比如：

```php
<?php
require('config.inc.php');

// 这里是公共部分的代码，所有请求方法都会执行下面的代码
echo '所有请求方法都会执行这里的代码';

// 定义处理GET请求的代码
function method_get () {
  echo 'GET请求方法的处理代码';
}

// 定义处理POST请求的代码
function method_post () {
  echo 'POST请求方法的处理代码';
}

// 定义处理DELETE请求的代码
function method_delete () {
  echo 'DELETE请求方法的处理代码';
}

// 定义处理PUT请求的代码
function method_put () {
  echo 'PUT请求方法的处理代码';
}

?>
```

## 模板渲染

LeiPHP中可以通过`APP::render()`来渲染HTML模板：

```php
// 设置模板变量
APP::setLocals('模板变量', '值');
// 渲染模板
APP::render('模板名');
```

模板文件存放在`template`目录内，比如要渲染`template/index.html`：

```php
APP::render('index');
```

以下为模板渲染相关的方法：

* `APP::getTemplate($name, $locals)` 载入模板文件，若不指定后缀名，会自动加上`.html`，以常量`APP_TEMPLATE_ROOT`定义的模板目录作为根目录，模板文件实际上为php程序文件，第二个参数为模板中可用的变量，在模板中通过`$locals`来读取（若无命名冲突也可以直接使用键名），返回渲染后的内容
* `APP::setLocals($name, $value)` 设置模板变量
* `APP::getLocals($name)` 取模板变量值
* `APP::render($name, $locals, $layout = '')` 自动为`$locals`加上用`APP::setLocals()`设置的变量，并渲染模板。如果指定了视图模板`$layout`，则需要在视图模板中通过`$body`变量来获取模板内容。

## 操作MySQL数据库

LeiPHP中提供了一个静态类 __SQL__ 来操作MySQL数据库：

* `SQL::connect($server = 'localhost:3306', $username = 'root', $password = '',$database = '');`连接到数据库，当配置了数据库连接时，leiapp会自动执行此方法来连接到数据库，若你的程序中已经通过`mysql_connect`来创建了一个数据库连接，可以不用再执行此方法连接数据库；
* `SQL::getAll($sql)` 或 `SQL::getData($sql)` 查询SQL，并返回数组格式的结果，失败返回`FALSE`；
* `SQL::getOne($sql)` 或 `SQL::getLine($sql)` 查询SQL，仅返回第一条结果，失败返回`FALSE`；
* `SQL::update($sql)` 或 `SQL::runSql($sql)` 查询SQL，返回受影响的记录数，一般用于执行插入或更新操作；
* `SQL::id()` 或 `SQL::lastId()` 返回最后插入的一条记录的ID；
* `SQL::errno()` 返回最后执行的一条SQL语句的出错号
* `SQL::errmsg()` 返回最后执行的一条SQL语句的出错信息
* `SQL::escape($str)` 返回安全的SQL字符串

更简便的数据库操作：

* `SQL::getAll($table, $where)` 查询所有记录，其中$table是表名，$where是一个条件数组，如：array('id' => 1)
* `SQL::getOne($table, $where)` 查询一条记录
* `SQL::update($table, $where, $update)` 更新记录并返回受影响的记录数，其中$update是要更新的数据数组，如：array('name' => 'haha')
* `SQL::insert($table, $data)` 插入一条记录并返回其ID，其中$data是一个数组，如：array('name' => 'haha', 'age' => 20)
* `SQL::delete($table, $where)` 删除记录

条件格式：

* 普通：`array('a' => 1, 'b' => 2)` 相当于 `a=1 AND b=2`
* 指定连接操作符：`array('link' => 'OR', 'a' => 1, 'b' => 2)` 相当于 `a=1 OR b=2`
* 指定比较操作符：`array('a' => array('>' => 2))` 相当于 `a>2`
* 同一个字段多个条件：`array('a' => array('>' => 2, '<' => 5))` 相当于`(a>2 AND a < 5)`
* 指定多个条件的连接操作符：`array('a' => array('link' => 'OR', '>' => 2, '<' => 5))`相当于 `(a>2 OR a < 5)`

## 上传文件操作

LeiPHP中提供了一个静态类 __UPLOAD__ 来操作上传文件：

* `UPLOAD::get($filename)` 返回指定名称的上传文件信息，该名称为`<form>`表单中的`<input type="file">`中的**name**值，该返回值为一个数组，包含以下项： __name__（名称）， __type__ （MIME类型）， __size__ （大小），__tmp_name__ （临时文件名）；
* `UPLOAD::move($file, $target)` 移动上传的文件到指定位置，第一个参数为`UPLOAD::get($filename)`的返回值，第二个参数为目标文件名；

## 调试信息操作

LeiPHP中提供了一个静态类 __DEBUG__ 来操作调试信息，当定义了常量`APP_DEBUG`时，
会在页面底部输出调试信息：

* `DEBUG::put($msg = '', $title = '')` 输出调试信息
* `DEBUG::get()` 取调试信息
* `DEBUG::clear()` 清除所有调试信息

## 应用相关操作

LeiPHP中提供了一个静态类 __APP__ 来进行应用相关的操作，及一些公共函数：

* `APP::encryptPassword ($password)` 加密密码，返回一个加盐处理后的MD5字符串，如：`FF:15855D447208A6AB4BD2CC88D4B91732:83`；
* `APP::validatePassword ($password, $encrypted)` 验证密码，第一个参数为待验证的密码，第二个参数为`APP::encryptPassword ($password)`返回的字符串，返回`TRUE`或`FALSE`；
* `APP::dump($var)` 打印变量结构，一般用于调试；
* `APP::showError($msg)` 显示出错信息
* `APP::load($filename)` 载入依赖的php文件，若不指定后缀名，会自动加上`.php`，默认以当前php文件为根目录，若文件名以`/`开头，则以常量`APP_ROOT`定义的应用目录作为根目录；
* `APP::sendJSON($data)` 返回JSON格式数据
* `APP::sendError($msg, $data = array())` 返回JSON格式的出错信息：`{"error":"msg"}`
* `APP::authEncode($string, $key, $expirey)` 加密账户验证信息，可指定过期时间
* `APP::authDecode($string, $key)` 加密账户验证信息
* `APP::getTemplate($name, $locals)` 载入模板文件，若不指定后缀名，会自动加上`.html`，以常量`APP_TEMPLATE_ROOT`定义的模板目录作为根目录，模板文件实际上为php程序文件，第二个参数为模板中可用的变量，在模板中通过`$locals`来读取（若无命名冲突也可以直接使用键名），返回渲染后的内容
* `APP::setLocals($name, $value)` 设置模板变量
* `APP::getLocals($name)` 取模板变量值
* `APP::render($name, $locals, $layout = '')` 自动为`$locals`加上用`APP::setLocals()`设置的变量，并渲染模板。如果指定了视图模板`$layout`，则需要在视图模板中通过`$body`变量来获取模板内容。
* `APP::init()` 初始化LeiPHP；
* `APP::end()` 提前退出

## 自动路由

LeiPHP中提供了一个静态类 __ROUTER__ 来进行路由相关的操作：

* `ROUTER::register($path, $function, $is_preg = false)` 注册中间件，其中`$path`为路径前缀，`$function`为要执行的函数，如果`$is_preg`为`true`表示`$path`是一个正则表达式;
* `ROUTER::run($dir, $path)` 执行自动路由。其中`$dir`是要自动加载的PHP文件所在的目录，以应用目录`APP_ROOT`中定义的目录为根目录，默认为`action`目录，`$path`是当前请求的路径，默认为`$_GET['__path__']`;

### 示例

应用统一入口文件：index.php

```php
<?php
require('config.inc.php');
ROUTER::run('action', @$_GET['__path__']);
?>
```

需要配置服务器的URL Rewrite，比如将 `/app/(.*)` 的所有请求转到`/app/index.php?__path__=$1`

### Apache的配置示例

```text
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^app/(.*)$ /app/index.php?%{QUERY_STRING}&__path__=$1 [L]
```

### Nginx的配置示例

```nginx
if (!-e $request_filename) {
  rewrite "^/app/(.*)" "/app/index.php?%{QUERY_STRING}&__path__=$1" last;
}
```

### SAE的配置示例

```yaml
handle:
 - rewrite: if(!is_dir() && !is_file() && path~"^app/(.*)") goto "app/index.php?%{QUERY_STRING}&__path__=$1"
```

当请求 `/app/my/action` 时，会自动执行文件 `/action/my/action.php`

如请求 `/app/my/action/` ，则自动执行文件 `/action/my/action/index.php`

## License

基于MIT协议发布。

```text
Copyright (c) 2012-2018 Lei Zongmin(雷宗民) <leizongmin@gmail.com>
http://ucdok.com

The MIT License

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```
