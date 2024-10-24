<?php

// Si l'utilisateur n'utilise pas ce fichier dans un autre contexte
// que depuis la page index.php, le rediriger à l'accueil
if ($_SERVER['PHP_SELF'] != '/index.php') {
    echo '<meta http-equiv="refresh" content="0; url=/" />';
    header('Location: /');
    // Stopper l'exécution du script
    exit();
}

class UserController
{
    /* MÉTHODES */

    /* Ajouts */
    // Ajouter un utilisateur
    public static function addUser(string $nickname, string $password, bool $is_mod = false): bool
    {
        // On tente d'ajouter l'utilisateur en base de données
        $result = User::insertUser($nickname, $password, $is_mod);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de création de l\'utilisateur "' . $nickname . '" avec le mot de passe "' . $password . '" (admin: "' . ($is_mod ? 'true' : 'false') . '") !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On renvoie le succès/l'échec de l'opération
            return $result;
        }
    }

    // Ajouter un focus (suivre un utilisateur)
    public static function addFocus(int $focuserId, $focusedId): bool
    {
        // On tente d'ajouter l'utilisateur en base de données
        $result = User::insertFocuser($focuserId, $focusedId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de l\'ajout du focus de l\'utilisateur "' . $focuserId . '" sur l\'utilisateur "' . $focusedId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On renvoie le succès/l'échec de l'opération
            return $result;
        }
    }

    /* Vérifications */
    // Vérifier si l'utilisateur est connecté
    public static function userConnected(): int
    {
        // On vérifie si l'utilisateur est connecté
        if (isset($_SESSION['user']['id_user'])) {
            // Si l'utilisateur est connecté, on renvoie l'id de l'utilisateur
            return $_SESSION['user']['id_user'];
        } else {
            // Sinon, on renvoie 0
            return 0;
        }
    }

    // Vérifier si l'utilisateur suit un autre utilisateur
    public static function userFocuses(int $focuserId, int $focusedId): bool
    {
        // On tente de récupérer la liste des abonnements de l'utilisateur dans la base de données
        $result = User::selectFocusedById($focuserId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la vérification du focus de l\'utilisateur "' . $focuserId . '" sur l\'utilisateur "' . $focusedId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // S'il y a au moins un abonnement
            if (count($result) > 0) {
                // Pour chaque utilisateur focalisé
                foreach ($result as $focused) {
                    // Si cet utilisateur est celui présumément focalisé
                    if ($focused->id_user == $focusedId) {
                        // On renvoie vrai
                        return true;
                    }
                }
                // Si aucun utilisateur ne fait pas partie des abonnements de l'utilisateur
                // On renvoie faux
                return false;
            } else {
                // S'il n'y a pas d'abonnés
                // On renvoie faux
                return false;
            }
        }
    }

    /* Récupérations */
    // Récupérer un utilisateur par son id
    public static function getUserById(int $userId): object | false
    {
        // On tente de récupérer l'utilisateur en base de données
        $result = User::selectUserById($userId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération de l\'utilisateur avec l\'id "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // S'il y a un utilisateur
            if ($result) {
                // On renvoie l'utilisateur
                return $result[0];
            } else {
                // S'il n'y a pas d'utilisateur
                // On renvoie un échec
                return false;
            }
        }
    }

    // Récupérer tous les utilisateurs au nom similaire
    public static function getUsersByName(string $nickname): array|false
    {
        // On tente de récupérer les utilisateurs en base de données
        $result = User::selectUsersByName($nickname);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération des utilisateurs avec le nom "' . $nickname . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // S'il n'y a au moins un utilisateur
            if (count($result) > 0) {
                // On renvoie le tableau d'utilisateurs
                return $result;
            } else {
                // S'il n'y a pas d'utilisateur
                // On renvoie un échec
                return false;
            }
        }
    }

    // Récupérer un utilisateur à partir de son pseudo et de son mot de passe
    public static function getUserByCredentials(string $username, string $password): object | false
    {
        // On tente de récupérer l'utilisateur en base de données à partir de son pseudo
        $result = User::selectUserByName($username);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération des informations de l\'utilisateur "' . $username . '" avec le mot de passe "' . $password . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } elseif ($result) {
            // Si l'utilisateur existe

            // Check si $password correspond au mot de passe
            if (password_verify($password, $result->password)) {
                // Si les mots de passes correspondent
                // Renvoyer l'objet utilisateur
                return $result;
            } else {
                // Si le mot de passe ne correspond pas
                // On renvoie un échec
                Controller::printLog('L\'utilisateur "' . $username . '" a n\'a pas réussi à se connecter avec le mot de passe incorrect "' . $password . '" !');
                return false;
            }
        } else {
            Controller::printLog('L\'utilisateur "' . $username . '" n\'existe pas et n\'a donc pas pu se connecter avec le mot de passe "' . $password . '" !');
            // L'utilisateur n'existe pas
            // On renvoie un échec
            return false;
        }
    }

    // Récupérer les utilisateurs qui suivent l'utilisateur
    public static function getFocusersById(int $userId): array | false
    {
        // On tente de récupérer les utilisateurs en base de données à partir de l'id de l'utilisateur
        $result = User::selectFocusersById($userId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération des utilisateurs focalisés par l\'utilisateur "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // S'il n'y a au moins un utilisateur
            if (count($result) > 0) {
                // On renvoie le tableau d'utilisateurs
                return $result;
            } else {
                // S'il n'y a pas d'utilisateur
                // On renvoie un échec
                return false;
            }
        }
    }

    // Récupérer les utilisateurs que l'utilisateur suit
    public static function getFocusedById(int $userId): array | false
    {
        // On tente de récupérer les utilisateurs en base de données à partir de l'id de l'utilisateur
        $result = User::selectFocusedById($userId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération des utilisateurs qui suivent l\'utilisateur "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // S'il n'y a au moins un utilisateur
            if (count($result) > 0) {
                // On renvoie le tableau d'utilisateurs
                return $result;
            } else {
                // S'il n'y a pas d'utilisateur
                // On renvoie un échec
                return false;
            }
        }
    }

    /* Modifications */
    // Changer sa photo de profil
    public static function changeProfilePic(int $userId, string $mediaUrl): bool
    {

        // On tente de changer la photo
        $result = User::updateProfilePic($userId, $mediaUrl);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la modification de la photo de profil de l\'utilisateur "' . $userId . '" en "' . $mediaUrl . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // Si l'opération a réussi
            if ($result) {
                // On stocke la nouvelle image de profil dans la session
                $_SESSION['user']['p_img_url'] = $mediaUrl;
                // On renvoie un succès
                return true;
            } else {
                // On renvoie un échec
                return false;
            }
        }
    }

    // Changer sa description
    public static function changeDescription(int $userId, string $description): bool
    {
        // On tente de changer la description
        $result = User::updateDescription($userId, $description);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // Si l'opération a réussi
            if ($result) {
                // On stocke la nouvelle description dans la session
                $_SESSION['user']['description'] = $description;
                // On renvoie un succès
                return true;
            } else {
                // On renvoie un échec
                return false;
            }
        }
    }

    /* Suppressions */
    // Supprimer un utilisateur 
    public static function removeUser(int $userId): bool
    {
        // On tente de supprimer l'utilisateur en base de données à partir de son id
        $result = User::deleteUser($userId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la suppression de l\'utilisateur "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'utilisateur a bien été supprimé
            // On renvoie un succès/échec selon le résultat
            return $result;
        }
    }

    // Supprimer un focus
    public static function removeFocus(int $focuserId, int $focusedId): bool
    {
        // On tente de supprimer le focus en base de données à partir de son id
        $result = User::deleteFocus($focuserId, $focusedId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la suppression du focus de "' . $focuserId . '" sur l\'utilisateur "' . $focusedId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si le focus a bien été supprimé
            // On renvoie un succès/échec selon le résultat
            return $result;
        }
    }
}

/* GESTION DES REQUÊTES PAR FORMULAIRE */
// Si l'utilisateur soumet un formulaire de création de compte
if (isset($_POST['fRegister'])) {
    // Vérifier que les champs sont remplis
    if (
        !isset($_POST['fUserName']) || empty($_POST['fUserName'])
        || !isset($_POST['fPass']) || empty($_POST['fPass'])
        || !isset($_POST['fPassConfirm']) || empty($_POST['fPassConfirm'])
    ) {
        // Si les champs ne sont pas remplis, stocker l'erreur
        Controller::setState(STATE_ERROR, 'Veuillez remplir tous les champs.');
    } else {
        // Vérifier que le nom d'utilisateur ne dépasse pas 64 charactère 
        if (strlen($_POST['fUserName']) > 64) {
            // Si le nom d'utilisateur dépasse 64 charactère, stocker l'erreur
            Controller::setState(STATE_ERROR, 'Le nom d\'utilisateur ne doit pas dépasser 64 charactères.');
        }
        // Vérifier que les mots de passe correspondent
        if ($_POST['fPass'] === $_POST['fPassConfirm']) {
            // Tenter de créer l'utilisateur
            $result = UserController::addUser($_POST['fUserName'], $_POST['fPass']);
            if (!$result) {
                // Si l'utilisateur n'a pas été créé, stocker l'erreur
                Controller::setState(STATE_ERROR, 'Création du compte impossible, veuillez réessayer.');
            } else {
                // Si l'utilisateur a été créé, stocker le succès
                Controller::setState(STATE_SUCCESS, 'Création du compte réussie !');
            }
        } else {
            // Si les mots de passe ne correspondent pas, stocker l'erreur
            Controller::setState(STATE_ERROR, 'Les mots de passe ne correspondent pas.');
        }
    }
}

// Si l'utilisateur soumet un formulaire de connexion
if (isset($_POST['fLogin'])) {
    // Vérifier que les champs sont remplis
    if (
        !isset($_POST['fUserName']) || empty($_POST['fUserName'])
        || !isset($_POST['fPass']) || empty($_POST['fPass'])
    ) {
        // Si tous les champs ne sont pas remplis, stocker l'erreur
        Controller::setState(STATE_ERROR, 'Veuillez remplir tous les champs.');
    } else {
        // Si tous les champs sont remplis
        // Tenter de récupérer les informations de l'utilisateur
        $user = UserController::getUserByCredentials($_POST['fUserName'], $_POST['fPass']);
        if (!$user) {
            // Si l'utilisateur n'existe pas ou que le mot de passe n'est pas bon, stocker l'erreur
            Controller::printLog('L\'utilisateur "' . $_POST['fUserName'] . '" n\'a pas réussi à se connecter avec le mot de passe "' . $_POST['fPass'] . '" !');
            Controller::setState(STATE_ERROR, 'Connexion impossible, veuillez vérifier vos identifiants.');
        } else {
            // Si l'utilisateur existe et que le mot de passe est bon, stocker les informations dans la session
            $_SESSION['user'] = (array) $user;
            Controller::printLog('L\'utilisateur "' . $_POST['fUserName'] . '" a réussi à se connecter avec le mot de passe "' . $_POST['fPass'] . '" !');
            // Rediriger vers la page d'accueil
            header('Location: /');
            // Stopper l'exécution du script
            exit();
        }
    }
}
// Si l'utilisateur se déconnecte
if (isset($_POST['fLogOut'])) {
    // Retirer toutes les variables de la session
    unset($_SESSION['user']);
    // Détruire la session
    session_destroy();
    // Rediriger vers la page d'accueil
    header('Location: /');
    // Stopper l'exécution du script
    exit();
}

// Si l'utilisateur soumet un formulaire de recherche d'utilisateurs
if (isset($_POST['fSearch'])) {
    // Rediriger vers la page de recherche
    header('Location: /?page=search&user=' . $_POST['fSearchUser']);
    // Stopper l'exécution du script
    exit();
}

// Si l'utilisateur soumet un formulaire de focus d'un utilisateur
if (isset($_POST['fFocus'])) {
    // Tenter de focaliser l'utilisateur
    $result = UserController::addFocus($_SESSION['user']['id_user'], $_POST['fFocus']);
    if (!$result) {
        // Si l'utilisateur n'a pas été focalisé, stocker l'erreur
        Controller::setState(STATE_ERROR, 'Impossible de focaliser l\'utilisateur, veuillez réessayer.');
    } else {
        // Si l'utilisateur a été focalisé, stocker le succès
        Controller::setState(STATE_SUCCESS, 'Vous suivez maintenant l\'utilisateur !');
    }
}

// Si l'utilisateur soumet un formulaire d'unfocus d'un utilisateur
if (isset($_POST['fUnFocusUser'])) {
    // Tenter de ne plus focaliser l'utilisateur
    $result = UserController::removeFocus($_SESSION['user']['id_user'], $_POST['fUnFocusUser']);
    if (!$result) {
        // Si l'utilisateur n'a pas été focalisé, stocker l'erreur
        Controller::setState(STATE_ERROR, 'Impossible de ne plus défocaliser l\'utilisateur, veuillez réessayer.');
    } else {
        // Si l'utilisateur a été unfocus, stocker le succès
        Controller::setState(STATE_SUCCESS, 'Vous ne focalisez plus l\'utilisateur !');
    }
}

// Si un formulaire de changement de photo de profil est soumis
if (isset($_FILES['fProfilePic'])) {
    // Si aucun fichier n'a été uploadé
    if (empty($_FILES['fProfilePic']) || $_FILES['fProfilePic']['error'] == UPLOAD_ERR_NO_FILE) {
        // On stocke un message d'erreur
        Controller::setState(STATE_ERROR, 'Vous devez sélectionner un fichier de type image pour pouvoir changer de photo de profil !');
    } else {
        // Par défaut, l'URL du média associé au fichier est nulle
        $mediaUrl = null;
        // Si un fichier a été uploadé

        // Si une erreur est survenue lors de l'upload
        if ($_FILES['fProfilePic']['error'] != UPLOAD_ERR_OK || !$_FILES['fProfilePic']['tmp_name']) {
            // On stocke le message d'erreur à afficher
            Controller::setState(STATE_ERROR, 'Erreur: Le fichier n\'a pas pu être uploadé');
        } elseif (!preg_match('/image\//', $_FILES['fProfilePic']['type'])) {
            // Si le fichier n'est pas une image
            // On stocke le message d'erreur à afficher
            Controller::setState(STATE_ERROR, 'Votre fichier doit être une image !');
        } elseif ($_FILES['fProfilePic']['size'] > 10000000) {
            // Si la taille du fichier est supérieure à 10Mo
            // On stocke le message d'erreur à afficher
            Controller::setState(STATE_ERROR, 'Le fichier est trop volumineux !');
        } else {
            // Si le dossier de l'utilisateur n'existe pas
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR)) {
                // Créer le dossier de l'utilisateur
                mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR);
            }
            // Si le dossier de stockage des images de profil n'existe pas
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
                // Créer le dossier de stockage des images de profil
                mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
            }
            // On déclare l'url dans laquelle stocker l'image de profil
            $mediaUrl = DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $_SESSION['user']['id_user'] . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $_FILES['fProfilePic']['name'];

            if (!move_uploaded_file($_FILES['fProfilePic']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $mediaUrl)) {
                Controller::setState(STATE_ERROR, 'Impossible d\'uploader le fichier en raison d\'une erreur côté serveur');
            }
        }
        // S'il n'y a eu aucune erreur
        if (Controller::getState()['state'] != STATE_ERROR) {
            // On tente d'ajouter l'url du fichier en base de données
            $profilePicUpdate = UserController::changeProfilePic($_SESSION['user']['id_user'], $mediaUrl);
            if (!$profilePicUpdate) {
                // Si une erreur survient, on stocke le message d'erreur à afficher
                Controller::setState(STATE_ERROR, '⚠ Votre photo de profil était déjà enregistrée sous ce nom en base de données ! Les autres utilisateurs risquent de ne pas voir votre nouvelle photo de profil tout de suite...');
            } else {
                // Si le changement s'est bien déroulé, on stocke le message de succès à afficher
                Controller::setState(STATE_SUCCESS, 'La photo de profil a bien été changée !');
            }
        }
    }
}

// Si un formulaire de changement de description est soumis
if (isset($_POST['fDescription'])) {
    // On tente de changer la description
    $descriptionUpdate = UserController::changeDescription($_SESSION['user']['id_user'], $_POST['fDescription']);
    if (!$descriptionUpdate) {
        // Si une erreur survient, on stocke le message d'erreur à afficher
        Controller::setState(STATE_ERROR, '⚠ Votre description n\'a pas été modifiée !');
    } else {
        // Si le changement s'est bien déroulé, on stocke le message de succès à afficher
        Controller::setState(STATE_SUCCESS, 'La description a bien été changée !');
    }
}
