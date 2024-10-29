<?php
// Include the controller which itself includes the rest of the back-end
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'controller.php';

// Include the head of the page
require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'structure' . DIRECTORY_SEPARATOR . 'head.php');
// Include the navigation menu
require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'structure' . DIRECTORY_SEPARATOR . 'menu.php');

// If the database is not installed correctly
if (!InstallController::isDBInstalled()) {
	// If the user is not on the installation page
	if (!isset($_GET['page']) || $_GET['page'] != 'install') {
		// Redirect to the installation page
		header('Location: /?page=install');
		// Stop the script execution
		exit();
	}
}
if (isset($_GET['page'])) {
	// If the user requests a specific page
	// If the requested page exists
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $_GET['page'] . '.php')) {
		// Include the requested page
		require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $_GET['page'] . '.php');
	} else {
		// If the requested page does not exist
		// Mimic the behavior of a 404 error code
		$_GET['code'] = '404';
		// Include the error handling page
		require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'error.php');
	}
} else {
	// If the user did not request a specific page
	// Include the home page
	require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'posts.php');
}
// Include the footer
require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'structure' . DIRECTORY_SEPARATOR . 'footer.php');

