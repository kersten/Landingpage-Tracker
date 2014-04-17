=== Landingpage Tracker ===
Contributors: kerstenb
Donate link: http://thekersten.com/
Tags: tracking, landingpages, landingpage, cookie, users, adwords, seo, advertisement, marketing, ads, cf7, contact form 7
Requires at least: 3.8
Tested up to: 3.9
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin gives you the possibility to keep track of your users initial entry page.

== Description ==

This plugin helps you to keep track of your visitors entry page. The first version of this plugin gives you the
possibility to match a part of the received query string.

This value for example gives you the option to add it as an hidden field to your contact form to be able to easily see
from which channel you get the most leads.

== Example ==

The visitor has clicked an ad on Google AdWords where the query string was set to
http://www.example.com/my-great-landingpage/?ref=adwords.

In the admin section of the plugin you can add a new tracker that listens to this ref value. Therefore create a tracker
with the "Match" field set to "ref=adwords". Select the desired cookie, e.g. "AdWords Visitor" an click "Add Tracker".

If the visitor now enters your page with the above query string the Landingpage Tracker sets a cookie with an hash
value that refers to the value "AdWords Visitor".

To get the current set value just use the handy shortcode [landingpage_tracker_get_cookie_name].

== Installation ==

Download the ZIP file and upload the directory Landingpage-Tracker to your wp-content/plugins folder.

== Changelog ==

= 0.1 =
* Initial plugin functionality.