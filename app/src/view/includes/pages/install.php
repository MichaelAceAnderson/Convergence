<div class="main">
	<!-- <iframe width="50%" height="350px" src="" frameborder="0" allowFullScreen="">
		Bientôt » Vidéo d'installation de la base de données !
	</iframe> -->
	<?php
	// Vérifier si une erreur a été stockée par le contrôleur
	if (Controller::getState()['state'] == STATE_ERROR) {
		// Si le contrôleur a stocké une erreur, l'afficher
		echo '<h1 class="notification error">' . Controller::getState()['message'] . '</h1>';
	}
	// Vérifier si un succès a été stocké par le contrôleur
	elseif (Controller::getState()['state'] == STATE_SUCCESS) {
		// Si le contrôleur a stocké un succès, l'afficher
		echo '<h1 class="notification success">' . Controller::getState()['message'] . '</h1>';
	}

	// Si la connexion à la BDD n'a pas pu être établie
	if (Model::getPdo() == null) {
		// Afficher un message d'erreur
		echo '<h1 class="notification warning">Make sure you have a running MariaDB database named "'.DB_NAME.'" with the correct credentials configured !</h1>';
	} else {
		// Si la connexion à la BDD a pu être établie

		// Si la base de données n'est pas correctement installée
		if (!InstallController::isDBInstalled()) {
			// Afficher un message d'erreur
			echo '<h1 class="notification error">La base de données n\'est pas correctement installée !</h1>';
		} else {
			// Si la base de données est correctement installée
			// Si l'utilisateur n'est pas connecté ou n'est pas administrateur, et qu'il est sur la page d'installation
			if (!isset($_SESSION['user']) || $_SESSION['user']['is_mod'] != 1 && $_GET['page'] == 'install') {
				// Rediriger l'utilisateur vers la page d'accueil
				header('Location: /');
				exit();
			} else {
				// Afficher un message d'avertissement
				echo '<h1 class="notification warning">La base de données semble correctement installée ! 
			Utiliser ce formulaire supprimera toutes vos données et réinstallera la base de données à neuf !</h1>';
			}
		}
	?>

		<!-- Formulaire d'installation de la base de données -->
		<form method="POST" action="">
			<!-- Nom d'utilisateur -->
			<label for="fUserName">Nom d'utilisateur de la base de données</label>
			<input type="text" name="fUserName" placeholder="Nom d'utilisateur BDD" required>
			<!-- Mot de passe -->
			<label for="fPass">Mot de passe</label>
			<input type="password" name="fPass" placeholder="Mot de passe" required>
			<!-- Bouton de soumission -->
			<input type="submit" name="fInstall" value="Installer">
		</form>
	<?php
	}

	?>
</div>