<?php

// Si l'utilisateur n'utilise pas ce fichier dans un autre contexte
// que depuis la page index.php, le rediriger à l'accueil
if ($_SERVER['PHP_SELF'] != '/index.php') {
    echo '<meta http-equiv="refresh" content="0; url=/" />';
    header('Location: /');
    // Stopper l'exécution du script
    exit();
}

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'model.php';


// Si la session du client n'est pas démarrée
if (!isset($_SESSION)) {
    // Démarrer la session
    session_start();
}

// Code s'appuyant sur le modèle et appelé par les formulaires des vues

class Controller
{
    /* PROPRIÉTÉS/ATTRIBUTS */
    // État de la dernière action effectuée
    private static int $state = STATE_NONE;
    // Message de la dernière action effectuée
    private static string $message = '';

    /* MÉTHODES */
    // Écrire dans un fichier log
    public static function printLog(string $msg): bool
    {
        $date = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $date = $date->format('d-m-y H:i:s');
        if (LOGLEVEL < 1) {
            // Si le niveau de log est inférieur à 1, on ne logge pas
            return false;
        }
        $logFile = fopen($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "c_data". DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'controller.log', 'a+');
        if (!$logFile) {
            // S'il est impossible d'ouvrir le fichier de log
            return false;
        }
        if (!fwrite($logFile, PHP_EOL . '[' . $date . '] Contrôleur: ' . $msg)) {
            // S'il est impossible d'écrire dans le fichier de log
            return false;
        }
        if (!fclose($logFile)) {
            // S'il est impossible de fermer le fichier de log
            return false;
        }
        return true;
    }

    // Définir l'état à afficher dans les vues
    public static function setState(int $state, string $message): void
    {
        self::$state = $state;
        self::$message = $message;
    }

    // Récupérer l'état de la dernière action effectuée
    public static function getState(): array
    {
        return array('state' => self::$state, 'message' => self::$message);
    }
}
/// NOTE: Il n'est pas possible d'inclure via un foreach, il faut suivre l'ordre selon l'interdépendance des contrôleurs/données

// Inclure le contrôleur d'utilisateurs
require_once __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'UserController.php';
// Inclure le contrôleur d'installation de la base de données
require_once __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'InstallController.php';
// Inclure le contrôleur de posts
require_once __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'PostController.php';
