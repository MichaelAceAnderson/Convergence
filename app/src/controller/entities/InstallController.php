<?php

// Si l'utilisateur n'utilise pas ce fichier dans un autre contexte
// que depuis la page index.php, le rediriger à l'accueil
if ($_SERVER['PHP_SELF'] != '/index.php') {
    echo '<meta http-equiv="refresh" content="0; url=/" />';
    header('Location: /');
    // Stopper l'exécution du script
    exit();
}

class InstallController
{
    /* MÉTHODES */

    /* Ajouts */
    // Installer la base de données
    public static function installDB(string $adminName, string $adminPass): bool | Exception
    {
        // Création de la base de données
        $dbInstallStatus = Install::installDB();
        // Si l'installation de base de données a échoué
        if ($dbInstallStatus instanceof Exception) {
            // On renvoie l'erreur
            return $dbInstallStatus;
        } else {
            // Si l'installation en base de données a renvoyé une erreur
            if (!$dbInstallStatus) {
                // Si l'installation de la BDD a réussi mais qu'une erreur inconnue survient
                // On définit l'erreur à renvoyer
                $error = new Exception('Une erreur inattendue est survenue lors de l\'installation de la base de données (Administrateur: "' . $adminPass . '", Mot de passe admin: "' . $adminPass . '") !');
                // On logge l'erreur
                Controller::printLog(Model::getError($error));
                // On renvoie l'erreur
                return $error;
            } else {
                // Si l'installation de la base de données a réussi

                // Créer l'utilisateur principal, admin et propriétaire
                if (!UserController::addUser($adminName, $adminPass, true)) {
                    // Si l'utilisateur n'a pas pu être créé, on arrête l'installation
                    // On définit l'erreur à renvoyer
                    $error = new Exception('L\'utilisateur administrateur "' . $adminName . '" avec le mot de passe "' . $adminPass . '" n\'a pas pu être créé en base de données !');
                    // On logge l'erreur
                    Controller::printLog(Model::getError($error));
                    // On renvoie l'erreur
                    return $error;
                } else {
                    // Récupération de l'utilisateur admin, propriétaire du site
                    $adminAccount = UserController::getUserByCredentials($adminName, $adminPass);
                    if (!$adminAccount) {
                        // Si l'utilisateur admin n'a pas pu être récupéré en base de données
                        // On définit l'erreur à renvoyer
                        $error = new Exception('L\'utilisateur administrateur "' . $adminName . '" avec le mot de passe "' . $adminPass . '" n\'a pas pu être récupéré en base de données !');
                        // On logge l'erreur
                        Controller::printLog(Model::getError($error));
                        // On renvoie l'erreur
                        return $error;
                    } else {
                        // Si l'utilisateur admin a bien été récupéré en base de données
                        // Renvoyer un succès
                        return true;
                    }
                }
            }
        }
    }

    /* Vérifications */
    // Vérifier si la base de données est correctement installée
    public static function isDBInstalled(): bool
    {
        // Demander au modèle de vérifier l'installation de la base de données
        $result = Install::isDBInstalled();
        // Si une erreur survient lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('La base de données n\'est pas installée correctement !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si la BDD est installée et que toutes les infos nécessaires y sont présentes
            // On renvoie un succès
            return true;
        }
    }
}

/* GESTION DES REQUÊTES PAR FORMULAIRE */
// Si le formulaire d'installation a été soumis
if (isset($_POST['fInstall'])) {
    // Vérification des champs
    if (
        isset($_POST['fUserName']) && $_POST['fUserName'] != ''
        && isset($_POST['fPass']) && $_POST['fPass'] != ''
    ) {
        $installStatus = InstallController::installDB($_POST['fUserName'], $_POST['fPass']);
        if ($installStatus instanceof Exception) {
            // Si une erreur est survenue, on journalise le message d'erreur et on l'affiche à l'utilisateur
            Controller::printLog(Model::getError($installStatus));
            Controller::setState(STATE_ERROR, Model::getError($installStatus, HTML));
        } else {
            if ($installStatus) {
                // Si l'installation a bien réussi
                if (isset($_SESSION)) {
                    // Retirer toutes les variables de la session
                    unset($_SESSION);
                    // Détruire la session
                    session_destroy();
                }
                // On redirige vers l'accueil
                header('Location: /');
                // Stopper l'exécution du script
                exit();
            } else {
                // Ne devrait jamais arriver
                Controller::setState(STATE_ERROR, 'Une erreur est survenue lors de l\'installation de la base de données !');
            }
        }
    } else {
        Controller::setState(STATE_ERROR, 'Veuillez remplir tous les champs !');
    }
}
