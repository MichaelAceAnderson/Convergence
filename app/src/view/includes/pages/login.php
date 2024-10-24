<div class="main">
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
	?>
	<!-- Formulaire de création de compte -->
	<div class="section-title">
		<h1>Créer un compte</h1>
		<hr>
	</div>
	<form method="POST" action="">
		<!-- Nom d'utilisateur -->
		<label for="fUserName">Nom d'utilisateur</label>
		<input type="text" name="fUserName" id="fUserName" placeholder="Nom d'utilisateur" required>
		<!-- Mot de passe -->
		<label for="fPass">Mot de passe</label>
		<input type="password" name="fPass" id="fPass" placeholder="Mot de passe" required>
		<!-- Confirmation du mot de passe -->
		<label for="fPassConfirm">Confirmation de mot de passe</label>
		<input type="password" name="fPassConfirm" placeholder="Confirmation du mot de passe" required>
		<!-- Bouton de soumission du formulaire -->
		<input type="submit" name="fRegister" value="S'enregistrer">
	</form>
	<!-- Formulaire de connexion -->
	<div class="section-title">
		<h1>Vous avez déjà un compte ? Connectez-vous !</h1>
		<hr>
	</div>
	<form method="POST" action="">
		<!-- Nom d'utilisateur -->
		<label for="fUserName">Nom d'utilisateur</label>
		<input type="text" name="fUserName" id="fUserName" placeholder="Nom d'utilisateur" required>
		<!-- Mot de passe -->
		<label for="fPass">Mot de passe</label>
		<input type="password" name="fPass" id="fPass" placeholder="Mot de passe" required>
		<!-- Bouton de soumission du formulaire -->
		<input type="submit" name="fLogin" value="Se connecter">
	</form>
</div>