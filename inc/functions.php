﻿<?php
function print_rh($v) {
    echo '<pre>';
    print_r($v);
    echo '</pre>';
}

/*
 * Fonction get_org_actions($id, $list_actions)
 * -----
 * Constructions des actions pour chaque organigramme
 * -----
 * @global  integer     $id                     Identifiant de l'élément
 * @global  array       $list_actions           Liste des actions pouvant être effectué
 * -----
 * @return  string       $flux                  flux contenant les actions
 * -----
 * $Author: Maxime B $
 * $Date: 2011-09-19 17:14 $
 * $Copyright: GLOBALIS media systems $
 */
function get_org_actions($id, $list_actions) {
    $flux ='<div class="row-actions">';
        $cpt = 1;
        $nb_action = sizeof($list_actions);
        foreach($list_actions as $k => $v) {
            if($k != 'add') {
                $flux.='<span><a href="'.WP_GL_ORG_BASE_URL.'&action='.$k.'&id='.$id.'">'.$v.'</a></span>';
            }
            else {
                $flux.='<span><a href="'.WP_GL_ORG_BASE_URL.'&action='.$k.'">'.$v.'</a></span>';
            }

            if($cpt < $nb_action)
                $flux.=' | ';
            $cpt++;
        }
    $flux.='</div>';

    return $flux;
}

/*
 * Fonction qstr($value)
 * -----
 * Permet d'échapper la valeur pour l'insertion en base de données
 * -----
 * @global  string     $value                     Valeur à echapper
 * -----
 * @return  string     $value                    retourne la valeur échappé
 * -----
 * $Author: Maxime B $
 * $Date: 2011-09-19 17:14 $
 * $Copyright: GLOBALIS media systems $
 */
function qstr($value) {
    return '\''.$value.'\'';
}

// Fonction permettant de créer le formulaire de création d'un élément de l'organigramme
function form_elem_org() {
    $field_elem = get_option('wp_gl_org_option_field_elem');
    $flux = '<div id="form_elem" style="display:none">';
    $flux.='    <div class="stuffbox metabox-holder" style="width:350px">';
    $flux.='        <h3><label>'.__('Nouvel élément').'</label></h3>';
    $flux.='        <div class="inside">';
    
    if(!empty($field_elem)) {
        $flux.='<div class="field_org" style="display:none" id="choice_type_elem">';
        $flux.='<label title="enfant"><input type="radio" name="type_elem" value="'.__('enfant').'" checked="checked"> <span>Enfant</span></label>';
        $flux.='<label title="frere"><input type="radio" name="type_elem" value="'.__('frere').'"> <span>Frère</span></label>';
        $flux.='</div>';
    
        foreach($field_elem as $k => $v) {
            $flux.='<div class="field_org">';
            if($v['type'] == 'text') {
                $flux.='<label>'.$v['label'].'</label>';
                $flux.='<input type="text" name="elem_'.$k.'" value="" class="elem_'.$k.'" tabindex="1">';
            }
            
            $flux.='</div>';
        }
        $flux.='<input name="add_elem" type="button" class="button-primary" id="add_elem" tabindex="4" accesskey="p" value="'.__('Ajouter').'">';
    }
    else {
        $flux.='<p>'.__('Problème lors de la récuperation des options.').'</p>';
    }
    $flux.='        </div>';
    $flux.='    </div>';
    $flux.='</div>';
    
    return $flux;
}