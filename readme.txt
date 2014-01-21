=== User Stats ===
Contributors: Dean Robinson
Donate link: http://www.apinapress.com/
Tags: user, author, stats, statistics, post count
Requires at least: 3.6
Tested up to: 3.7.1
Stable tag: 1.0.7
License: GPLv2 or later

User Stats makes it easy to see at a glance stats about your users, including: post count, post views, article costs, costs per 1000 views and more.

== Description ==

User Stats provides an easy way to see at a glance stats about your users, including: post count, post views, article costs, costs per 1000 views and more.

== Installation ==

The plugin is simple to install:

1. Download `user-stats.zip`
1. Unzip
1. Upload `user-stats` directory to your `/wp-content/plugins` directory
1. Go to the plugin management page and enable the plugin
1. Stats and Settings are under the User menu

== Frequently Asked Questions ==

= Where do I find the stats? =

The main statistics page for admins is located under Users > User Stats

= What about if I want my users to see stats? =

Go to Users > Users Stats and then clck the Settings tab. Make sure the "Enable stats in users Profile?" option is ticked and then users can view simplified stats in their profile.

= I need users to see basic stats but don't allow Profile acccess =

You can add the shortcode [userstats] to a post or page to enable users to see simplified stats from the front end (user must be logged in).

If the "Enable stats in users Profile?" is disabled, the shortcode will still show the users stats.

== Screenshots ==

1. The main User Stats screen.
2. Settings screen

== Changelog ==

= 1.0.7 =

* Fix:		Resolved a bunch of notices
* Fix:		Added support for PHP versions below 5.3 - this didn't get included properly in the last update.

= 1.0.6 =

 * Tweak:	Added support for PHP versions below 5.3

= 1.0.5 =

* Fix:     The profile option as it wasnt displaying the table.
* Tweak:   Changed the settings page to accomodate larger numbers of users
* FEATURE: Added ability to filter the profile/shortcode tables
* FEATURE: Added the ability to rename all the column headings
* FEATURE: Added a total row to the bottom of the profile and shortcode tables. If the table goes over 10 rows, it will insert a total row at the top of the table as well with JavaScript.

= 1.0.4 =

* FEATURE: Added shortcode to view individual users stats from the front end [userstats] (user must be logged in to view).

= 1.0.3 =

* FEATURE: Added view and costing to indivudual author profile (plus option to toggle on/off)
* Fixed undefined indexes

= 1.0.2 =

* Changed the post load structure to use AJAX, to reduce potential page load times.
* Localised the text strings allowing for translation and added a (very bad) Finnish translation.
* Added ability to remove users from the overview section.
* Added ability to filter posts by date.
* Added some styles to the settings to make it a bit easier to read.
* Added ability to reset the view counts on individual posts.
* Added feature to have a combined overview total for multiple selected users.
* Added feature to have a combined posts for multiple selected users.
* Removed the Individual Author bars - moved Roles column to the overview

= 1.0.1 =

* Fixed false positives caused by widgets. Plugin now requires wp_head to be available in the theme.

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.7 =
Minor upgrade to fix notices and add a fix for < PHP 5.3

= 1.0.4 =
Adds a shortcode for front end stats (user must be logged in to view).