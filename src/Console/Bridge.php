<?php
/**
 * API-API Console Bridge class
 *
 * @package APIAPIConsole
 * @since 1.0.0
 */

namespace APIAPI\Console;

use APIAPI\Core\Exception;

if ( ! class_exists( 'APIAPI\Console\Bridge' ) ) {

	/**
	 * Bridge class for the API-API Console.
	 *
	 * @since 1.0.0
	 */
	class Bridge {
		/**
		 * The unique API-API slug.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string
		 */
		private $apiapi_slug = '';

		/**
		 * Path to the configuration file.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string
		 */
		private $config_file = '';

		/**
		 * The API-API instance.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var APIAPI\Core\APIAPI
		 */
		private $apiapi;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $apiapi_slug Unique API-API slug.
		 * @param string $config_file Optional. Path to a configuration file.
		 */
		public function __construct( $apiapi_slug, $config_file = '' ) {
			$this->apiapi_slug = $apiapi_slug;
			$this->config_file = $config_file;
		}

		/**
		 * Returns all registered structures.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return array Array of structures.
		 */
		public function get_structures() {
			return apiapi_manager()->structures()->get_all();
		}

		/**
		 * Returns all registered authenticators.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return array Array of authenticators.
		 */
		public function get_authenticators() {
			return apiapi_manager()->authenticators()->get_all();
		}

		/**
		 * Returns all registered transporters.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return array Array of transporters.
		 */
		public function get_transporters() {
			return apiapi_manager()->transporters()->get_all();
		}

		/**
		 * Returns a specific registered structure.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $name Name of the structure.
		 * @return APIAPI\Core\Structures\Structure|null The structure object, or null.
		 */
		public function get_structure( $name ) {
			return apiapi_manager()->structures()->get( $name );
		}

		/**
		 * Returns a specific registered authenticator.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $name Name of the authenticator.
		 * @return APIAPI\Core\Authenticators\Authenticator|null The authenticator object, or null.
		 */
		public function get_authenticator( $name ) {
			return apiapi_manager()->authenticators()->get( $name );
		}

		/**
		 * Returns a specific registered transporter.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $name Name of the transporter.
		 * @return APIAPI\Core\Transporters\Transporter|null The transporter object, or null.
		 */
		public function get_transporter( $name ) {
			return apiapi_manager()->transporters()->get( $name );
		}

		/**
		 * Returns the base configuration array.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return array Configuration array.
		 */
		public function get_config() {
			$base_config = array(
				'transporter' => apiapi_manager()->transporters()->get_default_name(),
			);

			if ( ! empty( $this->config_file ) && file_exists( $this->config_file ) ) {
				$json_config = file_get_contents( $this->config_file );

				$base_config = array_merge( $base_config, json_decode( $json_config, true ) );
			}

			return $base_config;
		}

		/**
		 * Returns all data to be passed to the console scripts.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return array JavaScript data.
		 */
		public function get_js_data() {
			return array(
				'ajaxUrl'        => AJAX::get_base_url(),
				'structures'     => array_keys( $this->get_structures() ),
				'authenticators' => array_keys( $this->get_authenticators() ),
				'transporters'   => array_keys( $this->get_transporters() ),
				'config'         => $this->get_config(),
			);
		}

		/**
		 * Instantiates the API-API.
		 *
		 * It must not be instantiated already when calling this method.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param APIAPI\Core\Config|array $config Configuration object or array.
		 */
		private function instantiate_apiapi( $config ) {
			if ( apiapi( $this->apiapi_slug ) ) {
				throw new Exception( 'API-API already instantiated.', 0, array( 'status_code' => 500 ) );
			}

			$this->apiapi = apiapi( $this->apiapi_slug, $config );
		}

		/**
		 * Returns the current URL.
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 *
		 * @param string $mode Optional. Either 'full', 'path' or 'host' to include parts up to
		 *                     that part. Default 'full'.
		 * @return string Current URL.
		 */
		public static function get_current_url( $mode = 'full' ) {
			$parts = array(
				'protocol' => isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
				'host'     => $_SERVER['HTTP_HOST'],
				'path'     => $_SERVER['REQUEST_URI'],
			);

			if ( false !== strpos( $parts['path'], '?' ) ) {
				list( $parts['path'], $parts['query'] ) = explode( '?', $parts['path'], 2 );
			}

			$url = '';

			switch ( $mode ) {
				case 'full':
					if ( ! empty( $parts['query'] ) ) {
						$url = '?' . $parts['query'] . $url;
					}
				case 'path':
					if ( ! empty( $parts['path'] ) ) {
						$url = $parts['path'] . $url;
					}
				case 'host':
					$url = $parts['protocol'] . '://' . $parts['host'] . $url;
			}

			return $url;
		}
	}

}
