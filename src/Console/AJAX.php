<?php
/**
 * API-API Console AJAX class
 *
 * @package APIAPIConsole
 * @since 1.0.0
 */

namespace APIAPI\Console;

use APIAPI\Core\Transporters\Transporter;
use APIAPI\Core\Exception as APIAPI_Exception;
use Exception;

if ( ! class_exists( 'APIAPI\Console\AJAX' ) ) {

	/**
	 * AJAX class for the API-API Console.
	 *
	 * @since 1.0.0
	 */
	class AJAX {
		/**
		 * The AJAX request data.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array
		 */
		private $request = array();

		/**
		 * Available actions.
		 *
		 * @since 1.0.0
		 * @access private
		 * @static
		 * @var array
		 */
		private static $actions = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $request Request data.
		 */
		public function __construct( $request ) {
			$this->request = $this->stripslashes_deep( $request );
		}

		/**
		 * Handles the current request.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function handle_request() {
			if ( empty( $this->request['action'] ) ) {
				$this->serve_error_response( new Exception( 'Missing action.' ) );
			}

			if ( ! isset( self::$actions[ $this->request['action'] ] ) ) {
				$this->serve_error_response( new Exception( 'Invalid action.' ) );
			}

			$callback = self::$actions[ $this->request['action'] ];

			try {
				$response = call_user_func( $callback, $this->request );
			} catch( APIAPI_Exception $e ) {
				$this->serve_error_response( $e );
			} catch ( Exception $e ) {
				$this->serve_error_response( $e );
			}

			$this->serve_success_response( $response );
		}

		/**
		 * Serves a success response.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param array $response Response data.
		 */
		private function serve_success_response( $response ) {
			$this->serve_response( $response, 200 );
		}

		/**
		 * Serves an error response.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param Exception $exception Exception describing the error.
		 */
		private function serve_error_response( $exception ) {
			$response = array(
				'error'   => 'true',
				'message' => $exception->getMessage(),
			);

			$status_code = 400;

			if ( is_a( $exception, 'APIAPI\Core\Exception' ) ) {
				$data = $exception->getData();
				if ( ! empty( $data ) ) {
					$response['data'] = $data;

					if ( isset( $response['data']['status_code'] ) ) {
						$status_code = (int) $response['data']['status_code'];
					}
				}
			}

			$this->serve_response( $response, $status_code );
		}

		/**
		 * Serves a response.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param array $response    Response data.
		 * @param int   $status_code Response status code.
		 */
		private function serve_response( $response, $status_code ) {
			$protocol = in_array( $_SERVER['SERVER_PROTOCOL'], array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ), true ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
			$status_message = Transporter::get_status_message( $status_code );

			header( $protocol . ' ' . $status_code . ' ' . $status_message, true, $status_code );
			header( 'Content-Type: application/json; charset=utf-8' );

			echo json_encode( $response );
			exit;
		}

		/**
		 * Removes slashes from an array of values.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param mixed $data Data to remove slashes from.
		 * @return mixed Data with removed slashes.
		 */
		private function stripslashes_deep( $data ) {
			if ( is_string( $data ) ) {
				return stripslashes( $data );
			}

			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					$data[ $key ] = $this->stripslashes_deep( $value );
				}
			}

			return $data;
		}

		/**
		 * Registers an AJAX action.
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 *
		 * @param string   $action   The action identifier.
		 * @param callable $callback The action callback.
		 */
		public static function register_action( $action, $callback ) {
			self::$actions[ $action ] = $callback;
		}

		/**
		 * Returns the URL to send AJAX requests to.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return string AJAX URL.
		 */
		public static function get_base_url() {
			return Bridge::get_current_url( 'host' ) . '/?ajax=1';
		}

		/**
		 * Listens for whether the current request is an AJAX request.
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 */
		public static function listen() {
			if ( isset( $_REQUEST['ajax'] ) && $_REQUEST['ajax'] ) {
				$request = $_REQUEST;

				unset( $request['ajax'] );

				$ajax = new self( $request );
				$ajax->handle_request();
				exit;
			}
		}
	}

}
