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
	<!-- Formulaire de recherche d'utilisateurs -->
	<div class="section-title">
		<h1>Rechercher un utilisateur</h1>
		<hr>
	</div>
	<form action="" method="POST">
		<label for="fSearchUser">Nom de l'utilisateur:</label>
		<input type="search" name="fSearchUser" placeholder="Nom de l'utilisateur" />
		<input type="submit" name="fSearch" value="Rechercher" />
	</form>

	<!-- Affichage des résultats de la recherche -->
	<?php
	if (isset($_GET['user'])) {
		$usersTable = UserController::getUsersByName($_GET['user']);
		if (!$usersTable) {
			echo 'Aucun utilisateur trouvé';
		} else {
			echo "<table class='search-results'>";
			echo "<tr><th>Utilisateurs trouvés</th></tr>";
			foreach ($usersTable as $user) {
				echo '<tr>
					<td>
						<a href="/?page=profile&id=' . $user->id_user . '">
							<img src="' . ($user->p_img_url ?? '//img/profile.jpg') . '" alt="Avatar de ' . ($user->nickname ?? 'Utilisateur inconnu') . '" />
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