<?php
// Fichier de configuration
define('WP_GL_ORG_VERSION', '1.0');


/**
 * Remember plugin path & URL
 */
define('GB_SK_PATH', plugin_basename( realpath(dirname( __FILE__ ).'/..')  ));
define('GB_SK_COMPLETE_PATH', WP_PLUGIN_DIR.'/'.GB_SK_PATH);
define('GB_SK_URL', WP_PLUGIN_URL.'/'.GB_SK_PATH);

/**
 * Translation domain name for this plugin
 */
define('GB_SK_DOMAIN', 'gl_org');
define('WP_GL_ORG_TABLE_ORGANIGRAMME', 'gl_org');