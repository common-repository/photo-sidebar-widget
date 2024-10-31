=== SmugMug Photo sidebar widget ===
Tags: SmugMug, WordPress, WordPress Plugin, RSS, widget, images, photos, Flickr, sidebar, Blogger, pixelpost
Contributors: mproulx
Requires at least: 2.5
Tested up to: 2.5
Stable tag: trunk

== Description ==

The SmugMug Photo Sidebar widget displays a user-selectable number of random photos from one (or several) RSS photo feeds in the sidebar of a Word Press blog.
New features in version 2.0 include multiple widget support and image caching (which may speed up page loads).

== Installation ==

1. Download and unzip `photo-sidebar-widget.zip`
2. Place `photo_sb.php` in your blog's plugin subdirectory (e.g., `/wp-content/plugins/`)
3. (Optional) If you plan to set up caching, create a subdirectory for the images (e.g. `/wp-content/plugins/photo-sb`)
4. Activate the plugin from the Plugins tab of your blog's administrative panel
5. Go to the Widgets page from the Presentation tab
6. Click the 'Add' link on the widget to add the widget to the appropriate sidebar.
7. Click the 'Edit' link on the widget's placeholder to open the settings form.
8. Use the form to point to RSS feeds, adjust any of the visual or cache settings to fit your preferences and save the sidebar settings.
9. Click the 'Save' button on the settings form.
10. Click the 'Save Changes' button at the bottom of the Current Widgets section.
11. If you would like to have multiple copies of the widget on your blog, simply click the 'Add' button again.

== Frequently Asked Questions ==

= I installed the plugin but can't see it.  What's wrong? =

If the widget doesn't show up on the Sidebar Widgets page:
* Check to make sure the plugin was uploaded to the widgets subdirectory.
* Check to make sure both the widgets plugin and the photos plugin are activated.

* Version 2.1 of the widget won't work with versions of WordPress older than 2.5.  You can download version 2.0 or earlier, which will work with 2.0-2.3

If the widget doesn't appear on the sidebar once the plugin has been added:
* Make sure the plugin has been dragged to the sidebar.
* Be sure to save changes on the plugin page.

If the widget title appears but no photos appear below it:
* Make sure the feed URL is entered correctly in the settings page

= The widget doesn't work with my theme.  How can I fix this? =

This widget has been tested on a number of themes and is compatible with all truly widget-ready themes.  However, certain formatting settings are "locked" in the code.  

The code isn't locked in stone, however.  You may be able to edit the PHP file to adjust things to your particular code.  You may also contact the plugin author for suggestions; these may result in a better plugin in the future.

= What RSS feeds work with this plugin? =

Currently, this plugin supports RSS feeds from SmugMug, Flickr, Gallery2, Blogger and pixelpost services.

== Homepage ==

[http://www.district30.net/photo-sidebar-widget-version-20](http://www.district30.net/photo-sidebar-widget-version-20)