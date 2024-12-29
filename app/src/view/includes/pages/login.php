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
	?>
	<!-- Account creation form -->
	<div class="section-title">
		<h1>Create an account</h1>
		<hr>
	</div>
	<form method="POST" action="">
		<!-- Username -->
		<label for="fUserName">Username</label>
		<input type="text" name="fUserName" id="fUserName" placeholder="Username" required>
		<!-- Password -->
		<label for="fPass">Password</label>
		<input type="password" name="fPass" id="fPass" placeholder="Password" required>
		<!-- Password confirmation -->
		<label for="fPassConfirm">Confirm Password</label>
		<input type="password" name="fPassConfirm" placeholder="Confirm Password" required>
		<!-- Form submission button -->
		<input type="submit" name="fRegister" value="Register">
	</form>
	<!-- Login form -->
	<div class="section-title">
		<h1>Already have an account? Log in!</h1>
		<hr>
	</div>
	<form method="POST" action="">
		<!-- Username -->
		<label for="fUserName">Username</label>
		<input type="text" name="fUserName" id="fUserName" placeholder="Username" required>
		<!-- Password -->
		<label for="fPass">Password</label>
		<input type="password" name="fPass" id="fPass" placeholder="Password" required>
		<!-- Form submission button -->
		<input type="submit" name="fLogin" value="Log in">
	</form>
</div>