<?php
/*
 * Script permettant de gérer l'upload des photos pour un élément de l'organnigramme données
 */

if(isset($_GET['upload_photo'])) {
    ob_get_clean(); // Suppression du flux généré auparavant

    if(isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] ==  'add')) {
        if( isset($_FILES['upload_photo']) ) { // si formulaire soumis
            $tmp_file = $_FILES['upload_photo']['tmp_name'];
            if( !is_uploaded_file($tmp_file) ) {
                exit("Le fichier est introuvable");
            }

            // on vérifie maintenant l'extension
            $type_file = $_FILES['upload_photo']['type'];

            if(!strstr($type_file, 'jpg') && !strstr($type_file, 'jpeg') && !strstr($type_file, 'bmp') && !strstr($type_file, 'gif')) {
                exit('<div class="error_up">Le fichier n\'est pas une images</div>');
            }

            // Génération d'un uniqid pour le nom du fichier
            $name_file = generate_name_photo();
            $tmp = explode('.', $_FILES['upload_photo']['name']);
            $name_file.='.'.end($tmp);

            // on copie le fichier dans le dossier de destination
            if( !move_uploaded_file($tmp_file, WP_GL_ORG_UPLOAD_PATH . $name_file) ) {
                exit('<div class="error_up">Impossible de copier le fichier</div>');
            }

            echo '<div class="path_photo">'.WP_GL_ORG_UPLOAD_PATH . $name_file.'</div>';
        }
        exit;
    }
    exit;
}

?>