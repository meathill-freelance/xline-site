<?php
/**
 * Created by PhpStorm.
 * User: meathill
 * Date: 14-10-2
 * Time: 下午10:23
 */
require_once dirname(__FILE__) . '/../inc/Spokesman.class.php';
$pdo = require_once dirname(__FILE__) . '/../inc/pdo.php';

$cloth_color = array("252525", "ABAEB5", "1F6742", "FCFCFC", "27458B", "AA2327", "EEC43E", "ED406E", "E05631", "608DCE");
$cloth_color_name = array("黑色", "灰色", "松绿", "白色", "皇家蓝", "大学红", "郁金色", "淡粉", "橘红", "校园蓝");
$char_color = array("252525", "B53536", "FFB51E", "22314E", "A1A6AC", "195C3B", "1323B7", "C9532B", "493477", "958049", "FE467C", "016769", "3AA2FB", "FFFFFF");
$char_color_name = array("黑色", "大学红", "郁金色", "宝蓝色", "灰色", "松绿", "皇家蓝", "橘色", "宫廷紫", "金属黄", "荧光粉", "青色", "校园蓝", "白色");
// 取上衣的id
$sql = "SELECT `object_id`,`term_taxonomy_id`
        FROM `wp_term_relationships`
        WHERE `term_taxonomy_id` IN (36,37)"; // 36上衣，37短裤
$terms = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$top = array();
$pants = array();
foreach ($terms as $term) {
  if ($term['term_taxonomy_id'] == 36) {
    $top[] = $term['object_id'];
  } else {
    $pants[] = $term['object_id'];
  }
}


$order_id = (int)$_REQUEST['id'];

// 取发货信息
$sql = "SELECT `meta_key`, `meta_value`
        FROM `wp_postmeta`
        WHERE `post_id`=$order_id";
$meta = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$ship = array();
foreach ($meta as $item) {
  $ship[$item['meta_key']] = $item['meta_value'];
}

// 取用户名
$sql = "SELECT `user_nicename`
        FROM `wp_users`
        WHERE `ID`=" . $ship['_customer_user'];
$nickname = $pdo->query($sql)->fetchColumn();

// 取order_item
$sql = "SELECT `order_item_id`
				FROM `wp_woocommerce_order_items`
				WHERE `order_id`=$order_id";
$items = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

$items_sql = implode(',', $items);

// 取对应的设计
$sql = "SELECT `order_item_id`, `meta_key`, `meta_value`
				FROM `wp_woocommerce_order_itemmeta`
				WHERE `order_item_id` IN ($items_sql) AND `meta_key` IN ('_qty', 'pa_size', '_product_id')";
$item_meta = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$order_items = array();
$designs = array();
foreach ($item_meta as $meta) {
  if (array_key_exists($meta['order_item_id'], $order_items)) {
    $order_item = $order_items[$meta['order_item_id']];
    $order_item[$meta['meta_key']] = $meta['meta_value'];
  } else {
    $order_item = array(
      $meta['meta_key'] => $meta['meta_value'],
    );
  }
  if ($meta['meta_key'] == 'pa_size') {
    $designs[] = $meta['meta_value'];
  }
  $order_items[$meta['order_item_id']] = $order_item;
}
$designs = array_unique($designs);
$design_sql = implode(',', $designs);

// 取生产数量
$quantity = array();
foreach ($order_items as $order_item) {
  $quantity[$order_item['_product_id']] = $order_item['_qty'];
}

// 取设计的图片
$sql = "SELECT `id`,`thumbnail`
        FROM `t_user_diy`
        WHERE `id` IN ($design_sql)";
$thumbnails = $pdo->query($sql)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_COLUMN);

// 取球队信息
$sql = "SELECT `design_id`, `playername`, `number`, `size`
        FROM `t_cart_item_map`
        WHERE `order_id`=$order_id AND `status`=2";
$players = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$members = array();
foreach ($players as $player) {
  if (array_key_exists($player['design_id'], $members)) {
    $group = $members[$player['design_id']];
  } else {
    $group = array();
  }
  $is_same = false;
  foreach ($group as $member) {
    if ($player['playername'] == $member['playername'] && $player['number'] == $member['number']
      && $player['size'] == $member['size']) {
      $is_same = true;
      break;
    }
  }
  if (!$is_same) {
    $group[] = $player;
  }
  $members[$player['design_id']] = $group;
}


// 取该设计信息
$sql = "SELECT `id`,`data1`,`data2`
				FROM `t_diy_detail`
				WHERE `id` IN ($design_sql)";
$design_details = $pdo->query($sql)->fetchALL(PDO::FETCH_ASSOC);

$tid = array();
$designs = array();
foreach ($design_details as $detail) {
  $design = array(
    'thumbnail' => $thumbnails[$detail['id']],
    'members' => $members[$detail['id']],
    'parts' => array(),
  );
  for ($i = 1; $i < 3; $i++) {
    $json = json_decode($detail["data$i"], true);
    $tid[] = $json['tid'];
    $design['parts'][] = $json;
  }
  $designs[] = $design;
}

// 取衣服版式
$tid_sql = implode(',', $tid);
$sql = "SELECT `ID`, `post_title`
        FROM `wp_posts`
        WHERE `ID` IN ($tid_sql)";
$product_names = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE);
foreach ($designs as $key => $design) {
  foreach ($design['parts'] as &$detail) {
    $detail['product_name'] = $product_names[$detail['tid']];
    $detail['quantity'] = $quantity[$detail['tid']];
    foreach ($detail['steps'] as &$step) {
      switch ($step['type']) {
        case 'color':
          $step['type'] = '布料颜色';
          $color = strtoupper(dechex($step['color']));
          $step['content'] = $cloth_color_name[array_search($color, $cloth_color)];
          break;

        case 'teamname':
          $step['type'] = '文字';
          $color = strtoupper(dechex($step['color']));
          $step['content'] = implode('，<br>', array(
            '字体：' . $step['font'],
            '颜色：' . $char_color_name[array_search($color, $char_color)],
            '内容：' . $step['teamname'],
          ));
          break;

        case 'number':
          $step['type'] = '号码';
          $color = strtoupper(dechex($step['color']));
          $color2 = strtoupper(dechex($step['color2']));
          $color = $char_color_name[array_search($color, $char_color)];
          $color2 = $char_color_name[array_search($color2, $char_color)];
          $step['content'] = implode('，<br>', array(
            '样式：' . $step['style'],
            '颜色：' . $color . ', ' . $color2,
            '数字：' . $number,
          ));
          break;
      }
    }
  }
  $designs[$key] = $design;
}


$result = array(
  'nickname' => $nickname,
  'ship' => $ship,
  'designs' => $designs,
);

Spokesman::toHTML($result, dirname(__FILE__) . '/../template/output.html');