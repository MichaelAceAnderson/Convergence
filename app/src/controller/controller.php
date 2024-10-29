<?php

// If the user is not accessing this file from index.php, redirect to the homepage
if ($_SERVER['PHP_SELF'] != '/index.php') {
	echo '<meta http-equiv="refresh" content="0; url=/" />';
	header('Location: /');
	// Stop script execution
	exit();
}

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'model.php';

// If the client session is not started
if (!isset($_SESSION)) {
	// Start the session
	session_start();
}

// Code relying on the model and called by the views' forms

class Controller
{
	/* PROPERTIES/ATTRIBUTES */
	// State of the last performed action
	private static int $state = STATE_NONE;
	// Message of the last performed action
	private static string $message = '';

	/* METHODS */
	// Write to a log file
	public static function printLog(string $msg): bool
	{
		$date = new DateTime('now', new DateTimeZone('Europe/Paris'));
		$date = $date->format('d-m-y H:i:s');
		if (LOGLEVEL < 1) {
			// If the log level is less than 1, do not log
			return false;
		}
		$logFile = fopen($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "c_data". DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'controller.log', 'a+');
		if (!$logFile) {
			// If it is impossible to open the log file
			return false;
		}
		if (!fwrite($logFile, PHP_EOL . '[' . $date . '] Controller: ' . $msg)) {
			// If it is impossible to write to the log file
			return false;
		}
		if (!fclose($logFile)) {
			// If it is impossible to close the log file
			return false;
		}
		return true;
	}

	// Set the state to be displayed in the views
	public static function setState(int $state, string $message): void
	{
		self::$state = $state;
		self::$message = $message;
	}

	// Get the state of the last performed action
	public static function getState(): array
	{
		return array('state' => self::$state, 'message' => self::$message);
	}
}
// NOTE: It is not possible to include via a foreach, the order must be followed according to the interdependence of controllers/data

// Include the user controller
require_once __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'UserController.php';
// Include the database installation controller
require_once __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'InstallController.php';
// Include the posts controller
require_once __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'PostController.php';

