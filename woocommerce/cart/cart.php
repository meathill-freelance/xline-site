<?php
/**
 * Cart Page
 * 界面变化较大，基本相当于重写
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

wc_print_notices();

do_action( 'woocommerce_before_cart' );

ob_start();
do_action( 'woocommerce_before_cart_table' );
$before_cart_table = ob_get_clean();

$size = array(
  '大/L',
  '加大/XL',
  '加加大/XL',
  '中/M',
  '小/S',
);

// xline的逻辑，以设计为基础列表商品
$result = array(
  'cart_url' => esc_url(WC()->cart->get_cart_url()),
  'has_coupon' => WC()->cart->coupons_enabled(),
  'wpnonce' => wp_create_nonce('woocommerce-cart'),
  'before_cart_table' => $before_cart_table,
);
$designs = array();
$pdo = require dirname(__FILE__) . "/../../inc/pdo.php";
// 开始整理购物车中的物品，以design_id为分类依据
foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
  $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
  $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
  $is_visible = apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key);
  if (!$product || !$product->exists() || $cart_item['quantity'] == 0 || !$is_visible) {
    continue;
  }

  $variation = $cart_item['variation'];
  $design_id = $variation['attribute_pa_size']; // 设计id放在不影响售价的尺码属性中
  $design = array_key_exists($design_id, $designs) ? $designs[$design_id] : array(
    'id' => $design_id,
    'clothes' => array(),
    'cart_items' => array(),
  );
  $design['cart_items'][] = $cart_item_key;
  $design['clothes'][] = array(
    'title' => apply_filters('woocommerce_cart_item_name', $product->get_title(), $cart_item, $cart_item_key),
    'price' => apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($product), $cart_item, $cart_item_key),
  );

  // 取所有号码姓名等
  $sql = "SELECT *
          FROM `t_cart_item_map`
          WHERE `design_id`=$design_id AND `cart_item_key`='$cart_item_key' AND `status`=0";
  $members = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  if (array_key_exists('member', $design)) {
    foreach ($members as $key => $member) {
      $design['member'][$key]['group'] = implode(',', array($member['id'], $design['member'][$key]['id']));
    }
  } else {
    foreach ($members as $key => $member) {
      $members[$key]['size'] = $size[(int)$member['size'] - 1];
    }
    $design['count'] = count($members);
    $design['member'] = $members;
  }

  // 取缩略图
  if (!array_key_exists('thumbnail', $design)) {
    $sql = "SELECT `thumbnail`
            FROM `t_user_diy`
            WHERE `id`=$design_id";
    $design['thumbnail'] = $pdo->query($sql)->fetchColumn();
  }
  $designs[$design_id] = $design;
}
// 计算每套的总计价格
foreach ($designs as $id => $design) {
  $amount = 0;
  foreach ($design['clothes'] as $cloth) {
    $matches = array();
    preg_match('/\d+\.\d+/', $cloth['price'], $matches);
    $amount += $matches[0];
  }
  $design['clothes'][] = array(
    'title' => '总计',
    'price' => "&yen; $amount",
  );
  $design['cart_items'] = implode(',', $design['cart_items']);
  $designs[$id] = $design;
}

$result['designs'] = array_values($designs);

Spokesman::toHTML($result, dirname(__FILE__) . '/../../template/cart.html');

do_action( 'woocommerce_after_cart_table' ); ?>

<div class="cart-collaterals">

	<?php do_action( 'woocommerce_cart_collaterals' ); ?>

	<?php woocommerce_cart_totals(); ?>

</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
