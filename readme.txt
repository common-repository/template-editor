=== Options for Block Themes ===
Contributors: domainsupport
Donate link: https://webd.uk/product/support-us/
Tags: block theme, templates, template parts, google fonts, global styles
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 5.6
Stable tag: 1.3.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import / Export global styles, templates and template parts to Full Site Editing / Block Themes without a child theme!

== Description ==
= Template Editor =

With WordPress v5.9 the first default theme with Full Site Editing was launched ... Twenty Twenty-Two.

This plugin expands on the functionality to manage (export / import) templates that are not part of the theme without having to have a child theme.

Having a block theme also removes links to the Customizer. This plugin changes that!

Options for Block Themes adds an option for expandable submenus to the Navigation Block modal.

This plugin will also enable the Template Editor in Gutenberg for full site editing for any theme!

= Reinstate Customizer Links =

Activating the plugin will inject the "Customize" links back into the "Dashboard - Appearance" and Admin Bar locations for Wordpress prior to v6.0. There is an option to disable this if you prefer.

= Edit Existing Templates =

Once you've installed this plugin, head over to "Dashboard - Appearance - Manage Templates" where you can change the title, description and name of customized templates from the active theme or any other theme.

= Duplicate Templates =

Save templates as a copy to the active theme.

This allows you to use the [WordPress Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/) to name your templates so that they can be used for custom post types, custom taxonomies, specific post or pages or anything you like!

= Delete Templates =

Easily delete templates from the active theme or any other theme. This doesn't delete the orginal theme template, just any customizations you have made.

= Download Templates =

Download your template as .json files to back them up or to migrate them to another WordPress site.

= Upload Templates =

Upload template .json files to the active theme. This allows you to restore a template you backed up or migrate a template to another WordPress site.

= Download Global Styles =

Download your global styles as .json files to back them up or to migrate them to another WordPress site or theme.

= Upload Global Styles =

Upload global styles .json files to the active theme. This allows you to restore global styles you backed up or migrate global styles to another WordPress site.

= Sticky Header =

Easily enable a sticky header / menu in full site editor themes like Twenty Twenty-Two.

= Animate Site Logo =

Enable an option to shrink the header site logo when you scroll down the page.

= Add Google Fonts to Editor =

NB: This is only relevant to WordPress v5.8 - v6.4 and classic themes as the Font Library was introduced into core in v6.5

Choose Google Fonts and add them to the full screen editor global styles options!

= Remove Block Theme Fonts =

NB: This is only relevant to WordPress v5.8 - v6.4 as the Font Library was introduced into core in v6.5

This plugin will allow you to choose which theme fonts are included in the FSE editor and if not required will prevent them from being loaded on the front end.

== Installation ==

Easily use this plugin to enable the Template Editor on your site ...

1) Install "Template Editor" automatically or by uploading the ZIP file.
2) Activate the plugin through the "Plugins" menu in WordPress.
3) Start using the Template Editor with your theme.

== Changelog ==

= 1.3.4 =
* Added an option for expandable submenus to the Navigation Block modal

= 1.3.3 =
* Fixed a minor JavaScript bug

= 1.3.2 =
* Updated "Shrink header logo" option to add "shrink-logo" class to the header on scroll even if a Site Logo block isn't present

= 1.3.0 =
* Added support for persistent object cache when uploading global styles to the active theme

= 1.2.9 =
* Fixed a bug when saving settings

= 1.2.8 =
* Re-enabled Google Font functionality for classic themes, fixed bug with mulitple logos shrinking

= 1.2.7 =
* Bug fixed when using a child them with "Remove Theme Fonts" option

= 1.2.6 =
* Preparing for the release of WordPress v6.5

= 1.2.5 =
* Added the ability to download, delete and upload global styles

= 1.2.4 =
* Updated the JavaScript for sticky headers when the admin bar is showing

= 1.2.3 =
* If Google font download fails then notice is shown and plugin falls back non-local fonts

= 1.2.2 =
* Fixed bug with Theme Font Removal
* Fixed bug with Google Font Weight Order
* Fixed bug with hosted Google Fonts not loading in the editor
* Added weights to Google Font dropdowns

= 1.2.1 =
* Fixed a bug with sticky header when the admin bar is showing
* Added option to choose Google "font-display" property
* Added a fix for the Shortcode Block bug introduced in WordPress v6.2.2

= 1.2.0 =
* Fixed a bug when removing theme fonts, added an option to hide links to the Customizer

= 1.1.9 =
* Bugs fixed and work undertaken to prepare for Fonts API

= 1.1.8 =
* Various bugs and translation issues fixed

= 1.1.7 =
* Paved the way for more on-scroll header animations
* Added an option for shrunken logo width

= 1.1.6 =
* General housekeeping

= 1.1.5 =
* Fixed a bug that prevented multiple font styles for the same Google font

= 1.1.2 =
* Added a theme option to attempt to host Google Fonts locally

= 1.1.1 =
* Added a theme option to animate (shrink) the header Site Logo block on scroll

= 1.1.0 =
* Added ability to disable and prevent FSE fonts from being loaded
* Bug fix

= 1.0.9 =
* Added ability to upload, download or delete template parts
* Added Google Fonts to editor

= 1.0.8 =
* Bug fix

= 1.0.7 =
* Tweaked sticky header CSS
* Bug fix

= 1.0.6 =
* Added option to set the header as a sticky header

= 1.0.5 =
* Show the "Customize" admin menu item in "Dashboard - Appearance" and in the Admin Bar

= 1.0.4 =
* Added the ability to select, download templates from inactive themes and to delete templates

= 1.0.3 =
* Bug fixes

= 1.0.2 =
* Added the ability to download and upload templates

= 1.0.1 =
* Added the ability to edit or duplicate customized templates

= 1.0.0 =
* First version of the plugin

== Upgrade Notice ==

= 1.3.4 =
* Added an option for expandable submenus to the Navigation Block modal
