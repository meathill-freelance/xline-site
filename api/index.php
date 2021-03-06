<?php
$pdo = require_once(dirname(__FILE__) . '/../inc/pdo.php');
require_once(dirname(__FILE__) . '/../inc/API.class.php');
require_once(dirname(__FILE__) . '/../inc/Spokesman.class.php');

define('WOOCOMMERCE_CART', true);

$api = new API(array(
  'fetch' => 'fetch',
  'update' => 'update',
  'create' => 'create',
  'delete' => 'delete',
));

function fetch() {
  global $pdo;
  $p = (int)$_REQUEST['p'];
  $id = (int)$_REQUEST['id'];

  $sql = "SELECT `data$p`
          FROM `t_diy_detail`
          WHERE `id`=$id";
  $json = $pdo->query($sql)->fetchColumn();

  $json = json_decode($json, true);
  foreach ($json['steps'] as $key => $step) {
    $step['color'] = dechex($step['color']);
    $step['color2'] = dechex($step['color2']);
    $json['steps'][$key] = $step;
  }

  Spokesman::say($json);
}

function update($args, $attr) {
  if (isset($attr['group'])) {
    $group = $attr['group'];
    unset($attr['group']);
    unset($attr['id']);
  }
  global $pdo;
  $set = array();
  $values = array();
  foreach ($attr as $key => $value) {
    $set[] = "`$key`=:$key";
    $values[":$key"] = $value;
  }
  $set = implode(', ', $set);
  $sql = "UPDATE `t_cart_item_map`
          SET $set
          WHERE `id` IN ($group)";
  $sth = $pdo->prepare($sql);
  $result = $sth->execute($values);
  $attr['sql'] = $sql;

  Spokesman::judge($result, '修改成功', '修改失败', $attr);
}

function create($args, $attr) {
  require_once dirname(__FILE__) . '/../../../../wp-load.php';
  global $pdo;
  $keys = explode(',', $attr['keys']);
  $me = get_current_user_id();
  $group = array();
  foreach ($keys as $key) {
    $cart_item = WC()->cart->get_cart()[$key];
    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $key);
    $design_id = $cart_item['variation']['attribute_pa_size'];
    $quantity = $cart_item['quantity'];
    WC()->cart->set_quantity($key, $quantity + 1);
    $sql = "INSERT INTO `t_cart_item_map`
          (`cart_item_key`, `product_id`, `design_id`, `user_id`, `playername`, `number`, `size`)
          VALUES (:key, :product_id, :design_id, :user_id, :playername, :number, :size)";
    $state = $pdo->prepare($sql);
    $state->execute(array(
      ':key' => $key,
      ':product_id' => $product_id,
      ':design_id' => $design_id,
      ':user_id' => $me,
      ':playername' => $attr['playername'],
      ':number' => $attr['number'],
      ':size' => $attr['size'],
    ));
    $group[] = $pdo->lastInsertId();
  }

  WC()->cart->calculate_totals();
  Spokesman::judge($group, '添加成功', '添加失败', array(
    'quantity' => $quantity + 1,
    'sub' => WC()->cart->subtotal,
    'ship' => WC()->cart->shipping_total,
    'amount' => WC()->cart->total,
  ), array('group' => implode(',', $group)));
}

function delete($args) {
  require_once dirname(__FILE__) . '/../../../../wp-load.php';
  $args = Spokesman::extract();
  $id = $args['id'];
  $me = get_current_user_id();
  global $pdo;

  // 取map内容
  $sql = "SELECT `cart_item_key`, `design_id`, `playername`, `number`, `size`
          FROM `t_cart_item_map`
          WHERE `id`=$id AND `user_id`=$me";
  $map = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

  // 取其它同一套设计的衣服
  $key = $map['cart_item_key'];
  $design_id = $map['design_id'];
  $player_name = $map['playername'];
  $number = $map['number'];
  $size = $map['size'];
  $sql = "SELECT `id`, `cart_item_key`
          FROM `t_cart_item_map`
          WHERE `design_id`=$design_id AND `playername`='$player_name' AND `number`=$number
            AND `size`=$size AND `user_id`=$me AND `status`=0 AND `cart_item_key`!='$key'";
  $other = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

  // 删掉这套衣服
  $ids = array($id);
  if ($other) {
    $ids[] = $other['id'];
  }
  $ids = implode(',', $ids);
  $sql = "UPDATE `t_cart_item_map`
          SET `status`=1
          WHERE `id` IN ($ids)";
  $pdo->query($sql);

  // 修改购物车
  $keys = array($key);
  if ($other) {
    $keys[] = $other['cart_item_key'];
  }
  foreach ($keys as $key) {
    $cart_item = WC()->cart->get_cart()[$key];
    $quantity = $cart_item['quantity'];
    WC()->cart->set_quantity($key, $quantity - 1);
  }

  WC()->cart->calculate_totals();
  // 返回状态
  Spokesman::say(array(
    'code' => 0,
    'msg' => '删除成功',
    'quantity' => $quantity - 1,
    'sub' => WC()->cart->subtotal,
    'ship' => WC()->cart->shipping_total,
    'amount' => WC()->cart->total,
  ));
}