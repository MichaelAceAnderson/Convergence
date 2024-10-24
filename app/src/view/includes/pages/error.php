<?php if (!(isset($_GET['static']) && $_GET['static'] == false)) {
    header('Refresh:5; url=/');
    echo '<META http-equiv="refresh" content="5; URL=/">';
} ?>
<div class="main">
    <div class="content">
        <h1 class="notification error">
            <?php
            echo 'Erreur ' . $_GET['code'] . ' : <br>';
            switch ($_GET['code']) {
                case '400':
                    echo 'Échec de l\'analyse HTTP !';
                    break;

                case '401':
                    echo 'Le pseudo ou le mot de passe n\'est pas correct !';
                    break;

                case '402':
                    echo 'Le client doit reformuler sa demande avec les bonnes données de paiement!';
                    break;

                case '403':
                    echo 'Accès interdit !';
                    break;

                case '404':
                    echo 'La page n\'existe pas ou plus !';
                    break;

                case '405':
                    echo 'Méthode non autorisée!';
                    break;

                case '406':
                    echo 'La requête n\'a pas pu aboutir a temps !';
                    break;

                case '500':
                    echo 'Erreur interne au serveur ou serveur saturé !';
                    break;

                case '501':
                    echo 'Le serveur ne supporte pas le service demandé !';
                    break;

                case '502':
                    echo 'Mauvaise passerelle !';
                    break;

                case '503':
                    echo ' Service indisponible !';
                    break;

                case '504':
                    echo 'Trop de temps à la réponse !';
                    break;

                case '505':
                    echo 'Version HTTP non supportée! ';
                    break;

                default:
                    echo 'Erreur inconnue !';
                    break;
            }
            if (!(isset($_GET['static']) && $_GET['static'] == false)) {
                echo '<br>Vous allez être redirigé vers la page d\'accueil dans 5 secondes.';
            }

            ?>
        </h1>
    </div>
</div>