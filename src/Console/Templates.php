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
				'input-static',
				'input-float',
				'input-integer',
				'input-boolean',
				'input-array',
				'input-enum',
				'input-string',
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
				<app-header :baseUrl="baseUrl" headline="API-API Console"></app-header>
				<app-main :ajaxUrl="ajaxUrl" :structureNames="structureNames" navigationDefaultHeadline="Available Structures" inspectorDefaultHeadline="Current Route" inspectorDefaultContent="/* This area will show the latest API response. */"></app-main>
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
				<h1 class="headline">
					<a :href="baseUrl">{{headline}}</a>
				</h1>
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
				<div class="navigation-wrap">
					<div class="navigation">
						<div class="navigation-header">
							<strong>{{navigationHeadline}}</strong>
							<span class="navigation-header-buttons" v-if="'list' !== structureView">
								<button class="btn btn-default btn-xs" v-on:click.stop.prevent="toggleInfoPanel()">{{navigationButtonInfoPanelText}}</button>
								<button class="btn btn-default btn-xs" v-on:click.stop.prevent="setView( 'structures' )">{{navigationButtonBackText}}</button>
							</span>
						</div>
						<div class="navigation-content-wrap">
							<div class="navigation-content">
								<ul>
									<li v-for="content in navigationContents">
										<a class="navigation-content-link" :href="'#' + content" v-on:click.stop.prevent="setView( content )">{{content}}</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="inspector-wrap">
				<div class="inspector">
						<div class="inspector-route">
							<strong>{{inspectorHeadline}}</strong>
							<span class="inspector-header-buttons" v-if="'list' !== structureView && 'list' !== routeView">
								<button class="btn btn-default btn-xs" v-on:click.stop.prevent="toggleParamsForm()">{{inspectorButtonParamsFormText}}</button>
								<button class="btn btn-primary btn-xs" v-on:click.stop.prevent="performRequest()">{{inspectorButtonSendRequestText}}</button>
							</span>
						</div>
						<div class="inspector-content-wrap">
							<textarea v-model="inspectorContent" class="inspector-content" readonly></textarea>
						</div>
					</div>
				</div>
				<div class="modal fade" :class="{ in: infoPanelOpen }" role="dialog" tabindex="-1">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" :aria-label="closeText" v-on:click.stop.prevent="toggleInfoPanel()">
										<span aria-hidden="true">&times;</span>
									</button>
									<h4 class="modal-title">{{infoPanelHeadline}}</h4>
								</div>
								<div class="modal-body">
									<form v-if="currentStructure" class="form-horizontal">
										<div class="form-group">
											<label class="col-sm-3 control-label">Name</label>
											<div class="col-sm-9">
												<input-static :value="currentStructure.name"></input-static>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-3 control-label">Title</label>
											<div class="col-sm-9">
												<input-static :value="currentStructure.title"></input-static>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-3 control-label">Description</label>
											<div class="col-sm-9">
												<input-static :value="currentStructure.description"></input-static>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-3 control-label">Base URI</label>
											<div class="col-sm-9">
												<input-static :value="currentStructure.baseUri"></input-static>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-3 control-label">Authenticator</label>
											<div class="col-sm-9">
												<input-static :value="currentStructure.authenticator"></input-static>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal fade" :class="{ in: paramsFormOpen }" role="dialog" tabindex="-1">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" :aria-label="closeText" v-on:click.stop.prevent="toggleParamsForm()">
										<span aria-hidden="true">&times;</span>
									</button>
									<h4 class="modal-title">{{paramsFormHeadline}}</h4>
								</div>
								<div class="modal-body">
									<form v-if="currentStructure && currentRoute" class="form-horizontal">
										<p v-if="currentRoute.description">{{currentRoute.description}}</p>
										<template v-if="currentStructure.baseUriParams.length">
											<h5>Base URI parameters</h5>
											<div v-for="param in currentStructure.baseUriParams" class="form-group">
												<label class="col-sm-3 control-label">
													{{param.name}}
													<span v-if="param.required" class="required">*</span>
												</label>
												<div class="col-sm-9">
													<input-static v-if="param.internal" :value="'This is an internal parameter.'"></input-static>
													<input-float v-else-if="param.type === 'number' || param.type === 'float'" v-model="params[param.name]" :minimum="param.minimum" :maximum="param.maximum"></input-float>
													<input-integer v-else-if="param.type === 'integer'" v-model="params[param.name]" :minimum="param.minimum" :maximum="param.maximum"></input-integer>
													<input-boolean v-else-if="param.type === 'boolean'" v-model="params[param.name]"></input-boolean>
													<input-array v-else-if="param.type === 'array'" v-model="params[param.name]"></input-array>
													<input-enum v-else-if="param.type === 'string' && param.enum && param.enum.length" v-model="params[param.name]" :options="param.enum"></input-enum>
													<input-string v-else v-model="params[param.name]"></input-string>
													<span v-if="param.description && param.description.length" class="help-block">{{param.description}}</span>
												</div>
											</div>
										</template>
										<template v-if="currentStructure.globalParams.length">
											<h5>Global parameters</h5>
											<div v-for="param in currentStructure.globalParams" class="form-group">
												<label class="col-sm-3 control-label">
													{{param.name}}
													<span v-if="param.required" class="required">*</span>
												</label>
												<div class="col-sm-9">
													<input-static v-if="param.internal" :value="'This is an internal parameter.'"></input-static>
													<input-float v-else-if="param.type === 'number' || param.type === 'float'" v-model="params[param.name]" :minimum="param.minimum" :maximum="param.maximum"></input-float>
													<input-integer v-else-if="param.type === 'integer'" v-model="params[param.name]" :minimum="param.minimum" :maximum="param.maximum"></input-integer>
													<input-boolean v-else-if="param.type === 'boolean'" v-model="params[param.name]"></input-boolean>
													<input-array v-else-if="param.type === 'array'" v-model="params[param.name]"></input-array>
													<input-enum v-else-if="param.type === 'string' && param.enum && param.enum.length" v-model="params[param.name]" :options="param.enum"></input-enum>
													<input-string v-else v-model="params[param.name]"></input-string>
													<span v-if="param.description && param.description.length" class="help-block">{{param.description}}</span>
												</div>
											</div>
										</template>
										<template v-if="currentRoute.primaryParams.length">
											<h5>Primary parameters</h5>
											<div v-for="param in currentRoute.primaryParams" class="form-group">
												<label class="col-sm-3 control-label">
													{{param.name}}
													<span v-if="param.required" class="required">*</span>
												</label>
												<div class="col-sm-9">
													<input-static v-if="param.internal" :value="'This is an internal parameter.'"></input-static>
													<input-float v-else-if="param.type === 'number' || param.type === 'float'" v-model="params[param.name]" :minimum="param.minimum" :maximum="param.maximum"></input-float>
													<input-integer v-else-if="param.type === 'integer'" v-model="params[param.name]" :minimum="param.minimum" :maximum="param.maximum"></input-integer>
													<input-boolean v-else-if="param.type === 'boolean'" v-model="params[param.name]"></input-boolean>
													<input-array v-else-if="param.type === 'array'" v-model="params[param.name]"></input-array>
													<input-enum v-else-if="param.type === 'string' && param.enum && param.enum.length" v-model="params[param.name]" :options="param.enum"></input-enum>
													<input-string v-else v-model="params[param.name]"></input-string>
													<span v-if="param.description && param.description.length" class="help-block">{{param.description}}</span>
												</div>
											</div>
										</template>
										<template v-if="currentRoute.methodParams.length">
											<h5>Method parameters</h5>
											<div v-for="param in currentRoute.methodParams" class="form-group">
												<label class="col-sm-3 control-label">
													{{param.name}}
													<span v-if="param.required" class="required">*</span>
												</label>
												<div class="col-sm-9">
													<input-static v-if="param.internal" :value="'This is an internal parameter.'"></input-static>
													<input-float v-else-if="param.type === 'number' || param.type === 'float'" v-model="params[param.name]" :minimum="param.minimum" :maximum="param.maximum"></input-float>
													<input-integer v-else-if="param.type === 'integer'" v-model="params[param.name]" :minimum="param.minimum" :maximum="param.maximum"></input-integer>
													<input-boolean v-else-if="param.type === 'boolean'" v-model="params[param.name]"></input-boolean>
													<input-array v-else-if="param.type === 'array'" v-model="params[param.name]"></input-array>
													<input-enum v-else-if="param.type === 'string' && param.enum && param.enum.length" v-model="params[param.name]" :options="param.enum"></input-enum>
													<input-string v-else v-model="params[param.name]"></input-string>
													<span v-if="param.description && param.description.length" class="help-block">{{param.description}}</span>
												</div>
											</div>
										</template>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal fade" :class="{ in: errorMessageOpen }" role="dialog" tabindex="-1">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" :aria-label="closeText" v-on:click.stop.prevent="toggleErrorMessage()">
										<span aria-hidden="true">&times;</span>
									</button>
									<h4 class="modal-title">{{errorMessageHeadline}}</h4>
								</div>
								<div class="modal-body">
									<div class="row">
										<div class="col-sm-3">
											<strong>Status Code</strong>
										</div>
										<div class="col-sm-9">
											<span v-if="lastError && lastError.statusCode">
												{{lastError.statusCode}}
											</span>
											<span v-if="lastError && lastError.statusText">
												{{lastError.statusText}}
											</span>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-3">
											<strong>Message</strong>
										</div>
										<div class="col-sm-9">
											<span v-if="lastError && lastError.message">
												{{lastError.message}}
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal fade" :class="{ in: redirectNoticeOpen }" role="dialog" tabindex="-1">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" :aria-label="closeText" v-on:click.stop.prevent="toggleRedirectNotice()">
										<span aria-hidden="true">&times;</span>
									</button>
									<h4 class="modal-title">{{redirectNoticeHeadline}}</h4>
								</div>
								<div class="modal-body">
									<p v-if="lastRedirect">
										In order to authenticate, you will need to be redirected to {{lastRedirect}}.
										<br>
										After having authenticated, you will be redirected back to the API-API Console.
									</p>
									<p v-if="lastRedirect">
										<a :href="lastRedirect">Redirect now!</a>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div v-if="infoPanelOpen || paramsFormOpen || errorMessageOpen || redirectNoticeOpen" class="modal-backdrop fade" :class="{ in: infoPanelOpen || paramsFormOpen || errorMessageOpen || redirectNoticeOpen }"></div>
			</main>
			<?php
		}

		private static function print_template_input_static() {
			?>
			<p class="form-control-static">{{value}}</p>
			<?php
		}

		private static function print_template_input_float() {
			?>
			<input type="number" ref="input" :value="value" v-on:input="updateValue( $event.target.value )" class="form-control" step="0.01" :min="minimum" :max="maximum">
			<?php
		}

		private static function print_template_input_integer() {
			?>
			<input type="number" ref="input" :value="value" v-on:input="updateValue( $event.target.value )" class="form-control" step="1" :min="minimum" :max="maximum">
			<?php
		}

		private static function print_template_input_array() {
			?>
			<input type="text" ref="input" :value="value.join(',')" v-on:input="updateValue( $event.target.value )" class="form-control">
			<?php
		}

		private static function print_template_input_boolean() {
			?>
			<input type="checkbox" ref="input" :value="value" v-on:input="updateValue( $event.target.value )" class="form-control">
			<?php
		}

		private static function print_template_input_enum() {
			?>
			<select ref="input" :value="value" v-on:input="updateValue( $event.target.value )" class="form-control">
				<option v-for="option in options" :value="option">{{option}}</option>
			</select>
			<?php
		}

		private static function print_template_input_string() {
			?>
			<input type="text" ref="input" :value="value" v-on:input="updateValue( $event.target.value )" class="form-control">
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
	}

}
