=== Revised Status ===
Contributors: yivi
Tags: revisions, publishing-status
Requires at least: 4.0
Tested up to: 4.2.2
Stable tag: 0.6
License: GPLv2 or later
License URI: http//www.gnu.org/licenses/gpl-2.0.html
Domain Path: /lang
Text Domain: wp-revised-status

Saves and restores publishing status in post revisions, replacing the default Revisions metabox with a modified metabox with pub status.

== Description ==

Sometimes it would be useful to track changes in publishing status throughout time, but native WP revisions will always inherit the parent's publishing status.

This plugin enables saving the post status ('published', 'draft', 'pending', etc) with each revision, so you can track publishing status where you have many users and accountability is desirable.

= Available Hooks =

**`wp-revised-status_tracked-posttypes`**
You can use this to set up posttypes to track without using the options page. Your function should return an associative array.

E.g.: 

`
 add_filter( 'wp-revised-status_tracked-posttypes', function( $enabled ) {
 $enabled['post'] = 1;
 $enabled['page'] = 1;
 
 return $enabled;
 }
`

**`wp-revised-status_untracked-posttypes`**
Exactly the inverse of the previous hook. ** What you disable on this hook takes precedence to what you enable in the `tracked_posttypes` one.

E.g.:

`
function my_plugin_no_history( $disabled ) {
    $disabled['page'] = 1;
    
    return $disabled;
}
add_filter( 'wp-revised-status_untracked-posttypes', 'my_plugin_no_history' );
`

**`wp-status-revised_disable-options`**
If you are using the plugin inside a theme or another plugin, and want to disable the options page, you can just do:

`add_filter( 'wp-status-revised_disable-options', '__return_true' )`



= Github =
Github repository at plugin at http://github.com/yivi/myplugin

== Installation ==

1. Unzip plugin's files in a folder inside `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where is published information saved? =
An entry will be created on your post_meta for each revision, that will be deleted whenever a revision is deleted.

== Screenshots ==

1. The new revision status metabox
2. Options page to enable publishing status history for registered post types.

== Changelog ==

= 0.6 =
* Initial public release