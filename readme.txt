=== AmR Clearskys Availability modification for 2.7 ===
Contributors: Clearskys.net, Anmari
Donate link: http://webdesign.anmari.com/web-tools/donate/
Tags: calendar, property, properties, availability, widget
Requires at least: 2.6
Tested up to: 2.7.1
Version: 1.1a
Stable tag: 1.1a

Clearskys Property Availability calendar modified for 2.7.1 with wordpress in own directory, shortcodes, multiple properties (rudimentary).

== Description ==

Please see [Clearskys](http://dev.clearskys.net/Wordpress/Availability)  for all functioning.  I altered as noted for my own purposes - I could not wait till they delivered a new version.   I took some extra time to offer it up to the wordpress community as it seemed there were a few folk needing it.   If this fix was helpful, please donate, or write a credit post linking back to my site!  Don't forget to acknowledge the original authors.


== Version History ==

= Version 1.1a =
*   fixed to allow for wordpress in own directory, and relocated content directory
*   fix alternate logic of listing so shades will alternate
*   moved admin links to one section in admin  menu under Bookings for ease of user
*   allowed for additional properties through rudimentary include file - hopefully clearskys will provide a version soon.
*   changed to shortcode usage to upgrade and to avoid problems with validation (else wordpress insert a <p> before and </p> after - this breaks the validation.
*   developed a css file for the page calendar - add this to your standard stylesheet.


== Installation ==

1. Unzip the folder into your wordpress plugins folder.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add  [amr-clearskys-bookings  property=1 months=12] to a page and to reference a single property.  NB Property must be before month due to way that original logic was coded.
4. Manage the plugin through the Bookings admin screen.
6. Add calendar css to your css.
7. If you are using the provided css, Check in the Bookings config that the month block has <div class="calendar"><table>{monthblock}</table></div>

== Screenshots ==

1. Added Bookings section in admin menu, also shows two properties, same dates
2. Edit booking screen - shows property dropdown
3. Add booking screen, also with property selection
4. The write/edit page screen showing the shortcode usage
5. The file to be edited to show your property names on dropdowns.
6. Calendar in page


== Frequently Asked Questions ==

= Css?=

Add the provided calendar css to your themes stylesheet.