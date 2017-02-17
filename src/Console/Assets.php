<?php
/**
 * API-API Console Assets class
 *
 * @package APIAPIConsole
 * @since 1.0.0
 */

namespace APIAPI\Console;

if ( ! class_exists( 'APIAPI\Console\Assets' ) ) {

	/**
	 * Assets class for the API-API Console.
	 *
	 * @since 1.0.0
	 */
	class Assets {
		/**
		 * Enqueues the necessary stylesheets.
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 */
		public static function enqueue_styles() {
			$base_url = Bridge::get_current_url( 'host' );

			$assets_base_url = $base_url . '/assets';
			$vendor_base_url = $base_url . '/node_modules';

			?>
			<link rel="stylesheet" type="text/css" href="<?php echo $vendor_base_url; ?>/bootstrap/dist/css/bootstrap.css">
			<link rel="stylesheet" type="text/css" href="<?php echo $assets_base_url; ?>/app.css">
			<?php
		}

		/**
		 * Enqueues the necessary scripts.
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 */
		public static function enqueue_scripts() {
			$base_url = Bridge::get_current_url( 'host' );

			$assets_base_url = $base_url . '/assets';
			$vendor_base_url = $base_url . '/node_modules';

			?>
			<script type="text/javascript" src="<?php echo $vendor_base_url; ?>/vue/dist/vue.js"></script>
			<script type="text/javascript" src="<?php echo $vendor_base_url; ?>/vue-resource/dist/vue-resource.js"></script>
			<script type="text/javascript" src="<?php echo $assets_base_url; ?>/components.js"></script>
			<script type="text/javascript" src="<?php echo $assets_base_url; ?>/app.js"></script>
			<?php
		}
	}

}
