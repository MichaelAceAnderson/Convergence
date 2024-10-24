<?php

// Si l'utilisateur n'utilise pas ce fichier dans un autre contexte
// que depuis la page index.php, le rediriger à l'accueil
if ($_SERVER['PHP_SELF'] != '/index.php') {
    echo '<meta http-equiv="refresh" content="0; url=/" />';
    header('Location: /');
    // Stopper l'exécution du script
    exit();
}

// Code s'appuyant sur le modèle et appelé par les formulaires des vues

class PostController
{
    /* MÉTHODES */

    /* Ajouts */
    // Ajouter un post 
    public static function addPost(int $authorId, string $content, ?string $mediaUrl): int
    {
        // On tente d'ajouter le post en base de données
        $result = Post::insertPost($authorId, $content, $mediaUrl);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la création du post par l\'utilisateur "' . $authorId . '" avec le contenu "' . $content . '" et le media à l\'URL ' . ($mediaUrl ?? '(aucune)') . '!');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On renvoie le succès/l'échec
            return $result;
        }
    }

    // Ajouter une reaction à un post
    public static function addReaction(int $userId, int $postId, int $reactionId): bool
    {
        // On tente d'ajouter la réaction en base de données
        $result = Post::insertReaction($userId, $postId, $reactionId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de l\'ajout de la réaction "' . $reactionId . '" au post "' . $postId . '" par l\'utilisateur "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le succès/l'échec
            return $result;
        }
    }

    /* Récupérations */
    // Récupérer un post
    public static function getPost(int $postId): array | false
    {
        // On tente de récupérer le post en base de données
        $result = Post::selectPost($postId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération du post "' . $postId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le résultat de la requête (tableau)
            return $result;
        }
    }

    // Récupérer les réactions d'un post
    public static function getReaction(int $userId, int $postId): object | false
    {
        // On tente de récupérer les réactions en base de données
        $result = Post::selectReaction($userId, $postId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération des réactions du post "' . $postId . '" pour l\'utilisateur "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le résultat de la requête (ligne de tableau)
            return $result;
        }
    }

    // Récupérer le nombre de réactions d'un post selon son id
    public static function getReactionsCount(int $postId, int $reactionType): int | false
    {
        // On tente de récupérer le nombre de réactions en base de données
        $result = Post::selectReactionsCount($postId, $reactionType);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération du nombre de réactions du post "' . $postId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le résultat de la requête (entier)
            return $result;
        }
    }

    // Récupérer tous les posts
    public static function getAllPosts(): array | false
    {
        // On tente de récupérer les posts en base de données
        $result = Post::selectPosts();
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération des posts !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le résultat de la requête (tableau)
            return $result;
        }
    }

    // Récupérer tous les posts d'un utilisateur à partir de son id
    public static function getPostsByUserId(int $userId): array | false
    {
        // On tente de récupérer les posts en base de données
        $result = Post::selectPostsByUserId($userId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération des posts de l\'utilisateur "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le résultat de la requête (tableau)
            return $result;
        }
    }
    // Récupérer tous les posts des utilisateurs suivis par un utilisateur à partir de son id
    public static function getFeedPostsById(int $userId): array | false
    {
        // On tente de récupérer les posts en base de données
        $result = Post::selectFeedPostsById($userId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération des posts des utilisateurs focalisés par l\'utilisateur "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le résultat de la requête (tableau)
            return $result;
        }
    }

    // Récupérer l'id du prochain post à créer
    public static function getNextPostId(): int | false
    {
        // On tente de récupérer l'id du prochain post à créer en base de données
        $result = Post::selectNextPostId();
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la récupération de l\'id du prochain post !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le résultat de la requête (id du prochain post)
            return $result;
        }
    }

    /* Suppressions */
    // Réinitialiser les posts
    public static function clearPosts(): int
    {
        // On tente de supprimer tous les posts en base de données et les fichiers associés
        $result = Post::deletePosts();
        // Si une erreur est survenue, on l'affiche et on la logge
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la suppression des posts !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On retourne -1 pour indiquer que la requête a échoué
            return -1;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le nombre de lignes supprimées (0 ou plus)
            return $result;
        }
    }

    // Supprimer un post 
    public static function removePost(int $postId): bool
    {
        // On tente de supprimer le post en base de données
        $result = Post::deletePost($postId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la suppression du post "' . $postId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le succès/l'échec selon le résultat
            return $result;
        }
    }

    // Supprimer la réaction d'un utilisateur sur un post donné
    public static function removeReaction(int $userId, int $postId): bool
    {
        // On tente de supprimer le post en base de données
        $result = Post::deleteReaction($userId, $postId);
        // Si une erreur est survenue lors de l'appel du modèle
        if ($result instanceof Exception) {
            // On définit l'erreur du contrôleur
            $result = new Exception('Une erreur est survenue lors de la suppression de la réaction du post "' . $postId . '" par l\'utilisateur "' . $userId . '" !');
            // On logge l'erreur
            Controller::printLog(Model::getError($result));
            // On renvoie un échec
            return false;
        } else {
            // Si l'opération a été effectuée en BDD
            // On retourne le succès/l'échec selon le résultat
            return $result;
        }
    }
}

/* GESTION DES REQUÊTES PAR FORMULAIRE */
// Si un formulaire de création de post est soumis
if (isset($_POST['fPost'])) {
    // On vérifie que le titre du post n'est pas vide
    if (!isset($_POST['fPostContent']) || empty($_POST['fPostContent'])) {
        // Si le contenu du post est vide
        // On affiche un message d'erreur
        Controller::setState(STATE_ERROR, 'Le contenu du post ne peut pas être vide !');
    } else {
        // Par défaut, l'URL du média associé au post est nulle
        $mediaUrl = null;
        // On récupère l'id du prochain post à créer
        $postId = PostController::getNextPostId();
        // Si une erreur est survenue lors de la récupération de l'id du prochain post
        if (!$postId) {
            // On affiche un message d'erreur
            Controller::setState(STATE_ERROR, 'Une erreur est survenue lors de la communication avec la base de données');
        } else {
            // Si un fichier a été uploadé
            if (!empty($_FILES) && $_FILES['fPostMedia']['error'] != UPLOAD_ERR_NO_FILE) {
                // Erreur éventuelle de l'upload
                $error = $_FILES['fPostMedia']['error'];
                // Si une erreur est survenue lors de l'upload
                if ($_FILES['fPostMedia']['error'] != UPLOAD_ERR_OK || !$_FILES['fPostMedia']['tmp_name']) {
                    // On stocke le message d'erreur à afficher
                    Controller::setState(STATE_ERROR, 'Erreur: Le fichier n\'a pas pu être uploadé');
                } elseif ((!preg_match('/video\//', $_FILES['fPostMedia']['type'])) && !preg_match('/image\//', $_FILES['fPostMedia']['type'])) {
                    // Si le fichier n'est pas une image ou une vidéo
                    // On stocke le message d'erreur à afficher
                    Controller::setState(STATE_ERROR, 'Votre fichier doit être une image ou une vidéo !');
                } elseif ($_FILES['fPostMedia']['size'] > 10000000) {
                    // Si la taille du fichier est supérieure à 10Mo
                    // On stocke le message d'erreur à afficher
                    Controller::setState(STATE_ERROR, 'Le fichier est trop volumineux !');
                } else {
                    if (preg_match('/image\//', $_FILES['fPostMedia']['type'])) {
                        // Si le fichier est une vidéo

                        // Si le dossier de stockage des posts n'existe pas
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR)) {
                            // Créer le dossier de stockage des post
                            mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR);
                        }
                        // Si le dossier de stockage des images de post n'existe pas
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
                            // Créer le dossier de stockage des images de post
                            mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
                        }
                        // Si le dossier de stockage des images de ce post n'existe pas
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId)) {
                            // Créer le dossier de stockage des images de ce post n'existe pas
                            mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId);
                        }
                        // On le place dans le dossier du post partie image
                        $mediaUrl = DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId . DIRECTORY_SEPARATOR . $_FILES['fPostMedia']['name'];
                    } elseif (preg_match('/video\//', $_FILES['fPostMedia']['type'])) {
                        // Si le fichier est une vidéo

                        // Si le dossier de stockage des images de post n'existe pas
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
                            // Le créer
                            mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR);
                        }
                        // On crée le dossier du post partie vidéo
                        mkdir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId);
                        // On le place dans le dossier du post partie vidéo
                        $mediaUrl = DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId . DIRECTORY_SEPARATOR . $_FILES['fPostMedia']['name'];
                    }
                    if (!move_uploaded_file($_FILES['fPostMedia']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $mediaUrl)) {
                        Controller::setState(STATE_ERROR, 'Impossible d\'uploader le fichier en raison d\'une erreur côté serveur');
                    }
                }
            }
        }
    }
    // S'il n'y a eu aucune erreur
    if (Controller::getState()['state'] != STATE_ERROR) {
        // On tente d'ajouter le post en base de données
        $postCreation = PostController::addPost($_SESSION['user']['id_user'], $_POST['fPostContent'], $mediaUrl);
        if (!$postCreation) {
            // Si une erreur survient, on stocke le message d'erreur à afficher
            Controller::setState(STATE_ERROR, 'Une erreur est survenue lors de l\'ajout du post');
        } else {
            // Si l'ajout du post s'est bien déroulé, on stocke le message de succès à afficher
            Controller::setState(STATE_SUCCESS, 'Le post a bien été ajouté !');
        }
    }
}

// Si un formulaire de suppression de tous les posts est soumis
if (isset($_POST['fClearPosts'])) {
    // On tente de supprimer tous les posts en base de données
    if (PostController::clearPosts() < 0) {
        // Sinon, on stocke le message d'erreur à afficher
        Controller::setState(STATE_ERROR, 'Une erreur est survenue lors de la suppression des posts');
    } else {
        // Si la suppression des posts s'est bien déroulée, on stocke le message de succès à afficher
        Controller::setState(STATE_SUCCESS, 'Tous les posts ont bien été supprimés !');
    }
}

// Si un formulaire de suppression de un post est soumis
if (isset($_POST['fDeletePostId'])) {
    // On tente de supprimer le post précisé en base de données
    if (!PostController::removePost($_POST['fDeletePostId'])) {
        // Sinon, on stocke le message d'erreur à afficher
        Controller::setState(STATE_ERROR, 'Une erreur est survenue lors de la suppression du post');
    } else {
        // Si la suppression du post s'est bien déroulée, on stocke le message de succès à afficher
        Controller::setState(STATE_SUCCESS, 'Le post a bien été supprimé !');
    }
}

// Si un formulaire de suppression de tous les posts est soumis
if (isset($_POST['fDeleteAllPosts'])) {
    // On tente de supprimer tous les posts en base de données
    if (PostController::clearPosts() < 0) {
        // Sinon, on stocke le message d'erreur à afficher
        Controller::setState(STATE_ERROR, 'Une erreur est survenue lors de la suppression des posts');
    } else {
        // Si la suppression des posts s'est bien déroulée, on stocke le message de succès à afficher
        Controller::setState(STATE_SUCCESS, 'Tous les posts ont bien été supprimés !');
    }
}

// Si un formulaire de suppression de réaction est soumis
if (isset($_POST['fPostReactionRemove'])) {
    // On tente de supprimer la réaction liée au post et l'utilisateur précisés en base de données
    if (!PostController::removeReaction($_SESSION['user']['id_user'], $_POST['fPostReactionRemove'])) {
        // Sinon, on stocke le message d'erreur à afficher
        Controller::setState(STATE_ERROR, 'Une erreur est survenue lors de la suppression de la reaction');
    } else {
        // Si la suppression de la réaction du post s'est bien déroulée, on stocke le message de succès à afficher
        Controller::setState(STATE_SUCCESS, 'La réaction a bien été supprimée !');
    }
}

// Si un formulaire d'ajout de réaction est soumis
if (isset($_POST['fPostReaction'])) {
    // On tente d'ajouter la réaction liée au post et l'utilisateur précisés en base de données
    if (!PostController::addReaction($_SESSION['user']['id_user'], $_POST['fPostReactionPostId'], $_POST['fPostReaction'])) {
        // Sinon, on stocke le message d'erreur à afficher
        Controller::setState(STATE_ERROR, 'Une erreur est survenue lors de l\'ajout de la reaction');
    } else {
        // Si l'ajout de la réaction du post s'est bien déroulée, on stocke le message de succès à afficher
        Controller::setState(STATE_SUCCESS, 'La réaction a bien été ajoutée !');
    }
}
