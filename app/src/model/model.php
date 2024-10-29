<?php
// Code to include in the controller

declare(strict_types=1);

// If the user is not using this file in another context
// than from the index.php page, redirect to the homepage
if ($_SERVER['PHP_SELF'] != '/index.php') {
	echo '<meta http-equiv="refresh" content="0; url=/" />';
	header('Location: /');
	// Stop script execution
	exit();
}

// Database host
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
// Database port
define('DB_PORT', getenv('DB_PORT') ?: '3306');
// Name of the database to use
define('DB_NAME', getenv('MARIADB_DATABASE') ?: 'convergence');
// Database user name
define('DB_USER', getenv('MARIADB_USER') ?: 'root');
// Database user password
define('DB_PASS', getenv('MARIADB_PASSWORD') ?: 'root');

// 0: No errors displayed/logged, 
// 1: Errors intended for the user, 
// 2: Direct source of errors (developers), 
// 3: Complete trace of the source (developers)
define('LOGLEVEL', 3);
// Constant for the type of error display
define('RAW', 1);
define('HTML', 2);

// Define state constants
define('STATE_NONE', -1);
define('STATE_SUCCESS', 1);
define('STATE_ERROR', 0);
class Model
{
	/* PROPERTIES/ATTRIBUTES */
	// Database connection
	private static ?PDO $pdo = null;
	// Query to process
	private static mixed $stmt = null;

	/* METHODS */

	/* Setters */
	// Set the query to process
	public static function setStmt(mixed $query): void
	{
		self::$stmt = $query;
	}

	// Set the connection to use
	public static function setPdo(PDO|null $pdo): void
	{
		self::$pdo = $pdo;
	}

	/* Getters */
	// Get the query to process
	public static function getStmt(): mixed
	{
		return self::$stmt;
	}

	// Get the connection to use
	public static function getPdo(): ?PDO
	{
		return self::$pdo;
	}

	/* OTHER METHODS */
	// Format the error of an exception
	public static function getError(Exception $error, int $mode = RAW): string
	{
		$errorMsg = '';
		if (LOGLEVEL >= 1) {
			$errorMsg = 'Error: ' . $error->getMessage();
		}
		if (LOGLEVEL >= 2) {
			$errorMsg .= '<br>Source of the error: ' . $error->getFile() . ':' . $error->getLine();
		}
		if (LOGLEVEL >= 3) {
			$errorMsg .= '<br>Error trace (string): ' . $error->getTraceAsString();
			$errorMsg .= '<br>Error code: ' . $error->getCode();
		}
		if ($mode == RAW) {
			// Definition of regex for formatting
			$formatting = array(
				array('/\<br\>|\<br\/\>/', '/\<b\>|\<\/b\>/'),
				array(PHP_EOL, '')
			);
			// Format the error message to replace raw line breaks with HTML line breaks
			$errorMsg = preg_replace($formatting[0], $formatting[1], $errorMsg);
		}

		return $errorMsg;
	}

	// Write to a log file
	public static function printLog(string $msg): bool
	{
		$date = new DateTime('now', new DateTimeZone('Europe/Paris'));
		$date = $date->format('d-m-y H:i:s');
		if (LOGLEVEL < 1) {
			// If the log level is less than 1, do not log
			return false;
		}
		$logFile = fopen($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "c_data". DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'model.log', 'a+');
		if (!$logFile) {
			// If it is impossible to open the log file
			return false;
		}
		if (!fwrite($logFile, PHP_EOL . '[' . $date . '] Model: ' . $msg)) {
			// If it is impossible to write to the log file
			return false;
		}
		if (!fclose($logFile)) {
			// If it is impossible to close the log file
			return false;
		}
		return true;
	}

	// Recursively delete a folder and its contents
	public static function rmdir_r(string $path): bool
	{
		if (!is_dir($path)) {
			// If it is not a folder
			// Log the error
			self::printLog('Unable to delete the item ' . $path . ' because it is not a folder');
			// Return failure
			return false;
		} else {
			// If it is a folder
			// Scan the folder contents
			$objects = scandir($path);
			// For each item in the folder
			foreach ($objects as $object) {
				// If it is neither the current folder nor the parent folder
				if ($object != '.' && $object != '..') {
					// If the item is a folder
					if (is_dir($path . DIRECTORY_SEPARATOR . $object) && !is_link($path . DIRECTORY_SEPARATOR . $object)) {
						// Recursively call the function on this subfolder
						self::rmdir_r($path . DIRECTORY_SEPARATOR . $object);
					} else {
						// If it is a file, delete the item
						if (!unlink($path . DIRECTORY_SEPARATOR . $object)) {
							// If it is impossible to delete the item
							// Log the error
							self::printLog('Unable to delete the item ' . $path . DIRECTORY_SEPARATOR . $object);
							return false;
						}
					}
				}
			}
			if (!rmdir($path)) {
				// If it is impossible to delete the folder
				// Log the error
				self::printLog('Unable to delete the folder ' . $path);
				return false;
			}
		}
		// Return success
		return true;
	}
}

if (!extension_loaded('PDO')) {
	Model::printLog('The PDO extension is not installed on the PHP server!');
	Controller::setState(STATE_ERROR, 'The PDO extension is not installed on the PHP server!');
} elseif (!extension_loaded('pdo_mysql')) {
	Model::printLog('The pdo_mysql extension for PostgreSQL is not installed on the PHP server!');
	Controller::setState(STATE_ERROR, 'The pdo_mysql extension for PostgreSQL is not installed on the PHP server!');
} else {
	// If the PDO and pdo_mysql extensions are installed, loaded, and configured in php.ini
	// Attempt to create a connection to the database
	try {
		// Retrieve the database connection credentials
		$username = DB_USER;
		$password = DB_PASS;
		// If the installation is in progress, use the installation credentials
		if (isset($_POST['fInstall'])) {
			$username = $_POST['fUserName'];
			$password = $_POST['fPass'];
		}
		// Use PDO (PHP Data Objects) to connect to the database
		// PDO has the advantage of being compatible with multiple DBMS (MySQL, PostgreSQL, SQLite, etc.)
		Model::setPdo(
			new \PDO(
				'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . '',
				$username,
				$password,
				[
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
					\PDO::ATTR_TIMEOUT => 2,
					// \PDO::ATTR_EMULATE_PREPARES => true,
					// \PDO::ATTR_PERSISTENT => true,
					// \PDO::ATTR_STRINGIFY_FETCHES => true,
				]
			)
		);
	} catch (PDOException $e) {
		// Log the error
		Model::printLog(Model::getError($e));
		// If an error occurs, destroy the connection
		Model::setPdo(null);
	}
	// If the connection is established, log the message
	if (!is_null(Model::getPdo())) {
		Model::printLog('Connection to the database ' . DB_NAME . ' successfully established! (host: ' . DB_HOST . ', user: ' . DB_USER . ', port: ' . DB_PORT . ', DBMS: MySQL)');
	}
}
// Include all models in the entities folder
foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . '*.php') as $filename) {
	if (!include_once $filename) {
		Model::printLog('Unable to include the file ' . $filename);
	}
}
