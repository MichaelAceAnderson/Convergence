<div class="main">
	<?php
	// If the user is not connected
	if (!UserController::userConnected()) {
		// Redirect to the login/registration page
		echo '<h1 class="notification error">You must be logged in to access a user profile
		<br/>You will be redirected to the login page in 5 seconds...</h1>';
		echo '<meta http-equiv="refresh" content="5; URL=/?page=login" />';
		header('Refresh:5; url=/?page=login');
	} else {
		// Check if an error has been stored by the controller
		if (Controller::getState()['state'] == STATE_ERROR) {
			// If the controller has stored an error, display it
			echo '<h1 class="notification error">' . Controller::getState()['message'] . '</h1>';
		}
		// Check if a success has been stored by the controller
		elseif (Controller::getState()['state'] == STATE_SUCCESS) {
			// If the controller has stored a success, display it
			echo '<h1 class="notification success">' . Controller::getState()['message'] . '</h1>';
		}
		// If the user is connected 

		// If the user has not specified any user ID
		if (!isset($_GET['id']) || empty($_GET['id'])) {
			// Retrieve the connected user's information
			$userInfo = $_SESSION['user'];
		} else {
			// Retrieve the specified user's information
			$userInfo = UserController::getUserById($_GET['id']);
			// If the user exists
			if ($userInfo) {
				// Convert the user object to an information array
				$userInfo = (array) $userInfo;
			} else {
				// If the user does not exist
				$userInfo = null;
			}
		}
		if (is_null($userInfo)) {
			// If the user could not be retrieved
			echo '<h1 class="notification error">The user whose profile you want to view does not exist!</h1>';
		} else {
			// If the user could be retrieved

			// Retrieve the posts of the connected user
			$userPosts = PostController::getPostsByUserId($userInfo['id_user']);
			// Store if the connected user follows the user whose profile is displayed
			$isFocusing = UserController::userFocuses($_SESSION['user']['id_user'], $userInfo['id_user']);
			if (!$userPosts) {
				// If no post could be retrieved
				$postsHTML = 'No posts found for this user.';
			} else {
				// Store the post content in an HTML variable
				$postsHTML = '';
				// For each post of the user
				foreach ($userPosts as $post) {
					$postsHTML .= '<div class="post-block" id="post_' . $post->id_post . '">';
					// If the user follows the user whose profile is displayed
					if ($isFocusing) {
						// Create a link to the original post
						$postsHTML .= '<a href="/#post_' . $post->id_post . '">';
					} else {
						// If the user does not follow the user whose profile is displayed
						// Create a link to the image if it exists, otherwise, create a link to the post itself
						$postsHTML .= ($post->media_url ? '<a href="' . $post->media_url . '" target=_blank> ' : '<a href="/?page=profile&id=' . $post->id_user_author . '#post_' . $post->id_post . '">');
					}
					// If there is a media associated with the post
					// Display the media associated with the post
					if (!is_null($post->media_url)) {
						// Possible image extensions
						$imageExtensions = array("jpeg", "jpg", "png", "bmp", "gif", "tif", "psd", "ai");
						// Possible video extensions
						$videoExtensions = array("mp4", "m4v", "mov", "qt", "avi", "flv", "wmv", "asf", "mpeg", "mpg", "vob", "mkv", "asf", "rm", "rmvb");
						$fileExtension = pathinfo($post->media_url, PATHINFO_EXTENSION);
						// If the media URL corresponds to an image
						if (in_array($fileExtension, $imageExtensions)) {
							// Display an image
							$postsHTML .= '<img src="' . $post->media_url . '" alt="' . $post->content . '" title="' . $post->content . '" />';
						}
						// If the media URL corresponds to a video
						elseif (in_array($fileExtension, $videoExtensions)) {
							// Display a video
							$postsHTML .= '<video src="' . $post->media_url . '" autoplay="" muted="" loop="" title="' . $post->content . '" />';
						}
					} else {
						// If there is no image
						// Display the textual content
						$postsHTML .= '<p>' . $post->content . '</p>';
					}
					$postsHTML .= '</a>';
					$postsHTML .= '</div>';
				}
			}
	?>

			<!-- Display the user's profile -->
			<div class="section-title">
				<h1>Profile <?php
							// If the connected user is the user whose profile is displayed and they are not viewing their public profile
							if ($userInfo['id_user'] == $_SESSION['user']['id_user'] && !isset($_GET['id'])) {
								// Add a link to the public profile
								echo '(<a href="/?page=profile&id=' . $userInfo['id_user'] . '">View my public profile</a>)';
							}
							?></h1>
				<hr>
			</div>
			<div class="profile">
				<div class="identity">
					<?php
					// If the connected user is the user whose profile is displayed, and they are not viewing their public profile
					if ($userInfo['id_user'] == $_SESSION['user']['id_user'] && !isset($_GET['id'])) {
						// Add the profile picture change form
					?>
						<!-- Change profile picture -->
						<form method="post" enctype="multipart/form-data" action="" id="submitChangeProfilePic">
							<label for="fProfilePic">
								<img src="<?php echo $userInfo['p_img_url'] ?? '//img/profile.jpg'; ?>" />
								<i class="fas fa-camera"></i>
							</label>
							<input type="file" id="fProfilePic" name="fProfilePic" style="display : none;" onchange="document.getElementById('submitChangeProfilePic').submit();">
						</form>
					<?php
					} else {
						// If the connected user is not the user whose profile is displayed, or they are viewing their public profile
						// Display the profile picture
					?>
						<img src="<?php echo $userInfo['p_img_url'] ?? '//img/profile.jpg'; ?>" />
					<?php
					}
					?>

					<p>
						<?php echo $userInfo['nickname'] ?? 'Unknown user'; ?>
					</p>
					<?php
					// If the connected user is the user whose profile is displayed and they are not viewing their public profile
					if (isset($_SESSION['user']) && $_SESSION['user']['id_user'] == $userInfo['id_user'] && !isset($_GET['id'])) {
						// Make the description an input field
						echo '<form method="POST" action="">
							<input type="text" name="fDescription" placeholder="' . ($userInfo['description'] ?? 'Add a description...') . '" value="' . ($userInfo['description'] ?? '') . '"/>
							<button type="submit" name="fDescriptionChange"><i class="fas fa-user-edit"></i></button>
						</form>';
					} else {
						// If the connected user is not the user whose profile is displayed, or they are viewing their public profile
						// Display the description
						echo '<p>' . ($userInfo['description'] ?? 'No description.') . '</p>';
					}
					?>
					<p>
						Registered since:
						<?php
						setlocale(LC_TIME, "fr_FR");
						$date = new DateTimeImmutable($userInfo['register_date']);
						echo $date->format('m-Y');
						?>
					</p>
					<p title="Number of posts"><i class="fas fa-sticky-note"></i><?php echo count($userPosts); ?></p>
					<p title="Number of followers"><i class="fa-solid fa-eye"></i>
						<?php
						$focusers = UserController::getFocusersById($userInfo['id_user']);
						if (!$focusers) {
							echo '0';
						} else {
							echo count($focusers);
						}
						?>
					</p>
					<p title="Users followed"><i class="fa fa-search"></i>
						<?php
						$focused = UserController::getFocusersById($userInfo['id_user']);
						if (!$focused) {
							echo '0';
						} else {
							echo count($focused);
						}
						?>
					</p>
					<?php
					// If the user follows this profile
					if ($isFocusing) {
						// Display the unfollow button
						echo '<form method="POST" action="">
						<button type="submit" name="fUnFocusUser" value="' . $userInfo['id_user'] . '"><i class="fas fa-user-times"></i>Unfollow</button>
						</form>';
					} else {
						// Display the follow button
						echo '<form method="POST" action="">
						<button type="submit" name="fFocus" value="' . $userInfo['id_user'] . '"><i class="fas fa-user-plus"></i>Follow</button>
						</form>';
					}
					?>
				</div>
				<div class="container">
					<?php
					// Display the posts stored in the HTML variable
					echo $postsHTML;
					?>
				</div>
			</div>
			<?php
			// If the connected user is the user whose profile is displayed, and they are not viewing their public profile
			if ($userInfo['id_user'] == $_SESSION['user']['id_user'] && !isset($_GET['id'])) {
			?>
				<!-- Add a textual or media post -->
				<div class="post-add">
					<h1>Add a post</h1>
					<form method="POST" enctype="multipart/form-data">
						<!-- Textual post-->
						<label for="fPostContent">Post content:</label>
						<textarea name="fPostContent" placeholder="Express yourself..." required></textarea>
						<!-- File associated with the post -->
						<label for="fPostMedia">Add an image or video:</label>
						<input type="file" name="fPostMedia" />

						<input type="submit" name="fPost" value="Publish" />
					</form>
				</div>
	<?php
			}
		}
	}
	?>
</div>