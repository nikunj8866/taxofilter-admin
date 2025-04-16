<?php
/**
 * The main plugin class.
 *
 * @package    TaxoFilterAdmin
 * @subpackage TaxoFilterAdmin/includes
 */

if (!defined('WPINC')) {
    die;
}

class TaxoFilter_Admin {

    /**
     * The instance of this class.
     */
    private static $instance = null;

    /**
     * Stores options for the plugin.
     */
    private $options;

    /**
     * Returns the instance of this class.
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        
        // Add taxonomy filters to admin
        add_action('restrict_manage_posts', array($this, 'add_taxonomy_filters'));
        
        // Add screen options
        add_filter('screen_settings', array($this, 'add_screen_options'), 10, 2);
        
        // Save screen options
        add_action('wp_ajax_save_tax_filter_screen_options', array($this, 'save_screen_options'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Load plugin text domain
        add_action('init', array($this, 'load_plugin_textdomain'));
    }
    
    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'taxofilter-admin',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * Enqueue admin scripts.
     */
    public function enqueue_admin_scripts($hook) {
        if ('edit.php' === $hook) {
            wp_enqueue_script(
                'taxofilter-admin-js', 
                TAXOFILTER_ADMIN_PLUGIN_URL . 'assets/js/admin.js', 
                array('jquery'), 
                TAXOFILTER_ADMIN_VERSION, 
                true
            );
            
            wp_localize_script('taxofilter-admin-js', 'taxoFilterAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('taxofilter_admin_nonce')
            ));
            
            wp_enqueue_style(
                'taxofilter-admin-css',
                TAXOFILTER_ADMIN_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                TAXOFILTER_ADMIN_VERSION
            );
        }
    }

    /**
     * Settings section callback.
     */
    public function settings_section_callback() {
        echo '<p>' . esc_html__('Configure taxonomy filters for your admin screens.', 'taxofilter-admin') . '</p>';
    }

    /**
     * Add taxonomy filters to admin.
     */
    public function add_taxonomy_filters() {
        global $typenow;
        
        // Get all taxonomies for current post type
        $taxonomies = get_object_taxonomies($typenow, 'objects');
        
        if (empty($taxonomies)) {
            return;
        }
        
        // Get screen options
        $screen_options = get_option('taxofilter_admin_' . $typenow, array());

        
        foreach ($taxonomies as $taxonomy) {
            // Skip excluded taxonomies
            if ('post' === $typenow && 'category' === $taxonomy->name) {
                continue;
            }
            
            // Check if this taxonomy filter is enabled in screen options
            if (!empty($screen_options[$taxonomy->name])) {
                $this->render_taxonomy_dropdown($taxonomy);
            }
        }
    }

    /**
     * Render taxonomy dropdown.
     */
    private function render_taxonomy_dropdown($taxonomy) {
        $selected = (string) filter_input(INPUT_GET, $taxonomy->query_var);
        
        wp_dropdown_categories(array(
            /* translators: %s: Taxonomy name */
            'show_option_all' => sprintf(__('All %s', 'taxofilter-admin'), $taxonomy->label),
            'orderby'         => 'name',
            'order'           => 'ASC',
            'hide_empty'      => false,
            'hide_if_empty'   => true,
            'selected'        => $selected,
            'hierarchical'    => true,
            'name'            => $taxonomy->query_var,
            'taxonomy'        => $taxonomy->name,
            'value_field'     => 'slug',
        ));
    }

    /**
     * Add screen options.
     */
    public function add_screen_options($screen_settings, $screen) {
        if (!($screen->base === 'edit')) {
            return $screen_settings;
        }
        
        global $typenow;
        
        // Get all taxonomies for current post type
        $taxonomies = get_object_taxonomies($typenow, 'objects');
        
        if (empty($taxonomies)) {
            return $screen_settings;
        }
        
        // Get screen options
        $screen_options = get_option('taxofilter_admin_' . $typenow, array());
        
        $output = '<h5>' . esc_html__('Taxonomy filters', 'taxofilter-admin') . '</h5>';
        $output .= '<div class="taxofilter-admin-screen-options">';
        
        foreach ($taxonomies as $taxonomy) {
            // Skip excluded taxonomies
            if ('post' === $typenow && 'category' === $taxonomy->name) {
                continue;
            }
            
            $checked = (!empty($screen_options[$taxonomy->name])) ? 'checked="checked"' : '';
            
            $output .= '<label>';
            $output .= '<input type="checkbox" name="taxofilter_admin[' . esc_attr($taxonomy->name) . ']" class="taxofilter-admin-option" value="1" ' . $checked . ' />';
            $output .= esc_html($taxonomy->labels->name);
            $output .= '</label>';
        }
        
        $output .= '<div class="taxofilter-admin-save">';
        $output .= '<button type="button" class="button button-primary" id="save-taxofilter-admin">' . esc_html__('Save', 'taxofilter-admin') . '</button>';
        $output .= '<span class="spinner"></span>';
        $output .= '</div>';
        $output .= '</div>';
        
        return $screen_settings . $output;
    }

    /**
     * Save screen options via AJAX.
     */
    public function save_screen_options() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'taxofilter_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'taxofilter-admin')));
        }
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'taxofilter-admin')));
        }
        
        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : 'post';
        $filters = isset($_POST['filters']) ? array_map('sanitize_text_field', wp_unslash($_POST['filters'])) : array();
        
        $options = array();
        
        if (!empty($filters)) {
            foreach ($filters as $tax_name => $value) {
                $options[sanitize_text_field($tax_name)] = (bool) $value;
            }
        }
        
        // Save options
        update_option('taxofilter_admin_' . $post_type, $options);
        
        wp_send_json_success(array('message' => __('Settings saved successfully.', 'taxofilter-admin')));
    }
}