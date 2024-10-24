<?php

// Si l'utilisateur n'utilise pas ce fichier dans un autre contexte
// que depuis la page index.php, le rediriger à l'accueil
if ($_SERVER['PHP_SELF'] != '/index.php') {
    echo '<meta http-equiv="refresh" content="0; url=/" />';
    header('Location: /');
    // Stopper l'exécution du script
    exit();
}

class User
{
    /* MÉTHODES */

    /* Insertions */
    // Création d'un utilisateur en BDD
    public static function insertUser(string $nickname, string $password, bool $is_mod): bool | Exception
    {
        // Tenter d'ajouter un utilisateur
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'INSERT INTO convergence.c_user (nickname, password, is_mod) VALUES (:nickname, :password, :is_mod)'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher le pseudo en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('nickname', $nickname, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('Le pseudo n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Hasher le mot de passe (un échec lèvera une exception attrapée plus bas)
                $password = password_hash($password, PASSWORD_ARGON2ID);

                // Attacher le mot de passe en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('password', $password, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('Le mot de passe n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Attacher le booléen administrateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('is_mod', $is_mod, PDO::PARAM_BOOL)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('Le booléen administrateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si une erreur survient lors de l'exécution de la requête
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si insertion effectuée
                    if (Model::getStmt()->rowCount() > 0) {
                        // On renvoie un succès
                        return true;
                    } else {
                        // Si insertion pas effectuée
                        // On lance une erreur qui sera rattrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu insérer les données dans la base de données !');
                    }
                }
            }
        } catch (ValueError | Error $e) {
            // Si une erreur liée au hash de mot de passe est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de l\'insertion de l\'utilisateur "' . $nickname . '" dans la base de données avec le mot de passe "' . $password . '" (Admin: "' . $is_mod ? 'true' : 'false' . '"): Le mot de passe n\'a pas pu être hashé : '
                . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de l\'insertion de l\'utilisateur  "' . $nickname . '" dans la base de données avec le mot de passe "' . $password . '" (Admin: "' . $is_mod ? 'true' : 'false' . '"): '
                . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Ajouter un focalisateur à un utilisateur à partir de leurs id
    public static function insertFocuser(int $focuserId, int $focusedId): bool | Exception
    {
        // Tenter d'ajouter un focalisateur à un utilisateur
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'INSERT INTO convergence.c_focuser (id_user_focuser, id_user_focused) VALUES (:id_user_focuser, :id_user_focused)'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id de l'utilisateur qui suit en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_focuser', $focuserId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur suiveur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Attacher l'id de l'utilisateur focalisé en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_focused', $focusedId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur focalisé n\'a pas pu être attaché en paramètre à la requête préparée !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si une erreur survient lors de l'exécution de la requête
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si insertion effectuée
                    if (Model::getStmt()->rowCount() > 0) {
                        // On renvoie un succès
                        return true;
                    } else {
                        // Si insertion pas effectuée
                        // On lance une erreur qui sera rattrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu insérer les données dans la base de données !');
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de l\'insertion du focus de "' . $focuserId . '" sur l\'utilisateur "' . $focusedId . '" dans la base de données: '
                . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    /* Récupérations */
    // Récupérer le tableau des utilisateurs
    public static function selectUsers(): array | Exception
    {
        // Tenter de récupérer les utilisateurs
        try {
            if (is_null(Model::getPdo())) {
                // Si la connexion n'a pas pu être créée
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Exécuter la requête
                $stmt = Model::getPdo()->query(
                    'SELECT convergence.c_user.id_user, convergence.c_user.p_img_url, convergence.c_user.nickname, convergence.c_user.description, convergence.c_user.register_date, convergence.c_user.is_mod  
                    FROM convergence.c_user'
                );
                // Si la requête n'a pas pu être exécutée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                }
                // Définir le résultat à traiter
                Model::setStmt($stmt);

                // Récupérer les résultats
                $result = Model::getStmt()->fetchAll();
                // Si la récupération des résultats a échoué
                if ($result === false) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données dans la base de données !');
                } else {
                    // Si la récupération des résultats a réussi
                    // On renvoie les résultats
                    return $result;
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération des utilisateurs dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Récupérer un tableau d'utilisateurs à partir de leur pseudo
    public static function selectUsersByName(string $nickname): array | Exception
    {
        // Tenter de récupérer les utilisateurs à partir de leur pseudo
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'SELECT convergence.c_user.id_user, convergence.c_user.p_img_url, convergence.c_user.nickname, convergence.c_user.description, convergence.c_user.register_date, convergence.c_user.is_mod  
                    FROM convergence.c_user 
                    WHERE convergence.c_user.nickname
                    LIKE :nickname'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher le pseudo en paramètre à la requête préparée, formaté pour la recherche SQL avec LIKE
                $nickname = "%{$nickname}%";
                if (!Model::getStmt()->bindParam('nickname', $nickname, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('Le pseudo n\'a pas pu être attaché en paramètre à la requête préparée !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Récupérer les résultats
                    $result = Model::getStmt()->fetchAll();
                    // Si la récupération des résultats a échoué
                    if ($result === false) {
                        // On lance une erreur qui sera rattrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données dans la base de données !');
                    } else {
                        // Si la récupération des résultats a réussi
                        // On renvoie les résultats
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération des utilisateurs correspondant au pseudo "' . $nickname . '" dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Récupérer la ligne d'un utilisateur à partir de son id
    public static function selectUserById(int $userId): array | Exception
    {
        // Tenter de récupérer l'utilisateur à partir de son id
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'SELECT *
                    FROM convergence.c_user 
                    WHERE convergence.c_user.id_user = :id_user'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);

                // Attacher l'id utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user', $userId, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }

                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi
                    // Récupérer les résultats
                    $result = Model::getStmt()->fetchAll();
                    // Si la récupération des résultats a échoué
                    if ($result === false) {
                        // On lance une erreur qui sera rattrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données dans la base de données !');
                    } else {
                        // Si la récupération des résultats a réussi
                        // On renvoie les résultats
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération de l\'utilisateur correspondant à l\'id "' . $userId . '" dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Récupérer la ligne d'un utilisateur à partir de son pseudo
    public static function selectUserByName(string $nickname): object
    {
        // Tenter de récupérer l'utilisateur à partir de son pseudo
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    "SELECT *  
                    FROM convergence.c_user 
                    WHERE convergence.c_user.nickname = :nickname"
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher le pseudo utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('nickname', $nickname, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('Le pseudo utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi
                    // Récupérer les résultats
                    $result = Model::getStmt()->fetch();
                    // Si la récupération des résultats a échoué
                    if ($result === false) {
                        // On lance une erreur qui sera rattrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données dans la base de données !');
                    } else {
                        // Si la récupération des résultats a réussi
                        // On renvoie les résultats
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération de l\'utilisateur correspondant au pseudo "' . $nickname . '" dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Sélectionner tous les utilisateurs focalisés par un utilisateur à partir de son id
    public static function selectFocusedById(int $userId): array | Exception
    {
        // Tenter de récupérer tous les utilisateurs focalisé par un utilisateur à partir de son id
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    "SELECT convergence.c_user.id_user, convergence.c_user.nickname, convergence.c_user.p_img_url
                    FROM convergence.c_user 
                    INNER JOIN convergence.c_focuser 
                    ON convergence.c_user.id_user = convergence.c_focuser.id_user_focused
                    WHERE convergence.c_focuser.id_user_focuser = :id_user_focuser"
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_focuser', $userId, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réussi
                    // Récupérer les résultats
                    $result = Model::getStmt()->fetchAll();
                    // Si la récupération des résultats a échoué
                    if ($result === false) {
                        // On lance une erreur qui sera rattrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données dans la base de données !');
                    } else {
                        // Si la récupération des résultats a réussi
                        // On renvoie les résultats
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération des utilisateurs focalisés par l\'utilisateur correspondant à l\'id "' . $userId . '" dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Séléctionner tous les suiveurs d'un utilisateur à partir de son id
    public static function selectFocusersById(int $userId): array | Exception
    {
        // Tenter de récupérer tous les suiveurs d'un utilisateur à partir de son id
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    "SELECT convergence.c_user.id_user, convergence.c_user.nickname, convergence.c_user.p_img_url
                    FROM convergence.c_user 
                    INNER JOIN convergence.c_focuser 
                    ON convergence.c_user.id_user = convergence.c_focuser.id_user_focuser
                    WHERE convergence.c_focuser.id_user_focused = :id_user_focused"
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête de récupération n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_focused', $userId, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si la requête a réuss
                    // Récupérer les résultats
                    $result = Model::getStmt()->fetchAll();
                    // Si la récupération des résultats a échoué
                    if ($result === false) {
                        // On lance une erreur qui sera rattrapée plus bas
                        throw new Exception('La requête a été exécutée mais n\'a pas pu récupérer les données dans la base de données !');
                    } else {
                        // Si la récupération des résultats a réussi
                        // On renvoie les résultats
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Une erreur est survenue lors de la récupération des utilisateurs qui suivent l\'utilisateur correspondant à l\'id "' . $userId . '" dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    /* Modifications */
    // Changer de photo de profil
    public static function updateProfilePic(int $userId, string $media_url): bool | Exception
    {
        // Tenter d'ajouter une nouvelle photo
        try {
            // Si la connexion n'a pas pu être crée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à reussi 
                // Préparer la requête 
                $stmt = Model::getPdo()->prepare(('UPDATE convergence.c_user 
                    SET convergence.c_user.p_img_url = :p_img_url
                    WHERE convergence.c_user.id_user = :id_user'

                ));
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher le lien en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('p_img_url', $media_url, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('Le lien de la photo de profil n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Attacher l'id de l'utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user', $userId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Si la requête a pu être préparée
                // On lance la requête
                if (!Model::getStmt()->execute()) {
                    // Si une erreur survient lors de l'exécution de la requête
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si le changement a été effectuée
                    if (Model::getStmt()->rowCount() > 0) {
                        // On renvoie un succés
                        return true;
                    } else {
                        // Si le changement n'a pas été effectuée
                        // On retourne faux pour indiquer que le changement n'a pas été effectué, probablement car le lien est le même que celui déjà enregistré
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur 
            $e = new Exception('Une erreur est survenue lors du changement de la photo de profil de l\'utilisateur "' . $userId . '" en " ' . $media_url . ' " dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Changer de description
    public static function updateDescription(int $userId, string $description): bool | Exception
    {
        // Tenter de changer la description
        try {
            // Si la connexion n'a pas pu être crée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à reussi 
                // Préparer la requête 
                $stmt = Model::getPdo()->prepare(('UPDATE convergence.c_user 
                    SET convergence.c_user.description = :description
                    WHERE convergence.c_user.id_user = :id_user'

                ));
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas 
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher la description en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('description', $description, PDO::PARAM_STR)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas 
                    throw new Exception('La description n\'a pas pu être attachée en paramètre à la requête préparée !');
                }
                // Attacher l'id de l'utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user', $userId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas 
                    throw new Exception('L\'id de l\'utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Si la requête a pu être préparée
                // On lance la requête
                if (!Model::getStmt()->execute()) {
                    // Si une erreur survient lors de l'exécution de la requête
                    // On lance une erreur qui sera rattrapée plus bas 
                    throw new Exception('La requête n\'a pas pu être exécutée !');
                } else {
                    // Si le changement a été effectuée
                    if (Model::getStmt()->rowCount() > 0) {
                        // On renvoie un succés
                        return true;
                    } else {
                        // Si le changement n'a pas été effectuée
                        // On retourne faux pour indiquer que le changement n'a pas été effectué, probablement car la description est la même que celle déjà enregistrée
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur 
            $e = new Exception('Une erreur est survenue lors du changement de la description de l\'utilisateur "' . $userId . '" en " ' . $description . ' " dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    /* Supressions */
    // Supprimer un utilisateur
    public static function deleteUser(int $userId): bool | Exception
    {
        // Tenter de supprimer un utilisateur
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'DELETE FROM convergence.c_user 
                    WHERE convergence.c_user.id_user = :id;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête de n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id', $userId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id utilisateur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera rattrapée plus bas
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
            $e = new Exception('Une erreur est survenue lors de la suppression de l\'utilisateur correspondant à l\'id "' . $userId . '" dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    // Supprimer un focus
    public static function deleteFocus(int $focuserId, int $focusedId): bool | Exception
    {
        // Tenter de supprimer un focus
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera rattrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi

                // Préparer la requête
                $stmt = Model::getPdo()->prepare(
                    'DELETE FROM convergence.c_focuser 
                    WHERE convergence.c_focuser.id_user_focuser = :id_user_focuser 
                    AND convergence.c_focuser.id_user_focused = :id_user_focused;'
                );
                // Si la requête n'a pas pu être préparée
                if (!$stmt) {
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('La requête n\'a pas pu être préparée !');
                }
                // Définir la requête à traiter
                Model::setStmt($stmt);
                // Attacher l'id utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_focuser', $focuserId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur suiveur n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Attacher l'id utilisateur en paramètre à la requête préparée
                if (!Model::getStmt()->bindParam('id_user_focused', $focusedId, PDO::PARAM_INT)) {
                    // Si le paramètre n'a pas pu être attaché
                    // On lance une erreur qui sera rattrapée plus bas
                    throw new Exception('L\'id de l\'utilisateur focalisé n\'a pas pu être attaché en paramètre à la requête préparée !');
                }
                // Exécuter la requête
                if (Model::getStmt()->execute() === false) {
                    // Si la requête n'a pas pu être exécutée
                    // On lance une erreur qui sera rattrapée plus bas
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
            $e = new Exception('Une erreur est survenue lors de la suppression du focus de l\'utilisateur "' . $focuserId . '" sur l\'utilisateur "' . $focusedId . '" dans la base de données: ' . $e->getMessage());
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }
}
