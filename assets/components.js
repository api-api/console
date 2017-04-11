( function( Vue ) {

	var templates = [
		{
			name: 'app',
			props: {
				ajaxUrl: String,
				structureNames: Array,
				authenticatorNames: Array,
				transporterNames: Array,
				storageNames: Array,
				config: Object
			}
		},
		{
			name: 'app-header',
			props: {
				headline: String
			}
		},
		{
			name: 'app-main',
			props: {
				ajaxUrl: {
					type: String,
					required: true
				},
				structureNames: {
					type: Array,
					required: true
				},
				navigationDefaultHeadline: {
					type: String,
					default: 'Available Structures'
				},
				inspectorDefaultHeadline: {
					type: String,
					default: 'Current Route'
				},
				inspectorDefaultContent: {
					type: String,
					default: '/* This area will show the latest API response. */'
				},
				inspectorLoadingContent: {
					type: String,
					default: '/* Loading... */'
				},
				navigationButtonInfoPanelText: {
					type: String,
					default: 'See Info'
				},
				navigationButtonBackText: {
					type: String,
					default: 'Back'
				},
				inspectorButtonParamsFormText: {
					type: String,
					default: 'Modify Parameters'
				},
				inspectorButtonSendRequestText: {
					type: String,
					default: 'Send Request'
				},
				infoPanelHeadline: {
					type: String,
					default: 'Structure Information'
				},
				paramsFormHeadline: {
					type: String,
					default: 'Modify Parameters'
				},
				errorMessageHeadline: {
					type: String,
					default: 'Error'
				},
				redirectNoticeHeadline: {
					type: String,
					default: 'Pending Redirect'
				},
				closeText: {
					type: String,
					default: 'Close'
				},
				loadingText: {
					type: String,
					default: 'Loading...'
				}
			},
			data: function() {
				return {
					structureView: 'list',
					routeView: 'list',
					infoPanelOpen: false,
					paramsFormOpen: false,
					errorMessageOpen: false,
					redirectNoticeOpen: false,
					params: {},
					currentStructure: null,
					currentRoute: null,
					performingRequest: false,
					lastResponse: undefined,
					lastError: undefined,
					lastRedirect: undefined,
					inspectorContent: this.inspectorDefaultContent
				};
			},
			computed: {
				navigationHeadline: function() {
					if ( 'list' === this.structureView ) {
						return this.navigationDefaultHeadline;
					}

					if ( null === this.currentStructure ) {
						return this.loadingText;
					}

					return this.currentStructure.title;
				},
				navigationContents: function() {
					if ( 'list' === this.structureView ) {
						return this.structureNames;
					}

					var routeIdentifiers = [];
					if ( null !== this.currentStructure ) {
						for ( var i in this.currentStructure.routes ) {
							routeIdentifiers.push( this.currentStructure.routes[ i ].method + ' ' + this.currentStructure.routes[ i ].uri );
						}
					}

					return routeIdentifiers;
				},
				inspectorHeadline: function() {
					if ( 'list' === this.structureView || 'list' === this.routeView ) {
						return this.inspectorDefaultHeadline;
					}

					if ( null === this.currentStructure || null === this.currentRoute ) {
						return this.loadingText;
					}

					return this.currentRoute.method + ' ' + this.currentRoute.uri;
				}
			},
			watch: {
				structureView: function( structureView ) {
					this.params = {};
					this.lastResponse = undefined;
					this.lastError = undefined;
					this.lastRedirect = undefined;

					this.currentStructure = null;
					if ( 'list' !== structureView ) {
						this.getStructure( structureView );
					}
				},
				routeView: function( routeView ) {
					this.params = {};
					this.lastResponse = undefined;
					this.lastError = undefined;
					this.lastRedirect = undefined;

					this.currentRoute = null;
					if ( 'list' !== routeView && 'list' !== this.structureView ) {
						var match = routeView.match( /^(GET|POST|PUT|PATCH|DELETE) / );
						var route = routeView.substring( match[0].length );
						var method = match[1];

						this.getRoute( this.structureView, route, method );
					}
				},
				lastResponse: function( lastResponse ) {
					if ( this.performingRequest ) {
						return;
					}

					if ( ! lastResponse ) {
						this.inspectorContent = this.inspectorDefaultContent;
					} else {
						this.inspectorContent = JSON.stringify( lastResponse, null, 2 );
					}
				},
				performingRequest: function( performingRequest ) {
					if ( performingRequest ) {
						this.inspectorContent = this.inspectorLoadingContent;
					} else if ( ! this.lastResponse ) {
						this.inspectorContent = this.inspectorDefaultContent;
					} else {
						this.inspectorContent = JSON.stringify( this.lastResponse, null, 2 );
					}
				}
			},
			methods: {
				setView: function( name ) {
					if ( 'structures' === name ) {
						this.structureView = 'list';
						this.routeView = 'list';
					} else if ( 'routes' === name ) {
						this.routeView = 'list';
					} else if ( name.match( /^(GET|POST|PUT|PATCH|DELETE) / ) ) {
						this.routeView = name;
					} else {
						this.structureView = name;
					}
				},
				toggleInfoPanel: function() {
					if ( this.infoPanelOpen ) {
						this.infoPanelOpen = false;
					} else {
						this.infoPanelOpen = true;
					}
				},
				toggleParamsForm: function() {
					if ( this.paramsFormOpen ) {
						this.paramsFormOpen = false;
					} else {
						this.paramsFormOpen = true;
					}
				},
				toggleErrorMessage: function() {
					if ( this.errorMessageOpen ) {
						this.errorMessageOpen = false;
					} else {
						this.errorMessageOpen = true;
					}
				},
				toggleRedirectNotice: function() {
					if ( this.redirectNoticeOpen ) {
						this.redirectNoticeOpen = false;
					} else {
						this.redirectNoticeOpen = true;
					}
				},
				performRequest: function() {
					var vm = this;

					function handleNoResponseBody( response ) {
						vm.lastError = {
							statusCode: response.status,
							statusText: response.statusText,
							message: 'No response body was received.'
						};

						vm.toggleErrorMessage();

						vm.performingRequest = false;
					}

					vm.performingRequest = true;

					this.$http.get( this.ajaxUrl, {
						params: {
							action: 'perform_request',
							structure_name: this.currentStructure.name,
							route_name: this.currentRoute.uri.replace( /\\/g, '\\\\' ),
							method_name: this.currentRoute.method,
							params: this.params
						}
					}).then( function( response ) {
						if ( null === response.body ) {
							handleNoResponseBody( response );
							return;
						}

						if ( response.body.redirect ) {
							vm.lastRedirect = response.body.redirect;

							vm.toggleRedirectNotice();

							vm.performingRequest = false;
							return;
						}

						vm.lastResponse = response.body;

						vm.performingRequest = false;
					}, function( response ) {
						if ( null === response.body ) {
							handleNoResponseBody( response );
							return;
						}

						vm.lastError = {
							statusCode: response.status,
							statusText: response.statusText,
							message: response.body.message
						};

						vm.toggleErrorMessage();

						vm.performingRequest = false;
					});
				},
				getStructureNames: function() {
					var vm = this;
					this.$http.get( this.ajaxUrl, {
						params: {
							action: 'get_structure_names'
						}
					}).then( function( response ) {
						vm.structureNames = response.body;
					}, function( response ) {
						console.error( response.body.message );
					});
				},
				getStructure: function( structure ) {
					var vm = this;
					this.$http.get( this.ajaxUrl, {
						params: {
							action: 'get_structure',
							structure_name: structure
						}
					}).then( function( response ) {
						vm.currentStructure = response.body;
					}, function( response ) {
						console.error( response.body.message );
					});
				},
				getRoute: function( structure, route, method ) {
					var vm = this;
					this.$http.get( this.ajaxUrl, {
						params: {
							action: 'get_route',
							structure_name: structure,
							route_name: route.replace( /\\/g, '\\\\' ),
							method_name: method
						}
					}).then( function( response ) {
						vm.currentRoute = response.body;
					}, function( response ) {
						console.error( response.body.message );
					});
				}
			}
		},
		{
			name: 'app-footer',
			props: {
				copyright: String
			}
		},
		{
			name: 'input-static',
			props: {
				value: {
					type: String
				}
			}
		},
		{
			name: 'input-float',
			props: {
				value: {
					type: Number,
					default: 0.0
				},
				minimum: {
					type: Number
				},
				maximum: {
					type: Number
				}
			},
			methods: {
				updateValue: function( value ) {
					this.$emit( 'input', Number( value ) );
				}
			}
		},
		{
			name: 'input-integer',
			props: {
				value: {
					type: Number,
					default: 0
				},
				minimum: {
					type: Number
				},
				maximum: {
					type: Number
				}
			},
			methods: {
				updateValue: function( value ) {
					this.$emit( 'input', Number( value ) );
				}
			}
		},
		{
			name: 'input-boolean',
			props: {
				value: {
					type: Boolean,
					default: false
				}
			},
			methods: {
				updateValue: function( value ) {
					this.$emit( 'input', Boolean( value ) );
				}
			}
		},
		{
			name: 'input-array',
			props: {
				value: {
					type: Array,
					default: function() {
						return [];
					}
				}
			},
			methods: {
				updateValue: function( value ) {
					this.$emit( 'input', value.split( ',' ) );
				}
			}
		},
		{
			name: 'input-enum',
			props: {
				value: {
					type: String,
					default: ''
				},
				options: {
					type: Array,
					default: function() {
						return [];
					}
				}
			},
			methods: {
				updateValue: function( value ) {
					this.$emit( 'input', String( value ) );
				}
			}
		},
		{
			name: 'input-string',
			props: {
				value: {
					type: String,
					default: ''
				}
			},
			methods: {
				updateValue: function( value ) {
					this.$emit( 'input', String( value ) );
				}
			}
		}
	];

	var optionalProperties = [
		'props',
		'data',
		'computed',
		'watch',
		'methods'
	];

	for ( var i in templates ) {
		var data = {
			template: '#' + templates[ i ].name + '-template'
		};

		for ( var j in optionalProperties ) {
			if ( 'undefined' !== typeof templates[ i ][ optionalProperties[ j ] ] ) {
				data[ optionalProperties[ j ] ] = templates[ i ][ optionalProperties[ j ] ];
			}
		}

		Vue.component( templates[ i ].name, data );
	}

})( Vue );
