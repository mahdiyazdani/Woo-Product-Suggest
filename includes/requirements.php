<?php
/**
 * Query WooCommerce activation.
 *
 * @author      Mahdi Yazdani
 * @package     Woo Product Suggest
 * @since       1.0
 */
if (!function_exists('woo_product_suggest_req_notice_error')):
	function woo_product_suggest_req_notice_error() {
		$class = 'notice notice-error';
		$message = __( 'Woo Product Suggest is enabled but not effective. It requires WooCommerce in order to work.', 'woo-product-suggest' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
endif;
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) :
	add_action( 'admin_notices', 'woo_product_suggest_req_notice_error' );
endif;