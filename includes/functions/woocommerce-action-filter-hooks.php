<?php
/**
 * Include action/filter hooks realted to WooCommerce.
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 */

/**
 * Remove add to cart function on shop and category pages.
 *
 * @return void
 */
function tti_platform_remove_add_to_cart_buttons() {
	if ( is_product_category() || is_shop() ) {
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
	}
}
add_action( 'woocommerce_after_shop_loop_item', 'tti_platform_remove_add_to_cart_buttons', 1 );

/**
 * Show enroll me notification before add to cart button single product page
 *
 * @since 1.4
 */
function tti_platform_before_add_to_cart_btn() {
	global $product;

	$product_id  = $product->get_id();
	$is_show_msg = false;

	$wdm_ldgr_paid_course     = get_post_meta( $product_id, '_is_ldgr_paid_course', true );
	$is_group_purchase_active = get_post_meta( $product_id, '_is_group_purchase_active', true );

	if ( function_exists( 'ldgr_is_user_in_group' ) ) {
		if ( ! ldgr_is_user_in_group( $product_id ) ) {
			$is_show_msg = true;
		}
	}

	if ( 'on' === $wdm_ldgr_paid_course && 'on' === $is_group_purchase_active && $is_show_msg ) {
		echo '<div class="ttisi-front-noti" style="display:none;"><p style="margin:0px;">IMPORTANT - Check <strong>"Enroll Me"</strong> if you will be completing the assessment yourself. Selecting this option will take one purchased usage and the remaining usages (if purchasing more than one) will be banked so you can assign them to respondents as needed.</p></div>';
		?>
		<script type="text/javascript">
			jQuery( document ).ready(function() {
				if ( jQuery( ".wdm-enroll-me-div" ).length ) {
					jQuery( ".ttisi-front-noti" ).show();
				} else {
					jQuery( ".ttisi-front-noti" ).hide();
				}
			});
			</script>
		<?php
	}
}
add_action( 'woocommerce_before_add_to_cart_button', 'tti_platform_before_add_to_cart_btn', 100 );

/**
 * Show product view button below product thumbnail
 *
 * @since 1.4
 */
function tti_platform_shop_view_product_button() {
	global $product;
	$link = $product->get_permalink();
	echo '<a href="' . esc_url( $link ) . '" class="button addtocartbutton">View Product</a>';
}
add_action( 'woocommerce_after_shop_loop_item', 'tti_platform_shop_view_product_button', 10 );
