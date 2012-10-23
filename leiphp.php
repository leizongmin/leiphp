<?php
/**
 * LeiPHP
 *
 * @author 老雷<leizongmin@gmail.com>
 * @version 0.1
 */

// 开始时间
define('APP_TIMESTAMP_START', microtime(true));

/* 处理不同的请求方法 */
function _leiphp_request_method_router () {
  $method = strtolower($_SERVER['REQUEST_METHOD']);
  $funcname = "method_$method";
  define('APP_TIMESTAMP_ROUTE', microtime(true));
  if (function_exists($funcname)) {
    $funcname();
  } elseif (function_exists('method_all')) {
    $funcname = 'method_all';
    method_all();
  } else {
    $funcname = 'method_undefine';
  }

  $accept_type = strtolower(trim($_SERVER['HTTP_ACCEPT']));
  if (defined('APP_DEBUG') && substr($accept_type, 0, 9) == 'text/html') {
    $spent2 = round((microtime(true) - APP_TIMESTAMP_ROUTE) * 1000, 3);
    $spent = round((microtime(true) - APP_TIMESTAMP_START) * 1000, 3);
    $debug = DEBUG::clear();
    echo "<div style='
    font-size: 12px;
    line-height: 1.6em;
    text-align: left;
    color: #666;
    padding: 12px 4px;
    border: 1px solid #DDD;
'>Debug<br>Function $funcname spent: {$spent2}ms<br>Total spent: {$spent}ms<br>
<hr><pre>$debug</pre>
</div>";
  }
}
register_shutdown_function('_leiphp_request_method_router');


/**
 * MySQL 数据库操作
 */
if (!class_exists('SQL', FALSE)) {
  class SQL {
    /**
     * 连接到数据库
     * 成功返回true, 失败返回false
     *
     * @param {string} $server
     * @param {string} $username
     * @param {string} $password
     * @param {string} $database
     * @return {bool}
     */
    public static function connect ($server = 'localhost:3306', $username = 'root', $password = '', $database = '') {
      $timestamp = microtime(true);
      mysql_connect($server, $username, $password);
      $r = mysql_select_db($database);
      DEBUG::put('Connected: '.$username.'@'.$server.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'MySQL');
      // 设置默认字符集为utf-8
      // SQL::update('set names utf8');
      return $r;
    }

    /**
     * 获取出错信息
     * 返回数据格式：  {id:出错ID, error:出错描述}
     *
     * @return {array}
     */
    public static function error () {
      return array(
        'id'  =>  mysql_errno(),
        'error' =>  mysql_error()
        );
    }

    /**
     * 返回出错代码
     *
     * @return {int}
     */
    public static function errno () {
      return mysql_errno();
    }

    /**
     * 返回出错描述信息
     *
     * @return {string}
     */
    public static function errmsg () {
      return mysql_error();
    }

    /**
     * 查询并返回所有数据
     * 格式为： [{字段名:值, 字段名:值 ...}, ...]，返回FALSE表示失败
     *
     * @param {string} $sql
     * @return {array}
     */
    public static function getAll ($sql) {
      $timestamp = microtime(true);
      $r = mysql_query($sql);
      if (mysql_errno()) {
        DEBUG::put('Query: '.$sql.' fail: #'.mysql_errno(), 'MySQL');
        return FALSE;
      }
      $data = array();
      while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
        $data[] = $row;
      }
      DEBUG::put('Query: '.$sql.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'MySQL');
      return count($data) < 1 ? FALSE : $data;
    }
    public static function getData ($sql) {
      return SQL::getAll($sql);
    }

    /**
     * 查询并返回一行数据 格式为 {字段名:值, 字段名:值 ...}，返回FALSE表示失败
     *
     * @param {string} $sql
     * @return {array}
     */
    public static function getOne ($sql) {
      $sql .= ' LIMIT 1';
      $data = SQL::getAll($sql);
      return $data == FALSE ? FALSE : $data[0];
    }
    public static function getLine ($sql) {
      return SQL::getOne($sql);
    }

    /**
     * 执行SQL命令 返回影响的记录行数
     *
     * @param {string} $sql
     * @return {int}
     */
    public static function update ($sql) {
      $timestamp = microtime(true);
      mysql_query($sql);
      if (mysql_errno()) {
        DEBUG::put('Query: '.$sql.' fail: #'.mysql_errno(), 'MySQL');
      } else {
        DEBUG::put('Query: '.$sql.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'MySQL');
      }
      return mysql_affected_rows();
    }
    public static function runSql ($sql) {
      return SQL::runSql($sql);
    }

    /**
     * 取最后插入ID
     *
     * @return {int}
     */
    public static function id () {
      return mysql_insert_id();
    }
    public static function lastId () {
      return SQL::id();
    }

    /**
     * 转换为SQL安全字符串
     *
     * @param {string} $str
     * @return {string}
     */
    public static function escape ($str) {
      return  mysql_escape_string($str);
    }
  }
}
/**
 * 调试信息流操作
 */
if (!class_exists('DEBUG', FALSE)) {
  class DEBUG {
    public static $stack = '';

    /**
     * 添加到DEBUG流
     *
     * @param {string} $msg
     * @param {string} $title
     */
    public static function put ($msg = '', $title = '') {
      if (defined('APP_DEBUG')) {
        if (!empty($title)) {
          $msg = '['.$title.'] '.$msg;
        }
        DEBUG::$stack .= $msg."\r\n";
      }
    }

    /**
     * 获取DEBUG流
     *
     * @return {string}
     */
    public static function get () {
      return DEBUG::$stack;
    }

    /**
     * 清空DEBUG流，并返回之前的信息
     *
     * @return {string}
     */
    public static function clear () {
      $ret = DEBUG::$stack;
      DEBUG::$stack = '';
      return $ret;
    }
  }
}

/**
 * 上传文件管理
 */
if (!class_exists('UPLOAD', FALSE)) {
  class UPLOAD {
    /**
     * 获取上传文件
     * 返回格式：{name: 名称, type: 文件MIME类型, size: 大小, tmp_name: 临时文件名}
     *
     * @param {string} $filename
     * @return {array}
     */
    public static function get ($filename) {
      if (isset($_FILES[$filename])) {
        $uploaded_file = array(
          'name' => $_FILES[$filename]['name'],
          'type' => $_FILES[$filename]['type'],
          'size' => $_FILES[$filename]['size'],
          'tmp_name' => $_FILES[$filename]['tmp_name']
          );
      } elseif (isset($_POST[$filename])) {
        $uploaded_file = array(
          'name' => $_POST[$filename],
          );
      } elseif (isset($GLOBALS['HTTP_POST_FILES'][$filename])) {
        global $HTTP_POST_FILES;
        $uploaded_file = array(
          'name' => $HTTP_POST_FILES[$filename]['name'],
          'type' => $HTTP_POST_FILES[$filename]['type'],
          'size' => $HTTP_POST_FILES[$filename]['size'],
          'tmp_name' => $HTTP_POST_FILES[$filename]['tmp_name']
          );
      } elseif (isset($GLOBALS['HTTP_POST_VARS'][$filename])) {
        global $HTTP_POST_VARS;
        $uploaded_file = array(
          'name' => $HTTP_POST_VARS[$filename],
          );
      } else {
        $uploaded_file = array(
          'name' => $GLOBALS[$filename . '_name'],
          'type' => $GLOBALS[$filename . '_type'],
          'size' => $GLOBALS[$filename . '_size'],
          'tmp_name' => $GLOBALS[$filename]
        );
      }
      return $uploaded_file;
    }

    /**
     * 移动临时文件
     *
     * @param {array} $file
     * @param {string} $target
     * @return {string}
     */
    public static function move ($file, $target) {
      $timestamp = microtime(true);
      $source = is_array($file) ? $file['tmp_name'] : $file;
      move_uploaded_file($source, $target);
      DEBUG::put('Move '.$source.' to '.$target.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'Upload');
      return $target;
    }
  }
}

class APP {
  /**
   * 加密密码
   *
   * @param {string} $password
   * @return {string}
   */
  public static function encryptPassword ($password) {
    $random = strtoupper(md5(rand().rand()));
    $left = substr($random, 0, 2);
    $right = substr($random, -2);
    $newpassword = strtoupper(md5($left.$password.$right));
    return $left.':'.$newpassword.':'.$right;
  }

  /**
   * 验证密码
   *
   * @param {string} $password 待验证的密码
   * @param {string} $encrypted 密码加密字符串
   * @return {bool}
   */
  public static function validatePassword ($password, $encrypted) {
    $random = explode(':', strtoupper($encrypted));
    if (count($random) < 3) return FALSE;
    $left = $random[0];
    $right = $random[2];
    $main = $random[1];
    $newpassword = strtoupper(md5($left.$password.$right));
    return $newpassword == $main;
  }

  /**
   * 显示出错信息
   *
   * @param {string} $msg
   */
  public static function showError ($msg) {
    echo "<div style='color: #900;
  font-size: 16px;
  border: 1px solid #900;
  padding: 8px 12px;
  border-radius: 5px;
  margin: 12px 0px;'>$msg</div>";
  }

  /**
   * 载入模板
   * 如果指定了参数$layout，则会嵌套一个layout模板
   *
   * @param {string} $name
   * @param {array} $locals
   * @param {string} $layout
   */
  public static function template ($name, $locals, $layout = '') {
    if (!pathinfo($name, PATHINFO_EXTENSION)) {
      $name = $name.'.html';
    }
    if (empty($layout)) {
      $filename = APP_TEMPLATE_ROOT.$name;
      $timestamp = microtime(true);
      include($filename);
      DEBUG::put('Render '.$filename.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'Template');
    } else {
      if (!pathinfo($layout, PATHINFO_EXTENSION)) {
        $layout = $layout.'.html';
        $filename = APP_TEMPLATE_ROOT.$name;
        $timestamp = microtime(true);
        ob_start();
        include($filename);
        DEBUG::put('Render '.$filename.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'Template');
        $body = ob_get_clean();
        $filename = APP_TEMPLATE_ROOT.$layout;
        $timestamp = microtime(true);
        include($filename);
        DEBUG::put('Render '.$filename.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'Template');
      }
    }
  }

  /**
   * 加载文件
   * 文件名如果不指定扩展名，则自动加上.php再加载
   * 如果以 / 开头，则从应用根目录开始查找
   *
   * @param {string} $filename
   * @return {mixed}
   */
  public static function load ($filename) {
    if (!pathinfo($filename, PATHINFO_EXTENSION)) {
      $filename = $filename.'.php';
    }
    if (substr($filename, 0, 1) == '/') {
      $filename = APP_ROOT.substr($filename, 1);
    } else {
      $filename = dirname($_SERVER["SCRIPT_FILENAME"]).'/'.$filename;
    }
    return require($filename);
  }

  /**
   * 调试输出
   *
   * @param {mixed} $var
   */
  public static function dump ($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
  }

  /**
   * 初始化
   */
  public static function init () {
    // 是否关闭出错显示
    if (defined('APP_DEBUG')) {
      error_reporting(E_ALL);
      ini_set('display_errors', '1');
    } else {
      error_reporting(0);
      ini_set('display_errors', '0');
    }
    // 连接数据库
    if (defined('CONF_MYSQL_SERVER')) {
      SQL::connect(CONF_MYSQL_SERVER, CONF_MYSQL_USER, CONF_MYSQL_PASSWD, CONF_MYSQL_DBNAME);
    }
  }
}
