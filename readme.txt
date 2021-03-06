=== amr property availability (orig clearksys) ===
Contributors: clearskys, anmari
Tags: calendar, property, properties, availability, widget, guest, bookings
Requires at least: 2.6
Tested up to: 4.2.2
Version: 1.3
Stable tag: trunk

Offers simple bookings management for simple properties. Has an availability calendar per property and a feed.

== Description ==
Simple property booking an availability plugin.  Works out the box for one property. Add your own little file from the sample for more properties.
Add guest details, booking and payment status.   NOTE there is a version 1.3 available but wp is not showing it as latest for unknown reason. https://downloads.wordpress.org/plugin/amr-clearskys-availability-modification-for-27.1.3.zip

This is a mod of the original Clearskys Property Availability calendar which appears to no longer exist.
This version was initially modified for 2.7.1 with wordpress in own directory, shortcodes, multiple properties (rudimentary).
I have done a simple update, removing deprecated calls and notices. 
It could use a property add module (that was in clearskys 'paid' version I think), 
so has a rudimentary method to add the properties if you need more than one.
For more than 1 property, copy the amr_props_sample.php to amr_props.php, add/deletes line as indicated!

It has now been slightly refreshed for wordpress 4.3  beta - minor changes.


== Version History ==
= Version 1.3 =
*   fixed deprecated calls and notices. 
*   added link in settings page to auto create the calendar availabiity shortode in a page.
*   basic testing on 4.3alpha

= Version 1.2 =
*   fixed deprecated calls and notices. 
*   basic testing on 3.4.1


= Version 1.1c =
*   fix plugin name everyhwere, including edit and delete !   

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
3. For more than 1 property, copy the amr_props_sample.php to amr_props.php, add/deletes line as indicated!
4. Add  [amr-clearskys-bookings  property=1 months=12] to a page and to reference a single property.  NB Property must be before month due to way that original logic was coded.
5. Manage the plugin through the Bookings admin screen.
6. Add calendar css to your css.  If you are using the provided css, Check in the Bookings config that the month block has <div class="calendar"><table>{monthblock}</table></div>

== Screenshots ==

1. Added Bookings section in admin menu, also shows two properties, same dates
2. Availability calendar in twenty fifteen responsive small screen
3. Add booking screen, also with property selection
4. Edit booking screen - shows property dropdown
5. The file to be edited to show your property names on dropdowns.
6. Calendar in page with custom styling


== Frequently Asked Questions ==

= Css?=

Add the provided calendar css to your themes stylesheet.  
Please note that for some reason clearsky used different css classes etc for the widget. 
I am not redoing their whole plugin, so you will just have to deal with that.
Since you are unlikey to be using both, just put one or other batch of css in.
