<?php
// If the user is not using this file in another context
// than from the index.php page, redirect to the homepage
if ($_SERVER['PHP_SELF'] != '/index.php') {
	echo '<meta http-equiv="refresh" content="0; url=/" />';
	header('Location: /');
	// Stop the script execution
	exit();
}

class UserController
{
	/* METHODS */

	/* Additions */
	// Add a user
	public static function addUser(string $nickname, string $password, bool $is_mod = false): bool
	{
		// Attempt to add the user to the database
		$result = User::insertUser($nickname, $password, $is_mod);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while creating the user "' . $nickname . '" with the password "' . $password . '" (admin: "' . ($is_mod ? 'true' : 'false') . '")!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the success/failure of the operation
			return $result;
		}
	}

	// Add a focus (follow a user)
	public static function addFocus(int $focuserId, $focusedId): bool
	{
		// Attempt to add the user to the database
		$result = User::insertFocuser($focuserId, $focusedId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while adding the focus of user "' . $focuserId . '" on user "' . $focusedId . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the success/failure of the operation
			return $result;
		}
	}

	/* Verifications */
	// Check if the user is connected
	public static function userConnected(): int
	{
		// Check if the user is connected
		if (isset($_SESSION['user']['id_user'])) {
			// If the user is connected, return the user id
			return $_SESSION['user']['id_user'];
		} else {
			// Otherwise, return 0
			return 0;
		}
	}

	// Check if the user follows another user
	public static function userFocuses(int $focuserId, int $focusedId): bool
	{
		// Attempt to retrieve the user's subscription list from the database
		$result = User::selectFocusedById($focuserId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while checking the focus of user "' . $focuserId . '" on user "' . $focusedId . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If there is at least one subscription
			if (count($result) > 0) {
				// For each focused user
				foreach ($result as $focused) {
					// If this user is the one presumably focused
					if ($focused->id_user == $focusedId) {
						// Return true
						return true;
					}
				}
				// If no user is part of the user's subscriptions
				// Return false
				return false;
			} else {
				// If there are no subscribers
				// Return false
				return false;
			}
		}
	}

	/* Retrievals */
	// Retrieve a user by their id
	public static function getUserById(int $userId): object | false
	{
		// Attempt to retrieve the user from the database
		$result = User::selectUserById($userId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving the user with id "' . $userId . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If there is a user
			if ($result) {
				// Return the user
				return $result[0];
			} else {
				// If there is no user
				// Return failure
				return false;
			}
		}
	}

	// Retrieve all users with a similar name
	public static function getUsersByName(string $nickname): array|false
	{
		// Attempt to retrieve the users from the database
		$result = User::selectUsersByName($nickname);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving users with the name "' . $nickname . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If there is at least one user
			if (count($result) > 0) {
				// Return the array of users
				return $result;
			} else {
				// If there is no user
				// Return failure
				return false;
			}
		}
	}

	// Retrieve a user by their username and password
	public static function getUserByCredentials(string $username, string $password): object | false
	{
		// Attempt to retrieve the user from the database by their username
		$result = User::selectUserByName($username);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving the information of user "' . $username . '" with the password "' . $password . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} elseif ($result) {
			// If the user exists

			// Check if $password matches the password
			if (password_verify($password, $result->password)) {
				// If the passwords match
				// Return the user object
				return $result;
			} else {
				// If the password does not match
				// Return failure
				Controller::printLog('User "' . $username . '" failed to log in with incorrect password "' . $password . '"!');
				return false;
			}
		} else {
			Controller::printLog('User "' . $username . '" does not exist and therefore could not log in with password "' . $password . '"!');
			// The user does not exist
			// Return failure
			return false;
		}
	}

	// Retrieve users who follow the user
	public static function getFocusersById(int $userId): array | false
	{
		// Attempt to retrieve the users from the database by the user's id
		$result = User::selectFocusersById($userId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving users focused by user "' . $userId . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If there is at least one user
			if (count($result) > 0) {
				// Return the array of users
				return $result;
			} else {
				// If there is no user
				// Return failure
				return false;
			}
		}
	}

	// Retrieve users that the user follows
	public static function getFocusedById(int $userId): array | false
	{
		// Attempt to retrieve the users from the database by the user's id
		$result = User::selectFocusedById($userId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving users who follow user "' . $userId . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If there is at least one user
			if (count($result) > 0) {
				// Return the array of users
				return $result;
			} else {
				// If there is no user
				// Return failure
				return false;
			}
		}
	}
	/* Modifications */
	// Change profile picture
	public static function changeProfilePic(int $userId, string $mediaUrl): bool
	{
		// Attempt to change the profile picture
		$result = User::updateProfilePic($userId, $mediaUrl);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while changing the profile picture of user "' . $userId . '" to "' . $mediaUrl . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// If the operation was successful
			if ($result) {
				// Store the new profile picture in the session
				$_SESSION['user']['p_img_url'] = $mediaUrl;
				// Return success
				return true;
			} else {
				// Return failure
				return false;
			}
		}
	}

	// Change description
	public static function changeDescription(int $userId, string $description): bool
	{
		// Attempt to change the description
		$result = User::updateDescription($userId, $description);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// If the operation was successful
			if ($result) {
				// Store the new description in the session
				$_SESSION['user']['description'] = $description;
				// Return success
				return true;
			} else {
				// Return failure
				return false;
			}
		}
	}

	/* Deletions */
	// Delete a user
	public static function removeUser(int $userId): bool
	{
		// Attempt to delete the user from the database by their id
		$result = User::deleteUser($userId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while deleting the user "' . $userId . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the user was successfully deleted
			// Return success/failure based on the result
			return $result;
		}
	}

	// Delete a focus
	public static function removeFocus(int $focuserId, int $focusedId): bool
	{
		// Attempt to delete the focus from the database by its id
		$result = User::deleteFocus($focuserId, $focusedId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while deleting the focus of "' . $focuserId . '" on user "' . $focusedId . '"!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the focus was successfully deleted
			// Return success/failure based on the result
			return $result;
		}
	}
}

/* FORM REQUEST HANDLING */
// If the user submits a registration form
if (isset($_POST['fRegister'])) {
	// Check that the fields are filled
	if (
		!isset($_POST['fUserName']) || empty($_POST['fUserName'])
		|| !isset($_POST['fPass']) || empty($_POST['fPass'])
		|| !isset($_POST['fPassConfirm']) || empty($_POST['fPassConfirm'])
	) {
		// If the fields are not filled, store the error
		Controller::setState(STATE_ERROR, 'Please fill in all fields.');
	} else {
		// Check that the username does not exceed 64 characters
		if (strlen($_POST['fUserName']) > 64) {
			// If the username exceeds 64 characters, store the error
			Controller::setState(STATE_ERROR, 'The username must not exceed 64 characters.');
		}
		// Check that the passwords match
		if ($_POST['fPass'] === $_POST['fPassConfirm']) {
			// Attempt to create the user
			$result = UserController::addUser($_POST['fUserName'], $_POST['fPass']);
			if (!$result) {
				// If the user was not created, store the error
				Controller::setState(STATE_ERROR, 'Account creation failed, please try again.');
			} else {
				// If the user was created, store the success
				Controller::setState(STATE_SUCCESS, 'Account creation successful!');
			}
		} else {
			// If the passwords do not match, store the error
			Controller::setState(STATE_ERROR, 'Passwords do not match.');
		}
	}
}

// If the user submits a login form
if (isset($_POST['fLogin'])) {
	// Check that the fields are filled
	if (
		!isset($_POST['fUserName']) || empty($_POST['fUserName'])
		|| !isset($_POST['fPass']) || empty($_POST['fPass'])
	) {
		// If all fields are not filled, store the error
		Controller::setState(STATE_ERROR, 'Please fill in all fields.');
	} else {
		// If all fields are filled
		// Attempt to retrieve the user's information
		$user = UserController::getUserByCredentials($_POST['fUserName'], $_POST['fPass']);
		if (!$user) {
			// If the user does not exist or the password is incorrect, store the error
			Controller::printLog('User "' . $_POST['fUserName'] . '" failed to log in with password "' . $_POST['fPass'] . '"!');
			Controller::setState(STATE_ERROR, 'Login failed, please check your credentials.');
		} else {
			// If the user exists and the password is correct, store the information in the session
			$_SESSION['user'] = (array) $user;
			Controller::printLog('User "' . $_POST['fUserName'] . '" successfully logged in with password "' . $_POST['fPass'] . '"!');
			// Redirect to the homepage
			header('Location: /');
			// Stop script execution
			exit();
		}
	}
}
// If the user logs out
if (isset($_POST['fLogOut'])) {
	// Remove all session variables
	unset($_SESSION['user']);
	// Destroy the session
	session_destroy();
	// Redirect to the homepage
	header('Location: /');
	// Stop script execution
	exit();
}

// If the user submits a user search form
if (isset($_POST['fSearch'])) {
	// Redirect to the search page
	header('Location: /?page=search&user=' . $_POST['fSearchUser']);
	// Stop script execution
	exit();
}

// If the user submits a form to focus on a user
if (isset($_POST['fFocus'])) {
	// Attempt to focus on the user
	$result = UserController::addFocus($_SESSION['user']['id_user'], $_POST['fFocus']);
	if (!$result) {
		// If the user was not focused, store the error
		Controller::setState(STATE_ERROR, 'Unable to focus on the user, please try again.');
	} else {
		// If the user was focused, store the success
		Controller::setState(STATE_SUCCESS, 'You are now following the user!');
	}
}

// If the user submits a form to unfocus a user
if (isset($_POST['fUnFocusUser'])) {
	// Attempt to unfocus the user
	$result = UserController::removeFocus($_SESSION['user']['id_user'], $_POST['fUnFocusUser']);
	if (!$result) {
		// If the user was not unfocused, store the error
		Controller::setState(STATE_ERROR, 'Unable to unfocus the user, please try again.');
	} else {
		// If the user was unfocused, store the success
		Controller::setState(STATE_SUCCESS, 'You are no longer following the user!');
	}
}
// If a profile picture change form is submitted
if (isset($_FILES['fProfilePic'])) {
	// If no file was uploaded
	if (empty($_FILES['fProfilePic']) || $_FILES['fProfilePic']['error'] == UPLOAD_ERR_NO_FILE) {
		// Store an error message
		Controller::setState(STATE_ERROR, 'You must select an image file to change your profile picture!');
	} else {
		// By default, the media URL associated with the file is null
		$mediaUrl = null;
		// If a file was uploaded

		// If an error occurred during the upload
		if ($_FILES['fProfilePic']['error'] != UPLOAD_ERR_OK || !$_FILES['fProfilePic']['tmp_name']) {
			// Store the error message to display
			Controller::setState(STATE_ERROR, 'Error: The file could not be uploaded');
		} elseif (!preg_match('/image\//', $_FILES['fProfilePic']['type'])) {
			// If the file is not an image
			// Store the error message to display
			Controller::setState(STATE_ERROR, 'Your file must be an image!');
		} elseif ($_FILES['fProfilePic']['size'] > 10000000) {
			// If the file size is greater than 10MB
			// Store the error message to display
			Controller::setState(STATE_ERROR, 'The file is too large!');
		} else {
			// If the user's folder does not exist
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR)) {
				// Create the user's folder
				mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR);
			}
			// If the profile picture storage folder does not exist
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
				// Create the profile picture storage folder
				mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
			}
			// Declare the URL where the profile picture will be stored
			$mediaUrl = DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $_FILES['fProfilePic']['name'];

			if (!move_uploaded_file($_FILES['fProfilePic']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $mediaUrl)) {
				Controller::setState(STATE_ERROR, 'Unable to upload the file due to a server-side error');
			}
		}
		// If there were no errors
		if (Controller::getState()['state'] != STATE_ERROR) {
			// Attempt to add the file URL to the database
			$profilePicUpdate = UserController::changeProfilePic($_SESSION['user']['id_user'], $mediaUrl);
			if (!$profilePicUpdate) {
				// If an error occurs, store the error message to display
				Controller::setState(STATE_ERROR, '⚠ Your profile picture was already registered under this name in the database! Other users may not see your new profile picture immediately...');
			} else {
				// If the change was successful, store the success message to display
				Controller::setState(STATE_SUCCESS, 'The profile picture has been successfully changed!');
			}
		}
	}
}

// If a description change form is submitted
if (isset($_POST['fDescription'])) {
	// Attempt to change the description
	$descriptionUpdate = UserController::changeDescription($_SESSION['user']['id_user'], $_POST['fDescription']);
	if (!$descriptionUpdate) {
		// If an error occurs, store the error message to display
		Controller::setState(STATE_ERROR, '⚠ Your description has not been changed!');
	} else {
		// If the change was successful, store the success message to display
		Controller::setState(STATE_SUCCESS, 'The description has been successfully changed!');
	}
}
