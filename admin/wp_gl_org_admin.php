<?php
global $wpdb;

if(!empty($_POST)) {
    inititial_post();
}

//Mise en place de l'interface d'administration
$flux ='<div class="wrap">';

if(!defined(WP_GL_ORG_BASE_URL)) {
    define('WP_GL_ORG_BASE_URL', menu_page_url('organigramme', FALSE));
}

if(empty($_GET['action'])) {

    // Récuperation des organigrammes mis en places
    $sql = "SELECT * FROM ".$wpdb->prefix.WP_GL_ORG_TABLE_ORGANIGRAMME." ";
    $list_org = $wpdb->get_results($sql);

    // Definition des actions possibles pour la liste
    $list_actions = array(
        'edit' => __('Modifier'),
        'del'  => __('Suprrimer'),
        'view' => __('Afficher'),
    );

    $flux.='<h2>'.__('Organigramme').'<a href="'.WP_GL_ORG_BASE_URL.'&action=add" class="add-new-h2">'.__('Ajouter').'</a></h2>';

    // Gestion des messages d'informations
    if(isset($_GET['register'])) {
        $flux.='<div id="message" class="updated below-h2"><p>'.__('Enregistrement effectué.').'</p></div>';
    }

    if(isset($_GET['deleted'])) {
        $flux.='<div id="message" class="updated below-h2"><p>'.__('Element supprimé.').'</p></div>';
    }

    // Dans le cas ou nous n'avons encore aucun organigramme
    if(!empty($list_org)) {
        $flux.='<table class="wp-list-table widefat fixed posts" cellspacing="0">';
        $flux.='<thead><tr>';
        $flux.='    <th scope="col" id="title" class="manage-column">'.__('Titre').'</th>';
        $flux.='    <th scope="col" id="title" class="manage-column">'.__('Description').'</th>';
        $flux.='</tr></thead>';
        $flux.='<tfoot><tr>';
        $flux.='    <th scope="col" id="title" class="manage-column">'.__('Titre').'</th>';
        $flux.='    <th scope="col" id="title" class="manage-column">'.__('Description').'</th>';
        $flux.='</tr></tfoot>';
        $flux.='<tbody>';
        foreach($list_org as $organigramme) {
            $flux.='<tr>';
                $flux.='<td><strong><a href="'.WP_GL_ORG_BASE_URL.'&action=edit&id='.$organigramme->gl_org_id.'">'.$organigramme->titre.'</a></strong>'.get_org_actions($organigramme->gl_org_id, $list_actions).'</td>';
                $flux.='<td>'.$organigramme->description.'</td>';
            $flux.='</tr>';
        }
        $flux.='</tbody>';
        $flux.='</table>';
    }
    else {
        $flux.='<div id="message"><p>'.__('Il n\'y a aucun Organigramme, cliquez sur le bouton &laquo;Ajouter&raquo; pour créer un nouvel organigramme.').'</p></div>';
    }
}
else {

   $organigramme = '';
   $get_id_url = (!empty($_GET['id'])) ? '&id='.$_GET['id'] : '';
   $data = array(
        'org_titre'       => '',
        'org_description' => '',
        'org_structure'   => '',
    );

    if(!empty($_POST)) {
        $is_save = $_POST['save'];
        unset($_POST['save']);
        $data = array_merge($data, $_POST);
    }

    switch($_GET['action']) {
        // Gestion de l'ajout d'un nouvel organigramme
        case 'del' :
            // Gestion de la suppression
            if(!empty($_GET['id'])) {
                // On vérifie que l'enregistrement que l'on tente de supprimer existe bien en base de données
                $query = 'SELECT gl_org_id FROM '.$wpdb->prefix.WP_GL_ORG_TABLE_ORGANIGRAMME.' WHERE gl_org_id = \''.$_GET['id'].'\'';
                $id = $wpdb->get_col($query);

                if(empty($id[0])) {
                    wp_redirect(WP_GL_ORG_BASE_URL.'&error=del');
                }
            }
            else {
                wp_redirect(WP_GL_ORG_BASE_URL.'&error=del');
            }

            // Dans le cas ou nous avons aucune erreur, on supprime l'élément
            $query = ' DELETE FROM '.$wpdb->prefix.WP_GL_ORG_TABLE_ORGANIGRAMME.'  WHERE gl_org_id = \''.$id[0].'\' ';
            $wpdb->query($query);
            wp_redirect(WP_GL_ORG_BASE_URL.'&deleted');
        break;

        case 'edit' :
            // Récuperation de la liste des données
            if(isset($_GET['id'])) {
                $query = 'SELECT * FROM '.$wpdb->prefix.WP_GL_ORG_TABLE_ORGANIGRAMME.' WHERE gl_org_id = '.qstr($_GET['id']).' ';
                $result = $wpdb->get_row($query, 'ARRAY_A');

                // Si nous avons un résultat et que nous ne sommes pas en mode $_POST
                if(!empty($result)) {
                    if(empty($_POST)) {
                        $data['org_description'] = $result['description'];
                        $data['org_titre']       = $result['titre'];
                        $data['org_structure']   = $result['data'];
                    }
                }
                else {
                    wp_redirect(WP_GL_ORG_BASE_URL.'&error=edit');
                }
            }
            else {
                wp_redirect(WP_GL_ORG_BASE_URL.'&error=edit');
            }
        break;
    }

    $error = array();   // Initialisation du tableau d'erreur

    // Gestion de l'enregistrement

    if(!empty($_POST)) {
        // On vérifie que les données passé en $_POST sont bien rempli
        if(trim($_POST['org_titre']) == '' ) {
            $error[] = __('Le champ &laquo;Titre&raquo; est obligatoire.');
        }

        if(trim($_POST['org_description']) == '' ) {
            $error[] = __('Le champ &laquo;Description&raquo; est obligatoire.');
        }

        // Si nous n'avons pas d'erreur
        if(empty($error)) {
            // Si nous sommes en mode add, enregistrement des données en base de données
            if($_GET['action'] == 'add') {
                $query = "INSERT INTO ".$wpdb->prefix.WP_GL_ORG_TABLE_ORGANIGRAMME." ( titre, description, data) VALUES (".qstr($_POST['org_titre']).", ".qstr($_POST['org_description']).", ".qstr($_POST['org_structure']).")";
            }
            // Sinon, mise à jour des données en base de données
            else if($_GET['action'] == 'edit') {
                $query = "UPDATE ".$wpdb->prefix.WP_GL_ORG_TABLE_ORGANIGRAMME." SET titre = ".qstr($_POST['org_titre']).", description = ".qstr($_POST['org_description']).", data = ".qstr($_POST['org_structure'])." WHERE gl_org_id = ".qstr($_GET['id'])." ";
            }

            $wpdb->query($query);   // Execution de l'enregistrement ou de la mise à jour
            // Redirection vers la page d'accueil
            wp_redirect(WP_GL_ORG_BASE_URL.'&register');
        }
    }

    // Affichage des erreurs
    if(!empty($error)) {
        $flux.= '<div class="error">';
        $flux.='<span>'.implode('<span><br/></span>', $error).'</span>';
        $flux.='</div>';
    }

    // Mise en place du formulaire
    $flux.='<div id="icon-edit" class="icon32"><br></div>';
    $flux.= '<h2>'.__('Ajout d\'un nouvel organigramme').'</h2>';

    // Mise en place du formulaire d'ajout du nouvel organigramme
    $flux.='<form name="add_org" id="add_org" method="post" action="'.WP_GL_ORG_BASE_URL.'&action='.$_GET['action'].$get_id_url.'">';
    $flux.='    <div id="poststuff" class="metabox-holder has-right-sidebar">';
    $flux.='        <div class="stuffbox">';
    $flux.='            <h3><label for="org_titre">'.__('Titre').'</label></h3>';
    $flux.='            <div class="inside">';
    $flux.='                <input type="text" name="org_titre" value="'.$data['org_titre'].'" id="org_titre" tabindex="1">';
    $flux.='            </div>';
    $flux.='        </div>';

    $flux.='        <div class="stuffbox">';
    $flux.='            <h3><label for="org_description">'.__('Description').'</label></h3>';
    $flux.='            <div class="inside">';
    $flux.='                <input type="text" name="org_description" value="'.$data['org_description'].'" id="org_description" tabindex="1">';
    $flux.='            </div>';
    $flux.='        </div>';

    $flux.='        <div class="stuffbox">';
    $flux.='            <h3><label for="org_description">'.__('Gestion de l\'organigramme').'</label></h3>';
    $flux.='            <div class="inside">';

    // Dans le cas ou nous n'avons aucune données pour l'organigramme
    $alt ='#TB_inline?height=300&amp;width=350&amp;inlineId=form_elem';
    $flux.='<p id="empty_org">'.__('Votre organigramme est vide').'&nbsp;<input type="button" class="thickbox" name="'.__('Ajouter').'" value="'.__('Ajouter').'" alt="'.$alt.'" /></p>';
    $flux.='<input type="button" class="view_actions" name="'.__('Afficher les actions').'" value="'.__('Afficher les actions').'" />';

    if(empty($data['org_structure'])) {
        $flux.='<div id="view_org"></div>';
    }
    else {
        // Construction de l'organigramme
        $flux.='<div id="view_org">'.$data['org_structure'].'</div>';
    }

    $flux.='            </div>';
    $flux.='        </div>';

    $flux.='        <input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="'.__('Sauvegarder').'">';
    $flux.='        <a  href="'.WP_GL_ORG_BASE_URL.'" class="button-primary">'.__('Retour').'</a>';
    $flux.='    </div>';
    $flux.='            <input type="hidden" name="org_structure" value="" id="org_structure">';
    $flux.='</form>';
    $flux.= form_elem_org();
}

$flux.= '</div>';
// Affichage du flux;
if(!empty($flux)) {
    echo $flux;
}