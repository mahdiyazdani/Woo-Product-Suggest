<?php
/**
 * Woo Product Suggest Class.
 *
 * @author      Mahdi Yazdani
 * @package     Woo Product Suggest
 * @since       1.0
 */
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) exit;
if ( !class_exists( 'Woo_Product_Suggest' ) ) :
	class Woo_Product_Suggest {
		public function __construct($file) {
			// Display custom field(s) inside of Linked products tab (Single product page).
			add_action( 'woocommerce_product_options_related', array( $this, 'woo_product_suggest_setting' ), 10 );
			// Save custom field(s) value(s) in database.
			add_action( 'woocommerce_process_product_meta', array( $this, 'woo_product_suggest_save' ), 10 );
		}
		public function woo_product_suggest_setting() {
			global $woocommerce, $post;
			?>
			<div class="options_group">
				<p class="form-field">
					<label for="woo_product_suggest"><?php _e( 'Choose a product', 'woo-product-suggest' ); ?> <abbr class="required" title="required">*</abbr></label>
					<input type="hidden" class="wc-product-search" style="width: 50%;" id="_woo_product_suggest_id" name="_woo_product_suggest_id" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woo-product-suggest' ); ?>" data-action="woocommerce_json_search_products" data-multiple="false" data-allow_clear="true" data-exclude="<?php echo intval( $post->ID ); ?>" data-selected="<?php
						$product_id = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_woo_product_suggest_id', true ) ) );
						if( ! empty($product_id) ):
							$product = wc_get_product( $product_id[0] );
							if ( is_object( $product ) ) :
								$product_title = wp_kses_post( html_entity_decode( $product->get_formatted_name(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
								echo esc_attr( $product_title );
							endif;
						endif;
						
					?>" value="<?php echo $product_id ? $product_id[0] : ''; ?>" /> <?php echo wc_help_tip( __( 'Link an existing product or bundle into current product.', 'woo-product-suggest' ) ); ?>
				</p>
				<?php
					woocommerce_wp_textarea_input( 
						array( 
							'id'          => '_woo_product_suggest_notice', 
							'label'       => __( 'Custom Notice', 'woo-product-suggest' ) . ' <abbr class="required" title="required">*</abbr>', 
							'placeholder' => __( 'Custom notice will appear in single product page', 'woo-product-suggest' ), 
							'description' => __( 'Use %link% for appending product title and link into notice content.', 'woo-product-suggest' ) 
						)
					);
				?>
			</div>
			<?php
		}
		public function woo_product_suggest_save( $post_id ) {
			$woo_product_suggest_id = isset( $_POST['_woo_product_suggest_id'] ) ? array_filter( array_map( 'intval', explode( ',', $_POST['_woo_product_suggest_id'] ) ) ) : array();
			update_post_meta( $post_id, '_woo_product_suggest_id', $woo_product_suggest_id );
			$woo_product_suggest_notice = isset( $_POST['_woo_product_suggest_notice'] ) ? esc_attr($_POST['_woo_product_suggest_notice']) : '';
			update_post_meta( $post_id, '_woo_product_suggest_notice', $woo_product_suggest_notice );
		}
	}
endif;