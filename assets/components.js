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
				navigationView: String,
				navigationStructureHeadline: String,
				navigationStructures: Array
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
				view: String,
				structureHeadline: String,
				structures: Array,
				routes: Array,
				currentStructure: Object
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

					return this.routes;
				}
			},
			methods: {
				listStructures: function() {

				},
				listRoutes: function( structure ) {
					console.log( 'List routes for ' + structure );
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
