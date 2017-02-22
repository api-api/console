<?php

namespace APIAPI\Console;

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

$bridge = new Bridge( 'apiapi-console', dirname( __FILE__ ) . '/config.json' );

AJAX::listen();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>API-API Console</title>
<?php Assets::enqueue_styles(); ?>
</head>

<body>
	<div id="app"></div>

	<?php Templates::print_templates(); ?>

	<script type="text/javascript">
		var apiapiConsoleData = JSON.parse( '<?php echo json_encode( $bridge->get_js_data() ); ?>' );
	</script>

	<?php Assets::enqueue_scripts(); ?>
</body>
</html>
