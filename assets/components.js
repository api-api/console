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
					params: {},
					currentStructure: null,
					currentRoute: null
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
				},
				inspectorContent: function() {
					if ( 'list' === this.structureView || 'list' === this.routeView ) {
						return this.inspectorDefaultContent;
					}

					if ( null === this.currentStructure || null === this.currentRoute || ! this.currentRoute.lastResponse ) {
						return this.inspectorDefaultContent;
					}
				}
			},
			watch: {
				structureView: function( structureView ) {
					this.params = {};

					if ( 'list' === structureView ) {
						this.currentStructure = null;
					} else {
						this.getStructure( structureView );
					}
				},
				routeView: function( routeView ) {
					this.params = {};

					if ( 'list' === routeView ) {
						this.currentRoute = null;
					} else if ( 'list' !== this.structureView ) {
						var match = routeView.match( /^(GET|POST|PUT|PATCH|DELETE) / );
						var route = routeView.substring( match[0].length );
						var method = match[1];

						this.getRoute( this.structureView, route, method );
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
				sendAPIRequest: function() {

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
				},
				logParams: function() {
					console.log( this.params );
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
