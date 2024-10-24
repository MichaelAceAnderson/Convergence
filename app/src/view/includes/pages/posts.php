<script>
    // Copier l'argument de la fonction dans le presse-papiers
    function copyToClipBoard(text) {
        // Créer un élément temporaire
        var tempElement = document.createElement('input');
        // Lui donner la valeur de l'argument de la fonction
        tempElement.value = text;
        // Ajouter l'élément temporaire au DOM
        document.body.appendChild(tempElement);
        // Sélectionner le contenu de l'élément temporaire
        tempElement.select();
        // Copier le contenu de l'élément temporaire dans le presse-papiers
        document.execCommand('copy');
        // Supprimer l'élément temporaire du DOM
        document.body.removeChild(tempElement);

        alert('Le lien a été copié dans le presse-papiers !');
    }
</script>
<div class="main">
    <?php
    // Vérifier si une erreur a été stockée par le contrôleur 
    if (Controller::getState()['state'] == STATE_ERROR) {
        // Si le contrôleur a stocké une erreur, l'afficher
        echo '<h1 class="notification error">' . Controller::getState()['message'] . '</h1>';
    }
    // Vérifier si une erreur a été stockée par le contrôleur 
    if (Controller::getState()['state'] == STATE_SUCCESS) {
        // Si le contrôleur a stocké une erreur, l'afficher
        echo '<h1 class="notification success">' . Controller::getState()['message'] . '</h1>';
    }

    // Stocker si l'utilisateur est connecté
    $userConnected = UserController::userConnected();
    if ($userConnected) {
        // Récupérer tous les posts des utilisateurs focalisés (suivis) par l'utilisateur connecté
        $posts = PostController::getFeedPostsById($_SESSION['user']['id_user']);
    } else {
        // Si l'utilisateur n'est pas connecté
        // Récupérer tous les posts
        $posts = PostController::getAllPosts();
    }
    // S'il n'y a aucun post
    if (!$posts) {
        // Afficher un message d'erreur
        echo '<h1 class="notification warning">Aucun post trouvé</h1>';
    } else {
        // S'il y a au moins un post
        echo '<div class="section-title">';
        // Si l'utilisateur est administrateur, afficher le bouton pour supprimer tous les posts
        echo '<h1>Posts</h1>';
        echo '<hr>';
        if ($userConnected && $posts && $_SESSION['user']['is_mod']) {
            echo '<form method="POST" action="">
                    <button type="submit" name="fDeleteAllPosts">Supprimer tous les posts</button>
                </form>';
        }
        echo '</div>';

        // Pour chaque post du tableau des posts récupérés en base de données
        foreach ($posts as $post) {
            // Récupérer les informations de l'auteur du post
            $authorInfo = UserController::getUserById($post->id_user_author);

            // Créer un conteneur pour le post et la réaction
            echo '<div class="post-block">';
            // Créer un conteneur de post
            echo '<div class="post" id="post_' . $post->id_post . '">';
            // Créer un conteneur pour les informations de l'auteur
            echo '<div class="author">';
            echo '<a href="/?page=profile&id=' . $authorInfo->id_user . '">';
            // S'il y a une photo de profil, l'afficher, sinon afficher celle par défaut
            echo '<img src="' . ($authorInfo->p_img_url ?? '//img/profile.jpg') . '"/>';
            // Récupérer & afficher le nom de l'auteur à partir de l'id de l'auteur
            echo '<p>' . '@' . $authorInfo->nickname . '</p>';
            echo '</a>';
            echo '</div>';

            // Créer un conteneur pour le contenu du post
            echo '<div class="content">';
            // S'il y a un media associé au post
            // Afficher le media associé au post
            if (!is_null($post->media_url)) {
                // Extensions possibles d'images
                $imageExtensions = array("jpeg", "jpg", "png", "bmp", "gif", "tif", "psd", "ai");
                // Extensions possibles de vidéos
                $videoExtensions = array(
                    "mp4", "m4v", "mov", "qt", "avi", "flv", "wmv", "asf", "mpeg", "mpg", "vob", "mkv", "asf", "rm", "rmvb"
                );
                $fileExtension = pathinfo($post->media_url, PATHINFO_EXTENSION);
                // Si l'url du média correspond à une image
                if (in_array($fileExtension, $imageExtensions)) {
                    // Afficher une image
                    echo ('<img id="post_' . $post->id_post . '" src="' . $post->media_url . '" alt="' . $post->content . '" title="' . $post->content . '" />');
                }
                // Si l'url du média correspond à une vidéo
                elseif (in_array($fileExtension, $videoExtensions)) {
                    // Afficher une vidéo
                    echo ('<video id="post_' . $post->id_post . '" src="' . $post->media_url . '" autoplay="" controls="" muted="" loop="" title="' . $post->content . '" />');
                }
            }
            // Afficher le contenu du post
            echo '<p>' . $post->content . '</p>';
            // Afficher la date de création du post
            echo '<p class="timestamp">' . $post->creation_date . '</p>';
            echo '</div>';
            echo '</div>';
            // Récupérer le tableau d'objets du nombre de réaction par type de réaction
            $likeCount = PostController::getReactionsCount($post->id_post, 1);
            $dislikeCount = PostController::getReactionsCount($post->id_post, 2);
            // Créer la barre d'interaction
            echo '<div class="interactions">';
            // Si l'utilisateur est connecté
            if ($userConnected) {
                // Récupérer les réactions du post
                $reactions = PostController::getReaction($_SESSION['user']['id_user'], $post->id_post);
                // S'il y a une image associée au post
                if (!is_null($reactions) && $reactions != false) {
                    if ($reactions->reaction_type == 1) {
                        // S'il y a une réaction de type 1 (converger/aimer)
    ?>
                        <form action="#post_<?php echo $post->id_post; ?>" method="POST">
                            <button type="submit" name="fPostReactionRemove" value="<?php echo $post->id_post; ?>">
                                <img src="/img/converge-solid-light.png" alt="Ne plus converger" title="Ne plus converger" />
                                <?php echo $likeCount; ?>
                            </button>
                        </form>
                        <form action="#post_<?php echo $post->id_post; ?>" method="POST">
                            <input type="hidden" name="fPostReactionPostId" value="<?php echo $post->id_post; ?>" />
                            <button type="submit" name="fPostReaction" value="2">
                                <img src="/img/diverge-light.png" alt="Diverger" title="Diverger" /> <?php echo $dislikeCount; ?>
                            </button>
                        </form>
                    <?php

                    } elseif ($reactions->reaction_type == 2) {
                        // S'il y a une réaction de type 1 (diverger/pas aimer)
                    ?>
                        <form action="#post_<?php echo $post->id_post; ?>" method="POST">
                            <input type="hidden" name="fPostReactionPostId" value="<?php echo $post->id_post; ?>" />
                            <button type="submit" name="fPostReaction" value="1">
                                <img src="/img/converge-light.png" alt="Converger" title="Converger" /> <?php echo $likeCount; ?>
                            </button>
                        </form>
                        <form action="#post_<?php echo $post->id_post; ?>" method="POST">
                            <button type="submit" name="fPostReactionRemove" value="<?php echo $post->id_post; ?>">
                                <img src="/img/diverge-solid-light.png" alt="Ne plus diverger" title="Ne plus diverger" />
                                <?php echo $dislikeCount; ?>
                            </button>
                        </form>
                    <?php

                    }
                } else {
                    // S'il n'y a pas de réaction
                    ?>
                    <!-- Like -->
                    <form action="#post_<?php echo $post->id_post; ?>" method="POST">
                        <input type="hidden" name="fPostReactionPostId" value="<?php echo $post->id_post; ?>" />
                        <button type="submit" name="fPostReaction" value="1">
                            <img src="/img/converge-light.png" alt="Converger" title="Converger" /> <?php echo $likeCount; ?>
                        </button>
                    </form>
                    <!-- Dislike -->
                    <form action="#post_<?php echo $post->id_post; ?>" method="POST">
                        <input type="hidden" name="fPostReactionPostId" value="<?php echo $post->id_post; ?>" />
                        <button type="submit" name="fPostReaction" value="2">
                            <img src="/img/diverge-light.png" alt="Diverger" title="Diverger" /> <?php echo $dislikeCount; ?>
                        </button>
                    </form>
                <?php
                }
            } else {
                // Si l'utilisateur n'est pas connecté
                ?>
                <img src="/img/converge-light.png" alt="Convergences" title="Convergences" /> <?php echo $likeCount; ?>
                <img src="/img/diverge-light.png" alt="Divergences" title="Divergences" /> <?php echo $dislikeCount; ?>
    <?php

            }
            // Si l'utilisateur connecté est l'auteur ou admin 
            if ($userConnected) {
                if (
                    $_SESSION['user']['id_user'] == $post->id_user_author || $_SESSION['user']['is_mod']
                ) {
                    // Afficher le bouton supprimer
                    echo '<form method="POST" action="">
                            <button type="submit" name="fDeletePostId" title="Supprimer" value="' . $post->id_post . '"><i class="fa fa-trash"></i></button>
                        </form>';
                }
            }
            // Partager le lien du post
            echo '<button>
            <i class="fa fa-share" title="Partager" onclick="copyToClipBoard(\'http://' . $_SERVER['SERVER_NAME'] . '/?page=profile&id=' . $post->id_user_author . '#post_' . $post->id_post . '\')"></i>
            </button>';
            echo '</div>';
            echo '</div>';
        }
    }

    ?>
</div>