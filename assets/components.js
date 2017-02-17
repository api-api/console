( function( Vue ) {

	var templates = [
		{
			name: 'app'
		},
		{
			name: 'app-header',
			props: [ 'headline' ]
		},
		{
			name: 'app-main'
		},
		{
			name: 'app-footer',
			props: [ 'copyright' ]
		},
		{
			name: 'app-navigation'
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
