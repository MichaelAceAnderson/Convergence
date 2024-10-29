<?php
// If the user is not logged in as an administrator
if (!isset($_SESSION['user']['is_mod']) || $_SESSION['user']['is_mod'] == false) {
	// Redirect to the homepage
	header('Location: /');
	// Stop script execution
	exit();
}
?>
<style>
b {
	text-shadow: var(--theme-text-outline);
	color: red;
}

span {
	line-break: anywhere;
}

.main {
	font-size: 20px;
}
</style>
<div class="main">
	<pre>
	<?php

	// Arbitrary variable definitions to display for debugging
	$_POST['postVar'] = 'A form value';
	$test = 'Test string';
	// Definition of regex for formatting
	$formatting = array(array('/\\n/', '/' . PHP_EOL . '/', '/\[(\w+)\]/'), array('<br>', '', '<b>[${1}]</b>'));

	// PHP Variables
	echo '<b>PHP Vars: </b><br>';
	if (!isset($_SESSION)) {
		session_start();
	}
	echo '<b>SESSION values: </b><span>' . preg_replace($formatting[0], $formatting[1], print_r($_SESSION, true)) . '</span><br>';

	if (isset($_ENV)) {
		echo '<b>ENV values: </b><span>' . preg_replace($formatting[0], $formatting[1], print_r($_ENV, true)) . '</span><br>';
	}
	echo '<b>HTTP_RAW_POST_DATA values (now php://input): </b>' . file_get_contents('php://input') . '<br>';
	if (isset($http_response_header)) {
		echo '<b>http_response_header values: </b><span>' . preg_replace($formatting[0], $formatting[1], print_r($http_response_header, true)) . '</span><br>';
	}
	// Defined variables
	echo '<b>Defined vars: </font></b><span>' . preg_replace($formatting[0], $formatting[1], print_r(get_defined_vars(), true)) . '</span><br>' . PHP_EOL;
	// Defined constants
	echo '<b>Defined constants: </font></b><span>' . preg_replace($formatting[0], $formatting[1], print_r(get_defined_constants(), true)) . '</span><br>' . PHP_EOL;
	// Current working directory
	echo '<b>Current working directory: </font></b><span>' . print_r(getcwd(), true) . '</span><br>' . PHP_EOL;
	// Path of this file (debug.php, different from cwd if called from another file)
	echo '<b>__DIR__: </font></b><span>' . __DIR__ . '</span><br>' . PHP_EOL;
	// Last error returned by PHP
	echo '<b>php_errormsg values: </b><span>' . preg_replace($formatting[0], $formatting[1], print_r(error_get_last(), true)) . '</span><br>';

	// Custom variables
	echo '<b>Custom Vars:</b><br>';
	$conflen = strlen('SCRIPT');
	$B = substr(__FILE__, 0, strrpos(__FILE__, '/'));
	$A = substr($_SERVER['DOCUMENT_ROOT'], strrpos($_SERVER['DOCUMENT_ROOT'], $_SERVER['PHP_SELF']));
	$C = substr($B, strlen($A));
	$posconf = strlen($C) - $conflen - 1;
	$D = substr($C, 1, $posconf);
	$host = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $D;

	echo '<b>Host: </b><span>' . $host . '</span><br>' . PHP_EOL;

	// PHP Info
	// echo '<b>PHP Info: </font></b><span>' . phpinfo() . '</span><br>'.PHP_EOL;
	// PHP Info 32
	// echo '<b>PHP Info 32: </font></b><span>" . phpinfo(32) . "</span><br>'.PHP_EOL;

	?>
	</pre>
</div>