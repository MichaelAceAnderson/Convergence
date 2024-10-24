<nav>
	<div class="header">
		<img src="/img/c_logo-neon.png" alt="logo" />
		<p>Convergence</p>
	</div>
	<ul>
		<a href="/">
			<li>

				<i class="fas fa-house"></i>
				<p>Accueil</p>

			</li>
		</a>
		<?php
		// Si l'utilisateur n'est pas connecté
		if (!UserController::userConnected()) {
		?>
			<a href="/?page=login">
				<li>
					<i class="fas fa-user"></i>
					<p>Connexion</p>
				</li>
			</a>
		<?php
		} else {
			// Si l'utilisateur est connecté	
			//on va sur la page de profil
		?>
			<a href="/?page=profile">
				<li>
					<i class="fas fa-user"></i>
					<p>Profil</p>
				</li>
			</a>
		<?php
		}
		?>
		<a href="/?page=search">
			<li>
				<i class="fas fa-magnifying-glass"></i>
				<p>Rechercher</p>
			</li>
		</a>
	</ul>
	<?php
	// Si l'utilisateur est connecté
	if (UserController::userConnected()) {
		//On fait apparaître le bouton de déconnexion
	?>
		<ul class="logout">
			<a>
				<li>
					<form method="POST" action="">
						<i class="fas fa-door-open"></i>
						<p><input type="submit" name="fLogOut" value="Déconnexion"></p>
					</form>
				</li>
			</a>
		</ul>
	<?php
	}
	?>
</nav>