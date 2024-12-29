<?php
// If the user is not using this file in a context other than from the index.php page, redirect them to the homepage
if ($_SERVER['PHP_SELF'] != '/index.php') {
	echo '<meta http-equiv="refresh" content="0; url=/" />';
	header('Location: /');
	// Stop the script execution
	exit();
}

class User
{
	/* METHODS */

	/* Insertions */
	// Create a user in the database
	public static function insertUser(string $nickname, string $password, bool $is_mod): bool | Exception
	{
		// Try to add a user
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'INSERT INTO convergence.c_user (nickname, password, is_mod) VALUES (:nickname, :password, :is_mod)'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the nickname parameter to the prepared query
				if (!Model::getStmt()->bindParam('nickname', $nickname, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The nickname could not be bound to the prepared query!');
				}
				// Hash the password (a failure will throw an exception caught below)
				$password = password_hash($password, PASSWORD_ARGON2ID);

				// Bind the password parameter to the prepared query
				if (!Model::getStmt()->bindParam('password', $password, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The password could not be bound to the prepared query!');
				}
				// Bind the admin boolean parameter to the prepared query
				if (!Model::getStmt()->bindParam('is_mod', $is_mod, PDO::PARAM_BOOL)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The admin boolean could not be bound to the prepared query!');
				}

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If an error occurs during query execution
					throw new Exception('The query could not be executed!');
				} else {
					// If insertion succeeded
					if (Model::getStmt()->rowCount() > 0) {
						// Return success
						return true;
					} else {
						// If insertion did not succeed
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not insert the data into the database!');
					}
				}
			}
		} catch (ValueError | Error $e) {
			// If an error related to password hashing occurred
			// Log the error
			$e = new Exception('An error occurred while inserting the user "' . $nickname . '" into the database with the password "' . $password . '" (Admin: "' . $is_mod ? 'true' : 'false' . '"): The password could not be hashed: '
				. $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while inserting the user "' . $nickname . '" into the database with the password "' . $password . '" (Admin: "' . $is_mod ? 'true' : 'false' . '"): '
				. $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Add a focuser to a user by their ids
	public static function insertFocuser(int $focuserId, int $focusedId): bool | Exception
	{
		// Try to add a focuser to a user
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'INSERT INTO convergence.c_focuser (id_user_focuser, id_user_focused) VALUES (:id_user_focuser, :id_user_focused)'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the focuser user id parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_focuser', $focuserId, PDO::PARAM_INT)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The focuser user id could not be bound to the prepared query!');
				}
				// Bind the focused user id parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_focused', $focusedId, PDO::PARAM_INT)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The focused user id could not be bound to the prepared query!');
				}

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If an error occurs during query execution
					throw new Exception('The query could not be executed!');
				} else {
					// If insertion succeeded
					if (Model::getStmt()->rowCount() > 0) {
						// Return success
						return true;
					} else {
						// If insertion did not succeed
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not insert the data into the database!');
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while inserting the focus of "' . $focuserId . '" on the user "' . $focusedId . '" in the database: '
				. $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	/* Retrievals */
	// Retrieve the array of users
	public static function selectUsers(): array | Exception
	{
		// Try to retrieve the users
		try {
			if (is_null(Model::getPdo())) {
				// If the connection could not be created
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Execute the query
				$stmt = Model::getPdo()->query(
					'SELECT convergence.c_user.id_user, convergence.c_user.p_img_url, convergence.c_user.nickname, convergence.c_user.description, convergence.c_user.register_date, convergence.c_user.is_mod  
					FROM convergence.c_user'
				);
				// If the query could not be executed
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				}
				// Set the result to be processed
				Model::setStmt($stmt);

				// Retrieve the results
				$result = Model::getStmt()->fetchAll();
				// If the retrieval of results failed
				if ($result === false) {
					// Throw an error that will be caught below
					throw new Exception('The query was executed but could not retrieve the data from the database!');
				} else {
					// If the retrieval of results succeeded
					// Return the results
					return $result;
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the users from the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Retrieve an array of users by their nickname
	public static function selectUsersByName(string $nickname): array | Exception
	{
		// Try to retrieve the users by their nickname
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'SELECT convergence.c_user.id_user, convergence.c_user.p_img_url, convergence.c_user.nickname, convergence.c_user.description, convergence.c_user.register_date, convergence.c_user.is_mod  
					FROM convergence.c_user 
					WHERE convergence.c_user.nickname
					LIKE :nickname'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the nickname parameter to the prepared query, formatted for SQL LIKE search
				$nickname = "%{$nickname}%";
				if (!Model::getStmt()->bindParam('nickname', $nickname, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The nickname could not be bound to the prepared query!');
				}

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					throw new Exception('The query could not be executed!');
				} else {
					// Retrieve the results
					$result = Model::getStmt()->fetchAll();
					// If the retrieval of results failed
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the retrieval of results succeeded
						// Return the results
						return $result;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the users corresponding to the nickname "' . $nickname . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Retrieve a user's row by their id
	public static function selectUserById(int $userId): array | Exception
	{
		// Try to retrieve the user by their id
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'SELECT *
					FROM convergence.c_user 
					WHERE convergence.c_user.id_user = :id_user'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);

				// Bind the user id parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user', $userId, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The user id could not be bound to the prepared query!');
				}

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					throw new Exception('The query could not be executed!');
				} else {
					// If the query succeeded
					// Retrieve the results
					$result = Model::getStmt()->fetchAll();
					// If the retrieval of results failed
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the retrieval of results succeeded
						// Return the results
						return $result;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the user corresponding to the id "' . $userId . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
	// Retrieve a user's row by their nickname
	public static function selectUserByName(string $nickname): object
	{
		// Try to retrieve the user by their nickname
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					"SELECT *  
					FROM convergence.c_user 
					WHERE convergence.c_user.nickname = :nickname"
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the nickname parameter to the prepared query
				if (!Model::getStmt()->bindParam('nickname', $nickname, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The nickname could not be bound to the prepared query!');
				}
				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the query succeeded
					// Retrieve the results
					$result = Model::getStmt()->fetch();
					// If the retrieval of results failed
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the retrieval of results succeeded
						// Return the results
						return $result;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the user corresponding to the nickname "' . $nickname . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Retrieve all users focused by a user by their id
	public static function selectFocusedById(int $userId): array | Exception
	{
		// Try to retrieve all users focused by a user by their id
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					"SELECT convergence.c_user.id_user, convergence.c_user.nickname, convergence.c_user.p_img_url
					FROM convergence.c_user 
					INNER JOIN convergence.c_focuser 
					ON convergence.c_user.id_user = convergence.c_focuser.id_user_focused
					WHERE convergence.c_focuser.id_user_focuser = :id_user_focuser"
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the user id parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_focuser', $userId, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The user id could not be bound to the prepared query!');
				}
				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the query succeeded
					// Retrieve the results
					$result = Model::getStmt()->fetchAll();
					// If the retrieval of results failed
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the retrieval of results succeeded
						// Return the results
						return $result;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the users focused by the user corresponding to the id "' . $userId . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Retrieve all followers of a user by their id
	public static function selectFocusersById(int $userId): array | Exception
	{
		// Try to retrieve all followers of a user by their id
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					"SELECT convergence.c_user.id_user, convergence.c_user.nickname, convergence.c_user.p_img_url
					FROM convergence.c_user 
					INNER JOIN convergence.c_focuser 
					ON convergence.c_user.id_user = convergence.c_focuser.id_user_focuser
					WHERE convergence.c_focuser.id_user_focused = :id_user_focused"
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the user id parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_focused', $userId, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The user id could not be bound to the prepared query!');
				}
				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the query succeeded
					// Retrieve the results
					$result = Model::getStmt()->fetchAll();
					// If the retrieval of results failed
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the retrieval of results succeeded
						// Return the results
						return $result;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the users who follow the user corresponding to the id "' . $userId . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
	/* Modifications */
	// Change profile picture
	public static function updateProfilePic(int $userId, string $media_url): bool | Exception
	{
		// Try to add a new picture
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'UPDATE convergence.c_user 
					SET convergence.c_user.p_img_url = :p_img_url
					WHERE convergence.c_user.id_user = :id_user'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the URL parameter to the prepared query
				if (!Model::getStmt()->bindParam('p_img_url', $media_url, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The profile picture URL could not be bound to the prepared query!');
				}
				// Bind the user ID parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user', $userId, PDO::PARAM_INT)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The user ID could not be bound to the prepared query!');
				}
				// If the query could be prepared
				// Execute the query
				if (!Model::getStmt()->execute()) {
					// If an error occurs during query execution
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the change was made
					if (Model::getStmt()->rowCount() > 0) {
						// Return success
						return true;
					} else {
						// If the change was not made
						// Return false to indicate that the change was not made, probably because the URL is the same as the one already recorded
						return false;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while changing the profile picture of the user "' . $userId . '" to "' . $media_url . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Change description
	public static function updateDescription(int $userId, string $description): bool | Exception
	{
		// Try to change the description
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'UPDATE convergence.c_user 
					SET convergence.c_user.description = :description
					WHERE convergence.c_user.id_user = :id_user'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the description parameter to the prepared query
				if (!Model::getStmt()->bindParam('description', $description, PDO::PARAM_STR)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The description could not be bound to the prepared query!');
				}
				// Bind the user ID parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user', $userId, PDO::PARAM_INT)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The user ID could not be bound to the prepared query!');
				}
				// If the query could be prepared
				// Execute the query
				if (!Model::getStmt()->execute()) {
					// If an error occurs during query execution
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the change was made
					if (Model::getStmt()->rowCount() > 0) {
						// Return success
						return true;
					} else {
						// If the change was not made
						// Return false to indicate that the change was not made, probably because the description is the same as the one already recorded
						return false;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while changing the description of the user "' . $userId . '" to "' . $description . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
	/* Deletions */
	// Delete a user
	public static function deleteUser(int $userId): bool | Exception
	{
		// Try to delete a user
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'DELETE FROM convergence.c_user 
					WHERE convergence.c_user.id_user = :id;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the user id parameter to the prepared query
				if (!Model::getStmt()->bindParam('id', $userId, PDO::PARAM_INT)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The user id could not be bound to the prepared query!');
				}
				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If deletion succeeded
					if (Model::getStmt()->rowCount() > 0) {
						// Return success
						return true;
					} else {
						// If deletion did not succeed
						// Return false to indicate that the deletion was not performed
						return false;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while deleting the user corresponding to the id "' . $userId . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Delete a focus
	public static function deleteFocus(int $focuserId, int $focusedId): bool | Exception
	{
		// Try to delete a focus
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'DELETE FROM convergence.c_focuser 
					WHERE convergence.c_focuser.id_user_focuser = :id_user_focuser 
					AND convergence.c_focuser.id_user_focused = :id_user_focused;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Bind the focuser user id parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_focuser', $focuserId, PDO::PARAM_INT)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The focuser user id could not be bound to the prepared query!');
				}
				// Bind the focused user id parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_focused', $focusedId, PDO::PARAM_INT)) {
					// If the parameter could not be bound
					// Throw an error that will be caught below
					throw new Exception('The focused user id could not be bound to the prepared query!');
				}
				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If deletion succeeded
					if (Model::getStmt()->rowCount() > 0) {
						// Return success
						return true;
					} else {
						// If deletion did not succeed
						// Return false to indicate that the deletion was not performed
						return false;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while deleting the focus of the user "' . $focuserId . '" on the user "' . $focusedId . '" in the database: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
}