<?php
/**
 * Created by PhpStorm.
 * Date: 14-7-29
 * Time: 下午3:15
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since
*/
require_once dirname(__FILE__) . "/Spokesman.class.php";

// 检查是否登录
function line_is_login() {
  $result = array(
    'is_login' => true || is_user_logged_in(),
    'code' => 0,
  );
  Spokesman::say($result);
}
add_action('wp_ajax_nopriv_line_is_login', "line_is_login");
add_action('wp_ajax_line_is_login', "line_is_login");

function line_create_pic() {
  $pdo = require_once dirname(__FILE__) . "/pdo.php";
  $userid = get_current_user_id();
  $type = $_REQUEST['type'];
  $now = date('Y-m-d H:i:s');

  // 计算路径
  $uid = uniqid();
  $year = date('Y');
  $month = date('m');
  $path = dirname(__FILE__) . "../../../../user/$year/$month/";
  if (!is_dir($path)) {
    mkdir($path, 0777, true);
  }
  $filename = $path . "{$userid}_{$uid}.jpg";

  // 写入文件
  $fp = fopen($filename, 'w');
  fwrite($fp, $HTTP_RAW_POST_DATA);
  fclose($fp);

  // 插入记录
  $sql = "INSERT INTO `t_upload_log`
          (`userid`, `create_time`, `type`, `url`, `size`)
          VALUES (:userid, :now, :type, :url, :size)";
  $sth = $pdo->prepare($sql);
  $sth->execute(array(
    ':userid' => $userid,
    ':now' => $now,
    ':type' => $type,
    ':url' => esc_url(content_url('/')) . "user/$year/$month/{$userid}_{$uid}.jpg",
    ':size' => 0,
  ));
  $id = $pdo->lastInsertId();

  Spokesman::judge($id, '保存成功', '保存失败', array(
    'id' => $id,
    'url' => esc_url(content_url('/')) . "user/$year/$month/{$userid}_{$uid}.jpg",
  ));
  exit();
}
add_action('wp_ajax_nopriv_line_create_pic', "line_create_pic");
add_action('wp_ajax_line_create_pic', "line_create_pic");

// 保存diy结果
function line_save() {
  $pdo = require_once dirname(__FILE__) . "/pdo.php";
  $userid = get_current_user_id();
  $name = $_REQUEST['name'];
  $url = $_REQUEST['url'];
  $design = $_REQUEST['design'];
  $now = date('Y-m-d H:i:s');

  // 保存设计
  $sql = "INSERT INTO `t_user_diy`
          (`userid`, `create_time`, `update_time`, `name`, `thumbnail`)
          VALUES (:userid, :now, :now, :name, :url)";
  $sth = $pdo->prepare($sql);
  $sth->execute(array(
    ':userid' => $userid,
    ':now' => $now,
    ':name' => $name,
    ':url' => $url,
  ));
  $id = $pdo->lastInsertId();

  // 记录设计全文
  $sql = "INSERT INTO `t_diy_detail`
          (`id`, `json`)
          VALUES (:id, :json)";
  $sth = $pdo->prepare($sql);
  $check = $sth->execute(array(
    ':id' => $id,
    ':json' => $design,
  ));

  Spokesman::judge($check, '保存成功', '保存失败', array(
    'id' => $id,
  ));
  exit();
}
add_action('wp_ajax_nopriv_line_save', "line_save");
add_action('wp_ajax_line_save', "line_save");

// 保存壁纸
function line_create_wallpaper() {
  header('Content-type:application/json; charset: UTF-8');
  echo json_encode(array(
    'msg' => 'line wallpaper',
  ));
  exit();
}
add_action('wp_ajax_nopriv_line_create_wallpaper', "line_create_wallpaper");
add_action('wp_ajax_line_create_wallpaper', "line_create_wallpaper");

// 添加到购物车并结算
function line_buy() {
  header('Content-type:application/json; charset: UTF-8');
  echo json_encode(array(
    'msg' => 'line buy',
  ));
  exit();
}
add_action('wp_ajax_nopriv_line_buy', "line_buy");
add_action('wp_ajax_line_buy', "line_buy");