<?php

/**
 * Plugin Name: Organigramme
 * Plugin URI: http://www.globalis-ms.com
 * Description: Gestion dynamique d'organigramme
 * Author: GLOBALIS
 * Version: 1.0.0
 * Author URI: http://www.globalis-ms.com
 */

/**
 * Copyright 2011 Author Name
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Récuperation du fichier de configuration
require 'config/config.php';
require 'inc/functions.php';

/* Pour la configuration du plugin dans l'interface d'administration */
function wp_gl_org_admin() {
    include('admin/wp_gl_org_admin.php');
}

add_action('init', 'wp_gl_org_init');    // Initialisation du plugin à l'initialisation de wordpress
add_action('plugins_loaded', 'wp_gl_org_do_something');
add_action('admin_menu', 'wp_gl_org_admin_actions');
add_action('update_option_wp_gl_orgoption', 'wp_gl_org_do_something_else');
add_filter('the_posts', 'conditionally_add_scripts_and_styles'); // the_posts gets triggered before wp_head
function conditionally_add_scripts_and_styles($posts){
	if (empty($posts)) return $posts;

	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) {
        if(preg_match('`\[organigramme id=\"[0-9]+\"\]`', $post->post_content)) {
			$shortcode_found = true; // bingo!
			break;
        }
	}

	if ($shortcode_found) {
         wp_enqueue_script('jquery');
        $my_style_url  = plugins_url('css/slickmap.css', __FILE__);
        $my_style_file = WP_PLUGIN_DIR . '/wp_gl_org/css/slickmap.css';
        if(file_exists($my_style_file)) {
            wp_register_style('slickmap', $my_style_url);
            wp_enqueue_style( 'slickmap');
        }

        $my_script_url  = plugins_url('js/global.js', __FILE__);
        $my_script_file = WP_PLUGIN_DIR . '/wp_gl_org/js/global.js';
        if(file_exists($my_script_file)) {
            wp_register_script('myScriptsGlobalsFront', $my_script_url);
            wp_enqueue_script( 'myScriptsGlobalsFront');
        }
	}

	return $posts;
}

// Fonction permettant l'ajout des pages dans l'interface d'administration
function wp_gl_org_admin_actions() {
     $mypage = add_plugins_page("Organigramme", "Organigramme", "manage_options", "organigramme", "wp_gl_org_admin");

     // Add some style to this admin panel
     add_action('admin_print_styles-'.$mypage, 'gl_org_admin_menu_style' );

     // Add some scripts on this admin panel
     add_action('admin_print_scripts-'.$mypage, 'gl_org_admin_menu_script' );
}

// Ajout des script js et css pour l'interface d'administration
function potd_init() {
    if (is_admin()) {
        //wp_register_script('wp_gl_org.js', WP_GL_ORG_PLUGIN_URL . 'wp_gl_org.js', array('jquery'));
        //wp_enqueue_script('wp_gl_org.js');
    }
}

// Fonction permettant l'installation du plugin
function wp_gl_org_install() {
    global $wpdb;   // Récuperation de l'objet permettant la communication avec la base de données

    // Creation des options par défaut
    $fields_elem = array(
        'name' => array(
            'type' => 'text',
            'label'=> 'Nom'
        ),
        'description' => array(
            'type' => 'text',
            'label'=> 'Description',
        ),
    );
    add_option('wp_gl_org_option_field_elem', $fields_elem);

    // Creation d'une table si necessaire (seulement sis vous ne pouvez pas utiliser les options ou les tables postmeta)
    if(defined('WP_GL_ORG_TABLE_ORGANIGRAMME')) {
        $table_name = $wpdb->prefix . WP_GL_ORG_TABLE_ORGANIGRAMME;
        // Creation des tables du plugin si ces dernière n'existe pas
        if (isset($table_name) && $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            $sql = "
                CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                    `".WP_GL_ORG_TABLE_ORGANIGRAMME."_id` BIGINT(20) NOT NULL AUTO_INCREMENT  PRIMARY KEY,
                    `titre` VARCHAR(255) NOT NULL,
                    `description` VARCHAR(255),
                    `data` TEXT
                );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Insertion initiale
            $rows_affected = $wpdb->insert(
                $table_name,
                array(
                    'field_one' => 'a string'
                )
            );
        }
    }
}

function wp_gl_org_do_something() {
     // Instructions à effectuer lors du chargement des plugins
}

function wp_gl_org_do_something_else() {
     // Instructions à effectuer lors de la mise à jour des options
}

/**
 * Shortcode permettant d'afficher l'organigramme dans un article
 */
function display_org_func($atts) {
    global $wpdb;

    extract(shortcode_atts(array(
            'id' => ''  // Identifiant de l'organigramme
    ), $atts));

    if(!empty($id)) {
        // Récuperation des informations de l'organigramme
        $query = 'SELECT * FROM '.$wpdb->prefix.WP_GL_ORG_TABLE_ORGANIGRAMME.' WHERE gl_org_id = '.qstr($id).' ';
        $result = $wpdb->get_row($query, 'ARRAY_A');

        if(!empty($result)) {
            $flux = '<h2>'.$result['titre'].'</h2>';
            $flux.= '<p>'.$result['description'].'</p>';
            $flux.='<div id="view_org">'.$result['data'].'<div style="clear:both"></div></div>';
            return $flux;
        }
        else {
            return '<p class="error">'.__('Problème lors de la récuperation de l\organigramme').'</p>';
        }
    }
    else {
        return '<p class="error">'.__('Problème lors de la récuperation de l\organigramme').'</p>';
    }
}

add_shortcode("organigramme", "display_org_func");

/**
* Suppressions des tables et suppression des options
* lors de la suppression du plugin par l'utilisateur
* clicks "delete plugin"
*/
function wp_gl_org_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . WP_GL_ORG_TABLE_ORGANIGRAMME;

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        $qry = "
            DROP TABLE `{$table_name}`
        ";
        //$wpdb->query($qry);
    }

    delete_option('wp_gl_org_option_field_elem');
}

function wp_gl_org_init() {
    ob_start(); // Pour palier au problème de la redirection
    // Gestion de la langue
    load_plugin_textdomain('wp_gl_org', false, 'wp_gl_org/languages');
}

function gl_org_admin_menu_style() {
    wp_enqueue_style('thickbox');
    $my_style_url  = plugins_url('css/admin.css', __FILE__);
    $my_style_file = WP_PLUGIN_DIR . '/wp_gl_org/css/admin.css';
    if(file_exists($my_style_file)) {
        wp_register_style('myStyleSheets', $my_style_url);
        wp_enqueue_style( 'myStyleSheets');
    }

    $my_style_url  = plugins_url('css/slickmap.css', __FILE__);
    $my_style_file = WP_PLUGIN_DIR . '/wp_gl_org/css/slickmap.css';
    if(file_exists($my_style_file)) {
        wp_register_style('slickmap', $my_style_url);
        wp_enqueue_style( 'slickmap');
    }
}

function gl_org_admin_menu_script() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('thickbox');
    $my_script_url  = plugins_url('js/wp_gl_org.js', __FILE__);
    $my_script_file = WP_PLUGIN_DIR . '/wp_gl_org/js/wp_gl_org.js';
    if(file_exists($my_script_file)) {
        wp_register_script('myScripts', $my_script_url);
        wp_enqueue_script( 'myScripts');
    }
}

register_activation_hook( __FILE__, 'wp_gl_org_install');
add_shortcode( 'wp_gl_org', 'wp_gl_org_shortcode');    // Dans le cas ou vous avez besoin de créer des shortcodes
register_uninstall_hook( __FILE__, 'wp_gl_org_uninstall');