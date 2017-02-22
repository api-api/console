( function( Vue, data ) {

	var keys = Object.keys( data );

	var appTemplate = '<app';
	for ( var i in keys ) {
		appTemplate += ' :' + keys[ i ] + '="' + keys[ i ] + '"';
	}
	appTemplate += '></app>';

	var app = new Vue({
		el: '#app',
		template: appTemplate,
		data: data
	});

})( Vue, apiapiConsoleData );
