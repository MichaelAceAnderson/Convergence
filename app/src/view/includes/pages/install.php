<div class="main">
	<?php
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

	// If the connection to the database could not be established
	if (Model::getPdo() == null) {
		// Display an error message
		echo '<h1 class="notification warning">Make sure you have a running MariaDB database named "'.DB_NAME.'" with the correct credentials configured!</h1>';
	} else {
		// If the connection to the database could be established

		// If the database is not properly installed
		if (!InstallController::isDBInstalled()) {
			// Display an error message
			echo '<h1 class="notification error">The database is not properly installed!</h1>';
		} else {
			// If the database is properly installed
			// If the user is not logged in or is not an administrator, and they are on the installation page
			if (!isset($_SESSION['user']) || $_SESSION['user']['is_mod'] != 1 && $_GET['page'] == 'install') {
				// Redirect the user to the home page
				header('Location: /');
				exit();
			} else {
				// Display a warning message
				echo '<h1 class="notification warning">The database seems to be properly installed! 
			Using this form will delete all your data and reinstall the database from scratch!</h1>';
			}
		}
	?>

		<!-- Database installation form -->
		<form method="POST" action="">
			<!-- Username -->
			<label for="fUserName">Database username</label>
			<input type="text" name="fUserName" placeholder="Database username" required>
			<!-- Password -->
			<label for="fPass">Password</label>
			<input type="password" name="fPass" placeholder="Password" required>
			<!-- Submit button -->
			<input type="submit" name="fInstall" value="Install">
		</form>
	<?php
	}

	?>
</div>