<div class="main">
    <?php
    // Si l'utilisateur n'est pas connecté
    if (!UserController::userConnected()) {
        // Rediriger sur la page de connexion/enregistrement
        echo '<h1 class="notification error">Vous devez être connecté pour accéder au profil d\'un utilisateur
        <br/>Vous allez être redirigé vers la page de connexion dans 5 secondes...</h1>';
        echo '<meta http-equiv="refresh" content="5; URL=/?page=login" />';
        header('Refresh:5; url=/?page=login');
    } else {
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
        // Si l'utilisateur est connecté 

        // Si l'utilisateur n'a spécifié aucun identifiant d'utilisateur
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            // Récupérer les informations de l'utilisateur connecté
            $userInfo = $_SESSION['user'];
        } else {
            // Récupérer les informations de l'utilisateur spécifié
            $userInfo = UserController::getUserById($_GET['id']);
            // Si l'utilisateur existe
            if ($userInfo) {
                // Convertir l'objet utilisateur en tableau d'informations
                $userInfo = (array) $userInfo;
            } else {
                // Si l'utilisateur n'existe pas
                $userInfo = null;
            }
        }
        if (is_null($userInfo)) {
            // Si l'utilisateur n'a pas pu être récupéré
            echo '<h1 class="notification error">L\'utilisateur dont vous souhaitez voir le profil n\'existe pas !</h1>';
        } else {
            // Si l'utilisateur a pu être récupéré

            // Récupérer les posts de l'utilisateur connecté
            $userPosts = PostController::getPostsByUserId($userInfo['id_user']);
            // Stocker si l'utilisateur connecté suit l'utilisateur dont on affiche le profil
            $isFocusing = UserController::userFocuses($_SESSION['user']['id_user'], $userInfo['id_user']);
            if (!$userPosts) {
                // Si aucun post n'a pu être récupéré
                $postsHTML = 'Aucun post trouvé pour cet utilsateur.';
            } else {
                // Stocker le contenu du post dans une variable HTML
                $postsHTML = '';
                // Pour chaque post de l'utilisateur
                foreach ($userPosts as $post) {
                    $postsHTML .= '<div class="post-block" id="post_' . $post->id_post . '">';
                    // Si l'utilisateur suit l'utilisateur dont on affiche le profil
                    if ($isFocusing) {
                        // Créer un lien vers le post original
                        $postsHTML .= '<a href="/#post_' . $post->id_post . '">';
                    } else {
                        // Si l'utilisateur ne suit pas l'utilisateur dont on affiche le profil
                        // Créer un lien vers l'image si elle existe, sinon, créer un lien vers le post en lui-même
                        $postsHTML .= ($post->media_url ? '<a href="' . $post->media_url . '" target=_blank> ' : '<a href="/?page=profile&id=' . $post->id_user_author . '#post_' . $post->id_post . '">');
                    }
                    // S'il y a un media associé au post
                    // Afficher le media associé au post
                    if (!is_null($post->media_url)) {
                        // Extensions possibles d'images
                        $imageExtensions = array("jpeg", "jpg", "png", "bmp", "gif", "tif", "psd", "ai");
                        // Extensions possibles de vidéos
                        $videoExtensions = array("mp4", "m4v", "mov", "qt", "avi", "flv", "wmv", "asf", "mpeg", "mpg", "vob", "mkv", "asf", "rm", "rmvb");
                        $fileExtension = pathinfo($post->media_url, PATHINFO_EXTENSION);
                        // Si l'url du média correspond à une image
                        if (in_array($fileExtension, $imageExtensions)) {
                            // Afficher une image
                            $postsHTML .= '<img src="' . $post->media_url . '" alt="' . $post->content . '" title="' . $post->content . '" />';
                        }
                        // Si l'url du média correspond à une vidéo
                        elseif (in_array($fileExtension, $videoExtensions)) {
                            // Afficher une vidéo
                            $postsHTML .= '<video src="' . $post->media_url . '" autoplay="" muted="" loop="" title="' . $post->content . '" />';
                        }
                    } else {
                        // S'il n'y a pas d'image
                        // Afficher le contenu textuel
                        $postsHTML .= '<p>' . $post->content . '</p>';
                    }
                    $postsHTML .= '</a>';
                    $postsHTML .= '</div>';
                }
            }
    ?>

            <!-- Afficher le profil de l'utilisateur -->
            <div class="section-title">
                <h1>Profil <?php
                            // Si l'utilisateur connecté est l'utilisateur dont on affiche le profil et qu'il n'affiche pas son profil public
                            if ($userInfo['id_user'] == $_SESSION['user']['id_user'] && !isset($_GET['id'])) {
                                // Ajouter un lien vers le profil public
                                echo '(<a href="/?page=profile&id=' . $userInfo['id_user'] . '">Voir mon profil public</a>)';
                            }
                            ?></h1>
                <hr>
            </div>
            <div class="profile">
                <div class="identity">
                    <?php
                    // Si l'utilisateur connecté est l'utilisateur dont on affiche le profil, et qu'il n'affiche pas son profil public
                    if ($userInfo['id_user'] == $_SESSION['user']['id_user'] && !isset($_GET['id'])) {
                        // Ajouter le formulaire de changement de photo de profil
                    ?>
                        <!-- Changer de photo de profil -->
                        <form method="post" enctype="multipart/form-data" action="" id="submitChangeProfilePic">
                            <label for="fProfilePic">
                                <img src="<?php echo $userInfo['p_img_url'] ?? '//img/profile.jpg'; ?>" />
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="fProfilePic" name="fProfilePic" style="display : none;" onchange="document.getElementById('submitChangeProfilePic').submit();">
                        </form>
                    <?php
                    } else {
                        // Si l'utilisateur connecté n'est pas l'utilisateur dont on affiche le profil, ou qu'il affiche son profil public
                        // Afficher la photo de profil
                    ?>
                        <img src="<?php echo $userInfo['p_img_url'] ?? '//img/profile.jpg'; ?>" />
                    <?php
                    }
                    ?>

                    <p>
                        <?php echo $userInfo['nickname'] ?? 'Utilisateur inconnu'; ?>
                    </p>
                    <?php
                    // Si l'utilisateur connecté est l'utilisateur dont on affiche le profil et qu'il n'affiche pas son profil public
                    if (isset($_SESSION['user']) && $_SESSION['user']['id_user'] == $userInfo['id_user'] && !isset($_GET['id'])) {
                        // Faire de la description un champ de saisie
                        echo '<form method="POST" action="">
                            <input type="text" name="fDescription" placeholder="' . ($userInfo['description'] ?? 'Ajoutez une description...') . '" value="' . ($userInfo['description'] ?? '') . '"/>
                            <button type="submit" name="fDescriptionChange"><i class="fas fa-user-edit"></i></button>
                        </form>';
                    } else {
                        // Si l'utilisateur connecté n'est pas l'utilisateur dont on affiche le profil, ou qu'il affiche son profil public
                        // Afficher la description
                        echo '<p>' . ($userInfo['description'] ?? 'Pas de description.') . '</p>';
                    }
                    ?>
                    <p>
                        Inscrit depuis:
                        <?php
                        setlocale(LC_TIME, "fr_FR");
                        $date = new DateTimeImmutable($userInfo['register_date']);
                        echo $date->format('m-Y');
                        ?>
                    </p>
                    <p title="Nombre de posts"><i class="fas fa-sticky-note"></i><?php echo count($userPosts); ?></p>
                    <p title="Nombre de focalisateurs"><i class="fa-solid fa-eye"></i>
                        <?php
                        $focusers = UserController::getFocusersById($userInfo['id_user']);
                        if (!$focusers) {
                            echo '0';
                        } else {
                            echo count($focusers);
                        }
                        ?>
                    </p>
                    <p title="Utilisateurs focalisés"><i class="fa fa-search"></i>
                        <?php
                        $focused = UserController::getFocusersById($userInfo['id_user']);
                        if (!$focused) {
                            echo '0';
                        } else {
                            echo count($focused);
                        }
                        ?>
                    </p>
                    <?php
                    // Si l'utilisateur suit ce profil
                    if ($isFocusing) {
                        // Afficher le bouton de désabonnement
                        echo '<form method="POST" action="">
                        <button type="submit" name="fUnFocusUser" value="' . $userInfo['id_user'] . '"><i class="fas fa-user-times"></i>Ne plus focaliser</button>
                        </form>';
                    } else {
                        // Afficher le bouton d'abonnement
                        echo '<form method="POST" action="">
                        <button type="submit" name="fFocus" value="' . $userInfo['id_user'] . '"><i class="fas fa-user-plus"></i>Focaliser</button>
                        </form>';
                    }
                    ?>
                </div>
                <div class="container">
                    <?php
                    // Afficher les posts stockés dans la variable HTML
                    echo $postsHTML;
                    ?>
                </div>
            </div>
            <?php
            // Si l'utilisateur connecté est l'utilisateur dont on affiche le profil, et qu'il n'affiche pas son profil public
            if ($userInfo['id_user'] == $_SESSION['user']['id_user'] && !isset($_GET['id'])) {
            ?>
                <!-- Ajouter un post textuel ou media -->
                <div class="post-add">
                    <h1>Ajouter un post</h1>
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Post textuel-->
                        <label for="fPostContent">Contenu du post :</label>
                        <textarea name="fPostContent" placeholder="Exprimez-vous..." required></textarea>
                        <!-- Fichier associé au post -->
                        <label for="fPostMedia">Ajouter une image ou video :</label>
                        <input type="file" name="fPostMedia" />

                        <input type="submit" name="fPost" value="Publier" />
                    </form>
                </div>
    <?php
            }
        }
    }
    ?>
</div>