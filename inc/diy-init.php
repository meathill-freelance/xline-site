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
  $pdo = require dirname(__FILE__) . "/pdo.php";
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
  $pdo = require dirname(__FILE__) . "/pdo.php";
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

  // 衣服类型
  if ($cloth1 >= 140) {
    $type = 2;
  } elseif ($cloth1 <= 84) {
    $type = 0;
  } else {
    $type = 1;
  }

  // 设计名称为空的话以队名取代
  if (!$name) {
    foreach ($data1['step'] as $step) {
      if ($step['type'] == 'teamname' && $step['title'] == '队名') {
        $name = $step['teamname'];
        break;
      }
    }
  }

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
          (`userid`, `create_time`, `update_time`, `name`, `thumbnail`, `type`)
          VALUES (:userid, :now, :now, :name, :url, :type)";
  $sth = $pdo->prepare($sql);
  $sth->execute(array(
    ':userid' => $userid,
    ':now' => $now,
    ':name' => $name,
    ':url' => $url,
    ':type' => $type,
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
function line_buy($player_name = '', $number = '', $size = '') {
  header('Content-type:application/json; charset: UTF-8');

  $pdo = require dirname(__FILE__) . "/pdo.php";

  $design_id = $_REQUEST['id'];
  $products = $_REQUEST['pid'];
  $products = explode(',', $products);
  $quantity = 1;
  $variation_id = null;
  $variation = null;

  // 取设计稿
  $sql = "SELECT `cloth1`, `cloth2`, `data1`, `data2`
          FROM `t_diy_detail`
          WHERE `id`=$design_id";
  $design = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
  $data1 = json_decode($design['data1'], true);
  foreach ($data1['steps'] as $step) {
    if ($step['type'] == 'teamname') {
      if($step['title'] == '队名') {
        $teamname = $step['teamname'];
      } else {
        $player_name = $step['teamname'];
      }
    }
    if ($step['type'] == 'number') {
      $number = $step['number'];
    }
  }
  $me = get_current_user_id();

  foreach ($products as $product_id) {
    $product = new WC_Product_Variable($product_id);
    $variations = $product->get_available_variations();
    // 判断是否用了双色
    $data = $product_id == $design['cloth1'] ? $design['data1'] : $design['data2'];
    $data = json_decode($data, true);
    $color = '';
    foreach ($data['steps'] as $step) {
      if ($player_name == '' && $step['type'] == 'teamname' && $step['title'] == '姓名') {
        $player_name = $step['teamname'];
      }
      if ($step['type'] == 'number') {
        if ($step['style'] < 8) {
          $color = 'double';
        }
        $number = isset($number) ? $number : $step['number'];
      }
    }
    $variation = $variations[0]['attributes']['attribute_pa_number'] == $color ?
      $variations[0] : $variations[1];
    $variation_id = $variation['variation_id'];
    $variation = $variation['attributes'];
    $variation['attribute_pa_size'] = $design_id; // 把设计id放在不影响售价的尺码属性中
    $cart_item_data = array(
      'teamname' => $teamname,
      'customer' => $me,
    );

    $key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);

    $sql = "INSERT INTO `t_cart_item_map`
            (`cart_item_key`, `product_id`, `design_id`, `user_id`, `playername`, `number`, `size`)
            VALUES (:key, :product_id, :design_id, :user_id, :playername, :number, '1')";
    $sth = $pdo->prepare($sql);
    $check = $sth->execute(array(
      ':key' => $key,
      ':product_id' => $product_id,
      ':design_id' => $design_id,
      ':user_id' => $me,
      ':playername' => $player_name,
      ':number' => $number,
    ));
  }

  Spokesman::judge($check, '已成功添加至购物车', '添加失败');
  exit();
}
add_action('wp_ajax_nopriv_line_buy', "line_buy");
add_action('wp_ajax_line_buy', "line_buy");

function line_remove_design() {
  $cart_items = explode(',', $_POST['remove_item']);
  $design = $_POST['design'];
  $wpnonce = $_POST['wpnonce'];
  if (count($cart_items) == 0 || !$wpnonce || !wp_verify_nonce($wpnonce, 'woocommerce-cart')) {
    header('HTTP/1.1 400 Bad Request');
    Spokesman::say(array(
      'code' => 1,
      'msg' => '参数错误',
    ));
    exit();
  }

  foreach ($cart_items as $cart_item_key) {
    WC()->cart->set_quantity($cart_item_key, 0);
  }
  Spokesman::say(array(
    'code' => 0,
    'design' => $design,
    'msg' => '移除成功',
  ));
}
add_action('wp_ajax_nopriv_line_remove_design', "line_remove_design");
add_action('wp_ajax_line_remove_design', "line_remove_design");