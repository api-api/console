<?php
/**
 * API-API Console Templates class
 *
 * @package APIAPIConsole
 * @since 1.0.0
 */

namespace APIAPI\Console;

if ( ! class_exists( 'APIAPI\Console\Templates' ) ) {

	/**
	 * Templates class for the API-API Console.
	 *
	 * @since 1.0.0
	 */
	class Templates {
		/**
		 * Prints all templates to be used with Vue.
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 */
		public static function print_templates() {
			$template_names = array(
				'app',
				'app-header',
				'app-main',
				'app-footer',
				'app-navigation',
				'app-inspector',
			);

			foreach ( $template_names as $template_name ) {
				$method_name = 'print_template_' . str_replace( '-', '_', $template_name );

				if ( ! is_callable( array( __CLASS__, $method_name ) ) ) {
					continue;
				}

				echo '<script type="text/x-template" id="' . $template_name . '-template">';
				call_user_func( array( __CLASS__, $method_name ) );
				echo '</script>';
			}
		}

		/**
		 * Prints the 'app' template.
		 *
		 * @since 1.0.0
		 * @access private
		 * @static
		 */
		private static function print_template_app() {
			?>
			<div class="wrap">
				<app-header headline="API-API Console"></app-header>
				<app-main></app-main>
				<app-footer copyright="Made with love by the API-API Team."></app-footer>
			</div>
			<?php
		}

		/**
		 * Prints the 'app-header' template.
		 *
		 * @since 1.0.0
		 * @access private
		 * @static
		 */
		private static function print_template_app_header() {
			?>
			<header class="header container-fluid">
				<h1 class="headline">{{headline}}</h1>
			</header>
			<?php
		}

		/**
		 * Prints the 'app-main' template.
		 *
		 * @since 1.0.0
		 * @access private
		 * @static
		 */
		private static function print_template_app_main() {
			?>
			<main class="main container-fluid">
				<div class="content row">
					<app-navigation class="col-sm-3"></app-navigation>
					<app-inspector class="col-sm-9"></app-inspector>
				</div>
			</main>
			<?php
		}

		/**
		 * Prints the 'app-footer' template.
		 *
		 * @since 1.0.0
		 * @access private
		 * @static
		 */
		private static function print_template_app_footer() {
			?>
			<footer class="footer container-fluid">
				<p class="copyright">&copy; {{copyright}}</p>
			</footer>
			<?php
		}

		/**
		 * Prints the 'app-navigation' template.
		 *
		 * @since 1.0.0
		 * @access private
		 * @static
		 */
		private static function print_template_app_navigation() {
			?>
			<div class="navigation">
				Navigation
			</div>
			<?php
		}

		/**
		 * Prints the 'app-inspector' template.
		 *
		 * @since 1.0.0
		 * @access private
		 * @static
		 */
		private static function print_template_app_inspector() {
			?>
			<div class="inspector">
				Inspector
			</div>
			<?php
		}
	}

}
