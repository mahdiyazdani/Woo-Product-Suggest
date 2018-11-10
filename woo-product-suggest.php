<?php
/*
Plugin Name: 			Woo Product Suggest
Plugin URI:  			https://www.mypreview.one
Description: 			Suggest and link a WooCommerce product to an existing product or bundle with custom notice.
Version:     			1.1.0
Author:      			Mahdi Yazdani
Author URI:  			https://www.mypreview.one
Text Domain: 			woo-product-suggest
Domain Path: 			/languages
License:     			GPL2
License URI: 			https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 	3.0.0
WC tested up to: 		3.5.1

Woo Product Suggest is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Woo Product Suggest is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Woo Product Suggest. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
// Prevent direct file access
if (!defined('ABSPATH')) exit;
if (!class_exists('Woo_Product_Suggest')):
	/**
	 * The Woo Product Suggest - Class
	 */
	final class Woo_Product_Suggest

	{
		private $file;
		private static $_instance = null;
		/**
		 * Main Woo_Product_Suggest instance
		 *
		 * Ensures only one instance of Woo_Product_Suggest is loaded or can be loaded.
		 *
		 * @since 1.0.1
		 */
		public static function instance()

		{
			if (is_null(self::$_instance)) self::$_instance = new self();
			return self::$_instance;
		}
		/**
		 * Setup class.
		 *
		 * @since 1.0.1
		 */
		public function __construct()

		{
			$this->file = plugin_basename(__FILE__);
			add_action('init', array(
				$this,
				'textdomain'
			) , 10);
			add_action('admin_notices', array(
				$this,
				'activation'
			) , 10);
			add_action('woocommerce_product_write_panel_tabs', array(
				$this,
				'suggest_tab'
			) , 10);
			add_action('woocommerce_product_data_panels', array(
				$this,
				'suggest_tab_fields'
			) , 10);
			add_action('woocommerce_process_product_meta', array(
				$this,
				'suggest_tab_fields_save'
			) , 10, 1);
			add_action('admin_head', array(
				$this,
				'suggest_stylesheet'
			) , 10);
			add_action('woocommerce_single_product_summary', array(
				$this,
				'output_suggest_notice'
			) , 10);
			add_filter('plugin_action_links_' . plugin_basename($this->file) , array(
				$this,
				'additional_links'
			) , 10, 1);
		}
		/**
		 * Cloning instances of this class is forbidden.
		 *
		 * @since 1.0.1
		 */
		public function __clone()

		{
			_doing_it_wrong(__FUNCTION__, __('Cloning instances of this class is forbidden.', 'woo-product-suggest') , '1.0.1');
		}
		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.1
		 */
		public function __wakeup()

		{
			_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'woo-product-suggest') , '1.0.1');
		}
		/**
		 * Load languages file and text domains.
		 *
		 * @since 1.0.1
		 */
		public function textdomain()

		{
			$domain = 'woo-product-suggest';
			$locale = apply_filters('geo_topbar_textdoamin', get_locale() , $domain);
			load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
			load_plugin_textdomain($domain, FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
		}
		/**
		 * Query WooCommerce activation.
		 *
		 * @since 1.0.1
		 */
		public function activation()

		{
			if (!class_exists('woocommerce')):
				$html = '<div class="notice notice-error is-dismissible">';
				$html.= '<p>';
				$html.= __('Woo Product Suggest is enabled but not effective. It requires WooCommerce in order to work.', 'woo-product-suggest');
				$html.= '</p>';
				$html.= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Dismiss this notice.', 'woo-product-suggest') . '</span></button>';
				$html.= '</div>';
				echo $html;
			endif;
		}
		/**
		 * Register the Tab.
		 *
		 * @since 1.0.1
		 */
		public function suggest_tab()

		{
			if (class_exists('woocommerce')):
				$html = '';
				$html.= '<li class="woo_product_suggest-tab">';
				$html.= '<a href="#woo_product_suggest_data">';
				$html.= __('Product Suggest', 'woo-product-suggest');
				$html.= '</a>';
				$html.= '</li>';
				echo $html;
			endif;
		}
		/**
		 * Provide the corresponding tab content.
		 *
		 * @since 1.1.0
		 */
		public function suggest_tab_fields()

		{
			if (class_exists('woocommerce')):
				global $woocommerce, $post;
				$product_id = array_filter(array_map('absint', (array)get_post_meta($post->ID, '_woo_product_suggest_id', true)));
				?>
				<div id="woo_product_suggest_data" class="panel woocommerce_options_panel">
					<p class="form-field">
						<label for="woo_product_suggest"><?php _e('Choose a product', 'woo-product-suggest'); ?> <abbr class="required" title="required">*</abbr></label>
						<select class="wc-product-search" data-multiple="false" style="width: 50%;" id="_woo_product_suggest_id" name="_woo_product_suggest_id[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'woo-product-suggest'); ?>" data-action="woocommerce_json_search_products" data-allow_clear="true" data-exclude="<?php echo intval($post->ID); ?>">
						<?php 
						$product = wc_get_product($product_id[0]);
						if (is_object($product)):
							echo '<option value="' . esc_attr($product_id[0]) . '"' . selected(true, true, false) . '>' . wp_kses_post($product->get_formatted_name()) . '</option>';
						endif;
						?>
						</select>
						<?php echo wc_help_tip(esc_html__('Link an existing product or bundle to current product.', 'woo-product-suggest')); ?>
					</p>
					<?php
					woocommerce_wp_textarea_input(array(
						'id' => '_woo_product_suggest_notice',
						'label' => __('Custom Notice', 'woo-product-suggest') . ' <abbr class="required" title="required">*</abbr>',
						'description' => __('Use %link% for appending product title and link to the notice content.', 'woo-product-suggest') ,
						'desc_tip' => true
					));
					?>
					<p>
						<label><?php esc_html_e('Looking for a stylish theme?', 'woo-product-suggest'); ?></label>
						<p><a href="<?php echo esc_url('https://www.mypreview.one/hypermarket-plus.html'); ?>" target="_blank"><img src="<?php echo esc_url(trailingslashit(plugins_url('admin/', $this->file))); ?>/img/hypermarket-plus.png" style="max-width:100%;height:auto;" /></a></p>
					</p>
				</div><!-- End #woo_product_suggest_data -->
			<?php
			endif;
		}
		/**
		 * Saving fields values.
		 *
		 * @since 1.1.0
		 */
		public function suggest_tab_fields_save($post_id)

		{
			if (class_exists('woocommerce')):
				$woo_product_suggest_id = isset($_POST['_woo_product_suggest_id']) ? array_filter(array_map('absint', $_POST['_woo_product_suggest_id'])) : array();
				update_post_meta($post_id, '_woo_product_suggest_id', $woo_product_suggest_id);
				$woo_product_suggest_notice = isset($_POST['_woo_product_suggest_notice']) ? sanitize_textarea_field($_POST['_woo_product_suggest_notice']) : '';
				update_post_meta($post_id, '_woo_product_suggest_notice', $woo_product_suggest_notice);
			endif;
		}
		/**
		 * Apply custom CSS to admin area.
		 *
		 * @since 1.0.1
		 */
		public function suggest_stylesheet()

		{
			if (class_exists('woocommerce')):
				echo '<style id="woo-product-suggest-stylesheet" type="text/css">
					    .woo_product_suggest-tab a:before {
					      content: "\f313" !important;
					    } 
				  	</style>';
			endif;
		}
		/**
		 * Retrieve plugin option value(s).
		 *
		 * @since 1.1.0
		 */
		public function output_suggest_notice()

		{
			if (class_exists('woocommerce')):
				global $post;
				$output = '';
				$woo_product_suggest_id = array_filter(array_map('absint', (array)get_post_meta($post->ID, '_woo_product_suggest_id', true)));
				$woo_product_suggest_notice = esc_attr(get_post_meta($post->ID, '_woo_product_suggest_notice', true));
				$woo_product_suggest_link_shortcode = '%link%';
				if (isset($woo_product_suggest_id, $woo_product_suggest_notice) && !empty($woo_product_suggest_id) && !empty($woo_product_suggest_notice)):
					if (strpos($woo_product_suggest_notice, $woo_product_suggest_link_shortcode) !== false):
						$output.= str_replace($woo_product_suggest_link_shortcode, '<a href="' . esc_url(get_permalink($woo_product_suggest_id[0])) . '" target="_blank">' . get_the_title($woo_product_suggest_id[0]) . '</a>', $woo_product_suggest_notice);
					else:
						$output.= $woo_product_suggest_notice;
					endif;
					wc_print_notice($output, 'success');
				endif;
			endif;
		}
		/**
		 * Display plugin docs and support links in plugins table page.
		 *
		 * @since 1.1.0
		 */
		public function additional_links($links)

		{
			// Add support link to plugin list table
			$plugin_links = array();
			$plugin_links[] = sprintf(__('<a href="%s" target="_blank">Support</a>', 'woo-product-suggest') , esc_url('https://support.mypreview.one/t/woo-product-suggest'));
			return array_merge($plugin_links, $links);
		}
	}
endif;
/**
 * Returns the main instance of Woo_Product_Suggest to prevent the need to use globals.
 *
 * @since 1.0.1
 */
if (!function_exists('woo_product_suggest_initialization')):
	function woo_product_suggest_initialization()
	{
		return Woo_Product_Suggest::instance();
	}
	woo_product_suggest_initialization();
endif;
