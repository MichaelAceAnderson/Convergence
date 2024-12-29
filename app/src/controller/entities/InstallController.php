<?php

// If the user is not using this file in a context other than from the index.php page, redirect to the home page
if ($_SERVER['PHP_SELF'] != '/index.php') {
	echo '<meta http-equiv="refresh" content="0; url=/" />';
	header('Location: /');
	// Stop script execution
	exit();
}

class InstallController
{
	/* METHODS */

	/* Additions */
	// Install the database
	public static function installDB(string $adminName, string $adminPass): bool | Exception
	{
		// Create the database
		$dbInstallStatus = Install::installDB();
		// If the database installation failed
		if ($dbInstallStatus instanceof Exception) {
			// Return the error
			return $dbInstallStatus;
		} else {
			// If the database installation returned an error
			if (!$dbInstallStatus) {
				// If the database installation succeeded but an unknown error occurred
				// Define the error to return
				$error = new Exception('An unexpected error occurred during the database installation (Admin: "' . $adminName . '", Admin password: "' . $adminPass . '")!');
				// Log the error
				Controller::printLog(Model::getError($error));
				// Return the error
				return $error;
			} else {
				// If the database installation succeeded

				// Create the main user, admin, and owner
				if (!UserController::addUser($adminName, $adminPass, true)) {
					// If the user could not be created, stop the installation
					// Define the error to return
					$error = new Exception('The admin user "' . $adminName . '" with the password "' . $adminPass . '" could not be created in the database!');
					// Log the error
					Controller::printLog(Model::getError($error));
					// Return the error
					return $error;
				} else {
					// Retrieve the admin user, owner of the site
					$adminAccount = UserController::getUserByCredentials($adminName, $adminPass);
					if (!$adminAccount) {
						// If the admin user could not be retrieved from the database
						// Define the error to return
						$error = new Exception('The admin user "' . $adminName . '" with the password "' . $adminPass . '" could not be retrieved from the database!');
						// Log the error
						Controller::printLog(Model::getError($error));
						// Return the error
						return $error;
					} else {
						// If the admin user was successfully retrieved from the database
						// Return success
						return true;
					}
				}
			}
		}
	}
	/* VERIFICATIONS */
	// Check if the database is properly installed
	public static function isDBInstalled(): bool
	{
		// Ask the model to check the database installation
		$result = Install::isDBInstalled();
		// If an error occurs during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('The database is not properly installed!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the database is installed and all necessary information is present
			// Return success
			return true;
		}
	}
}

/* FORM REQUEST HANDLING */
// If the installation form has been submitted
if (isset($_POST['fInstall'])) {
	// Field verification
	if (
		isset($_POST['fUserName']) && $_POST['fUserName'] != ''
		&& isset($_POST['fPass']) && $_POST['fPass'] != ''
	) {
		$installStatus = InstallController::installDB($_POST['fUserName'], $_POST['fPass']);
		if ($installStatus instanceof Exception) {
			// If an error occurred, log the error message and display it to the user
			Controller::printLog(Model::getError($installStatus));
			Controller::setState(STATE_ERROR, Model::getError($installStatus, HTML));
		} else {
			if ($installStatus) {
				// If the installation succeeded
				if (isset($_SESSION)) {
					// Remove all session variables
					unset($_SESSION);
					// Destroy the session
					session_destroy();
				}
				// Redirect to the home page
				header('Location: /');
				// Stop script execution
				exit();
			} else {
				// Should never happen
				Controller::setState(STATE_ERROR, 'An error occurred during the database installation!');
			}
		}
	} else {
		Controller::setState(STATE_ERROR, 'Please fill in all fields!');
	}
}
