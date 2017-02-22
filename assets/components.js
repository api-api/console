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
				ajaxUrl: String,
				structureNames: Array,
				currentStructure: Object,
				navigationView: String,
				navigationStructureHeadline: String
			},
			methods: {
				getStructureNames: function() {
					var vm = this;
					this.$http.get( this.ajaxUrl, {
						params: {
							action: 'get_structure_names'
						}
					}).then( function( response ) {
						vm.structureNames = response.body;
						vm.navigationView = 'structures';
						vm.currentStructure = null;
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
						vm.navigationView = 'routes';
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
			name: 'app-navigation',
			props: {
				structureNames: Array,
				currentStructure: Object,
				view: String,
				structureHeadline: String
			},
			computed: {
				headline: function() {
					if ( 'structures' === this.view ) {
						return this.structureHeadline;
					}

					return this.currentStructure.name;
				},
				contents: function() {
					if ( 'structures' === this.view ) {
						return this.structureNames;
					}

					var routeIdentifiers = [];
					for ( var i in this.currentStructure.routes ) {
						routeIdentifiers.push( this.currentStructure.routes[ i ].method + ' ' + this.currentStructure.routes[ i ].uri );
					}

					return routeIdentifiers;
				}
			},
			methods: {
				backLinkClicked: function() {
					this.$emit( 'getStructureNames' );
				},
				linkClicked: function( structure ) {
					if ( 'structures' === this.view ) {
						this.$emit( 'getStructure', structure );
					} else {
						//TODO
					}
				}
			}
		},
		{
			name: 'app-inspector'
		}
	];

	var optionalProperties = [
		'props',
		'data',
		'computed',
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
