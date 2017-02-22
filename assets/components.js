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
			props: [ 'headline' ]
		},
		{
			name: 'app-main',
			props: [ 'navigation_headline', 'navigation_contents' ]
		},
		{
			name: 'app-footer',
			props: [ 'copyright' ]
		},
		{
			name: 'app-navigation',
			props: [ 'headline', 'contents' ]
		},
		{
			name: 'app-inspector'
		}
	];

	for ( var i in templates ) {
		var data = {
			template: '#' + templates[ i ].name + '-template'
		};

		if ( 'undefined' !== typeof templates[ i ].props ) {
			data.props = templates[ i ].props;
		}

		Vue.component( templates[ i ].name, data );
	}

	console.log( 'Vue components loaded!' );

})( Vue );
