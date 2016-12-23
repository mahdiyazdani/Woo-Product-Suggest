<?php
/**
 * Retrieve plugin option value(s).
 *
 * @author      Mahdi Yazdani
 * @package     Woo Product Suggest
 * @since       1.0
 */
if (!function_exists('woo_product_suggest_available_notice')): 
	function woo_product_suggest_available_notice() {
		global $post;
		$output = '';
		$woo_product_suggest_id = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_woo_product_suggest_id', true ) ) );
		$woo_product_suggest_notice = esc_attr( get_post_meta( $post->ID, '_woo_product_suggest_notice', true) );
		$woo_product_suggest_link_shortcode = '%link%';
		if( isset($woo_product_suggest_id, $woo_product_suggest_notice) && !empty($woo_product_suggest_id) && !empty($woo_product_suggest_notice) ) :
			if( strpos($woo_product_suggest_notice, $woo_product_suggest_link_shortcode) !== false ) :
				$output .= str_replace( $woo_product_suggest_link_shortcode, '<a href="' . esc_url( get_permalink($woo_product_suggest_id[0]) ) . '" target="_blank">' . get_the_title( $woo_product_suggest_id[0] ) . '</a>', $woo_product_suggest_notice );
			else:
				$output .= $woo_product_suggest_notice;
			endif;
			wc_print_notice( $output, 'success' );
		endif;
	}
endif;
add_action( 'woocommerce_single_product_summary', 'woo_product_suggest_available_notice', 10 );