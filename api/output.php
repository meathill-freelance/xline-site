<?php
/**
 * Created by PhpStorm.
 * User: meathill
 * Date: 14-10-2
 * Time: 下午10:23
 */
require_once dirname(__FILE__) . '/../inc/Spokesman.class.php';
$pdo = require_once dirname(__FILE__) . '/../inc/pdo.php';

$order_id = (int)$_REQUEST['id'];

// 取order_item
$sql = "SELECT `order_item_id`
				FROM `wp_woocommerce_order_items`
				WHERE `order_id`=$order_id";
$items = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

$items_sql = implode(',', $items);
// 取对应的设计
$sql = "SELECT `meta_value`
				FROM `wp_woocommerce_order_itemmeta`
				WHERE `order_item_id` IN ($items_sql) AND `meta_key`='pa_size'";
$designs = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
$designs = array_unique($designs);

$design_sql = implode(',', $designs);
// 取该设计应有的队伍信息
$sql = "SELECT `data1`,`data2`
				FROM `t_diy_detail`
				WHERE `id` IN ($design_sql)";
$design_details = $pdo->query($sql)->fetchALL(PDO::FETCH_ASSOC);

foreach ( $design_details as $key => $detail ) {
	$design_details[$key] = json_decode($detail, true);
}


$result = array();

Spokesman::toHTML($result, dirname(__FILE__) . '/../template/output.html');