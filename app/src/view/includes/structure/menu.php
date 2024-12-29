<nav>
	<div class="header">
		<img src="/img/c_logo_neon.svg" alt="logo" />
		<p>Convergence</p>
	</div>
	<ul>
		<a href="/">
			<li>
				<i class="fas fa-house"></i>
				<p>Home</p>
			</li>
		</a>
		<?php
		// If the user is not connected
		if (!UserController::userConnected()) {
		?>
			<a href="/?page=login">
				<li>
					<i class="fas fa-user"></i>
					<p>Login</p>
				</li>
			</a>
		<?php
		} else {
			// If the user is connected
			// go to the profile page
		?>
			<a href="/?page=profile">
				<li>
					<i class="fas fa-user"></i>
					<p>Profile</p>
				</li>
			</a>
		<?php
		}
		?>
		<a href="/?page=search">
			<li>
				<i class="fas fa-magnifying-glass"></i>
				<p>Search</p>
			</li>
		</a>
	</ul>
	<?php
	// If the user is connected
	if (UserController::userConnected()) {
		// Display the logout button
	?>
		<ul class="logout">
			<a>
				<li>
					<form method="POST" action="">
						<i class="fas fa-door-open"></i>
						<p><input type="submit" name="fLogOut" value="Logout"></p>
					</form>
				</li>
			</a>
		</ul>
	<?php
	}
	?>
</nav>
