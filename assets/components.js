( function( Vue ) {

	var templates = [
		{
			name: 'app',
			props: {
				ajaxUrl: String,
				structures: Array,
				authenticators: Array,
				transporters: Array,
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
				structures: Array,
				currentStructure: Object,
				navigationView: String,
				navigationStructureHeadline: String
			},
			methods: {
				getStructures: function() {
					var vm = this;
					this.$http.get( this.ajaxUrl, {
						params: {
							action: 'get_structures'
						}
					}).then( function( response ) {
						vm.structures = response.body;
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
				structures: Array,
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
						return this.structures;
					}

					var routeIdentifiers = [];
					for ( var i in this.currentStructure.routes ) {
						routeIdentifiers.push( this.currentStructure.routes[ i ].method + ' ' + this.currentStructure.routes[ i ].uri );
					}

					return routeIdentifiers;
				}
			},
			methods: {
				getStructures: function() {
					this.$emit( 'getStructures' );
				},
				getStructure: function( structure ) {
					this.$emit( 'getStructure', structure );
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
