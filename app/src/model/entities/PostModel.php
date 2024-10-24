<?php

// Si l'utilisateur n'utilise pas ce fichier dans un autre contexte
// que depuis la page index.php, le rediriger à l'accueil
if ($_SERVER['PHP_SELF'] != '/index.php') {
    echo '<meta http-equiv="refresh" content="0; url=/" />';
    header('Location: /');
    // Stopper l'exécution du script
    exit();
}

class Post
{
    /* MÉTHODES */

    /* Insertions */
    // Création d'un post en BDD
    public static function insertPost(int $authorId, string $content, ?string $mediaUrl = null): int | Exception
    {
        // Tenter d'ajouter le post en BDD
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'INSERT INTO convergence.c_post (content, id_user_author, media_url)
                    VALUES (:content, :id_user_author, :media_url);'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher le contenu du post en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('content', $content, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le contenu du post n\'a pas pu être attaché en paramètre à la requête !');
                }
                // Attacher l'id de l'auteur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_author', $authorId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id de l\'auteur du post n\'a pas pu être attaché en paramètre à la requête !');
                }
                // Attacher l'url du fichier attaché au post en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('media_url', $mediaUrl, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'url du fichier lié au post n\'a pas pu être attaché en paramètre à la requête !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si insertion effectuée
                    if (Model::getStmt()->rowCount() > 0) {
                        // Tenter de récupérer l'id du post
                        $result = self::selectNextPostId();
                        // Si l'id du post n'a pas pu être récupéré
                        if ($result instanceof Exception) {
                            // On lance une erreur qui sera attrapée plus bas
                            throw new Exception('L\'id du post inséré n\'a pas pu être récupéré !');
                        } else {
                            // Si l'id du post a pu être récupéré
                            // On renvoie l'id du post
                            return $result;
                        }
                    } else {
                        // Si insertion pas effectuée
                        // On lance une erreur qui sera attrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu insérer les données en base de données !');
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de l\'insertion du post par l\'utilisateur "' . $authorId . '" avec le contenu "' . $content . '" et le fichier média "' . $mediaUrl ?? '(aucun)' . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Ajouter des réactions
    public static function insertReaction(int $userId, int $postId, int $reactionId): bool | Exception
    {
        // Tenter d'ajouter une réaction
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // On vérifie si le post existe
                $post = self::selectPost($postId);
                // Si une erreur est survenue lors de la récupération du post
                if ($post instanceof Exception) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'a pas pu être récupéré !');
                }
                // Si le post n'existe pas
                if (!$post) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'existe pas !');
                }
                // Vérifier s'il existe déjà une réaction de l'utilisateur sur le post
                $reaction = self::selectReaction($userId, $postId);
                // Si une erreur est survenue lors de la récupération de la réaction
                if ($reaction instanceof Exception) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La vérification de l\'existence préalable d\'une réaction sur ce post a échoué !');
                }
                // Si une réaction existe déjà
                if ($reaction) {
                    // Supprimer la réaction
                    $result = self::deleteReaction($userId, $postId);
                    // Si une erreur est survenue lors de la suppression de la réaction
                    if ($result instanceof Exception) {
                        // On lance une erreur qui sera attrapée plus bas
                        throw new Exception('La réaction à remplacer n\'a pas pu être supprimée !');
                    }
                }

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'INSERT INTO convergence.c_post_reaction (id_user_reacted, id_post_reacted, reaction_type) 
                    VALUES (:id_user_reacted, :id_post_reacted, :reaction_type);'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id de l'utilisateur à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_reacted', $userId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Attacher l'id du post à la requête préparée
                if (!Model::getStmt()->bindParam('id_post_reacted', $postId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id du post n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Attacher l'id de la réaction à la requête préparée
                if (!Model::getStmt()->bindParam('reaction_type', $reactionId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id de la réaction n\'a pas pu être attaché en paramètre à la requête préparée !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si ajout effectué
                    if (Model::getStmt()->rowCount() > 0) {
                        // On renvoie un succès
                        return true;
                    } else {
                        // Si ajout pas effectué
                        // On lance une erreur qui sera attrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu insérer les données en base de données !');
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de l\'ajout de la réaction "' . $reactionId . '" par l\'utilisateur "' . $userId . '" sur le post "' . $postId . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    /* Séléctions */
    // Sélectionner le tableau des posts
    public static function selectPosts(): array | Exception
    {
        // Tenter de récupérer les posts
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'SELECT * FROM convergence.c_post ORDER BY creation_date DESC;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi, récupérer les résultats
                    $result = Model::getStmt()->fetchAll();
                    // Si les résultats n'ont pas pu être récupérés
                    if ($result === false) {
                        // On lance une erreur qui sera attrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données en base de données !');
                    } else {
                        // Si les résultats ont pu être récupérés
                        // On renvoie les résultats
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération des posts: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Sélectionner un tableau de posts en fonction de l'id de l'auteur
    public static function selectPostsByUserId(int $authorId): array | Exception
    {
        // Tenter de récupérer les posts
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'SELECT * FROM convergence.c_post WHERE id_user_author=:id_user_author ORDER BY creation_date DESC;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id de l'auteur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_author', $authorId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id de l\'auteur du post n\'a pas pu être attaché en paramètre à la requête préparée !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi, récupérer les résultats
                    $result = Model::getStmt()->fetchAll();
                    // Si les résultats n'ont pas pu être récupérés
                    if ($result === false) {
                        // On lance une erreur qui sera attrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données en base de données !');
                    } else {
                        // Si les résultats ont pu être récupérés
                        // On renvoie les résultats
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération des posts de l\'utilisateur "' . $authorId . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Sélectionner la ligne d'un seul post
    public static function selectPost(int $postId): array | Exception
    {
        // Tenter de récupérer le post spécifié
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    "SELECT convergence.c_post.id_user_author, convergence.c_user.nickname, convergence.c_post.content, convergence.c_post.media_url, convergence.c_post.creation_date
                    FROM convergence.c_post JOIN convergence.c_user
                    ON convergence.c_post.id_user_author=c_user.id_user
                    WHERE convergence.c_post.id_post=:id_post"
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id du post à la requête de récupération
                if (!Model::getStmt()->bindParam('id_post', $postId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Impossible d\'attacher l\'id "' . $postId . '" du post en paramètre à la requête de récupération du post !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi
                    if (Model::getStmt()->rowCount() > 0) {
                        // Récupérer les résultats
                        $result = Model::getStmt()->fetchAll();
                        // Si les résultats n'ont pas pu être récupérés
                        if ($result === false) {
                            // On lance une erreur qui sera attrapée plus bas
                            throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données en base de données !');
                        } else {
                            // Si les résultats ont pu être récupérés
                            // On renvoie les résultats
                            return $result;
                        }
                    } else {
                        // Si la requête a réussi mais qu'il n'y a pas de résultat
                        throw new Exception('Le post spécifié n\'existe pas !');
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération du post "' . $postId . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }
    // Sélectionner les posts des utilisateurs focalisés par l'utilisateur
    public static function selectFeedPostsById(int $userId): array | Exception
    {
        // Tenter de récupérer les posts des utilisateurs focalisés par l'utilisateur
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    "SELECT convergence.c_post.id_post, convergence.c_post.id_user_author, convergence.c_user.nickname, convergence.c_post.content, convergence.c_post.media_url, convergence.c_post.creation_date
                    FROM convergence.c_post JOIN convergence.c_user
                    ON convergence.c_post.id_user_author=c_user.id_user
                    WHERE convergence.c_post.id_user_author IN (
                        SELECT id_user_focused
                        FROM convergence.c_focuser
                        WHERE id_user_focuser=:id_user_focuser
                    )
                    ORDER BY convergence.c_post.creation_date DESC"
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id de l'utilisateur connecté en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_focuser', $userId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi
                    // Récupérer les résultats
                    $result = Model::getStmt()->fetchAll();
                    // Si les résultats n'ont pas pu être récupérés
                    if ($result === false) {
                        // On lance une erreur qui sera attrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données en base de données !');
                    } else {
                        // Si les résultats ont pu être récupérés
                        // On renvoie les résultats
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération des posts des utilisateurs focalisés par l\'utilisateur "' . $userId . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }


    // Sélectionner l'id du dernier post créé
    public static function selectNextPostId(): int | Exception
    {
        // Tenter de récupérer l'id du dernier post
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête
                $stmt = Model::getPdo()->query(
                    'SELECT `AUTO_INCREMENT`
                    FROM  INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_SCHEMA = \'convergence\'
                    AND   TABLE_NAME   = \'c_post\';'
                );
                // Si la requête n'a pas pu être exécutée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                }

                // Définir le résultat à traiter
                Model::setStmt($stmt);

                // Si la requête a réussi, récupérer les résultats
                $result = Model::getStmt()->fetch();
                // Si les résultats n'ont pas pu être récupérés
                if (is_null($result->AUTO_INCREMENT)) {
                    // On ne sait pas si c'est une erreur ou si le compteur n'est pas initialisé
                    // On retourne l'id 0 qui ne peut correspondre à aucun post, AUTO_INCREMENT démarrant à 1
                    return 0;
                } else {
                    // Si les résultats ont pu être récupérés
                    // On renvoie les résultats
                    return $result->AUTO_INCREMENT;
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération de l\'id du dernier post créé: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Sélectionner la réaction d'un utilisateur à partir de son id et de l'id du post
    public static function selectReaction(int $userId, int $postId): object | false
    {
        // Tenter de récupérer la réaction au post spécifié
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // On vérifie si le post existe
                $post = self::selectPost($postId);
                // Si une erreur est survenue lors de la récupération du post
                if ($post instanceof Exception) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'a pas pu être récupéré !');
                }
                // Si le post n'existe pas
                if (!$post) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'existe pas !');
                }

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'SELECT reaction_type 
                    FROM convergence.c_post_reaction    
                    WHERE convergence.c_post_reaction.id_user_reacted = :id_user_reacted
                    AND convergence.c_post_reaction.id_post_reacted = :id_post_reacted;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id de l'user à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_reacted', $userId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Attacher l'id du post à la requête préparée
                if (!Model::getStmt()->bindParam('id_post_reacted', $postId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id du post n\'a pas pu être attaché en paramètre à la requête préparée');
                }
                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi
                    if (Model::getStmt()->rowCount() > 0) {
                        // Récupérer les résultats
                        $result = Model::getStmt()->fetch();
                        // Si les résultats n'ont pas pu être récupérés
                        if ($result === false) {
                            // On lance une erreur qui sera attrapée plus bas
                            throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données dans la base de données !');
                        } else {
                            // Si les résultats ont pu être récupérés
                            // On renvoie les résultats
                            return $result;
                        }
                    } else {
                        // Si la requête a réussi mais qu'il n'y a pas de résultat
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération de la réaction du post "' . $postId . '" par l\'utilisateur "' . $userId . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Sélectionner le nombre de réactions d'un post à partir de son id
    public static function selectReactionsCount(int $postId, int $reactionId): int | Exception
    {
        // Tenter de récupérer le nombre de réactions au post spécifié
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // On vérifie si le post existe
                $post = self::selectPost($postId);
                // Si une erreur est survenue lors de la récupération du post
                if ($post instanceof Exception) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'a pas pu être récupéré !');
                }
                // Si le post n'existe pas
                if (!$post) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'existe pas !');
                }

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'SELECT COUNT(*) AS reactions_count
                    FROM convergence.c_post_reaction    
                    WHERE convergence.c_post_reaction.id_post_reacted = :id_post_reacted
                    AND convergence.c_post_reaction.reaction_type = :reaction_type;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id du post à la requête préparée
                if (!Model::getStmt()->bindParam('id_post_reacted', $postId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id du post n\'a pas pu être attaché en paramètre à la requête préparée');
                }
                // Attacher l'id de la réaction à la requête préparée
                if (!Model::getStmt()->bindParam('reaction_type', $reactionId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id de la réaction n\'a pas pu être attaché en paramètre à la requête préparée');
                }
                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi

                    // Récupérer les résultats
                    $result = Model::getStmt()->fetch();
                    // Si les résultats n'ont pas pu être récupérés
                    if ($result === false) {
                        // On lance une erreur qui sera attrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données dans la base de données !');
                    } else {
                        // Si les résultats ont pu être récupérés
                        // On renvoie les résultats
                        return $result->reactions_count;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération du nombre de réactions du post "' . $postId . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    /* Suppressions */
    // Suppression de tous les posts en BDD
    public static function deletePosts(): int | Exception
    {
        // Tenter de supprimer les posts
        try {
            // Supprimer de façon récursive les vidéos liées aux posts si elles existent
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
                if (!Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
                    // Si la suppression des fichiers a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Les vidéos liées aux posts n\'ont pas pu être supprimées !');
                }
            }
            // Supprimer de façon récursive les images liées aux posts si elles existent
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img') && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
                if (!Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
                    // Si la suppression des fichiers a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Les images liées aux posts n\'ont pas pu être supprimées !');
                }
            }

            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête de suppression des réactions
                $stmt = Model::getPdo()->query(
                    'DELETE FROM convergence.c_post_reaction'
                );
                // Si la requête n'a pas pu être exécutée
                if ($stmt === false) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête de suppression des réactions des posts n\'a pas pu être exécutée !');
                }

                // Préparer la requête de suppression des posts
                $stmt = Model::getPdo()->query(
                    'DELETE FROM convergence.c_post'
                );
                // Si la requête n'a pas pu être exécutée
                if ($stmt === false) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête de suppression des posts n\'a pas pu être exécutée !');
                }

                // Si suppression effectuée, renvoyer le nombre d'éléments supprimés
                return $stmt->rowCount();
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la suppression des posts: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Supprimer un post
    public static function deletePost(int $postId): bool | Exception
    {
        // Tenter de supprimer le post spécifié
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // On vérifie si le post existe
                $post = self::selectPost($postId);
                // Si une erreur est survenue lors de la récupération du post
                if ($post instanceof Exception) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'a pas pu être récupéré !');
                }
                // Si le post existe
                if ($post) {

                    // Supprimer de façon récursive les fichiers liés aux posts s'ils existent et sont des dossiers
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId)) {
                        Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR . $postId);
                    }
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId)) {
                        Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $postId);
                    }
                } else {
                    // Si le post n'existe pas
                    // On logge l'erreur
                    Model::printLog('Le post que vous souhaitez supprimer n\'existe pas !');
                    // On renvoie une erreur
                    return new Exception('Le post que vous souhaitez supprimer n\'existe pas !');
                }
                // Préparer la requête pour supprimer toutes les réactions liées au post
                $stmt = Model::getPdo()->prepare(
                    'DELETE FROM convergence.c_post_reaction WHERE convergence.c_post_reaction.id_post_reacted = :id_post;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête de suppression des réactions du post n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id du post à supprimer à la requête préparée
                if (!Model::getStmt()->bindParam('id_post', $postId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id du post n\'a pas pu être attaché en paramètre à la requête !');
                }
                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête de suppression des réactions n\'a pas pu être exécutée !');
                }

                // Préparer la requête pour supprimer le post
                $stmt = Model::getPdo()->prepare(
                    'DELETE FROM convergence.c_post WHERE convergence.c_post.id_post = :id_post;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête de suppression du post n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id du post à supprimer à la requête préparée
                if (!Model::getStmt()->bindParam('id_post', $postId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id du post n\'a pas pu être attaché en paramètre à la requête !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si suppression effectuée
                    if (Model::getStmt()->rowCount() > 0) {
                        // On renvoie un succès
                        return true;
                    } else {
                        // Si suppression pas effectuée
                        // On retourne faux pour indiquer que la suppression n'a pas été effectuée
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la suppression du post "' . $postId . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Supprimer les réactions
    public static function deleteReaction(int $userId, int $postId): bool | Exception
    {
        // Tenter de supprimer la réaction au post spécifié
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // On vérifie si le post existe
                $post = self::selectPost($postId);
                // Si une erreur est survenue lors de la récupération du post
                if ($post instanceof Exception) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'a pas pu être récupéré !');
                }
                // Si le post n'existe pas
                if (!$post) {
                    // Si le post n'existe pas
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le post n\'existe pas');
                }

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'DELETE FROM convergence.c_post_reaction 
                        WHERE convergence.c_post_reaction.id_user_reacted = :id_user_reacted 
                        AND convergence.c_post_reaction.id_post_reacted = :id_post_reacted;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id de l'utilisateur dont il faut supprimer la réaction à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_reacted', $userId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Attacher l'id du post dont il faut supprimer la réaction à la requête préparée
                if (!Model::getStmt()->bindParam('id_post_reacted', $postId, PDO::PARAM_INT)) {
                    // Si l'attache du paramètre a échoué
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('L\'id du post n\'a pas pu être attaché en paramètre à la requête préparée');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si suppression effectuée
                    if (Model::getStmt()->rowCount() > 0) {
                        // On renvoie un succès
                        return true;
                    } else {
                        // Si suppression pas effectuée
                        // On retourne faux pour indiquer que la suppression n'a pas été effectuée
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la suppression de la réaction du post "' . $postId . '" par l\'utilisateur "' . $userId . '": ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }
}
