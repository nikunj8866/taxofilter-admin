<?php
/**
 * Plugin Name: TaxoFilter Admin
 * Plugin URI: https://github.com/nikunj8866/taxofilter-admin
 * Description: Adds customizable taxonomy filters for posts and custom post types in the admin area.
 * Version: 1.0.0
 * Author: Nikunj Hatkar
 * Author URI: https://github.com/nikunj8866
 * Text Domain: taxofilter-admin
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/nikunj8866/taxofilter-admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('TAXOFILTER_ADMIN_VERSION', '1.0.0');
define('TAXOFILTER_ADMIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TAXOFILTER_ADMIN_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load the main plugin class
 */
require_once TAXOFILTER_ADMIN_PLUGIN_DIR . 'includes/class-taxofilter-admin.php';

/**
 * Initialize the plugin
 */
function taxofilter_admin_init() {
    TaxoFilter_Admin::get_instance();
}
add_action('plugins_loaded', 'taxofilter_admin_init');