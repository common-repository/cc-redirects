=== CC-Redirects ===
Contributors: ClearcodeHQ, PiotrPress
Tags: 301, redirect, redirects, seo, url, request, destination, 302, csv, clearcode, piotrpress
Requires PHP: 7.4
Requires at least: 4.9.4
Tested up to: 6.0.1
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

This plugin allows you to create simple redirect requests to another page on your site or elsewhere on the web.

== Description ==

This plugin allows you to create simple redirect requests to another page on your site or elsewhere on the web.

Redirects format is similar to the one that Apache uses. The `From` field should be relative to your website root. The `To` field can be either a full URL to any page on the web, or relative to your website root.
From: `/old-page/` To: `/new-page/`
From: `/old-page/` To: `http://example.com/new-page/`

To use wildcards, put an asterisk `*` after the folder name you want to redirect.
From: `/old-folder/*` To: `/redirect-everything-here/`

You can also use the asterisk `*` in the `To` field to replace whatever it matches in the `From` field.
From: `/old-folder/*` To: `/some/other/folder/*`
From: `/old-folder/*/content/` To: `/some/other/folder/*`

== Installation ==

= From your WordPress Dashboard =

1. Go to 'Plugins > Add New'
2. Search for 'CC-Redirects'
3. Activate the plugin from the Plugin section on your WordPress Dashboard.

= From WordPress.org =

1. Download 'CC-Redirects'.
2. Upload the 'cc-redirects' directory to your '/wp-content/plugins/' directory using your favorite method (ftp, sftp, scp, etc...)
3. Activate the plugin from the Plugin section in your WordPress Dashboard.

= Once Activated =

1. Visit the 'Update> Settings' page, select your preferred options and save them.

= Multisite =

The plugin can be activated and used for just about any use case.

* Activate at the site level to load the plugin on that site only.
* Activate at the network level for full integration with all sites in your network (this is the most common type of multisite installation).

== Screenshots ==

1. **CC-Redirects Settings** - Visit the 'Settings > Redirects' page, set your preferred redirects and save them.

== Changelog ==

= 1.1.1 =
*Release date: 08.08.2022*

* Added trim trailing slashes in comparison

= 1.1.0 =
*Release date: 26.07.2022*

* Added `comparison` method setting
* Added `parameters` forward setting

= 1.0.3 =
*Release date: 11.12.2020*

* Added column to posts lists and notice to posts edit screens in wp-admin

= 1.0.2 =
*Release date: 26.11.2019*

* Exclude 'wp-login.php' from redirection.

= 1.0.1 =
*Release date: 12.03.2019*

* Added 'code' param to wp_redirect() function.

= 1.0.0 =
*Release date: 27.03.2018*

* First stable version of the plugin.