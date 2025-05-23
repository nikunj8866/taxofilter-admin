=== TaxoFilter Admin ===
Contributors: nikunj8866
Tags: taxonomy, filters, admin, categories, tags
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds customizable taxonomy filters for posts and custom post types in the WordPress admin area.

== Description ==

TaxoFilter Admin enhances the WordPress admin experience by providing customizable taxonomy filters for all post types.

The plugin is open source and hosted on [GitHub](https://github.com/nikunj8866/taxofilter-admin). If you have any issues or feedback, please open an issue there.

**Key Features:**

* Automatically adds taxonomy dropdowns to post list screens
* Works with posts, pages, and custom post types
* Allows administrators to select which taxonomy filters to display via screen options
* Clean, developer-friendly code

== Installation ==

1. Upload the `taxofilter-admin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit any post list screen and use the screen options to configure which taxonomy filters to display

== Frequently Asked Questions ==

= Will this work with custom post types? =

Yes, the plugin automatically detects all registered post types and their associated taxonomies.

= Can I select which taxonomy filters to display? =

Yes, you can customize which taxonomy filters appear by accessing the Screen Options panel at the top of any post list screen.

= How can I exclude certain taxonomies from the screen options? =

You can use the `taxofilter_admin_excluded_taxonomies` filter to exclude specific taxonomies from showing up in the screen options for a post type.

Example usage:

```php
add_filter('taxofilter_admin_excluded_taxonomies', function($excluded, $post_type) {
    if ($post_type === 'post') {
        $excluded[] = 'category';
    }
    if ($post_type === 'custom_post_type') {
        $excluded[] = 'custom_taxonomy';
    }
    return $excluded;
}, 10, 2);

== Changelog ==

= 1.0.1 =
* TWEAK: Added `taxofilter_admin_excluded_taxonomies` filter to allow excluding specific taxonomies from the screen options.

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release