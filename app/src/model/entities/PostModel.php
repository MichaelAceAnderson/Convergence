<?php
// If the user is not using this file in a context other than from the index.php page, redirect to the homepage
if ($_SERVER['PHP_SELF'] != '/index.php') {
	echo '<meta http-equiv="refresh" content="0; url=/" />';
	header('Location: /');
	// Stop the script execution
	exit();
}

class Post
{
	/* METHODS */

	/* Insertions */
	// Create a post in the database
	public static function insertPost(int $authorId, string $content, ?string $mediaUrl = null): int | Exception
	{
		// Try to add the post to the database
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'INSERT INTO convergence.c_post (content, id_user_author, media_url)
					VALUES (:content, :id_user_author, :media_url);'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the post content as a parameter to the prepared query
				if (!Model::getStmt()->bindParam('content', $content, PDO::PARAM_STR)) {
					// If the parameter could not be attached
					// Throw an error that will be caught below
					throw new Exception('The post content could not be attached as a parameter to the query!');
				}
				// Attach the author id as a parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_author', $authorId, PDO::PARAM_INT)) {
					// If the parameter could not be attached
					// Throw an error that will be caught below
					throw new Exception('The author id could not be attached as a parameter to the query!');
				}
				// Attach the media file URL as a parameter to the prepared query
				if (!Model::getStmt()->bindParam('media_url', $mediaUrl, PDO::PARAM_STR)) {
					// If the parameter could not be attached
					// Throw an error that will be caught below
					throw new Exception('The media file URL could not be attached as a parameter to the query!');
				}

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If insertion succeeded
					if (Model::getStmt()->rowCount() > 0) {
						// Try to retrieve the post id
						$result = self::selectNextPostId();
						// If the post id could not be retrieved
						if ($result instanceof Exception) {
							// Throw an error that will be caught below
							throw new Exception('The inserted post id could not be retrieved!');
						} else {
							// If the post id could be retrieved
							// Return the post id
							return $result;
						}
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
			$e = new Exception('An error occurred while inserting the post by user "' . $authorId . '" with content "' . $content . '" and media file "' . $mediaUrl ?? '(none)' . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Add reactions
	public static function insertReaction(int $userId, int $postId, int $reactionId): bool | Exception
	{
		// Try to add a reaction
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Check if the post exists
				$post = self::selectPost($postId);
				// If an error occurred while retrieving the post
				if ($post instanceof Exception) {
					// Throw an error that will be caught below
					throw new Exception('The post could not be retrieved!');
				}
				// If the post does not exist
				if (!$post) {
					// Throw an error that will be caught below
					throw new Exception('The post does not exist!');
				}
				// Check if there is already a reaction from the user on the post
				$reaction = self::selectReaction($userId, $postId);
				// If an error occurred while retrieving the reaction
				if ($reaction instanceof Exception) {
					// Throw an error that will be caught below
					throw new Exception('The check for an existing reaction on this post failed!');
				}
				// If a reaction already exists
				if ($reaction) {
					// Delete the reaction
					$result = self::deleteReaction($userId, $postId);
					// If an error occurred while deleting the reaction
					if ($result instanceof Exception) {
						// Throw an error that will be caught below
						throw new Exception('The reaction to be replaced could not be deleted!');
					}
				}

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'INSERT INTO convergence.c_post_reaction (id_user_reacted, id_post_reacted, reaction_type) 
					VALUES (:id_user_reacted, :id_post_reacted, :reaction_type);'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the user id as a parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_reacted', $userId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The user id could not be attached as a parameter to the prepared query!');
				}
				// Attach the post id as a parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_post_reacted', $postId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The post id could not be attached as a parameter to the prepared query!');
				}
				// Attach the reaction id as a parameter to the prepared query
				if (!Model::getStmt()->bindParam('reaction_type', $reactionId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The reaction id could not be attached as a parameter to the prepared query!');
				}

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If addition succeeded
					if (Model::getStmt()->rowCount() > 0) {
						// Return success
						return true;
					} else {
						// If addition did not succeed
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not insert the data into the database!');
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while adding the reaction "' . $reactionId . '" by user "' . $userId . '" on post "' . $postId . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	/* Selections */
	// Select the array of posts
	public static function selectPosts(): array | Exception
	{
		// Try to retrieve the posts
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'SELECT * FROM convergence.c_post ORDER BY creation_date DESC;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the query succeeded, retrieve the results
					$result = Model::getStmt()->fetchAll();
					// If the results could not be retrieved
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the results could be retrieved
						// Return the results
						return $result;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the posts: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
	// Select an array of posts based on the author's id
	public static function selectPostsByUserId(int $authorId): array | Exception
	{
		// Try to retrieve the posts
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'SELECT * FROM convergence.c_post WHERE id_user_author=:id_user_author ORDER BY creation_date DESC;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the author's id as a parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_author', $authorId, PDO::PARAM_INT)) {
					// If the parameter could not be attached
					// Throw an error that will be caught below
					throw new Exception('The author\'s id could not be attached as a parameter to the prepared query!');
				}

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the query succeeded, retrieve the results
					$result = Model::getStmt()->fetchAll();
					// If the results could not be retrieved
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the results could be retrieved
						// Return the results
						return $result;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the posts of the user "' . $authorId . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Select a single post row
	public static function selectPost(int $postId): array | Exception
	{
		// Try to retrieve the specified post
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					"SELECT convergence.c_post.id_user_author, convergence.c_user.nickname, convergence.c_post.content, convergence.c_post.media_url, convergence.c_post.creation_date
					FROM convergence.c_post JOIN convergence.c_user
					ON convergence.c_post.id_user_author=c_user.id_user
					WHERE convergence.c_post.id_post=:id_post"
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the post id to the retrieval query
				if (!Model::getStmt()->bindParam('id_post', $postId, PDO::PARAM_INT)) {
					// If the parameter could not be attached
					// Throw an error that will be caught below
					throw new Exception('Unable to attach the post id "' . $postId . '" as a parameter to the retrieval query!');
				}

				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the query succeeded
					if (Model::getStmt()->rowCount() > 0) {
						// Retrieve the results
						$result = Model::getStmt()->fetchAll();
						// If the results could not be retrieved
						if ($result === false) {
							// Throw an error that will be caught below
							throw new Exception('The query was executed but could not retrieve the data from the database!');
						} else {
							// If the results could be retrieved
							// Return the results
							return $result;
						}
					} else {
						// If the query succeeded but there are no results
						throw new Exception('The specified post does not exist!');
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the post "' . $postId . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Select the posts of users focused by the user
	public static function selectFeedPostsById(int $userId): array | Exception
	{
		// Try to retrieve the posts of users focused by the user
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					"SELECT convergence.c_post.id_post, convergence.c_post.id_user_author, convergence.c_user.nickname, convergence.c_post.content, convergence.c_post.media_url, convergence.c_post.creation_date
					FROM convergence.c_post JOIN convergence.c_user
					ON convergence.c_post.id_user_author=c_user.id_user
					WHERE convergence.c_post.id_user_author IN (
						SELECT id_user_focused
						FROM convergence.c_focuser
						WHERE id_user_focuser=:id_user_focuser
					)
					ORDER BY convergence.c_post.creation_date DESC"
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the logged-in user's id as a parameter to the prepared query
				if (!Model::getStmt()->bindParam('id_user_focuser', $userId, PDO::PARAM_INT)) {
					// If the parameter could not be attached
					// Throw an error that will be caught below
					throw new Exception('The user\'s id could not be attached as a parameter to the prepared query!');
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
					// If the results could not be retrieved
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the results could be retrieved
						// Return the results
						return $result;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the posts of users focused by the user "' . $userId . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Select the id of the last created post
	public static function selectNextPostId(): int | Exception
	{
		// Try to retrieve the id of the last post
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query
				$stmt = Model::getPdo()->query(
					'SELECT `AUTO_INCREMENT`
					FROM  INFORMATION_SCHEMA.TABLES
					WHERE TABLE_SCHEMA = \'convergence\'
					AND   TABLE_NAME   = \'c_post\';'
				);
				// If the query could not be executed
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				}

				// Set the result to be processed
				Model::setStmt($stmt);

				// If the query succeeded, retrieve the results
				$result = Model::getStmt()->fetch();
				// If the results could not be retrieved
				if (is_null($result->AUTO_INCREMENT)) {
					// We don't know if it's an error or if the counter is not initialized
					// Return id 0 which cannot correspond to any post, AUTO_INCREMENT starts at 1
					return 0;
				} else {
					// If the results could be retrieved
					// Return the results
					return $result->AUTO_INCREMENT;
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the id of the last created post: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
	// Select a user's reaction based on their id and the post id
	public static function selectReaction(int $userId, int $postId): object | false
	{
		// Try to retrieve the reaction to the specified post
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Check if the post exists
				$post = self::selectPost($postId);
				// If an error occurred while retrieving the post
				if ($post instanceof Exception) {
					// Throw an error that will be caught below
					throw new Exception('The post could not be retrieved!');
				}
				// If the post does not exist
				if (!$post) {
					// Throw an error that will be caught below
					throw new Exception('The post does not exist!');
				}

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'SELECT reaction_type 
					FROM convergence.c_post_reaction    
					WHERE convergence.c_post_reaction.id_user_reacted = :id_user_reacted
					AND convergence.c_post_reaction.id_post_reacted = :id_post_reacted;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the user id to the prepared query
				if (!Model::getStmt()->bindParam('id_user_reacted', $userId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The user id could not be attached as a parameter to the prepared query!');
				}
				// Attach the post id to the prepared query
				if (!Model::getStmt()->bindParam('id_post_reacted', $postId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The post id could not be attached as a parameter to the prepared query');
				}
				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query could not be executed!');
				} else {
					// If the query succeeded
					if (Model::getStmt()->rowCount() > 0) {
						// Retrieve the results
						$result = Model::getStmt()->fetch();
						// If the results could not be retrieved
						if ($result === false) {
							// Throw an error that will be caught below
							throw new Exception('The query was executed but could not retrieve the data from the database!');
						} else {
							// If the results could be retrieved
							// Return the results
							return $result;
						}
					} else {
						// If the query succeeded but there are no results
						return false;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the reaction to the post "' . $postId . '" by user "' . $userId . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Select the number of reactions to a post based on its id
	public static function selectReactionsCount(int $postId, int $reactionId): int | Exception
	{
		// Try to retrieve the number of reactions to the specified post
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Check if the post exists
				$post = self::selectPost($postId);
				// If an error occurred while retrieving the post
				if ($post instanceof Exception) {
					// Throw an error that will be caught below
					throw new Exception('The post could not be retrieved!');
				}
				// If the post does not exist
				if (!$post) {
					// Throw an error that will be caught below
					throw new Exception('The post does not exist!');
				}

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'SELECT COUNT(*) AS reactions_count
					FROM convergence.c_post_reaction    
					WHERE convergence.c_post_reaction.id_post_reacted = :id_post_reacted
					AND convergence.c_post_reaction.reaction_type = :reaction_type;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the post id to the prepared query
				if (!Model::getStmt()->bindParam('id_post_reacted', $postId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The post id could not be attached as a parameter to the prepared query');
				}
				// Attach the reaction id to the prepared query
				if (!Model::getStmt()->bindParam('reaction_type', $reactionId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The reaction id could not be attached as a parameter to the prepared query');
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
					// If the results could not be retrieved
					if ($result === false) {
						// Throw an error that will be caught below
						throw new Exception('The query was executed but could not retrieve the data from the database!');
					} else {
						// If the results could be retrieved
						// Return the results
						return $result->reactions_count;
					}
				}
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while retrieving the number of reactions to the post "' . $postId . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	/* Deletions */
	// Delete all posts in the database
	public static function deletePosts(): int | Exception
	{
		// Try to delete the posts
		try {
			// Recursively delete videos related to posts if they exist
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
				if (!Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
					// If the file deletion failed
					// Throw an error that will be caught below
					throw new Exception('The videos related to the posts could not be deleted!');
				}
			}
			// Recursively delete images related to posts if they exist
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img') && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
				if (!Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
					// If the file deletion failed
					// Throw an error that will be caught below
					throw new Exception('The images related to the posts could not be deleted!');
				}
			}

			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Prepare the query to delete reactions
				$stmt = Model::getPdo()->query(
					'DELETE FROM convergence.c_post_reaction'
				);
				// If the query could not be executed
				if ($stmt === false) {
					// Throw an error that will be caught below
					throw new Exception('The query to delete post reactions could not be executed!');
				}

				// Prepare the query to delete posts
				$stmt = Model::getPdo()->query(
					'DELETE FROM convergence.c_post'
				);
				// If the query could not be executed
				if ($stmt === false) {
					// Throw an error that will be caught below
					throw new Exception('The query to delete posts could not be executed!');
				}

				// If deletion succeeded, return the number of deleted items
				return $stmt->rowCount();
			}
		} catch (Exception $e) {
			// If an error occurred
			// Log the error
			$e = new Exception('An error occurred while deleting the posts: ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
	// Delete a post
	public static function deletePost(int $postId): bool | Exception
	{
		// Try to delete the specified post
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded

				// Check if the post exists
				$post = self::selectPost($postId);
				// If an error occurred while retrieving the post
				if ($post instanceof Exception) {
					// Throw an error that will be caught below
					throw new Exception('The post could not be retrieved!');
				}
				// If the post exists
				if ($post) {

					// Recursively delete files related to posts if they exist and are directories
					if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId)) {
						Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId);
					}
					if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId)) {
						Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId);
					}
				} else {
					// If the post does not exist
					// Log the error
					Model::printLog('The post you want to delete does not exist!');
					// Return an error
					return new Exception('The post you want to delete does not exist!');
				}
				// Prepare the query to delete all reactions related to the post
				$stmt = Model::getPdo()->prepare(
					'DELETE FROM convergence.c_post_reaction WHERE convergence.c_post_reaction.id_post_reacted = :id_post;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query to delete the post reactions could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the post id to be deleted to the prepared query
				if (!Model::getStmt()->bindParam('id_post', $postId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The post id could not be attached as a parameter to the query!');
				}
				// Execute the query
				if (Model::getStmt()->execute() === false) {
					// If the query could not be executed
					// Throw an error that will be caught below
					throw new Exception('The query to delete the reactions could not be executed!');
				}

				// Prepare the query to delete the post
				$stmt = Model::getPdo()->prepare(
					'DELETE FROM convergence.c_post WHERE convergence.c_post.id_post = :id_post;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query to delete the post could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the post id to be deleted to the prepared query
				if (!Model::getStmt()->bindParam('id_post', $postId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The post id could not be attached as a parameter to the query!');
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
			$e = new Exception('An error occurred while deleting the post "' . $postId . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}

	// Delete reactions
	public static function deleteReaction(int $userId, int $postId): bool | Exception
	{
		// Try to delete the reaction to the specified post
		try {
			// If the connection could not be created
			if (is_null(Model::getPdo())) {
				// Throw an error that will be caught below
				throw new Exception('The connection to the database could not be established!');
			} else {
				// If the connection succeeded
				// Check if the post exists
				$post = self::selectPost($postId);
				// If an error occurred while retrieving the post
				if ($post instanceof Exception) {
					// Throw an error that will be caught below
					throw new Exception('The post could not be retrieved!');
				}
				// If the post does not exist
				if (!$post) {
					// If the post does not exist
					// Throw an error that will be caught below
					throw new Exception('The post does not exist');
				}

				// Prepare the query
				$stmt = Model::getPdo()->prepare(
					'DELETE FROM convergence.c_post_reaction 
						WHERE convergence.c_post_reaction.id_user_reacted = :id_user_reacted 
						AND convergence.c_post_reaction.id_post_reacted = :id_post_reacted;'
				);
				// If the query could not be prepared
				if (!$stmt) {
					// Throw an error that will be caught below
					throw new Exception('The query could not be prepared!');
				}
				// Set the query to be processed
				Model::setStmt($stmt);
				// Attach the user id whose reaction needs to be deleted to the prepared query
				if (!Model::getStmt()->bindParam('id_user_reacted', $userId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The user id could not be attached as a parameter to the prepared query!');
				}
				// Attach the post id whose reaction needs to be deleted to the prepared query
				if (!Model::getStmt()->bindParam('id_post_reacted', $postId, PDO::PARAM_INT)) {
					// If the parameter attachment failed
					// Throw an error that will be caught below
					throw new Exception('The post id could not be attached as a parameter to the prepared query');
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
			$e = new Exception('An error occurred while deleting the reaction to the post "' . $postId . '" by user "' . $userId . '": ' . $e->getMessage());
			Model::printLog(Model::getError($e));
			// Return the error
			return $e;
		}
	}
}