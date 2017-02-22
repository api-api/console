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
				<app-main :ajaxUrl="ajaxUrl" :structureNames="structureNames" navigationView="structures" navigationStructureHeadline="Available Structures"></app-main>
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
			<main class="main">
				<app-navigation :structureNames="structureNames" :currentStructure="currentStructure" :view="navigationView" :structureHeadline="navigationStructureHeadline" v-on:getStructureNames="getStructureNames" v-on:getStructure="getStructure"></app-navigation>
				<app-inspector></app-inspector>
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
			<div class="navigation-wrap">
				<div class="navigation">
					<div class="navigation-header">
						<strong>{{headline}}</strong>
						<button v-if="'structures' !== view" class="navigation-header-back-button btn btn-default btn-xs" v-on:click.stop.prevent="backLinkClicked()">Back</button>
					</div>
					<div class="navigation-content-wrap">
						<div class="navigation-content">
							<ul>
								<li v-for="content in contents">
									<a class="navigation-content-link" :href="'#' + content" v-on:click.stop.prevent="linkClicked(content)">{{content}}</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
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
			<div class="inspector-wrap">
				<div class="inspector">
					<div class="inspector-route">
						WordPress: POST /wp/v2/posts
					</div>
					<div class="inspector-content-wrap">
						<textarea class="inspector-content">/* Here you will find some JSON. */</textarea>
					</div>
				</div>
			</div>
			<?php
		}
	}

}
