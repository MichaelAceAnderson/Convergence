<?php
// Inclusion du contrôleur qui inclut lui-même le reste du back-end
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'controller.php';

// Inclure la tête de la page
require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'structure' . DIRECTORY_SEPARATOR . 'head.php');
// Inclure le menu de navigation
require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'structure' . DIRECTORY_SEPARATOR . 'menu.php');

// Si la base de données n'est pas installée correctement
if (!InstallController::isDBInstalled()) {
	// Si l'utilisateur n'est pas sur la page d'installation
	if (!isset($_GET['page']) || $_GET['page'] != 'install') {
		// Rediriger vers la page d'installation
		header('Location: /?page=install');
		// Stopper l'exécution du script
		exit();
	}
}
if (isset($_GET['page'])) {
	// Si l'utilisateur demande une page en particulier
	// Si la page demandée existe
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $_GET['page'] . '.php')) {
		// Inclure la page demandée
		require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $_GET['page'] . '.php');
	} else {
		// Si la page demandée n'existe pas
		// Imiter le comportement d'un code d'erreur 404
		$_GET['code'] = '404';
		// Inclure la page de traitement des erreurs
		require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'error.php');
	}
} else {
	// Si l'utilisateur n'a pas demandé de page en particulier
	// Inclure la page d'accueil
	require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'posts.php');
}
// Inclure le pied de page
require_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'structure' . DIRECTORY_SEPARATOR . 'footer.php');
