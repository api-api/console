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
				navigationStructureHeadline: {
					type: String,
					default: 'Available Structures'
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
					currentStructure: null,
					currentRoute: null
				};
			},
			computed: {
				navigationHeadline: function() {
					if ( 'list' === this.structureView ) {
						return this.navigationStructureHeadline;
					}

					if ( null === this.currentStructure ) {
						return this.loadingText;
					}

					return this.currentStructure.name;
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
				}
			},
			watch: {
				structureView: function( structureView ) {
					if ( 'list' === structureView ) {
						this.currentStructure = null;
					} else {
						this.getStructure( structureView );
					}
				},
				routeView: function( routeView ) {
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
					} else if ( 'routes' === name ) {
						this.routeView = 'list';
					} else if ( name.match( /^(GET|POST|PUT|PATCH|DELETE) / ) ) {
						this.routeView = name;
					} else {
						this.structureView = name;
					}
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
