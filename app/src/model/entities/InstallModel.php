<?php

// If the user is not using this file in a context other than from the index.php page, redirect to the homepage
if ($_SERVER['PHP_SELF'] != '/index.php') {
	echo '<meta http-equiv="refresh" content="0; url=/" />';
	header('Location: /');
	// Stop script execution
	exit();
}

final class Install
{
	/* METHODS */

	/* Insertions */
	// Install the database
	public static function installDB(): bool | Exception
	{
		// Delete all post images in case of reinstallation
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
			if (!Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
				// If deletion failed
				// Throw an error that will be caught below
				throw new Exception('The images related to the old posts could not be deleted!');
			}
		}

		// Delete all post videos in case of reinstallation
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
			if (!Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
				// If deletion failed
				// Throw an error that will be caught below
				throw new Exception('The videos related to the old posts could not be deleted!');
			}
		}
		// Attempt to install the database
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Attempt to install the database via the SQL file

				// Get the content of the database installation SQL file
				$sqlFile = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'Convergence_mysql.sql');
				// If the file could not be read
				if (!$sqlFile) {
					// Throw an error that will be caught below
					throw new Exception('The database installation SQL file could not be read! Make sure the "Convergence_mysql.sql" file is present in the "install" folder with read permissions!');
				}
				// Execute the content of the SQL file
				if (Model::getPdo()->exec($sqlFile) === false) {
					// If an error occurs, throw an exception that will be caught below
					throw new Exception('The database installation SQL file could not be executed!');
				}

				// If no error occurred so far
				// Log the success
				Model::printLog('Database installation successful!');
				// Return success
				return true;
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('Unable to install the database: ' . $e->getMessage() . '!');
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	/* Verifications */
	// Check if the database is installed
	public static function isDBInstalled(): bool | Exception
	{
		// Attempt to install the database
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The database is not accessible: Make sure you have a MySQL database named "' . DB_NAME . '" running and accessible with the user "' . DB_USER . '" and the password "' . DB_PASS . '"!');
			} else {
				// If the connection succeeded

				// Attempt to retrieve data from each table

				// Check that the "c_user" table exists
				$stmt = Model::getPdo()->query('SELECT 
					EXISTS (
						SELECT TABLE_NAME
						FROM INFORMATION_SCHEMA.TABLES 
						WHERE TABLE_SCHEMA = \'convergence\' 
						AND TABLE_NAME = \'c_user\'
						) 
						AS \'tableCount\';
						');
				if (!$stmt) {
					// If an error occurs, throw an exception that will be caught below
					throw new Exception('The database is not properly installed: The query to check the existence of the user table could not be executed!');
				}

				// Define the result to process
				Model::setStmt($stmt);

				// Retrieve the result
				$result = Model::getStmt()->fetch();
				// If the "c_user" table does not exist
				if ($result->tableCount == 0) {
					// Throw an error that will be caught below
					throw new Exception('The database is not properly installed: The "c_user" table does not exist!');
				}
				// Retrieve the data from the "c_user" table
				$stmt = Model::getPdo()->query('SELECT * FROM c_user WHERE is_mod = true');
				if (!$stmt) {
					// If an error occurs, throw an exception that will be caught below
					throw new Exception('The database is not properly installed: The query to retrieve administrator users could not be executed!');
				}

				// Define the result to process
				Model::setStmt($stmt);

				// Retrieve the results
				$result = Model::getStmt()->fetchAll();
				// If the result array does not have at least 1 element
				if (count($result) < 1) {
					// Throw an error that will be caught below
					throw new Exception('The database is not properly installed: No administrator was found!');
				}
				// If there is at least 1 administrator, continue

				// Check that the "c_post" table exists
				$stmt = Model::getPdo()->query('SELECT 
					EXISTS (
						SELECT TABLE_NAME
						FROM information_schema.TABLES 
						WHERE TABLE_SCHEMA = \'convergence\' 
						AND TABLE_NAME = \'c_post\'
						) as \'tableCount\';
						');
				$stmt = $stmt->fetch();
				// If there is no table with that name
				if ($stmt->tableCount < 1) {
					// Throw an error that will be caught below
					throw new Exception('The database is not properly installed: The "c_post" table does not exist!');
				}

				// Log the success
				Model::printLog('Database verification successful!');
				// Return success
				return true;
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('Database verification failed: ' . $e->getMessage() . '!');
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
}

