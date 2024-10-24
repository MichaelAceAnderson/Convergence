<?php

// Si l'utilisateur n'utilise pas ce fichier dans un autre contexte
// que depuis la page index.php, le rediriger à l'accueil
if ($_SERVER['PHP_SELF'] != '/index.php') {
    echo '<meta http-equiv="refresh" content="0; url=/" />';
    header('Location: /');
    // Stopper l'exécution du script
    exit();
}

final class Install
{
    /* MÉTHODES */

    /* Insertions */
    // Installer la base de données
    public static function installDB(): bool | Exception
    {
        // Supprimer toutes les images de post dans le cas d'une réinstallation
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
            if (!Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR)) {
                // Si la suppression a échoué
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('Les images liées aux anciens posts n\'ont pas pu être supprimées !');
            }
        }

        // Supprimer toutes les vidéos de post dans le cas d'une réinstallation
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR) && is_dir($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
            if (!Model::rmdir_r($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'c_data' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'video' . DIRECTORY_SEPARATOR)) {
                // Si la suppression a échoué
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('Les vidéos liées aux anciens posts n\'ont pas pu être supprimées !');
            }
        }
        // Tenter d'installer la base de données
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La connexion avec la base de données n\'a pas pu être établie !');
            } else {
                // Si la connexion à réussi
                // Tenter d'installer la base de données via le fichier SQL

                // Récupérer le contenu du fichier SQL d'installation de la BDD
                $sqlFile = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'Convergence_mysql.sql');
                // Si le fichier n'a pas pu être lu
                if (!$sqlFile) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('Le fichier SQL d\'installation de la base de données n\'a pas pu être lu ! Vérifier que le fichier "Convergence_mysql.sql" est bien présent dans le dossier "install" avec des droits en lecture !');
                }
                // Exécuter le contenu du fichier SQL
                if (Model::getPdo()->exec($sqlFile) === false) {
                    // Si une erreur survient, on lance une exception qui sera attrapée plus bas
                    throw new Exception('Le fichier SQL d\'installation de la base de données n\'a pas pu être exécuté !');
                }

                // Si aucune erreur n'est survenue jusque là
                // On logge le succès
                Model::printLog('Installation de la base de données réussie !');
                // On renvoie un succès
                return true;
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('Impossible d\'installer la base de données: ' . $e->getMessage() . ' !');
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }

    /* Vérifications */
    // Vérifier si la base de données est installée
    public static function isDBInstalled(): bool | Exception
    {
        // Tenter d'installer la base de données
        try {
            // Si la connexion n'a pas pu être créée
            if (is_null(Model::getPdo())) {
                // On lance une erreur qui sera attrapée plus bas
                throw new Exception('La base de données n\'est pas accessible: Assurez vous d\'avoir une base de données MySQL nommée "' . DB_NAME . '" en cours d\'éxécution et accessible avec l\'utilisateur "' . DB_USER . '" et le mot de passe "' . DB_PASS . '" !');
            } else {
                // Si la connexion à réussi

                // Tenter de récupérer des données depuis chacune des tables

                // Vérifier que la table "c_user" existe
                $stmt = Model::getPdo()->query('SELECT 
                    EXISTS (
                        SELECT TABLE_NAME
                        FROM INFORMATION_SCHEMA.TABLES 
                        WHERE TABLE_SCHEMA = \'convergence\' 
                        AND TABLE_NAME = \'c_user\'
                        ) 
                        AS \'tableCount\';
                        ');
                if (!$stmt) {
                    // Si une erreur survient, on lance une exception qui sera attrapée plus bas
                    throw new Exception('La base de données n\'est pas correctement installée: La requête de vérification de l\'existence de la table utilisateurs n\'a pas pu être exécutée !');
                }

                // Définir le résultat à traiter
                Model::setStmt($stmt);

                // Récupérer le résultat
                $result = Model::getStmt()->fetch();
                // Si la table "c_user" n'existe pas
                if ($result->tableCount == 0) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La base de données n\'est pas correctement installée: La table "c_user" n\'existe pas !');
                }
                // Récupérer les données de la table "c_user"
                $stmt = Model::getPdo()->query('SELECT * FROM c_user WHERE is_mod = true');
                if (!$stmt) {
                    // Si une erreur survient, on lance une exception qui sera attrapée plus bas
                    throw new Exception('La base de données n\'est pas correctement installée: La requête de récupération des utilisateurs administrateurs n\'a pas pu être exécutée !');
                }

                // Définir le résultat à traiter
                Model::setStmt($stmt);

                // Récupérer les résultats
                $result = Model::getStmt()->fetchAll();
                // Si le tableau de résultat n'a pas au moins 1 élément
                if (count($result) < 1) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La base de données n\'est pas correctement installée: Aucun administrateur n\'a été trouvé !');
                }
                // S'il existe au moins 1 administrateur, continuer

                // Vérifier que la table "c_post" existe
                $stmt = Model::getPdo()->query('SELECT 
                    EXISTS (
                        SELECT TABLE_NAME
                        FROM information_schema.TABLES 
                        WHERE TABLE_SCHEMA = \'convergence\' 
                        AND TABLE_NAME = \'c_post\'
                        ) as \'tableCount\';
                        ');
                $stmt = $stmt->fetch();
                // S'il n'existe pas une table à ce nom
                if ($stmt->tableCount < 1) {
                    // On lance une erreur qui sera attrapée plus bas
                    throw new Exception('La base de données n\'est pas correctement installée: La table "c_post" n\'existe pas !');
                }

                // On logge le succès
                Model::printLog('Vérification de la base de données concluante !');
                // On renvoie un succès
                return true;
            }
        } catch (Exception $e) {
            // Si une erreur est survenue
            // On logge l'erreur
            $e = new Exception('La vérification de la base de données a échoué: ' . $e->getMessage() . ' !');
            Model::printLog(Model::getError($e));
            // On renvoie l'erreur
            return $e;
        }
    }
}
