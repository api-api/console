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

			// For AJAX requests, the API-API instance is instantiated in the individual callback.
			if ( ! isset( $_REQUEST['ajax'] ) ) {
				$this->instantiate_apiapi( $this->get_config() );
			}

			AJAX::register_action( 'perform_request', array( $this, 'ajax_perform_request' ) );
			AJAX::register_action( 'get_structure_names', array( $this, 'ajax_get_structure_names' ) );
			AJAX::register_action( 'get_structure', array( $this, 'ajax_get_structure' ) );
			AJAX::register_action( 'get_route', array( $this, 'ajax_get_route' ) );
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
		 * Returns all registered storages.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return array Array of storages.
		 */
		public function get_storages() {
			return apiapi_manager()->storages()->get_all();
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
		 * Returns a specific registered storage.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $name Name of the storage.
		 * @return APIAPI\Core\Transporters\Transporter|null The storage object, or null.
		 */
		public function get_storage( $name ) {
			return apiapi_manager()->storages()->get( $name );
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
				'transporter'            => apiapi_manager()->transporters()->get_default_name(),
				'config_updater'         => true,
				'config_updater_storage' => 'cookie',
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
				'ajaxUrl'            => AJAX::get_base_url(),
				'structureNames'     => array_keys( $this->get_structures() ),
				'authenticatorNames' => array_keys( $this->get_authenticators() ),
				'transporterNames'   => array_keys( $this->get_transporters() ),
				'storageNames'       => array_keys( $this->get_storages() ),
				'config'             => $this->get_config(),
			);
		}

		/**
		 * Sends a redirect URL to terminate an AJAX request.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $redirect_url URL to redirect to.
		 */
		public function redirect_via_ajax( $redirect_url ) {
			AJAX::serve_success_response( array(
				'redirect_to' => $redirect_url,
			) );
		}

		/**
		 * Performs a request to a specific API structure, route and method.
		 *
		 * Used as an AJAX callback.
		 *
		 * In case the request needs to be redirected, the AJAX request terminates early by calling
		 * the redirect_via_ajax() method.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $request Request data.
		 * @return array Array of response parameters.
		 */
		public function ajax_perform_request( $request ) {
			if ( ! isset( $request['structure_name'] ) ) {
				throw new Exception( 'No structure name given.', 0, array( 'status' => 400 ) );
			}

			if ( ! isset( $request['route_name'] ) ) {
				throw new Exception( 'No route name given.', 0, array( 'status' => 400 ) );
			}

			if ( ! isset( $request['method_name'] ) ) {
				throw new Exception( 'No method name given.', 0, array( 'status' => 400 ) );
			}

			$structure = $this->get_structure( $request['structure_name'] );
			if ( ! $structure ) {
				throw new Exception( sprintf( 'The structure %s does not exist.', $request['structure_name'] ), 0, array( 'status' => 404 ) );
			}

			try {
				$route = $structure->get_route_object( $request['route_name'] );
			} catch ( Exception $e ) {
				throw new Exception( sprintf( 'The route %1$s in structure %2$s does not exist.', $request['route_name'], $request['structure_name'] ), 0, array( 'status' => 404 ) );
			}

			if ( ! in_array( $request['method_name'], $route->get_supported_methods(), true ) ) {
				throw new Exception( sprintf( 'The route %1$s in structure %2$s does not support the %3$s method.', $request['route_name'], $request['structure_name'], $request['method_name'] ), 0, array( 'status' => 404 ) );
			}

			$config = $this->get_config();
			if ( ! isset( $config[ $request['structure_name'] ] ) ) {
				$config[ $request['structure_name'] ] = array();
			}
			if ( ! isset( $config[ $request['structure_name'] ]['authentication_data'] ) ) {
				$config[ $request['structure_name'] ]['authentication_data'] = array();
			}
			$config[ $request['structure_name'] ]['authentication_data']['authorize_redirect_callback'] = array( $this, 'redirect_via_ajax' );

			$this->instantiate_apiapi( $config );

			$request_obj = $this->apiapi->get_request_object( $request['structure_name'], $request['route_name'], $request['method_name'] );

			if ( isset( $request['params'] ) ) {
				foreach ( $request['params'] as $param => $value ) {
					$request_obj->set_param( $param, $value );
				}
			}

			$response_obj = $this->apiapi->send_request( $request_obj );

			return $response_obj->get_params();
		}

		/**
		 * Returns all available structure names.
		 *
		 * Used as an AJAX callback.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $request Request data.
		 * @return array Array of structure names.
		 */
		public function ajax_get_structure_names( $request ) {
			return array_keys( $this->get_structures() );
		}

		/**
		 * Returns a specific structure object.
		 *
		 * Used as an AJAX callback.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $request Request data.
		 * @return array Structure data.
		 */
		public function ajax_get_structure( $request ) {
			if ( ! isset( $request['structure_name'] ) ) {
				throw new Exception( 'No structure name given.', 0, array( 'status' => 400 ) );
			}

			$structure = $this->get_structure( $request['structure_name'] );
			if ( ! $structure ) {
				throw new Exception( sprintf( 'The structure %s does not exist.', $request['structure_name'] ), 0, array( 'status' => 404 ) );
			}

			$config_data = $this->get_config_data_for_key( $structure->get_config_key(), array(
				'mode'                => '',
				'authentication_data' => $structure->get_authentication_data_defaults(),
			) );

			$base_uri_params = $structure->get_base_uri_params( $config_data['mode'] );
			$global_params   = $structure->get_global_params();

			$structure_data = array(
				'name'               => $structure->get_name(),
				'title'              => $structure->get_title(),
				'description'        => $structure->get_description(),
				'baseUri'            => $structure->get_base_uri( $config_data['mode'] ),
				'baseUriParams'      => $this->make_assoc_params_indexed( $base_uri_params ),
				'globalParams'       => $this->make_assoc_params_indexed( $global_params ),
				'routes'             => array(),
				'authenticator'      => $structure->get_authenticator(),
				'authenticationData' => $config_data['authentication_data'],
			);

			foreach ( $structure->get_route_objects() as $route_name => $route ) {
				foreach ( $route->get_supported_methods() as $method ) {
					$structure_data['routes'][] = $this->get_route_data( $route, $method );
				}
			}

			return $structure_data;
		}

		/**
		 * Returns a specific route object.
		 *
		 * Used as an AJAX callback.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $request Request data.
		 * @return array Route data.
		 */
		public function ajax_get_route( $request ) {
			if ( ! isset( $request['structure_name'] ) ) {
				throw new Exception( 'No structure name given.', 0, array( 'status' => 400 ) );
			}

			if ( ! isset( $request['route_name'] ) ) {
				throw new Exception( 'No route name given.', 0, array( 'status' => 400 ) );
			}

			if ( ! isset( $request['method_name'] ) ) {
				throw new Exception( 'No method name given.', 0, array( 'status' => 400 ) );
			}

			$structure = $this->get_structure( $request['structure_name'] );
			if ( ! $structure ) {
				throw new Exception( sprintf( 'The structure %s does not exist.', $request['structure_name'] ), 0, array( 'status' => 404 ) );
			}

			try {
				$route = $structure->get_route_object( $request['route_name'] );
			} catch ( Exception $e ) {
				throw new Exception( sprintf( 'The route %1$s in structure %2$s does not exist.', $request['route_name'], $request['structure_name'] ), 0, array( 'status' => 404 ) );
			}

			if ( ! in_array( $request['method_name'], $route->get_supported_methods(), true ) ) {
				throw new Exception( sprintf( 'The route %1$s in structure %2$s does not support the %3$s method.', $request['route_name'], $request['structure_name'], $request['method_name'] ), 0, array( 'status' => 404 ) );
			}

			return $this->get_route_data( $route, $request['method_name'] );
		}

		/**
		 * Returns route data for a given route and method to be used in JS.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param APIAPI\Core\Structures\Route $route  Route object.
		 * @param string                       $method Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @return array Data for the route and method.
		 */
		private function get_route_data( $route, $method ) {
			$primary_params = $route->get_primary_params();
			$method_params  = $route->get_method_params( $method, false );

			return array(
				'uri'                  => $route->get_uri(),
				'method'               => $method,
				'description'          => $route->get_method_description( $method ),
				'primaryParams'        => $this->make_assoc_params_indexed( $primary_params ),
				'methodParams'         => $this->make_assoc_params_indexed( $method_params ),
				'supportsCustomParams' => $route->method_supports_custom_params( $method ),
				'needsAuthentication'  => $route->method_needs_authentication( $method ),
			);
		}

		/**
		 * Transforms an associative array of parameters into an indexed array of parameters.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param array $params_assoc Associative array of parameter information.
		 * @return array Indexed array of parameter information.
		 */
		private function make_assoc_params_indexed( $params_assoc ) {
			$params = array();

			foreach ( $params_assoc as $param_name => $param_info ) {
				$params[] = array_merge( array( 'name' => $param_name ), $param_info );
			}

			return $params;
		}

		/**
		 * Returns configuration data for a specific key.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param string $config_key Configuration key.
		 * @param array  $defaults   Optional. Associative array of defaults to use. Default empty array.
		 * @return array Configuration data for the key.
		 */
		private function get_config_data_for_key( $config_key, $defaults = array() ) {
			$config = $this->get_config();

			if ( isset( $config[ $config_key ] ) ) {
				return array_merge_recursive( $defaults, $config[ $config_key ] );
			}

			return $defaults;
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
						if ( '.php' !== substr( $parts['path'], -4 ) && '/' !== substr( $parts['path'], -1 ) ) {
							$parts['path'] .= '/';
						}
						$url = $parts['path'] . $url;
					} else {
						$url = '/' . $url;
					}
				case 'host':
					$url = $parts['protocol'] . '://' . $parts['host'] . $url;
			}

			return $url;
		}
	}

}
