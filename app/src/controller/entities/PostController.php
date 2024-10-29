<?php

// If the user is not using this file in a context other than from the index.php page, redirect to the homepage
if ($_SERVER['PHP_SELF'] != '/index.php') {
	echo '<meta http-equiv="refresh" content="0; url=/" />';
	header('Location: /');
	// Stop script execution
	exit();
}

// Code relying on the model and called by the views' forms

class PostController
{
	/* METHODS */

	/* Additions */
	// Add a post
	public static function addPost(int $authorId, string $content, ?string $mediaUrl): int
	{
		// Attempt to add the post to the database
		$result = Post::insertPost($authorId, $content, $mediaUrl);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while creating the post by user "' . $authorId . '" with content "' . $content . '" and media at URL ' . ($mediaUrl ?? '(none)') . '!');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return success/failure
			return $result;
		}
	}

	// Add a reaction to a post
	public static function addReaction(int $userId, int $postId, int $reactionId): bool
	{
		// Attempt to add the reaction to the database
		$result = Post::insertReaction($userId, $postId, $reactionId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while adding reaction "' . $reactionId . '" to post "' . $postId . '" by user "' . $userId . '" !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return success/failure
			return $result;
		}
	}

	/* Retrievals */
	// Retrieve a post
	public static function getPost(int $postId): array | false
	{
		// Attempt to retrieve the post from the database
		$result = Post::selectPost($postId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving post "' . $postId . '" !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the query result (array)
			return $result;
		}
	}

	// Retrieve the reactions of a post
	public static function getReaction(int $userId, int $postId): object | false
	{
		// Attempt to retrieve the reactions from the database
		$result = Post::selectReaction($userId, $postId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving reactions for post "' . $postId . '" by user "' . $userId . '" !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the query result (row of array)
			return $result;
		}
	}

	// Retrieve the number of reactions of a post by its id
	public static function getReactionsCount(int $postId, int $reactionType): int | false
	{
		// Attempt to retrieve the number of reactions from the database
		$result = Post::selectReactionsCount($postId, $reactionType);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving the number of reactions for post "' . $postId . '" !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the query result (integer)
			return $result;
		}
	}

	// Retrieve all posts
	public static function getAllPosts(): array | false
	{
		// Attempt to retrieve the posts from the database
		$result = Post::selectPosts();
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving the posts !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the query result (array)
			return $result;
		}
	}

	// Retrieve all posts of a user by their id
	public static function getPostsByUserId(int $userId): array | false
	{
		// Attempt to retrieve the posts from the database
		$result = Post::selectPostsByUserId($userId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving the posts of user "' . $userId . '" !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the query result (array)
			return $result;
		}
	}

	// Retrieve all posts of users followed by a user by their id
	public static function getFeedPostsById(int $userId): array | false
	{
		// Attempt to retrieve the posts from the database
		$result = Post::selectFeedPostsById($userId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving the posts of users followed by user "' . $userId . '" !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the query result (array)
			return $result;
		}
	}

	// Retrieve the id of the next post to create
	public static function getNextPostId(): int | false
	{
		// Attempt to retrieve the id of the next post to create from the database
		$result = Post::selectNextPostId();
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while retrieving the id of the next post !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return the query result (id of the next post)
			return $result;
		}
	}

	/* Deletions */
	// Reset posts
	public static function clearPosts(): int
	{
		// Attempt to delete all posts from the database and associated files
		$result = Post::deletePosts();
		// If an error occurred, display and log it
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while deleting the posts !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return -1 to indicate that the request failed
			return -1;
		} else {
			// If the operation was performed in the database
			// Return the number of deleted rows (0 or more)
			return $result;
		}
	}

	// Delete a post
	public static function removePost(int $postId): bool
	{
		// Attempt to delete the post from the database
		$result = Post::deletePost($postId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while deleting post "' . $postId . '" !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return success/failure based on the result
			return $result;
		}
	}

	// Delete a user's reaction to a given post
	public static function removeReaction(int $userId, int $postId): bool
	{
		// Attempt to delete the reaction from the database
		$result = Post::deleteReaction($userId, $postId);
		// If an error occurred during the model call
		if ($result instanceof Exception) {
			// Define the controller error
			$result = new Exception('An error occurred while deleting the reaction to post "' . $postId . '" by user "' . $userId . '" !');
			// Log the error
			Controller::printLog(Model::getError($result));
			// Return failure
			return false;
		} else {
			// If the operation was performed in the database
			// Return success/failure based on the result
			return $result;
		}
	}
}
/* FORM REQUEST HANDLING */
// If a post creation form is submitted
if (isset($_POST['fPost'])) {
	// Check that the post content is not empty
	if (!isset($_POST['fPostContent']) || empty($_POST['fPostContent'])) {
		// If the post content is empty
		// Display an error message
		Controller::setState(STATE_ERROR, 'The post content cannot be empty!');
	} else {
		// By default, the URL of the media associated with the post is null
		$mediaUrl = null;
		// Retrieve the id of the next post to create
		$postId = PostController::getNextPostId();
		// If an error occurred while retrieving the id of the next post
		if (!$postId) {
			// Display an error message
			Controller::setState(STATE_ERROR, 'An error occurred while communicating with the database');
		} else {
			// If a file has been uploaded
			if (!empty($_FILES) && $_FILES['fPostMedia']['error'] != UPLOAD_ERR_NO_FILE) {
				// Possible upload error
				$error = $_FILES['fPostMedia']['error'];
				// If an error occurred during the upload
				if ($_FILES['fPostMedia']['error'] != UPLOAD_ERR_OK || !$_FILES['fPostMedia']['tmp_name']) {
					// Store the error message to display
					Controller::setState(STATE_ERROR, 'Error: The file could not be uploaded');
				} elseif ((!preg_match('/video\//', $_FILES['fPostMedia']['type'])) && !preg_match('/image\//', $_FILES['fPostMedia']['type'])) {
					// If the file is not an image or a video
					// Store the error message to display
					Controller::setState(STATE_ERROR, 'Your file must be an image or a video!');
				} elseif ($_FILES['fPostMedia']['size'] > 10000000) {
					// If the file size is greater than 10MB
					// Store the error message to display
					Controller::setState(STATE_ERROR, 'The file is too large!');
				} else {
					if (preg_match('/image\//', $_FILES['fPostMedia']['type'])) {
						// If the file is an image

						// If the post storage folder does not exist
						if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR)) {
							// Create the post storage folder
							mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR);
						}
						// If the post image storage folder does not exist
						if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
							// Create the post image storage folder
							mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
						}
						// If the storage folder for this post's images does not exist
						if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId)) {
							// Create the storage folder for this post's images
							mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId);
						}
						// Place it in the post image folder
						$mediaUrl = DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId . DIRECTORY_SEPARATOR . $_FILES['fPostMedia']['name'];
					} elseif (preg_match('/video\//', $_FILES['fPostMedia']['type'])) {
						// If the file is a video

						// If the post video storage folder does not exist
						if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
							// Create it
							mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR);
						}
						// Create the post video folder
						mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId);
						// Place it in the post video folder
						$mediaUrl = DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId . DIRECTORY_SEPARATOR . $_FILES['fPostMedia']['name'];
					}
					if (!move_uploaded_file($_FILES['fPostMedia']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $mediaUrl)) {
						Controller::setState(STATE_ERROR, 'Unable to upload the file due to a server-side error');
					}
				}
			}
		}
	}
	// If there were no errors
	if (Controller::getState()['state'] != STATE_ERROR) {
		// Attempt to add the post to the database
		$postCreation = PostController::addPost($_SESSION['user']['id_user'], $_POST['fPostContent'], $mediaUrl);
		if (!$postCreation) {
			// If an error occurs, store the error message to display
			Controller::setState(STATE_ERROR, 'An error occurred while adding the post');
		} else {
			// If the post was successfully added, store the success message to display
			Controller::setState(STATE_SUCCESS, 'The post has been successfully added!');
		}
	}

	// If a form to delete all posts is submitted
	if (isset($_POST['fClearPosts'])) {
		// Attempt to delete all posts from the database
		if (PostController::clearPosts() < 0) {
			// Otherwise, store the error message to display
			Controller::setState(STATE_ERROR, 'An error occurred while deleting the posts');
		} else {
			// If the posts were successfully deleted, store the success message to display
			Controller::setState(STATE_SUCCESS, 'All posts have been successfully deleted!');
		}
	}

	// If a form to delete a post is submitted
	if (isset($_POST['fDeletePostId'])) {
		// Attempt to delete the specified post from the database
		if (!PostController::removePost($_POST['fDeletePostId'])) {
			// Otherwise, store the error message to display
			Controller::setState(STATE_ERROR, 'An error occurred while deleting the post');
		} else {
			// If the post was successfully deleted, store the success message to display
			Controller::setState(STATE_SUCCESS, 'The post has been successfully deleted!');
		}
	}

	// If a form to delete all posts is submitted
	if (isset($_POST['fDeleteAllPosts'])) {
		// Attempt to delete all posts from the database
		if (PostController::clearPosts() < 0) {
			// Otherwise, store the error message to display
			Controller::setState(STATE_ERROR, 'An error occurred while deleting the posts');
		} else {
			// If the posts were successfully deleted, store the success message to display
			Controller::setState(STATE_SUCCESS, 'All posts have been successfully deleted!');
		}
	}

	// If a form to delete a reaction is submitted
	if (isset($_POST['fPostReactionRemove'])) {
		// Attempt to delete the reaction related to the specified post and user from the database
		if (!PostController::removeReaction($_SESSION['user']['id_user'], $_POST['fPostReactionRemove'])) {
			// Otherwise, store the error message to display
			Controller::setState(STATE_ERROR, 'An error occurred while deleting the reaction');
		} else {
			// If the reaction was successfully deleted, store the success message to display
			Controller::setState(STATE_SUCCESS, 'The reaction has been successfully deleted!');
		}
	}

	// If a form to add a reaction is submitted
	if (isset($_POST['fPostReaction'])) {
		// Attempt to add the reaction related to the specified post and user to the database
		if (!PostController::addReaction($_SESSION['user']['id_user'], $_POST['fPostReactionPostId'], $_POST['fPostReaction'])) {
			// Otherwise, store the error message to display
			Controller::setState(STATE_ERROR, 'An error occurred while adding the reaction');
		} else {
			// If the reaction was successfully added, store the success message to display
			Controller::setState(STATE_SUCCESS, 'The reaction has been successfully added!');
		}
	}
}
