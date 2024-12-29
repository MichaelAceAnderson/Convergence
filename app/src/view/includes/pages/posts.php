<script>
	// Copy the function argument to the clipboard
	function copyToClipBoard(text) {
		// Create a temporary element
		var tempElement = document.createElement('input');
		// Set the value of the temporary element to the function argument
		tempElement.value = text;
		// Add the temporary element to the DOM
		document.body.appendChild(tempElement);
		// Select the content of the temporary element
		tempElement.select();
		// Copy the content of the temporary element to the clipboard
		document.execCommand('copy');
		// Remove the temporary element from the DOM
		document.body.removeChild(tempElement);

		alert('The link has been copied to the clipboard!');
	}
</script>
<div class="main">
	<?php
	// Check if an error has been stored by the controller
	if (Controller::getState()['state'] == STATE_ERROR) {
		// If the controller has stored an error, display it
		echo '<h1 class="notification error">' . Controller::getState()['message'] . '</h1>';
	}
	// Check if a success message has been stored by the controller
	if (Controller::getState()['state'] == STATE_SUCCESS) {
		// If the controller has stored a success message, display it
		echo '<h1 class="notification success">' . Controller::getState()['message'] . '</h1>';
	}

	// Store if the user is connected
	$userConnected = UserController::userConnected();
	if ($userConnected) {
		// Retrieve all posts from users followed by the connected user
		$posts = PostController::getFeedPostsById($_SESSION['user']['id_user']);
	} else {
		// If the user is not connected
		// Retrieve all posts
		$posts = PostController::getAllPosts();
	}
	// If there are no posts
	if (!$posts) {
		// Display an error message
		echo '<h1 class="notification warning">No posts found</h1>';
	} else {
		// If there is at least one post
		echo '<div class="section-title">';
		// If the user is an admin, display the button to delete all posts
		echo '<h1>Posts</h1>';
		echo '<hr>';
		if ($userConnected && $posts && $_SESSION['user']['is_mod']) {
			echo '<form method="POST" action="">
					<button type="submit" name="fDeleteAllPosts">Delete all posts</button>
				</form>';
		}
		echo '</div>';

		// For each post in the array of posts retrieved from the database
		foreach ($posts as $post) {
			// Retrieve the author's information
			$authorInfo = UserController::getUserById($post->id_user_author);

			// Create a container for the post and the reaction
			echo '<div class="post-block">';
			// Create a container for the post
			echo '<div class="post" id="post_' . $post->id_post . '">';
			// Create a container for the author's information
			echo '<div class="author">';
			echo '<a href="/?page=profile&id=' . $authorInfo->id_user . '">';
			// If there is a profile picture, display it, otherwise display the default one
			echo '<img src="' . ($authorInfo->p_img_url ?? '//img/profile.jpg') . '"/>';
			// Retrieve & display the author's name from the author's id
			echo '<p>' . '@' . $authorInfo->nickname . '</p>';
			echo '</a>';
			echo '</div>';

			// Create a container for the post content
			echo '<div class="content">';
			// If there is a media associated with the post
			// Display the media associated with the post
			if (!is_null($post->media_url)) {
				// Possible image extensions
				$imageExtensions = array("jpeg", "jpg", "png", "bmp", "gif", "tif", "psd", "ai");
				// Possible video extensions
				$videoExtensions = array(
					"mp4", "m4v", "mov", "qt", "avi", "flv", "wmv", "asf", "mpeg", "mpg", "vob", "mkv", "asf", "rm", "rmvb"
				);
				$fileExtension = pathinfo($post->media_url, PATHINFO_EXTENSION);
				// If the media URL corresponds to an image
				if (in_array($fileExtension, $imageExtensions)) {
					// Display an image
					echo ('<img id="post_' . $post->id_post . '" src="' . $post->media_url . '" alt="' . $post->content . '" title="' . $post->content . '" />');
				}
				// If the media URL corresponds to a video
				elseif (in_array($fileExtension, $videoExtensions)) {
					// Display a video
					echo ('<video id="post_' . $post->id_post . '" src="' . $post->media_url . '" autoplay="" controls="" muted="" loop="" title="' . $post->content . '" />');
				}
			}
			// Display the post content
			echo '<p>' . $post->content . '</p>';
			// Display the post creation date
			echo '<p class="timestamp">' . $post->creation_date . '</p>';
			echo '</div>';
			echo '</div>';
			// Retrieve the array of objects for the number of reactions by reaction type
			$likeCount = PostController::getReactionsCount($post->id_post, 1);
			$dislikeCount = PostController::getReactionsCount($post->id_post, 2);
			// Create the interaction bar
			echo '<div class="interactions">';
			// If the user is connected
			if ($userConnected) {
				// Retrieve the post reactions
				$reactions = PostController::getReaction($_SESSION['user']['id_user'], $post->id_post);
				// If there is an image associated with the post
				if (!is_null($reactions) && $reactions != false) {
					if ($reactions->reaction_type == 1) {
						// If there is a type 1 reaction (like)
	?>
						<form action="#post_<?php echo $post->id_post; ?>" method="POST">
							<button type="submit" name="fPostReactionRemove" value="<?php echo $post->id_post; ?>">
								<img src="/img/converge-solid-light.png" alt="Unlike" title="Unlike" />
								<?php echo $likeCount; ?>
							</button>
						</form>
						<form action="#post_<?php echo $post->id_post; ?>" method="POST">
							<input type="hidden" name="fPostReactionPostId" value="<?php echo $post->id_post; ?>" />
							<button type="submit" name="fPostReaction" value="2">
								<img src="/img/diverge-light.png" alt="Dislike" title="Dislike" /> <?php echo $dislikeCount; ?>
							</button>
						</form>
					<?php

					} elseif ($reactions->reaction_type == 2) {
						// If there is a type 2 reaction (dislike)
					?>
						<form action="#post_<?php echo $post->id_post; ?>" method="POST">
							<input type="hidden" name="fPostReactionPostId" value="<?php echo $post->id_post; ?>" />
							<button type="submit" name="fPostReaction" value="1">
								<img src="/img/converge-light.png" alt="Like" title="Like" /> <?php echo $likeCount; ?>
							</button>
						</form>
						<form action="#post_<?php echo $post->id_post; ?>" method="POST">
							<button type="submit" name="fPostReactionRemove" value="<?php echo $post->id_post; ?>">
								<img src="/img/diverge-solid-light.png" alt="Undislike" title="Undislike" />
								<?php echo $dislikeCount; ?>
							</button>
						</form>
					<?php

					}
				} else {
					// If there are no reactions
					?>
					<!-- Like -->
					<form action="#post_<?php echo $post->id_post; ?>" method="POST">
						<input type="hidden" name="fPostReactionPostId" value="<?php echo $post->id_post; ?>" />
						<button type="submit" name="fPostReaction" value="1">
							<img src="/img/converge-light.png" alt="Like" title="Like" /> <?php echo $likeCount; ?>
						</button>
					</form>
					<!-- Dislike -->
					<form action="#post_<?php echo $post->id_post; ?>" method="POST">
						<input type="hidden" name="fPostReactionPostId" value="<?php echo $post->id_post; ?>" />
						<button type="submit" name="fPostReaction" value="2">
							<img src="/img/diverge-light.png" alt="Dislike" title="Dislike" /> <?php echo $dislikeCount; ?>
						</button>
					</form>
				<?php
				}
			} else {
				// If the user is not connected
				?>
				<img src="/img/converge-light.png" alt="Likes" title="Likes" /> <?php echo $likeCount; ?>
				<img src="/img/diverge-light.png" alt="Dislikes" title="Dislikes" /> <?php echo $dislikeCount; ?>
	<?php

			}
			// If the connected user is the author or an admin
			if ($userConnected) {
				if (
					$_SESSION['user']['id_user'] == $post->id_user_author || $_SESSION['user']['is_mod']
				) {
					// Display the delete button
					echo '<form method="POST" action="">
							<button type="submit" name="fDeletePostId" title="Delete" value="' . $post->id_post . '"><i class="fa fa-trash"></i></button>
						</form>';
				}
			}
			// Share the post link
			echo '<button>
			<i class="fa fa-share" title="Share" onclick="copyToClipBoard(\'http://' . $_SERVER['SERVER_NAME'] . '/?page=profile&id=' . $post->id_user_author . '#post_' . $post->id_post . '\')"></i>
			</button>';
			echo '</div>';
			echo '</div>';
		}
	}

	?>
</div>