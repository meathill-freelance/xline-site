<?php
/**
 * Checkout coupon form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! WC()->cart->coupons_enabled() ) {
	return;
}

$info_message = apply_filters( 'woocommerce_checkout_coupon_message', __( 'Have a coupon?', 'woocommerce' ) . ' <a href="#" class="showcoupon">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
wc_print_notice( $info_message, 'notice' );
?>

<form class="checkout_coupon form-horizontal" method="post" style="display:none">
	<div class="form-group">
    <div class="col-md-6 col-sm-8 col-xs-9">
      <input type="text" name="coupon_code" class="form-control" placeholder="请输入优惠券代码" id="coupon_code" />
    </div>
    <div class="col-md-6 col-sm-4 col-xs-3">
      <input class="btn btn-default" name="apply_coupon" value="使用优惠券" />
    </div>
	</div>
</form>