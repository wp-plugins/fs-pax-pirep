=== FS-Pax Pirep ===
Contributors: yorokobi
Donate Link: http://www.federalproductions.com
Tags: FSPassengers, Flight simulator
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 2.0.1

Adds scripted PIREP display as a plugin to WordPress.

== Description ==

This plugin is to be used in conjunction with [FSPassengers] (http://www.fspassengers.com) add-on for Flight Simulator 9 and X.

It retrieves, formats, and presents data from the all the flights filed using FSPassengers built in report filing system, which stores this information in an SQL database on your web server.  (Details on storing this data is provided with FSPassengers documentation)

With the plugin enabled any page or post with the short code `[fp-pirep-report]` will display the data.  Other content can be present on the page.   Exactly which data is presented is selected via check boxes on the FS-Pax Pirep settings page, located in "Settings->FP FS-Pax Pirep". Flights will automagically be presented paginated with 10 flights per page.

== Installation ==

This section describes how to install the plugin and get it working

1. Upload `fs-pax-pirep.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin though the 'Plugins' menu in WordPress
1. Select Options and your FSP directory (see FSP Docs for details on "your FSP directory")
1. Place `[fp-pirep-report]` in any page or post

== Frequently Asked Questions ==

= Can you help me set up my VA on my web server? = 

No. Setting up and FSP VA should be pursued though FSPassengers.com's forums.

== Screenshots  ==

1. Styled Output Screen

== Changelog ==

= 2.0.1 =

* Fixed bug that made the pagination dependent on the theme that my blog was using while I was developing this version (oops).

= 2.0 =

* Added pagination using jQuery and DataTables

= 1.0.3 =

* Corrected hard coded link to settings page (had my own site's url in it, oops)
* Added option to add attributes to the report links, I needed this to allow the addition of class=external so WP-Touch would work correctly.  Others might find it useful as well as it could be used for a target or id attribute, etc.

= 1.0.2 =

* Added screenshot

= 1.0.1 = 

* Corrected Plugin URI
* Added Link to settings page from plugins menu page
* Added info to Readme about CSS classes for output styling

= 1.0 =

* Initial Release

== Upgrade Notice ==

* NONE (yet)

== Additional Info ==

* Table of flights is .pireptable
* Column Titles are .pireptitle
* Cells are classed .pirepcell .pirepodd OR .pirepcell .pirepeven (allows alternating row colors)
* Summary table at the bottom is also .pireptable
* Summary table cell is classed .pirepsum

Using these classes you can add styling to the tables in your themes CSS style sheet or using the "Custom CSS" plugin.
