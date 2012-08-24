LeiPHP 微型的PHP框架
==============

此框架仅有一个文件，其中包含了MySQL数据库、上传文件、调试信息、导入依赖文件、模板和REST路由等一系列常用操作。适合用来快速写一些对性能要求不高的程序。


初始化
=============

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


// 输出调试信息，生成环境请去掉这行
define('APP_DEBUG', true);


// MYSQL数据库配置，如果不定义数据库配置，则不自动连接数据库
define('CONF_MYSQL_SERVER', '127.0.0.1:3306');  // 服务器
define('CONF_MYSQL_USER',   'root');            // 用户名
define('CONF_MYSQL_PASSWD', '123456');          // 密码
define('CONF_MYSQL_DBNAME', 'test');            // 数据库名


// 载入leiphp并初始化
require(APP_ROOT.'leiphp.php');
APP::init();
?>
```

在所有php程序中，均可载入`config.inc.php`文件唉实现初始化leiphp：

```php
<?php
require('config.inc.php');
// ...
?>
```


REST路由
===========

leiphp可以根据不同的请求方法来调用相应的处理函数完成请求，比如：

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

?>


操作MySQL数据库
===============

leiphp中提供了一个静态类**SQL**来操作MySQL数据库：

* `SQL::connect($server = 'localhost:3306', $username = 'root', $password = '', $database = '');` 连接到数据库，当配置了数据库连接时，leiapp会自动执行此方法来连接到数据库，若你的程序中已经通过`mysql_connect`来创建了一个数据库连接，可以不用再执行此方法连接数据库；

* `SQL::getAll($sql)` 或 `SQL::getData($sql)` 查询SQL，并返回数组格式的结果，失败返回`FALSE`；

* `SQL::getOne($sql)` 或 `SQL::getLine($sql)` 查询SQL，仅返回第一条结果，失败返回`FALSE`；

* `SQL::update($sql)` 或 `SQL::runSql($sql)` 查询SQL，返回受影响的记录数，一般用于执行插入或更新操作；

* `SQL::id()` 或 `SQL::lastId()` 返回最后插入的一条记录的ID；

* `SQL::errno()` 返回最后执行的一条SQL语句的出错号

* `SQL::errmsg()` 返回最后执行的一条SQL语句的出错信息

* `SQL::escape($str)` 返回安全的SQL字符串


上传文件操作
===============

leiphp中提供了一个静态类**UPLOAD**来操作上传文件：

* `UPLOAD::get($filename)` 返回指定名称的上传文件信息，该名称为`<form>`表单中的`<input type="file">`中的**name**值，该返回值为一个数组，包含以下项：**name**（名称），**type**（MIME类型），**size**（大小），**tmp_name**（临时文件名）；

* `UPLOAD::move($file, $target)` 移动上传的文件到指定位置，第一个参数为`UPLOAD::get($filename)`的返回值，第二个参数为目标文件名；


调试信息操作
=============

leiphp中提供了一个静态类**DEBUG**来操作调试信息，当定义了常量`APP_DEBUG`时，会在页面底部输出调试信息：

* `DEBUG::put($msg = '', $title = '')` 输出调试信息


应用相关操作
=============

leiphp中提供了一个静态类**APP**来进行应用相关的操作，及一些公共函数：

* `APP::encryptPassword ($password)` 加密密码，返回一个加盐处理后的MD5字符串，如：`FF:15855D447208A6AB4BD2CC88D4B91732:83`；

* `APP::validatePassword ($password, $encrypted)` 验证密码，第一个参数为待验证的密码，第二个参数为`APP::encryptPassword ($password)`返回的字符串，返回`TRUE`或`FALSE`；

* `APP::dump($var)` 打印变量结构，一般用于调试；

* `APP::load($filename)` 载入依赖的php文件，若不指定后缀名，会自动加上`.php`，默认以当前php文件为根目录，若文件名以`/`开头，则以常量`APP_ROOT`定义的应用目录作为根目录；

* `APP::template($name, $locals, $layout = '')` 载入模板文件，若不指定后缀名，会自动加上`.html`，以常量`APP_TEMPLATE_ROOT`定义的模板目录作为根目录，模板文件实际上为php程序文件，第二个参数为模板中可用的变量，在模板中通过`$locals`来读取，第三个参数为布局模板，默认不使用布局模板，若指定了布局模板，则需要在布局模板中通过变量`$body`来获取当前模板的内容，如：`<?php echo $body; ?>`；

* `APP::init()` 初始化leiphp；

