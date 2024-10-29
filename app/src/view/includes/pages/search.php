<div class="main">
	<?php
	// Check if an error has been stored by the controller
	if (Controller::getState()['state'] == STATE_ERROR) {
		// If the controller has stored an error, display it
		echo '<h1 class="notification error">' . Controller::getState()['message'] . '</h1>';
	}
	// Check if a success message has been stored by the controller
	elseif (Controller::getState()['state'] == STATE_SUCCESS) {
		// If the controller has stored a success message, display it
		echo '<h1 class="notification success">' . Controller::getState()['message'] . '</h1>';
	}
	?>
	<!-- User search form -->
	<div class="section-title">
		<h1>Search for a user</h1>
		<hr>
	</div>
	<form action="" method="POST">
		<label for="fSearchUser">User's name:</label>
		<input type="search" name="fSearchUser" placeholder="User's name" />
		<input type="submit" name="fSearch" value="Search" />
	</form>

	<!-- Display search results -->
	<?php
	if (isset($_GET['user'])) {
		$usersTable = UserController::getUsersByName($_GET['user']);
		if (!$usersTable) {
			echo 'No users found';
		} else {
			echo "<table class='search-results'>";
			echo "<tr><th>Found users</th></tr>";
			foreach ($usersTable as $user) {
				echo '<tr>
					<td>
						<a href="/?page=profile&id=' . $user->id_user . '">
							<img src="' . ($user->p_img_url ?? '//img/profile.jpg') . '" alt="Avatar of ' . ($user->nickname ?? 'Unknown user') . '" />
							<p>' . $user->nickname . '</p>
						</a>
					</td>
				</tr>';
			}
			echo '</table>';
		}
	}
	?>
</div>