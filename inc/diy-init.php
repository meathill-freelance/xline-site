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

function line_create_pic() {
  $pdo = require_once dirname(__FILE__) . "/pdo.php";
  $userid = get_current_user_id();
  $type = $_REQUEST['type'];
  $now = date('Y-m-d H:i:s');

  // 计算路径
  $uid = uniqid();
  $year = date('Y');
  $month = date('m');
  $path = dirname(__FILE__) . "/../../../design/$year/$month/";
  if (!is_dir($path)) {
    mkdir($path, 0777, true);
  }
  $filename = $path . "{$userid}_{$uid}.jpg";

  // 写入文件
  $fp = fopen($filename, 'w');
  fwrite($fp, file_get_contents("php://input"));
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
    ':url' => esc_url(content_url('/')) . "design/$year/$month/{$userid}_{$uid}.jpg",
    ':size' => 0,
  ));
  $id = $pdo->lastInsertId();

  Spokesman::judge($id, '保存成功', '保存失败', array(
    'id' => $id,
    'url' => esc_url(content_url('/')) . "design/$year/$month/{$userid}_{$uid}.jpg",
    'file' => $filename,
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
  $json = json_decode(stripslashes($design), true);
  $id = (int)$_REQUEST['id'];
  $now = date('Y-m-d H:i:s');

  // 设计细节
  $cloth1 = $json[0]['tid'];
  $data1 = $json[0];
  $data2 = count($json) > 1 ? $json[1] : '';
  $cloth2 = $data2 ? $data2['tid'] : '';

  if ($id > 0) { // 修改
    // 判断设计所有者
    $sql = "SELECT `userid`
            FROM `t_user_diy`
            WHERE `id`=$id";
    $author = $pdo->query($sql)->fetch(PDO::FETCH_COLUMN);
    if ($author !== $userid) {
      header('HTTP/1.1 401 Unauthorized');
      exit(json_encode(array(
        'code' => 2,
        'msg' => '不能修改别人的作品哦'
      )));
    }

    // 修改设计
    $sql = "UPDATE `t_user_diy`
            SET `update_time`=:now, `name`=:name, `thumbnail`=:url
            WHERE `id`=$id";
    $sth = $pdo->prepare($sql);
    $result = $sth->execute(array(
      ':now' => $now,
      ':name' => $name,
      ':url' => $url,
    ));
    // 修改设计记录
    if (!$result) {
      header('HTTP/1.1 400 Bad Request');
      exit(json_encode(array(
        'code' => 1,
        'msg' => '修改失败'
      )));
    }
    $sql = "UPDATE `t_diy_detail`
            SET `cloth1`=:cloth1, `cloth2`=:cloth2, `data1`=:data1, `data2`=:data2
            WHERE `id`=$id";
    $sth = $pdo->prepare($sql);
    $result = $sth->execute(array(
      ':cloth1' => $cloth1,
      ':cloth2' => $cloth2,
      ':data1' => $data1,
      ':data2' => $data2,
    ));
    Spokesman::judge($result, '修改成功', '修改失败');
    exit();
  }

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

  if (!$id) {
    Spokesman::say(array(
      'code' => 3,
      'msg' => '保存信息失败',
    ));
  }

  // 记录设计全文
  $sql = "INSERT INTO `t_diy_detail`
          (`id`, `cloth1`, `cloth2`, `data1`, `data2`)
          VALUES (:id, :cloth1, :cloth2, :data1, :data2)";
  $sth = $pdo->prepare($sql);
  $data = array(
    ':id' => $id,
    ':cloth1' => $cloth1,
    ':cloth2' => $cloth2,
    ':data1' => json_encode($data1),
    ':data2' => json_encode($data2),
  );
  $check = $sth->execute($data);

  Spokesman::judge($check, '保存成功', '保存失败', array(
    'id' => $id,
  ));
  exit();
}
add_action('wp_ajax_nopriv_line_save', "line_save");
add_action('wp_ajax_line_save', "line_save");

// 添加到购物车并结算
function line_buy() {
  header('Content-type:application/json; charset: UTF-8');

  global $woocommerce;
  $design_id = $_REQUEST['id'];
  $products = $_REQUEST['pid'];
  $products = explode(',', $products);
  $quantity = 1;
  $variation_id = null;
  $variation = null;
  $cart_item_data = array(
    'design_id' => $design_id,
  );
  foreach ($products as $product_id) {
    $check = $woocommerce->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);
  }

  Spokesman::judge($check, '购买成功', '购买失败');
  exit();
}
add_action('wp_ajax_nopriv_line_buy', "line_buy");
add_action('wp_ajax_line_buy', "line_buy");