=== AmR Clearskys Availability modification for 2.7 ===
Contributors: Clearskys.net, Anmari
Donate link: http://webdesign.anmari.com/web-tools/donate/
Tags: calendar, property, properties, availability, widget
Requires at least: 2.6
Tested up to: 2.7.1
Version: 1.1c
Stable tag: 1.1c

Clearskys Property Availability calendar modified for 2.7.1 with wordpress in own directory, shortcodes, multiple properties (rudimentary).

== Description ==

Please see [Clearskys](http://dev.clearskys.net/Wordpress/Availability)  for all functioning.  I altered as noted for my own purposes - I could not wait till they delivered a new version.   I took some extra time to offer it up to the wordpress community as it seemed there were a few folk needing it.   If this fix was helpful, please donate, or write a credit post linking back to my site!  Don't forget to acknowledge the original authors.


== Version History ==


= Version 1.1c =
*   more fix to folder url
*   also changed the property file to be a sample, and chcek for existence so as not to over right on upgrade!  So if it is a new install, you need to copy the amr_props_sample.php to amr_props.php, or else you will only have 1 property!


= Version 1.1b =
*   fixed the clearsksy file and directory references so css has no problems
*   added strong to default css in config for booked class so will at least highlight if no css.
*   tried to fix their feeds but it is not picking them up - if anyone wants to have a go at seeing why or is happy for to pay me - I'll look further.  One can add a shortcode [amr-clearskys-bookings property=1 months=2 feedlink="ical" ]  , but the resultant link does not work.  Or
[amr-clearskys-bookings property=1 months=2 feedlink="rss" ]  
Also happy to add a form for adding properties if someone wants to contribute to that exercise.   Meanwhile addd properties by updating the amr_props.php file - just add another line.

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

Add the provided calendar css to your themes stylesheet.  Please note that for some reason clearsky used different css classes etc for the widget. I am not redoingtheir whole plugin, so you will just have to deal with that - since you are unlikey to be using both, just put one or other batch of css in.
