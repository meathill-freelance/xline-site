<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

wc_print_notices();

do_action( 'woocommerce_before_cart' );
do_action( 'woocommerce_before_cart_table' );

// xline的逻辑，以设计为基础列表商品
$result = array(
  'cart_url' => esc_url(WC()->cart->get_cart_url()),
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
  $design = $designs[$design_id];
  $design = isset($design) ? $design : array(
    'id' => $design_id,
    'clothes' => array(),
  );
  $design['clothes'][] = array(
    'title' => apply_filters('woocommerce_cart_item_name', $product->get_title(), $cart_item, $cart_item_key),
    'price' => apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($product), $cart_item, $cart_item_key),
  );

  // 取所有号码姓名等
  if (!$design['member']) {
    $sql = "SELECT *
            FROM `t_cart_item_map`
            WHERE `design_id`=$design_id AND `cart_item_key`='$cart_item_key'";
    var_dump($sql);
    $design['member'] = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }
  // 取缩略图
  if (!$design['thumbnail']) {
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
    'price' => $amount,
  );
  $designs[$id] = $design;
}

$result['designs'] = array_values($designs);

Spokesman::toHTML($result, dirname(__FILE__) . '/../../template/cart.html');

?>
<table class="shop_table cart" cellspacing="0">
	<thead>
		<tr>
			<th class="product-remove">&nbsp;</th>
			<th class="product-thumbnail">&nbsp;</th>
			<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
			<th class="product-quantity"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th class="product-subtotal"><?php _e( 'Total', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

					<td class="product-remove">
						<?php
							echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );
						?>
					</td>

					<td class="product-thumbnail">
						<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

							if ( ! $_product->is_visible() )
								echo $thumbnail;
							else
								printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
						?>
					</td>

					<td class="product-name">
						<?php
							if ( ! $_product->is_visible() )
								echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key );
							else
								echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', $_product->get_permalink(), $_product->get_title() ), $cart_item, $cart_item_key );

							// Meta data
							echo WC()->cart->get_item_data( $cart_item );

               				// Backorder notification
               				if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
               					echo '<p class="backorder_notification">' . __( 'Available on backorder', 'woocommerce' ) . '</p>';
						?>
					</td>

					<td class="product-price">
						<?php
							echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
						?>
					</td>

					<td class="product-quantity">
						<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
							} else {
								$product_quantity = woocommerce_quantity_input( array(
									'input_name'  => "cart[{$cart_item_key}][qty]",
									'input_value' => $cart_item['quantity'],
									'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
									'min_value'   => '0'
								), $_product, false );
							}

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
						?>
					</td>

					<td class="product-subtotal">
						<?php
							echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
						?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_cart_contents' );
		?>
		<tr>
			<td colspan="6" class="actions">

				<?php if ( WC()->cart->coupons_enabled() ) { ?>
					<div class="coupon input-group">

            <input type="text" name="coupon_code" class="form-control" id="coupon_code" value="" placeholder="<?php _e( 'Coupon code', 'woocommerce' ); ?>" />
            <span class="input-group-btn">
              <button type="button" class="btn btn-default" name="apply_coupon"><?php _e( 'Apply Coupon', 'woocommerce' ); ?></button>
            </span>


						<?php do_action('woocommerce_cart_coupon'); ?>

					</div>
				<?php } ?>
        <div class="btn-group pull-right">
          <button type="button" class="btn btn-info" name="update_cart"><?php _e( 'Update Cart', 'woocommerce' ); ?></button>
          <button class="checkout-button btn btn-primary alt wc-forward" name="proceed" value="<?php _e( 'Proceed to Checkout', 'woocommerce' ); ?>"><?php _e( 'Proceed to Checkout', 'woocommerce' ); ?></button>
        </div>


				<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>

				<?php wp_nonce_field( 'woocommerce-cart' ); ?>
			</td>
		</tr>

		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	</tbody>
</table>

<?php do_action( 'woocommerce_after_cart_table' ); ?>

</form>

<div class="cart-collaterals">

	<?php do_action( 'woocommerce_cart_collaterals' ); ?>

	<?php woocommerce_cart_totals(); ?>

	<?php woocommerce_shipping_calculator(); ?>

</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
