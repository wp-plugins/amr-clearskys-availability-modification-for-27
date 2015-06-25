<?php
/*
Plugin Name: amr property availability (orig clearksys)
Plugin URI: http://blog.clearskys.net/plugins/availability-plugin/
Description: A version of the clearksy availability calendar and administration booking management engine - modifoed for 2.7 compatibility
Version: 1.2
Author: clearskys, anmari
Author URI: http://blog.clearskys.net, http://anmari.com
*/
/*  Copyright 2007 clearskys.net Ltd  (email : team@clearskys.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2 as published by
    the Free Software Foundation .

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// amr define('WPCPLUGINPATH', (DIRECTORY_SEPARATOR != '/') ? str_replace(DIRECTORY_SEPARATOR, '/', dirname(__FILE__)) : dirname(__FILE__));

$endpos1 = strripos(dirname(__FILE__), '/');
$endpos2 = strripos(dirname(__FILE__), '\\');
if ($endpos1 < $endpos2) {$endpos1 = $endpos2;}
define('WPCPLUGINNAME', substr(dirname(__FILE__), $endpos1+1 ));
define('WPCPLUGINURL',  WP_PLUGIN_URL .'/' .WPCPLUGINNAME) ;
define('WPCPLUGINDIR',  dirname(__FILE__)) ;

include_once('includes/csbooking.php');
$amr_props[1] = "Rental Property 1";
@include('amr-props.php');

class CSbook
{
	
	/* * The boostrap function */ 
	function CSbook() 
	{ 
		// Add the installation and uninstallation hooks 
//amr		$file = WPCPLUGINPATH . '/' . basename(__FILE__); 
		$file = __FILE__; 
		register_activation_hook($file, array(&$this, 'install')); 
		register_deactivation_hook($file, array(&$this, 'uninstall'));  
		// Administration menu
		add_action('admin_menu', array(&$this,"add_admin_pages"));
		// Administration header styles and javascript
		add_action('admin_head', array(&$this,'add_admin_header'));
		// Front end
		add_shortcode('amr-clearskys-bookings', array(&$this,'process_hooks'));  /* keep for comptibility */
		add_shortcode('property-availability-calendar', array(&$this,'process_hooks'));  /* better descriptive shortcode */
	//	add_filter('the_content', array(&$this,'process_hooks'));
		// add ajax call trap
		add_action('init',array(&$this,'check_ajax'));
		// Feed output hooks
		add_action('init',array(&$this,'process_feed'));


	} 
		
	/* * The installation function */ 
	function install() { 
		global $wpdb;

  		$table_name = $wpdb->prefix . "cs_booking";
   		if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
   		{
			$sql = "CREATE TABLE ".$table_name." (
 			 `id` int(11) NOT NULL auto_increment,
			  `remote_id` int(11) default '0',
			  `property_id` int(11) default '0',
			  `startdate` varchar(10) default '0000-00-00',
			  `enddate` varchar(10) default '0000-00-00',
			  `starttime` varchar(5) default '00:00',
			  `endtime` varchar(5) default '00:00',
			  `title` varchar(250) default NULL,
			  `notes` text,
			  `status` int(11) default '0',
			  `rentername` varchar(50) default NULL,
			  `renteremail` varchar(45) default NULL,
			  `rentertel` varchar(30) default NULL,
			  `renternotes` text,
			  `depositamount` varchar(10) default '0',
			  `fullamount` varchar(10) default '0',
			  PRIMARY KEY  (`id`),
			  KEY `startdate` (`startdate`),
			  KEY `enddate` (`enddate`),
			  KEY `property_id` (`property_id`)
			);";
		 	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sql);
   		}
   		
   		// properties?  amr

	} 
	/* * The uninstallation function */ 
	function uninstall() { }


	function check_ajax() {
		if(!empty($_REQUEST['call']) and ($_REQUEST['call'] == 'ajax') ){
			if(stristr($_GET["page"],'clearskys-bookings') && /* amr */
			function_exists('current_user_can') && current_user_can('moderate_comments')) {
				$this->show_bookings_panel();
				exit();
			} else {
				switch($_REQUEST["action"]) {
					case "_csformsubmit":
						$html = $this->createform("ajax",$_REQUEST['pid']);
						$startat = strpos($html,"<!--start of enquiry form-->");
						$endat = strpos($html,"<!--end of enquiry form-->");
						if($startat && $endat) {
							$html = substr($html,$startat, ($endat + strlen("<!--end of enquiry form-->"))-$startat);
						} else {
							$html = "";
						}
						echo $html;
						exit();
						break;
					default:
						return;
				}
			}
		} else {
			return;
		}
	}


function cs_bookings_menu() { /* amr*/	
	    echo "<h2>Bookings</h2>";
		}

	// Setup the menu page
	function add_admin_pages() 
	{
	// Create the  submenus 

      $hookname = add_object_page('Bookings','Bookings','edit_published_posts',__FILE__,array(&$this,'show_bookings_panel'));
	  //'tools.php?page=amr-clearskys-bookings/clearskys-bookings.php',array(&$this,'show_bookings_panel'));   
	if(function_exists('add_submenu_page')) {
	   	// Add a submenu to the custom top-level menu: 
//	add_submenu_page(__FILE__, "List Bookings","List Bookings", 6,	'list_bookings', array(&$this,'show_bookings_panel')	);	
//		add_submenu_page(__FILE__, "List Bookings","List Bookings", 6,	
//		'tools.php?page=amr-clearskys-bookings/clearskys-bookings.php', array(&$this,'show_bookings_panel'));
//	add_submenu_page(__FILE__, "Add Booking", "Add Booking", 6, __FILE__, array(&$this,'handle_booking_form'));
		add_submenu_page(__FILE__, "Add Booking", "Add Booking", 'edit_published_posts', 'add_bookings', array(&$this,'handle_booking_form'));

		}		
	  

		/* amr add_menu_page('Manage Bookings', 'Bookings', 'Author', file, [function], [icon_url]);   */
		if (current_user_can('edit_published_posts') ) {
			if (current_user_can('manage_options') ) {
//				add_options_page(__('Booking options'), __('Bookings Config'), 8, __FILE__, array(&$this,'show_options_panel'));
			add_submenu_page (__FILE__,__('Booking options'), __('Settings'), 'manage_options',
			'bookings_config', array(&$this,'show_options_panel'));
			}
//amr			add_management_page("Manage Bookings", "Bookings", 6, __FILE__, array(&$this,'show_bookings_panel'));	
//amr			add_submenu_page("post-new.php", "Add Booking", "Add Booking", 6, __FILE__, array(&$this,'handle_booking_form'));
		}
	}
	
	function add_admin_header() {
		
//amr		$site_uri = get_option('home'); /* needs fixing - amr */
//amr		$plugin_uri = WP_PLUGIN_URL.'/amr-clearskys-bookings/includes/';  /* amr */
		
//amr		$plugin_uri = 'http://localhost/wptest/wp-content/plugins/clearskys/includes/';
		
		if(!empty ($_GET["page"]) and (stristr($_GET["page"], 'bookings' )))  {
			echo '<script type="text/javascript" src="' .WPCPLUGINURL . '/includes/js/yahoo-dom-event.js" ></script>';
			echo '<script type="text/javascript" src="' .WPCPLUGINURL . '/includes/js/connection.js" ></script>';
			echo '<script type="text/javascript" src="' .WPCPLUGINURL . '/includes/js/animation.js" ></script>';
			echo '<script type="text/javascript" src="' .WPCPLUGINURL . '/includes/js/container.js" ></script>';
			echo '<script type="text/javascript" src="' .WPCPLUGINURL . '/includes/js/bookingsearch.js" ></script>';
			//echo '<link rel="stylesheet" type="text/css" href="' . WPCPLUGINURL . '/includes/css/container.css" />';
			echo '<link rel="stylesheet" type="text/css" href="' . WPCPLUGINURL . '/includes/css/bookingsearch.css" />';
		}
		
/* amr */
		
		return;
	}
	
	function isquerystring($url)
	{
		return stristr($url,'?');
	}
	
	/**
	 * Function: show_options_panel
	 * Description: displays and handles the options configuration panel.
	 * It is designed to work on it's own (when no other related plugins are activated)
	 * or to be displayed in a single panel with all related plugins.
	 *
	 */
	
	function show_options_panel()
	{

		// Get current database settings
		$cs = get_option("clearskys_config");
		if(!isset($cs["clearskys_propertyno"]))
		{
			$cs["clearskys_propertyno"] = '1';
			
			$cs["clearskys_calendar_monthblock"] = '<table class="calendar">{monthblock}</table>';
			$cs["clearskys_calendar_monthtitle"] = "<caption>{title}</caption>";
			$cs["clearskys_calendar_weekheader"] = "<thead><tr><th scope='col'>S</th><th scope='col'>M</th><th scope='col'>T</th><th scope='col'>W</th><th scope='col'>T</th><th scope='col'>F</th><th scope='col'>S</th></tr></thead>";
			$cs["clearskys_calendar_datesblock"] = "<tbody>{month}</tbody>";
			$cs["clearskys_calendar_weekrow"] = "<tr>{week}</tr>";
			$cs["clearskys_calendar_bookeddate"] = "<td class='booked'><strong>{day}</strong></td>";
			$cs["clearskys_calendar_bookedstartdate"] = "<td class='booked startday'><strong>{day}</strong></td>";
			$cs["clearskys_calendar_bookedenddate"] = "<td class='booked endday'><strong>{day}</strong></td>";
			$cs["clearskys_calendar_availabledate"] = "<td>{day}</td>";
			$cs["clearskys_calendar_afterevery_number"] = "0";
			$cs["clearskys_calendar_afterevery"] = "";
			
			
		}
		if(!isset($cs["clearskys_publicpath"]))
		{
			$cs["clearskys_publicpath"] = "/availability/feed";
			$cs["clearskys_privatepath"] = "/availability/mysecretcode";
		}
		if(!isset($cs["clearskys_publiclocale"]))
		{
			$cs["clearskys_publiclocale"] = "en_UK.UTF-8";
			$cs["clearskys_adminlocale"] = "en_UK.UTF-8";
		}
		if(!isset($cs["clearskys_endweek"]))
		{
			$cs["clearskys_endweek"] = 6;
		}
		
		update_option("clearskys_config",$cs);
		
		
			if(isset($_POST['submitted']))
			{
				// Update clicked so write to the database here
				foreach ($_POST as $key => $value) {
					if(substr($key,0,9) == 'clearskys') {
						$cs[$key] = stripslashes($value);
					}
					
				}
				update_option("clearskys_config",$cs);
				echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
			
			} 

			//for($n = 0;$n < count($cs);$n++) {
			//	$cs[$n] = htmlspecialchars($cs[$n], ENT_QUOTES);
			//}
			
			
			?>
			<div class="wrap">
			<h2>Booking options</h2>
			<?php 
			echo '<p>Use shortcode [property-availability-calendar months=6 property=1] to show a property availability calendar</p>';
			echo '<p><a href="'.admin_url( 'post-new.php?post_type=page&content=[property-availability-calendar months=6 property=1]').'">Create page with shortcode</a></p>'; 
			
			echo '<h3>Styling of booked days:</h3><p>Add css like td.booked {background-color: #EEE;}  to highlight the booked cells</p>';
			?>
			<form method="post">
			
			<?php	
		
		?> 
			<fieldset class="options">
			<h3>Calendar Options</h3>
			<p>Use the settings below to modify the layout and style of each month in your calendar.</p>
			
			
			<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr valign="top"> 
			<th width="33%" scope="row"> Month Block:</th> 
			<td><textarea name="clearskys_calendar_monthblock" rows="2" cols="25" style="width: 30em; overflow: auto;"><?php echo htmlspecialchars($cs["clearskys_calendar_monthblock"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr>
			<tr valign="top"> 
			<th width="33%" scope="row"> Month Title:</th> 
			<td><textarea name="clearskys_calendar_monthtitle" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_monthtitle"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr>
			<tr valign="top"> 
			<th width="33%" scope="row"> Week Header:</th> 
			<td><textarea name="clearskys_calendar_weekheader" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_weekheader"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr>
			<tr valign="top"> 
			<th width="33%" scope="row"> Dates Block:</th> 
			<td><textarea name="clearskys_calendar_datesblock" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_datesblock"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr>
			<tr valign="top"> 
			<th width="33%" scope="row"> Week Row:</th> 
			<td><textarea name="clearskys_calendar_weekrow" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_weekrow"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr>
			<tr valign="top"> 
			<th width="33%" scope="row"> Booked Date:</th> 
			<td><textarea name="clearskys_calendar_bookeddate" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_bookeddate"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr><tr valign="top">
			<tr valign="top"> 
			<th width="33%" scope="row"> First Booked Date:</th> 
			<td><textarea name="clearskys_calendar_bookedstartdate" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_bookedstartdate"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr><tr valign="top">
			<tr valign="top"> 
			<th width="33%" scope="row"> Last Booked Date:</th> 
			<td><textarea name="clearskys_calendar_bookedenddate" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_bookedenddate"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr><tr valign="top"> 
			<th width="33%" scope="row"> Available Date:</th> 
			<td><textarea name="clearskys_calendar_availabledate" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_availabledate"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr>
			<tr valign="top"> 
			<th width="33%" scope="row"> Use the code below:</th> 
			<td>
			<select name="clearskys_calendar_afterevery_number" id="clearskys_calendar_afterevery_number">
				<option value="0" <?php if($cs["clearskys_calendar_afterevery_number"] == '0') echo "selected"; ?>>never</option>
				<option value="1" <?php if($cs["clearskys_calendar_afterevery_number"] == '1') echo "selected"; ?>>after every month</option>
				<option value="2" <?php if($cs["clearskys_calendar_afterevery_number"] == '2') echo "selected"; ?>>after every 2nd month</option>
				<option value="3" <?php if($cs["clearskys_calendar_afterevery_number"] == '3') echo "selected"; ?>>after every 3rd month</option>
				<option value="4" <?php if($cs["clearskys_calendar_afterevery_number"] == '4') echo "selected"; ?>>after every 4th month</option>
				<option value="5" <?php if($cs["clearskys_calendar_afterevery_number"] == '5') echo "selected"; ?>>after every 5th month</option>
				<option value="6" <?php if($cs["clearskys_calendar_afterevery_number"] == '6') echo "selected"; ?>>after every 6th month</option>
			</select>
			<br/>
			<textarea name="clearskys_calendar_afterevery" rows="2" cols="25" style="width: 30em; "><?php echo htmlspecialchars($cs["clearskys_calendar_afterevery"], ENT_QUOTES); ?></textarea>
			</td> 
			</tr>
			</table>
			</fieldset>
			
<?php /*			<fieldset class="options">
			<h2>Booking Options</h2>
			<p>If you are synchronising your bookings with a Clearskys.net server, then enter the assigned number of your property below. To find out the number of your property, login to your account at <a href="http://www.clearskys.net" target=_blank>Clearskys.net</a>
			and make a note of the number next to your property details on the left hand of your property list.</p>
			<p>If you are not synchronising, then leave the Property ID set to 1.</p>
			
			<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr valign="top"> 
			<th width="33%" scope="row"> Property ID number:</th> 
			<td><input name="clearskys_propertyno" type="text" id="clearskys_propertyno" value="<?php echo $cs["clearskys_propertyno"]; ?>" size="5" style="width: 5em; " /> 
			</td> 
			</tr>
			
			</table>
			</fieldset>
*/?>			
			<fieldset class="options">
			<h3>Calendar Feeds</h3>
			<p>Use the settings below to setup the URLs that you want use for your Calendar feeds. The public feed outputs basic information about the bookings and can be made available to your website users. You should make the private feed URL as un-guessable as possible as it outputs full information for the booking.</p>
			<p>Please ensure there are no trailing slashes in your Feed URL</p>
			<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr valign="top"> 
			<th width="33%" scope="row"> Public calendar path:</th> 
			<td><input name="clearskys_publicpath" type="text" id="clearskys_publicpath" value="<?php echo $cs["clearskys_publicpath"]; ?>" size="25" style="width: 20em; " /> 
			<?php
			if($cs["clearskys_publicpath"]!="") {
				echo "&nbsp;<a href='" .  get_option('siteurl')  . $cs["clearskys_publicpath"];
				if($this->isquerystring($cs["clearskys_publicpath"])) {
					echo "&property=" . 
					$cs["clearskys_propertyno"] . "&feed=ical";
				} else {
					echo "/" . $cs["clearskys_propertyno"] . "?feed=ical";
				}
				echo "' title='Subscribe to iCal feed'>";
				echo "<img src='" 
								. WPCPLUGINURL ."/includes/images/date22x22.png' alt='iCal feed' width='22' height='22' style='vertical-align: bottom;' />";
//amr				.  get_option('siteurl')  . "/wp-content/plugins/clearskys/includes/images/date22x22.png' alt='iCal feed' width='22' height='22' style='vertical-align: bottom;' />";
				echo "</a>&nbsp;<a href='" .  get_option('siteurl') . $cs["clearskys_publicpath"];
				if($this->isquerystring($cs["clearskys_publicpath"])) {
					echo "&property=" . 
					$cs["clearskys_propertyno"] . "&feed=RSS";
				} else {
					echo "/" . $cs["clearskys_propertyno"] . "?feed=RSS";
				}
				echo "' title='Subscribe to RSS feed'>";
				echo "<img src='" 
				.WPCPLUGINURL .  "/includes/images/feed-icon16x16.png' alt='RSS feed' width='16' height='16' style='vertical-align: text-top;' />";
//amr				.  get_option('siteurl')  . "/wp-content/plugins/clearskys/includes/images/feed-icon16x16.png' alt='RSS feed' width='16' height='16' style='vertical-align: text-top;' />";
				echo "</a>";
			}
			?>
			</td> 
			</tr>
			<tr valign="top"> 
			<th width="33%" scope="row"> Private calendar path:</th> 
			<td><input name="clearskys_privatepath" type="text" id="clearskys_privatepath" value="<?php echo $cs["clearskys_privatepath"]; ?>" size="25" style="width: 20em; " /> 
			<?php
			if($cs["clearskys_privatepath"]!="") {
				echo "&nbsp;<a href='" .  get_option('siteurl')  . $cs["clearskys_privatepath"];
				if($this->isquerystring($cs["clearskys_privatepath"])) {
					echo "&property=" . 
					$cs["clearskys_propertyno"] . "&feed=ical";
				} else {
					echo "/" . $cs["clearskys_propertyno"] . "?feed=ical";
				}
				echo "' title='Subscribe to iCal feed'>";
				echo "<img src='" .   WPCPLUGINURL ."/includes/images/date22x22.png' alt='iCal feed' width='22' height='22' style='vertical-align: bottom;' />";
				echo "</a>&nbsp;<a href='" .  get_option('siteurl') . $cs["clearskys_privatepath"];
				if($this->isquerystring($cs["clearskys_privatepath"])) {
					echo "&property=" . 
					$cs["clearskys_propertyno"] . "&feed=RSS";
				} else {
					echo "/" . $cs["clearskys_propertyno"] . "?feed=RSS";
				}
				echo "' title='Subscribe to RSS feed'>";
				echo "<img src='" .  WPCPLUGINURL . "/includes/images/feed-icon16x16.png' alt='RSS feed' width='16' height='16' style='vertical-align: text-top;' />";
				echo "</a>";
			}
			?>
			</td> 
			</tr>
			</table>
			</fieldset>
			
			<fieldset class="options">
			<h3>Language Options</h3>
			<p>Use the settings below to change the output language of the booking dates. Note: This currently only changes the language of the date information, the interface will still be in the current language.</p>
			
			<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr valign="top"> 
			<th width="33%" scope="row"> Public Locale:</th> 
			<td>
				<select name="clearskys_publiclocale" id="clearskys_publiclocale" style="width: 15em; ">
					<option value='en_UK.UTF-8' <?php if($cs["clearskys_publiclocale"] == 'en_UK.UTF-8') echo "selected"; ?>>English</option>
					<option value='es_ES.UTF-8' <?php if($cs["clearskys_publiclocale"] == 'es_ES.UTF-8') echo "selected"; ?>>Spanish</option>
					<option value='de_DE.UTF-8' <?php if($cs["clearskys_publiclocale"] == 'de_DE.UTF-8') echo "selected"; ?>>German</option>
					<option value='fr_FR.UTF-8' <?php if($cs["clearskys_publiclocale"] == 'fr_FR.UTF-8') echo "selected"; ?>>French</option>
				</select>	
			</td> 
			</tr>
			<tr valign="top"> 
			<th width="33%" scope="row"> Admin Locale:</th> 
			<td>
				<select name="clearskys_adminlocale" id="clearskys_adminlocale" style="width: 15em; ">
					<option value='en_UK.UTF-8' <?php if($cs["clearskys_adminlocale"] == 'en_UK.UTF-8') echo "selected"; ?>>English</option>
					<option value='es_ES.UTF-8' <?php if($cs["clearskys_adminlocale"] == 'es_ES.UTF-8') echo "selected"; ?>>Spanish</option>
					<option value='de_DE.UTF-8' <?php if($cs["clearskys_adminlocale"] == 'de_DE.UTF-8') echo "selected"; ?>>German</option>
					<option value='fr_FR.UTF-8' <?php if($cs["clearskys_adminlocale"] == 'fr_FR.UTF-8') echo "selected"; ?>>French</option>
				</select>	
			</td> 
			</tr>
			</table>
			</fieldset>
			
			<fieldset class="options">
			<h3>Start of Week</h3>
			<p>Set the starting day of the week below. Make sure that you also modify the <b>Week Header</b> setting above so that the labels match the days.</p>
			
			<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr valign="top"> 
			<th width="33%" scope="row"> Start day:</th> 
			<td>
				<select name="clearskys_endweek" id="clearskys_endweek" style="width: 15em; ">
					<option value='6' <?php if($cs["clearskys_endweek"] == '6') echo "selected"; ?>>Sunday</option>
					<option value='0' <?php if($cs["clearskys_endweek"] == '0') echo "selected"; ?>>Monday</option>
				</select>	
			</td> 
			</tr>
			</table>
			</fieldset>
			
			
			<p class="submit"><input type="submit" name="Submit" value="Update Settings &raquo;" />
			 
			</p>
		
		
		<input type="hidden" name="submitted" value="yes" />
		</form>
		</div>
		<?php
		
		
	}
	
	/**
	 * Function: show_bookings_panel
	 * Description: Main function that redirects calls to different functions
	 * for each operation on the admin panel
	 *
	 */
	
	function show_bookings_panel()
	{ 
		//handle booking operations
		if (!empty($_REQUEST['action']) ) {
		switch($_REQUEST['action']) {
			case "search":
				$this->show_panel();
				break;
			case "ajaxsearch":
				$this->show_booking_results();
				break;
			case "delete":
				// Shows the delete booking panel
				//$this->delete_booking($_POST['booking_ID']);
				break;
			case "ajaxdelete":
				$this->ajaxdelete_booking();
				break;
			case "edit":
				$this->handle_booking_form();
				break;
			case "update":
				$this->handle_booking_form();
				break;
			default:
				// Shows the default booking list panel
				$this->show_panel();
				break;
		}
		}
		else $this->show_panel();
	}
	
	function show_booking_results() {
		// Get current database settings
		global $wpdb;
		global $amr_props;
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$site_uri = get_option('siteurl');
  		$plugin_uri =  WPCPLUGINURL .'/includes/'; /* amr */
  		setlocale(LC_ALL,$cs["clearskys_adminlocale"]);
 		if (!empty ($_REQUEST["property_id"])) 
			$propertyid = $_REQUEST["property_id"];
		else 
			$propertyid = 1;	
		
		$spropertyid = $propertyid;		
		if (!isset($_REQUEST['action']) or ( $_REQUEST['action'] == "")) {

			$rows = $booking->getinitiallist();
			$nomsg = "There are no bookings taking place this month... Search to see further.";
			$backlist = "&amp;baction=";
			$stype = "initial";
		} 
		else {
			$nomsg = "You do not currently have any bookings with this criteria...";
			$rows = array();
			if ($propertyid) {
				$spropertyid = $booking->xss_clean($propertyid);
				if (isset($_REQUEST["sstatus"])) 
					$sstatus = $booking->xss_clean($_REQUEST["sstatus"]);
				else
					$sstatus = '';
				if (isset($_REQUEST["m"])) {
					$smonth = $booking->xss_clean($_REQUEST["m"]); 
					}
				else $smonth = ''; 
				if (isset($_REQUEST["s"])) 
					$stext = $booking->xss_clean($_REQUEST["s"]);
				else 
					$stext = '';
				
				$backlist = "&amp;propertyid=" . $spropertyid . "&amp;sstatus=" . $sstatus . "&amp;m=" . $smonth . "&amp;s=" . $stext . "&amp;baction=search";
				
				$rows = $booking->getsearchlist($stext, $sstatus, $smonth, $spropertyid);
				
			}
		}
  		
		if (empty($sstatus)) 	$sstatus= '';
		if (empty($smonth)) 	$smonth= '';;
		if (empty($stext)) 		$stext = '';
		if (empty($stype)) 		$stype = '';
		?> 
		 
		<div class="resultsheader">
		<span class="searchheading">Search results (NB re-search to see all)( 
		<a href="<?php echo add_query_arg('page','add_bookings',get_admin_url('','admin.php')); ?>">add new booking</a>)</span> 
		<?php /* amr */
			if($cs["clearskys_privatepath"]!="") {
				echo "<a class='feedlink' href='" .  get_option('siteurl') . $cs["clearskys_privatepath"];
				if($this->isquerystring($cs["clearskys_privatepath"])) {
					echo "&property=" . $spropertyid . "&feed=RSS";
				} else {
					echo "/" . $spropertyid . "?feed=RSS";
				}
				echo "&sstatus=" . $sstatus;
				echo "&m=" . $smonth;
				echo "&s=" . $stext;
				echo "&type=" . $stype;
				
				echo "' title='Subscribe to RSS feed'>";

				echo '<img src="' .  
				//get_option('siteurl')  . "/wp-content/plugins" amr
				WPCPLUGINURL.
				'/includes/images/feed-icon16x16.png" alt="RSS feed" width="16" height="16" style="vertical-align: text-top;" />';
				echo "</a>";
				echo "<a class='feedlink' href='" .  get_option('home')  . $cs["clearskys_privatepath"]; /* amr */
				if($this->isquerystring($cs["clearskys_privatepath"])) {
					echo "&property=" . $spropertyid . "&feed=ical";
				} else {
					echo "/" . $spropertyid . "?feed=ical";
				}
				if($sstatus != "") echo "&sstatus=" . $sstatus;
				if($smonth != "") echo "&m=" . $smonth;
				if($stext != "") echo "&s=" . $stext;
				if($stype != "") echo "&type=" . $stype;
				echo "' title='Subscribe to iCal feed'>";
/* amr*/				echo "<img src='" .  WPCPLUGINURL  ."/includes/images/date16x16.png' alt='iCal feed' style='vertical-align: text-top;' />";
				echo "</a>";
			}
			?>
		<div style="clear: both;"></div>
		</div>
		<?php
		if(count($rows) == 0) {
 		?>
 			<div class='norows'>
		 	<?php echo $nomsg; ?>
		 	</div>
		 <?php
 		} else {
 			
 			// check for property plugin
 			if(class_exists('CSproperty')) {
				$csproperty = new CSproperty($wpdb,$wpdb->prefix);
			}
 			$rowclass = ' alternate'; 			
 			foreach($rows as $row) {
				if ($rowclass === ' alternate') {$rowclass = '';}
				else {$rowclass = ' alternate';}
				
 				echo "<div class='bookingrow$rowclass' id='booking-" . $row['id'] . "'>";
 				echo "<div class='calendarstart'>";
 				$today = strtotime($row['startdate']);
 				echo "<span class='month'>" . ucfirst(strftime("%b",$today)) . "</span>";
 				echo "<span class='day'><abbr title='" . ucfirst(strftime("%A",$today)) ."'>" . date("d",$today) . "</abbr></span>";
 				echo "<span class='year'>" . date("Y",$today) . "</span>";
 				echo "</div>";
 				echo "<div class='calendarend'>";
 				$today = strtotime($row['enddate']);
 				echo "<span class='month'>" . ucfirst(strftime("%b",$today)) . "</span>";
 				echo "<span class='day'><abbr title='" . ucfirst(strftime("%A",$today)) ."'>" . date("d",$today) . "</abbr></span>";
 				echo "<span class='year'>" . date("Y",$today) . "</span>";
 				echo "</div>";
 				echo "<span class='title'>" . $row['title'] . "</span>";
 				echo "<span class='property'>Property: ";
 				if(isset($csproperty)) {
 					$ref = $csproperty->getrefforproperty($row['property_id']);
 					echo ($ref) ? $ref[0]['reference'] : $row['property_id'];
 				} else {
 					echo $amr_props[$row['property_id']]; /* amr */
 				}
 				echo " (" . $booking->status($row['status']);
 				echo ")";
 				echo "</span>";
 				echo "<span class='rentor'>";
 				if($row['renteremail'] != "") echo "<a href='mailto:" . $row['renteremail'] . "' title='Click to send email to " . $row['renteremail'] . "'>";
 				if($row['rentername'] != "") {
 					echo $row['rentername'];
 				} else {
 					echo "No Renter name";
 				}
 				if($row['renteremail'] != "") echo "</a>";
 				echo " (";
 				echo ($row['rentertel'] != "") ? $row['rentertel'] : "No Telephone";
 				echo ")";
 				echo "</span>";
 				// Edit and delete menu
 				echo "<span class='menu'>";
 /*amr */				echo "<a class='editlink' id='editbooking-" . $row['id'] . "' href='admin.php?page="
					. WPCPLUGINNAME."/clearskys-bookings.php&amp;bookingid=" 
				    . $row['id'] . $backlist . "&amp;action=edit' title='Click to edit this booking'>";
 				echo "edit</a>";
 				echo " | ";
 				echo "<a class='deletelink' id='deletebooking-" . $row['id'] . "' href='admin.php?admin.php?page=".WPCPLUGINNAME."/clearskys-bookings.php&amp;bookingid=" 
				. $row['id'] . "&amp;action=delete'' title='Click to delete this booking'>";
 				echo "delete</a>";
 				echo "</span>";
 				echo "<div style='clear: both;'></div>";
 				echo "</div>";
 			}
 		}
	}
	
	/**
	 * Function: show_panel
	 * Description: Handles the main booking management functionality
	 * including the listing and searches
	 *
	 */
	function show_panel($msg = "") {
		// Get current database settings
		global $wpdb;
		global $amr_props;
		
		//$amr_props = amr_get_properties();  //amr wtf
		
		$pageheading = "Booking management";
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$site_uri = get_option('siteurl'); /* amr */
  		$plugin_uri =  WPCPLUGINURL .'/includes/';
  		setlocale(LC_ALL,$cs["clearskys_adminlocale"]);
		
		if (!empty ($_REQUEST['propertyid'])) 
			$propertyid = $_REQUEST['propertyid'];
		else
			$propertyid = '';
			
  		if (empty($amr_props)) {
			
			create_properties_notice();
			die();
			}
		
		if($msg != "") {
			echo '<div id="message" class="updated fade"><p><strong>' . $msg . '</strong></p></div>';
		}
  		?>
		<div class="wrap">
			<h2><?php echo $pageheading; ?></h2>
			
			<div id="bookingresultpanel">
			<?php $this->show_booking_results(); ?>
			</div>
			
			<div id="bookingsidepanel">
				<table id="bookingsearch" class="searchpanel" width="100%" cellpadding="0" cellspacing="0">
					<thead>
						<tr>
						<th><span class="searchheading">Search bookings</span>
						<span id="busysearch"><img src="<?php echo $plugin_uri; ?>images/indicator.gif" width="16" height="16" alt="Processing..." /></span>
						</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td valign="top">
								<form action="admin.php?page=amr-clearskys-bookings/clearskys-bookings.php" method="get" id="bookingsearchform">
									<fieldset>
									
									<label for="sstatus">Show with status:</label>
									<select name='sstatus' id='sstatus'>
								    <?php
								    			$stats = $booking->statuslist();
												if (!empty($_REQUEST['sstatus'])) 
													$st = $booking->xss_clean($_REQUEST['sstatus']);
												else $st = '';	
								    			echo '<option value="" ';
								    			if($st == "")  echo 'selected="selected"';
								    			echo '>Any Status</option>';
								    			foreach($stats as $key => $value) {
								    				echo '<option  value="' . $key . '" ';
								    				if($st != "" && $st == $key) echo 'selected="selected"';
								    				echo '>' . $value . '</option>';
								    			}
								    		
								    ?>
									</select>
									<label for="propertyid">For property:</label>
									<select name="propertyid" id="propertyid">
										<option value="" <?php if('' == $propertyid) echo 'selected="selected"'; ?>>All Properties</option>
									<?php

										if(class_exists('CSproperty')) {

											$csproperty = new CSproperty($wpdb,$wpdb->prefix);
											$rows = $csproperty->getpropertyreferencelist(True,3);
											foreach($rows as $row) {
												echo '<option value="' . $row['id'] . '" ';
												if($row['id'] == $_REQUEST['propertyid']) echo 'selected="selected"';
												echo '>';
												echo ($row['reference'] != "") ? $row['reference'] : $row['property_id'];
												echo '</option>';
											}
										} else {
									
												if (isset($amr_props)) foreach($amr_props as $key => $value) {
							    				echo '<option  value="' . $key. '" ';
							    				if(($propertyid != "") && ($propertyid == $key)) echo 'selected="selected"';
							    				echo '>' . $value . '</option>';
								    			}
												
	//		amr								$rows = $booking->getpropertylist();
	//										foreach($rows as $row) {
	//											echo '<option value="' . $row['property_id'] . '" ';
	//											if($row['property_id'] == $_REQUEST['propertyid']) echo 'selected="selected"';
	//											echo '>' . $row['property_id'] . '</option>';
	//										}
										}
										?>
									</select>

									<label for="m">Starting in:</label>
									<select name='m' id='m'>
								    <?php
								    	// get the lowest month and year and increase to next year
								    		
								    		$lowestbooking = $booking->getfirstdate();
								    		if(is_null($lowestbooking))
								    		{
								    			echo '<option  value="">No Bookings entered</option>';
								    		} else {
								    			$options = $booking->getstartdates();
								    			if (!empty($_REQUEST['m'])) 
													$m = $booking->xss_clean($_REQUEST['m']);
												else 
													$m = '';
								    			echo '<option  value="">All months</option>';
								    			foreach($options as $opt) {
								    				echo '<option  value="' . $opt['optval'] . '" ';
								    				if($m == $opt['optval']) echo 'selected';
								    				$zdate = strtotime($opt['optstart']);
								    				echo '>' . ucfirst(strftime("%B %Y",$zdate)) . ' (' . $opt['optnum'] .')</option>';
								    			}
								    		}
								    ?>
									</select>
									<?php if (!empty($_REQUEST['s'])) 
										$s = htmlspecialchars(strip_tags(stripslashes($_REQUEST['s'])), ENT_QUOTES); 
									else 
										$s = '';
									?>
									<label for="s">Containing the text:</label>
									<input name="s" id="s" type="text" value="
									<?php echo $s; ?>" size="20" />
									
									<input name="search" id="search" type="submit" value="Search" />

									<input type="hidden" name="action" value="search" style="display: none;"/>
									</fieldset>
								</form>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div style="clear: both;"></div>
		</div>
		<?php
	}
	
	function ajaxdelete_booking()
	{
		global $wpdb;
		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		do_action("clearskys_booking_pre_delete",$_GET['bookingid']);
  		echo $booking->delete($_GET['bookingid']);
  		do_action("clearskys_booking_post_delete",$_GET['bookingid']);
	}
	
	function delete_booking($id)
	{
		// Get current database settings
		global $wpdb;
		
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$booking->setid($id);
  		$siteurl = get_option('siteurl');
  		
  		do_action("clearskys_booking_predelete", $id);
		$result = $booking->delete($id);
		do_action("clearskys_booking_postdelete", $id);
  		
  		if($result == "") {
  			// Add Ok
  			$this->show_panel("Booking deleted.");
  		} else
  		{   echo $result;
  			// Error show form again with msg
  			$this->show_panel("Error: Could not delete booking.");
  		}
	}
	
	function update_booking($id)
	{
		// Get current database settings
		global $wpdb;
		
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$booking->setid($id);
  		$siteurl = get_option('siteurl');
  		// Get the entered details
  		$data = array(
  					'id' => $id,
  					'property_id' => $_POST['property_id'],
  					'title' => $_POST['bookingtitle'],
  					'startdate' => $_POST['startmonth'] . '-' . $_POST['startday'],
  					'enddate' => $_POST['endmonth'] . '-' . $_POST['endday'],
  					'status' => $_POST['status'],
  					'notes' => $_POST['notes'],
  					'rentername' => $_POST['rentername'],
  					'renteremail' => $_POST['renteremail'],
  					'rentertel' => $_POST['rentertel'],
  					'renternotes' => $_POST['renternotes'],
  					'depositamount' => $_POST['depositcurrency'] . $_POST['depositamount'],
  					'fullamount' => $_POST['fullcurrency'] . $_POST['fullamount'],
  					'starttime' => $_POST['starthour'] . ':' . $_POST['startmin'],
  					'endtime' => $_POST['endhour'] . ':' . $_POST['endmin']
  					);
  		$result = $booking->update($data);
  		do_action("clearskys_booking_update", $data);
  		
  		if($result == "") {
  			// Add Ok
  			//wp_redirect("edit.php?page=clearskys/clearskys-bookings.php");
  			//exit();
  			$this->show_panel("Booking details updated.");
  		} else
  		{
  			// Error show form again with msg
  			$data = $data + array("id" => $id);
  			$this->show_edit_panel($id, $result, $data);
  			
  		}
	}
	
	function add_booking()
	{
		// Get current database settings
		global $wpdb;
		
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$siteurl = get_option('siteurl');
  		// Get the entered details
  		$data = array(
  					'property_id' => $_POST['property_id'],
  					'title' => $_POST['bookingtitle'],
  					'startdate' => $_POST['startmonth'] . '-' . $_POST['startday'],
  					'enddate' => $_POST['endmonth'] . '-' . $_POST['endday'],
  					'status' => $_POST['status'],
  					'notes' => $_POST['notes'],
  					'rentername' => $_POST['rentername'],
  					'renteremail' => $_POST['renteremail'],
  					'rentertel' => $_POST['rentertel'],
  					'renternotes' => $_POST['renternotes'],
  					'depositamount' => $_POST['depositcurrency'] . $_POST['depositamount'],
  					'fullamount' => $_POST['fullcurrency'] . $_POST['fullamount'],
  					'starttime' => $_POST['starthour'] . ':' . $_POST['startmin'],
  					'endtime' => $_POST['endhour'] . ':' . $_POST['endmin']
  					);
  		$data = apply_filters('clearskys_booking_add_data', $data);
  		$result = $booking->add($data);
  		do_action("clearskys_booking_post_add", $data);
  		
		if($result == "") {
  			// Add Ok
  			$this->show_add_panel("Booking added successfully. <a href='admin.php?page="
			.WPCPLUGINNAME."/clearskys-bookings.php'>View bookings.</a>");
  		} else
  		{
  			// Error show form again with msg
  			$this->show_add_panel($result, $data);
  			//echo $result;
  		}
	}
	
	
	function handle_booking_form() {


		if(isset($_POST["action"])) {
			// add or update so process form
			switch($_POST["action"]) {
				case "add":
					$this->add_booking();
					break;
				case "update":
					$this->update_booking($_POST['id']);
					break;
				default:
					$this->show_panel();
					break;
			}
		} else {
			if(isset($_GET["bookingid"])) {
				// edit so grab id and display form
				$this->show_edit_panel($_GET["bookingid"]);
			} else {
				// add so display blank form
				$this->show_add_panel();
			}
		}
	}
	
	function show_add_panel($msg = "", $error = False)
	{
		// Get current database settings
		global $wpdb;
		
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$siteurl = get_option('siteurl');
		
		if($msg != "") {
			echo '<div id="message" class="updated fade"><p><strong>' . $msg . '</strong></p></div>';
		}
  		if($error) {
  			$row = $error;
  		} else {
  			$row = array(	
			'id'=> 1,
			'title'=>'',
			'property_id'=>'1',
			'rentername'=>'',
			'renteremail'=>'',
			'rentertel'=>''	,		
			'startdate' => date('Y-m-d'),
  			'enddate' => date('Y-m-d'),
  			'depositamount' => '0',
  			'fullamount' => '0',
			'status' => '',
			'renternotes' => '',
			'notes' => ''
  						);
  		}
		//print_r($row);
  		?>
		<script type="text/javascript" src="<?php echo WPCPLUGINURL ; ?>/includes/js/booking.js"></script>
		<div class="wrap">
			    <h2><a name="addnew"></a>Add New Booking</h2>
			    <?php
			    $this->add_booking_form($row, $booking, $cs);
			    ?>
			</div>
		<?php
	}
	
	
	function add_booking_form(
		$row,
		$booking, $cs)
	{
		global $wpdb;
		global $amr_props;
		
		if(class_exists('CSproperty')) {
			$csproperty = new CSproperty($wpdb,$wpdb->prefix);
		}	
		
		?>
		<form name="addbooking" id="addbooking" action="" method="post" onsubmit="return checkWholeForm(addbooking);">
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<th scope="col" width="100%" colspan="2">Booking Details</th>
				</tr>
				<tr>
					<td class="" valign="top">
					<?php
					if(class_exists('CSproperty')) {
						$prows = $csproperty->getpropertyreferencelist(true,3);
						if($prows) {
							?>
							<p><label for="property_id"><strong>Property reference</strong></label><br />
							<select name="property_id" id="property_id" style="width: 25em; padding: 2px;">
							<?php
							foreach($prows as $prow) {
								?>
								<option value="<?php echo $prow['id']; 
								?>" <?php echo ($row['property_id'] == $prow['id']) ? 'selected="selected"' : ''; 
								?>><?php echo $prow['reference']; ?></option>
								<?php
							}
							?>
							</select>
							</p>
							<?php
						} else {
							?>
							<input type="hidden" name="property_id" value="<?php echo $cs['clearskys_propertyno']; ?>" />
							<?php
						} 
					} 
					else { 
						if (!empty ($amr_props)) {	
							//$row = $amr_props;
								?>
						<p><label for="property_id"><strong>Property reference</strong></label><br />
						<select name="property_id" id="property_id" style="width: 15em; padding: 2px;">
								<?php
								foreach($amr_props as $key => $name) {
									?>
									<option value="<?php echo $key; ?>"> <?php echo $name; ?></option>
									<?php
								}
								?>
								</select>
								</p>
								<?php
								}
						else {	
							
							?>		
							
							</p>
									<input type="hidden" name="property_id" value="<?php echo $cs['clearskys_propertyno']; ?>" /> 
									<?php
								
						}
					}
					?>

					<p><label for="bookingtitle"><strong>Title</strong></label><br />
					<input name="bookingtitle" id="bookingtitle" 
					value="<?php echo $row['title']; ?>" size="35" style="width: 25em; padding: 2px;" />
					</p>
					<p><label for="startday"><strong>Arrival date / time</strong></label><br />
					<select name="startday" id="startday" style="width: 5em; padding: 2px;">
					<?php
					for($n=1;$n <= 31;$n++) {
						echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT) . '"';
						if($n == substr($row['startdate'],8,2)) { echo " selected"; }
						echo '>' . str_pad($n,2,"00",STR_PAD_LEFT) . '</option>';
					}
					?>
					</select>&nbsp;
					<select name="startmonth" id="startmonth" style="width: 10em; padding: 2px;">
					<?php
						// get the span for the drop downs
						//setlocale(LC_TIME,$cs["clearskys_adminlocale"]);
						
						$adate = strtotime(date("Y") . '-' . date("m") . '-01');
						$sdate = strtotime('-6 months',$adate);
						$edate = strtotime('+18 months',$adate);
						for($cdate = $sdate; $cdate < $edate; $cdate = strtotime('+1 month',$cdate)) {
							echo '<option value="' . date("Y-m",$cdate) . '"';
							if(substr($row['startdate'],0,7) == date("Y-m",$cdate)) { echo " selected"; }
							echo '>' . ucfirst(strftime("%b %Y",$cdate)) . '</option>';
						}
					?>
					</select><br />
					<select name="starthour" id="starthour" style="width: 5em; padding: 2px;">
					<?php
						for($n=0;$n<=23;$n++) {
							echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT);
							echo '">'. str_pad($n,2,"00",STR_PAD_LEFT);
							echo '</option>';
						}
					?>
					</select>&nbsp;
					<select name="startmin" id="startmin" style="width: 5em; padding: 2px;">
					<?php
						for($n=0;$n<=59;$n++) {
							echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT);
							echo '">:'. str_pad($n,2,"00",STR_PAD_LEFT);
							echo '</option>';
						}
					?>
					</select>
					</p>
					<p><label for="endday">
					<strong>Departure date / time</strong></label><br />
					<select name="endday" id="endday" style="width: 5em; padding: 2px;">
					<?php
					for($n=1;$n <= 31;$n++) {
						echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT) . '"';
						if(substr($row['enddate'],8,2) == $n) { echo " selected"; }
						echo '>' . str_pad($n,2,"00",STR_PAD_LEFT) . '</option>';
					}
					?>
					</select>&nbsp;
					<select name="endmonth" id="endmonth" style="width: 10em; padding: 2px;">
					<?php
						// get the span for the drop downs
						for($cdate = $sdate; $cdate < $edate; $cdate = strtotime('+1 month',$cdate)) {
							echo '<option value="' . date("Y-m",$cdate) . '"';
							if(substr($row['enddate'],0,7) == date("Y-m",$cdate)) { echo " selected"; }
							echo '>' . ucfirst(strftime("%b %Y",$cdate)) . '</option>';
						}
					?>
					</select><br />
					<select name="endhour" id="endhour" style="width: 5em; padding: 2px;">
					<?php
						for($n=0;$n<=23;$n++) {
							echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT);
							echo '">'. str_pad($n,2,"00",STR_PAD_LEFT);
							echo '</option>';
						}
					?>
					</select>&nbsp;
					<select name="endmin" id="endmin" style="width: 5em; padding: 2px;">
					<?php
						for($n=0;$n<=59;$n++) {
							echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT);
							echo '">:'. str_pad($n,2,"00",STR_PAD_LEFT);
							echo '</option>';
						}
					?>
					</select>
					</p>
					<p><label for="status"><strong>Booking Status</strong></label><br />
					<select name="status" id="status" style="width: 15em; padding: 2px;">
					<?php
						$status = $booking->statuslist();
						foreach($status as $key => $value) {
							echo "<option value=\"$key\"";
							if($key == $row['status']) { echo " selected"; }
							echo ">$value</option>";
						}
					?>
					</select></p>
					<p><label for="depositamount"><strong>Deposit Amount</strong></label><br />
					<select name="depositcurrency" id="depositcurrency" style="width: 5em; padding: 2px;">
					<option value=""></option>
					<option value="USD">USD</option>
					<option value="GBP">GBP</option>
					<option value="EUR">EUR</option>
					</select>&nbsp;
					<input name="depositamount" id="depositamount" value="<?php echo $row['depositamount']; ?>" size="10" style="width: 10em; padding: 2px;" />
					</p>
					<p><label for="fullamount"><strong>Full Amount</strong></label><br />
					<select name="fullcurrency" id="fullcurrency" style="width: 5em; padding: 2px;">
					<option value=""></option>
					<option value="USD">USD</option>
					<option value="GBP">GBP</option>
					<option value="EUR">EUR</option>
					</select>&nbsp;
					<input name="fullamount" id="fullamount" value="<?php echo $row['fullamount']; ?>" size="10" style="width: 10em; padding: 2px;" />
					</p>
					</td>
					<td class="" valign="top">
					<p><label for="notes"><strong>Notes</strong></label><br />
					<textarea name="notes" id="notes" rows="10" cols="35" style="width: 90%; padding: 2px;"><?php echo stripslashes($row['notes']);?></textarea>
					</p>
					</td>
				</tr>
			</table>
			
			<br />
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
			        <th scope="col" width="100%" colspan="2">Guest Details</th>
				</tr>
				<tr>
					<td class="alternate" valign="top">
					<p><label for="rentername"><strong>Guests Name</strong></label><br />
					<input name="rentername" id="rentername" value="<?php echo stripslashes($row['rentername']);?>" size="35" style="width: 25em; padding: 2px;" />
					</p>
					<p><label for="renteremail">
					<strong>Email address</strong></label><br />
					<input name="renteremail" id="renteremail" value="<?php echo $row['renteremail'];?>" size="35" style="width: 25em; padding: 2px;" />
					</p>
					<p><label for="rentertel">
					<strong>Telephone number</strong></label><br />
					<input name="rentertel" id="rentertel" value="<?php echo $row['rentertel'];?>" size="35" style="width: 25em; padding: 2px;" />
					</p>
					</td>
					<td class="alternate" valign="top">
					<p><label for="renternotes">
					<strong>Notes</strong></label><br />
					<textarea name="renternotes" id="renternotes" rows="10" cols="35" style="width: 90%; padding: 2px;"><?php echo stripslashes($row['renternotes']);?></textarea>
					</p>
					</td>
				</tr>
			</table>
			<br />
			
			<input type="hidden" name="action" value="add" />
	    		<input type="submit" name="addbooking" value="Add Booking &raquo;" />
	    		
	    </form>
		<br style="clear:both;" />
		
		<?php
	}
	
	function show_edit_panel($id, $msg = "", $error = False)
	{
		// Get current database settings
		global $wpdb;
		
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$id = $booking->xss_clean($id);
  		$booking->setid($id);
  		$siteurl = get_option('siteurl');
  		setlocale(LC_ALL,$cs["clearskys_adminlocale"]);
		
		if($msg != "") {
			echo '<div id="message" class="updated fade"><p><strong>' . $msg . '</strong></p></div>';
		}
  		if($error) {
  			$row = $error;
  		} else {
  			$row = $booking->getbooking();
  		}
		
  		?>
		<script type="text/javascript" src="<?php echo  WPCPLUGINURL ; ?>/includes/js/booking.js"></script>
		<div class="wrap">
		    <h2><a name="addnew"></a>Edit Booking</h2>
		    <?php
		    
		    $this->edit_booking_form($row, $booking, $cs);
		    ?>
		</div>
		<?php
	}
	
	function edit_booking_form($row, $booking, $cs)
	{
		global $wpdb;
		global $amr_props;
		
		$amr_props = amr_get_properties();
		
		if(!is_null($row)) {
		?>
		<form name="editbooking" id="editbooking" action="" method="post" onsubmit="return checkWholeForm(editbooking);">
			<input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<th scope="col" width="100%" colspan="2">Booking Details</th>
				</tr>
				<tr>
					<td class="" valign="top">
					<?php
					if(isset($csproperty)) {
						$prows = $csproperty->getpropertyreferencelist(true,3);
						if($prows) {
							?>
							<p><label for="property_id">
							<strong>Property reference</strong></label><br />
							<select name="property_id" id="property_id" style="width: 25em; padding: 2px;">
							<option value="0">No property set</option>
							<?php
							foreach($prows as $prow) {
								?>
								<option value="<?php echo $prow['id']; ?>" <?php echo ($row['property_id'] == $prow['id']) ? 'selected="selected"' : ''; ?>><?php echo $prow['reference']; ?></option>
								<?php
							}
							?>
							</select>
							</p>
							<?php
						} 
						else {
							?>
							<input type="hidden" name="property_id" value="<?php echo $row['property_id']; ?>" />
							<?php
						}
					}
					else { 
						if (isset ($amr_props)) {			
									?>
							<p><label for="property_id"><strong>Property reference</strong></label><br />
							<select name="property_id" id="property_id" style="width: 15em; padding: 2px;">
									<?php
									foreach($amr_props as $key => $name) {
										?>
										<option value="<?php echo $key; ?>"<?php if ($row['property_id'] == $key) {
													echo ' selected="selected"'; }?>
										> <?php echo $name; ?></option>
										<?php
									}
									?>
									</select>
									</p>
									<?php
						}					

					 else {
							?>
							<input type="hidden" name="property_id" value="<?php echo $row['property_id']; ?>" />
							<?php
						} 
					}
					?>
					<p><label for="bookingtitle">
					<strong>Title</strong></label><br />
					<input name="bookingtitle" id="bookingtitle" value="<?php echo $row['title'];?>" size="35" style="width: 25em; padding: 2px;" />
					</p>
					<p><label for="startday">
					<strong>Arrival date / time</strong></label><br />
					<select name="startday" id="startday" style="width: 5em; padding: 2px;">
					<?php
					for($n=1;$n <= 31;$n++) {
						echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT) . '"';
						if($n == substr($row['startdate'],8,2)) { echo " selected"; }
						echo '>' . str_pad($n,2,"00",STR_PAD_LEFT) . '</option>';
					}
					?>
					</select>&nbsp;
					<select name="startmonth" id="startmonth" style="width: 10em; padding: 2px;">
					<?php
						// get the span for the drop downs
						
						
						$adate = strtotime(date("Y") . '-' . date("m") . '-01');
						$sdate = strtotime('-6 months',$adate);
						$edate = strtotime('+18 months',$adate);
						for($cdate = $sdate; $cdate < $edate; $cdate = strtotime('+1 month',$cdate)) {
							echo '<option value="' . date("Y-m",$cdate) . '"';
							if(substr($row['startdate'],0,7) == date("Y-m",$cdate)) { echo " selected"; }
							echo '>' . ucwords(strftime("%b %Y",$cdate)) . '</option>';
						}
					?>
					</select><br />
					<select name="starthour" id="starthour" style="width: 5em; padding: 2px;">
					<?php
						for($n=0;$n<=23;$n++) {
							echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT) . '"';
							if(str_pad($n,2,"00",STR_PAD_LEFT) == substr($row['starttime'],0,2)) echo " selected";
							echo '>'. str_pad($n,2,"00",STR_PAD_LEFT);
							echo '</option>';
						}
					?>
					</select>&nbsp;
					<select name="startmin" id="startmin" style="width: 5em; padding: 2px;">
					<?php
						for($n=0;$n<=59;$n++) {
							echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT) .'"';
							if(str_pad($n,2,"00",STR_PAD_LEFT) == substr($row['starttime'],3,2)) echo " selected";
							echo '>:'. str_pad($n,2,"00",STR_PAD_LEFT);
							echo '</option>';
						}
					?>
					</select>
					</p>
					<p><label for="endday">
					<strong>Departure date</strong></label><br />
					<select name="endday" id="endday" style="width: 5em; padding: 2px;">
					<?php
					for($n=1;$n <= 31;$n++) {
						echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT) . '"';
						if(substr($row['enddate'],8,2) == $n) { echo " selected"; }
						echo '>' . str_pad($n,2,"00",STR_PAD_LEFT) . '</option>';
					}
					?>
					</select>&nbsp;
					<select name="endmonth" id="endmonth" style="width: 10em; padding: 2px;">
					<?php
						// get the span for the drop downs
						for($cdate = $sdate; $cdate < $edate; $cdate = strtotime('+1 month',$cdate)) {
							echo '<option value="' . date("Y-m",$cdate) . '"';
							if(substr($row['enddate'],0,7) == date("Y-m",$cdate)) { echo " selected"; }
							echo '>' . ucwords(strftime("%b %Y",$cdate)) . '</option>';
						}
					?>
					</select><br />
					<select name="endhour" id="endhour" style="width: 5em; padding: 2px;">
					<?php
						for($n=0;$n<=23;$n++) {
							echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT) . '"';
							if(str_pad($n,2,"00",STR_PAD_LEFT) == substr($row['endtime'],0,2)) echo " selected";
							echo '>'. str_pad($n,2,"00",STR_PAD_LEFT);
							echo '</option>';
						}
					?>
					</select>&nbsp;
					<select name="endmin" id="endmin" style="width: 5em; padding: 2px;">
					<?php
						for($n=0;$n<=59;$n++) {
							echo '<option value="' . str_pad($n,2,"00",STR_PAD_LEFT) . '"';
							if(str_pad($n,2,"00",STR_PAD_LEFT) == substr($row['endtime'],3,2)) echo " selected";
							echo '>:'. str_pad($n,2,"00",STR_PAD_LEFT);
							echo '</option>';
						}
					?>
					</select>
					</p>
					<p><label for="status">
					<strong>Booking Status</strong></label><br />
					<select name="status" id="status" style="width: 15em; padding: 2px;">
					<?php
						$status = $booking->statuslist();
						foreach($status as $key => $value) {
							echo "<option value=\"$key\"";
							if($key == $row['status']) { echo " selected"; }
							echo ">$value</option>";
						}
					?>
					</select>
					</p>
					<p><label for="depositamount">
					<strong>Deposit Amount</strong></label><br />
					<select name="depositcurrency" id="depositcurrency" style="width: 5em; padding: 2px;">
					<option value=""></option>
					<?php
						$cur = substr($row['depositamount'],0,3);
						switch($cur) {
							case "GBP":
								$amount = substr($row['depositamount'],3,10);
								break;
							case "USD":
								$amount = substr($row['depositamount'],3,10);
								break;
							case "EUR":
								$amount = substr($row['depositamount'],3,10);
								break;
							default:
								$amount = $row['depositamount'];
								break;
						}
					?>
					<option value="USD"<?php if($cur == "USD") echo " selected"; ?>>USD</option>
					<option value="GBP"<?php if($cur == "GBP") echo " selected"; ?>>GBP</option>
					<option value="EUR"<?php if($cur == "EUR") echo " selected"; ?>>EUR</option>
					</select>&nbsp;
					<input name="depositamount" id="depositamount" value="<?php echo $amount; ?>" size="10" style="width: 10em; padding: 2px;" />
					</p>
					<p><label for="fullamount">
					<strong>Full Amount</strong></label><br />
					<select name="fullcurrency" id="fullcurrency" style="width: 5em; padding: 2px;">
					<option value=""></option>
					<?php
						$cur = substr($row['fullamount'],0,3);
						switch($cur) {
							case "GBP":
								$amount = substr($row['fullamount'],3,10);
								break;
							case "USD":
								$amount = substr($row['fullamount'],3,10);
								break;
							case "EUR":
								$amount = substr($row['fullamount'],3,10);
								break;
							default:
								$amount = $row['fullamount'];
								break;
						}
					?>
					<option value="USD"<?php if($cur == "USD") echo " selected"; ?>>USD</option>
					<option value="GBP"<?php if($cur == "GBP") echo " selected"; ?>>GBP</option>
					<option value="EUR"<?php if($cur == "EUR") echo " selected"; ?>>EUR</option>
					</select>&nbsp;
					<input name="fullamount" id="fullamount" value="<?php echo $amount; ?>" size="10" style="width: 10em; padding: 2px;" />
					</p>
					</td>
					<td class="" valign="top">
					<p><label for="notes">
					<strong>Notes</strong></label><br />
					<textarea name="notes" id="notes" rows="10" cols="35" style="width: 90%; padding: 2px;"><?php echo stripslashes($row['notes']);?></textarea>
					</p>
					</td>
				</tr>
			</table>
			
			<br />
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
			        <th scope="col" width="100%" colspan="2">Guest Details</th>
				</tr>
				<tr>
					<td class="alternate" valign="top">
					<p><label for="rentername">
					<strong>Guests Name</strong></label><br />
					<input name="rentername" id="rentername" value="<?php echo stripslashes($row['rentername']);?>" size="35" style="width: 25em; padding: 2px;" />
					</p>
					<p><label for="renteremail">
					<strong>Email address</strong></label><br />
					<input name="renteremail" id="renteremail" value="<?php echo $row['renteremail'];?>" size="35" style="width: 25em; padding: 2px;" />
					</p>
					<p><label for="rentertel">
					<strong>Telephone number</strong></label><br />
					<input name="rentertel" id="rentertel" value="<?php echo $row['rentertel'];?>" size="35" style="width: 25em; padding: 2px;" />
					</p>
					</td>
					<td class="alternate" valign="top">
					<p><label for="renternotes">
					<strong>Notes</strong></label><br />
					<textarea name="renternotes" id="renternotes" rows="10" cols="35" style="width: 90%; padding: 2px;"><?php echo stripslashes($row['renternotes']);?></textarea>
					</p>
					</td>
				</tr>
			</table>
			<br />
			<input type="hidden" name="action" value="update" />
	    	<input type="submit" name="editbooking" value="Update Booking &raquo;" />
	    </form>
		
		<?php
		}
		$spropertyid = '';
		$sstatus = '';
		$smonth = ''; 
		$stext = '';
		$baction = '';
		if (!empty($_REQUEST["propertyid"])) $spropertyid = $_REQUEST["propertyid"];

		if (!empty($_REQUEST["sstatus"])) $sstatus = $_REQUEST["sstatus"];
		if (!empty($_REQUEST["m"])) $smonth = $_REQUEST["m"]; 
		if (!empty($_REQUEST["s"])) $stext = $_REQUEST["s"];
		if (!empty($_REQUEST["baction"])) $baction = $_REQUEST["baction"];
			
		$backlist = "&amp;propertyid=" . $spropertyid . "&amp;sstatus=" . $sstatus . "&amp;m=" . $smonth . "&amp;s=" . $stext . "&amp;action=" . $baction;
		?>
		<br style="clear:both;" />
		<p><a href="admin.php?page=<?php echo WPCPLUGINNAME; ?>/clearskys-bookings.php<?php echo $backlist; ?>">&laquo; Return to booking list</a></p>
	
		<?php 		/* fixed amr */
	}
	
	function process_hooks($matches) {
		/**
		 * Tags to look for
		 * <!-- clearskys#calendar-months(n) -->
		 * <!-- clearskys#calendar-love -->
		 * <!-- clearskys#hcalendar-months(n) -->
		 * 
		 * <!-- clearskys#calendar-property(n) -->
		 * <!-- clearskys#calendar-propertyfromattr(n) -->
		 * <!-- clearskys#calendar-propertyfromuri(n) -->
		 * 
		 * <!-- clearskys#calendar-feedlink(ical|rss) -->
		 * <!-- clearskys#calendar-feedjscriptlink(ical|rss) -->
		 * <!-- clearskys#calendar-feedurl(ical|rss) -->
		 */
		global $wpdb;
		
		if (empty($matches)) $matches = array('months'=>6, 'property'=>1);
		
		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
		
  		$cs = get_option('clearskys_config');
		$propertyid = False;
//amr		$matches = array();

		
		// Get an array of all the clearskys comment tags
//amr		preg_match_all("<!--[ ]*clearskys#[h]*calendar-([a-zA-z]*)(\([0-9a-zA-Z]*\))?[ ]*-->", $content, $matches);
	    $content = '';
		
		if(count($matches) > 1) {
//amr			for($n=0; $n<count($matches[1]); $n++) {
//amr				switch($matches[$n]) {
				foreach ($matches as $n=>$number) {
				switch($n) {
					case "months":
						// get months by removing the brackets
//amr						$number = preg_replace("[\(|\)]","",$matches[2][$n]);

						$thedate = strtotime(date("Y-m-01"));
						$month = "";
						if(is_numeric($number)) { 
							$monthcount = 0;
							for($z=1; $z<=$number; $z++) {
								// add a month to the output
								$month .= $this->buildmonth(date("Y", $thedate),date("m",$thedate),$propertyid);
								$thedate = strtotime("+1 month", $thedate);
								$monthcount++;
								if($monthcount == $cs["clearskys_calendar_afterevery_number"]) {
									$month .= $cs["clearskys_calendar_afterevery"];
									$monthcount = 0;
								}
							}
						}

						$content .= $month;
						break;
					case "property":
						// set property id to build the calendar for
						// by removing the brackets
//amr						$number = preg_replace("[\(|\)]","",$matches[2][$n]);
						if(is_numeric($number)) {
							$propertyid = $number;
						}
//amr						$content = str_replace('<' . $matches[0][$n] . '>',"",$content);
						break;
					case "propertyfromattr":
//amr						$attr = preg_replace("[\(|\)]","",$matches[2][$n]);
						if($number != "") {
							$propertyid = $booking->xss_clean($number);
							if(!is_numeric($propertyid)) {
								$propertyid = False;
							} 
						}
//amr						$content = str_replace('<' . $matches[0][$n] . '>',"",$content);
						break;
					case "propertyfromuri":
//amr						$number = preg_replace("[\(|\)]","",$matches[2][$n]);
						if(is_numeric($number)) {
							$uri = $_SERVER['REQUEST_URI'];
							$urisplit = explode("/",$uri);
							if(count($urisplit) > $number && is_numeric($urisplit[$number])) {
								$propertyid = $urisplit[$number];
							} else {
								$propertyid = False;
							}
						}
//amr						$content = str_replace('<' . $matches[0][$n] . '>',"",$content);

						break;
					case "feedlink":
//amr						$format = preg_replace("[\(|\)]","",$matches[2][$n]);
						$format = $number;

						if(strtolower($format) == 'rss' || strtolower($format) == 'ical') {
							$link = "<a href='" .  get_option('siteurl')  . $cs["clearskys_publicpath"];
							if($propertyid) {
								$useprop = $propertyid;
							} else {
								$useprop = $cs["clearskys_propertyno"];
							}
							if($this->isquerystring($cs["clearskys_publicpath"])) {
								$link .= "&property=" . $useprop . "&feed=" . strtolower($format);
							} else {
								$link .=  "/" . $useprop . "?feed=" . strtolower($format);
							}
							if(strtolower($format) == 'rss') {
								$link .= "' title='Subscribe to RSS feed'>";
								$link .= "<img src='" .  WPCPLUGINURL . "/includes/images/feed-icon16x16.png' alt='RSS feed' width='16' height='16' style='vertical-align: text-top; border-width:0;' />";
							} else {
								$link .= "' title='Subscribe to iCal feed'>";
								$link .= "<img src='" .  WPCPLUGINURL . "/includes/images/date22x22.png' alt='iCal feed' width='22' height='22' style='vertical-align: bottom;' />";
							}
							$link .= "</a>";

							$content = $link;

						}
						break;
					case "feedjscriptlink":
//amr						$format = preg_replace("[\(|\)]","",$matches[2][$n]);
						$format=$number;
						if(strtolower($format) == 'rss' || strtolower($format) == 'ical') {
							$link = "<a href='";
							$useurl =  get_option('siteurl')  . $cs["clearskys_publicpath"];
							if($propertyid) {
								$useprop = $propertyid;
							} else {
								$useprop = $cs["clearskys_propertyno"];
							}
							if($this->isquerystring($cs["clearskys_publicpath"])) {
								$useurl .= "&property=" . $useprop . "&feed=" . strtolower($format);
							} else {
								$useurl .=  "/" . $useprop . "?feed=" . strtolower($format);
							}
							$link .= $useurl;
							if(strtolower($format) == 'rss') {
								$link .= "' title='Subscribe to RSS feed'";
								$link .= " onclick='prompt(\"To subscribe to this feed, cut and paste the URL below into your Feed Reader when asked for subscribe details\",\"$useurl\"); return false;'";
								$link .= ">";
/* amr*/								$link .= "<img src='" .   WPCPLUGINURL ."/includes/images/feed-icon16x16.png' alt='RSS feed' width='16' height='16' style='vertical-align: text-top;' />";
							} else {
								$link .= "' title='Subscribe to iCal feed'";
								$link .= " onclick='prompt(\"To subscribe to this feed, cut and paste the URL below into your Calendar application when asked for subscribe details\",\"$useurl\"); return false;'";
								$link .= ">";
								$link .= "<img src='" .   WPCPLUGINURL ."/includes/images/date22x22.png' alt='iCal feed' width='22' height='22' style='vertical-align: bottom;' />";
							}
							$link .= "</a>";
//amr							$content = str_replace('<' . $matches[0][$n] . '>',$link,$content);
							$content .= $link;
						}
						break;
					case "feedurl":
//						$format = preg_replace("[\(|\)]","",$matches[2][$n]);
						$format = $number;

						if(strtolower($format) == 'rss' || strtolower($format) == 'ical') {
							$useurl =  get_option('siteurl')  . $cs["clearskys_publicpath"];
							if($propertyid) {
								$useprop = $propertyid;
							} else {
								$useprop = $cs["clearskys_propertyno"];
							}
//							if($this->isquerystring($cs["clearskys_publicpath"])) {
								$useurl .= "&property=" . $useprop . "&feed=" . strtolower($format);
//							} else {
//								$useurl .=  "/" . $useprop . "?feed=" . strtolower($format);
//							}
							$link = $useurl;
//amr							$content = str_replace('<' . $matches[0][$n] . '>',$link,$content);
							$content .= $link;
						}
						break;
				}
			}
		}

    	 	return $content;
	}
	
	function process_feed() {
		if(!isset($_GET['feed'])) {
			// not a feed url so return
			return;
		}
		$path = $_SERVER['REQUEST_URI'];
		$feed = $_GET['feed'];
		$cs = get_option('clearskys_config'); 
		$siteurl = get_option('siteurl');
		$class = "";
				
		if(isset($_GET['property'])) {
			// query string feed url
			$path = "?" . $_SERVER['QUERY_STRING'];
			if(stristr($path, $cs['clearskys_privatepath'])) {
				$class= "Private";
			} elseif(stristr($path, $cs['clearskys_publicpath'])) {
				$class= "Public";
			}
			$property = $_GET['property'];
		} elseif(isset($_GET['propertyid'])) {
			$path = "?" . $_SERVER['QUERY_STRING'];
			if(stristr($path, $cs['clearskys_privatepath'])) {
				$class= "Private";
			} elseif(stristr($path, $cs['clearskys_publicpath'])) {
				$class= "Public";
			}
			$property = $_GET['propertyid'];
		} else {
			// URI string feed url
			// first strip off the query string part of it
			$qstring = "?" . $_SERVER['QUERY_STRING'];
			$path = str_replace($qstring,"",$path);
			if(stristr($path, $cs['clearskys_privatepath'])) {
				$class= "Private";
			} elseif(stristr($path, $cs['clearskys_publicpath'])) {
				$class= "Public";
			}
			// get the last segment in the uri
			$urisplit = explode("/",$path);
			if(count($urisplit) > 0 && is_numeric($urisplit[count($urisplit)-1])) {
				$property = $urisplit[count($urisplit)-1];
			} else {
				$property = False;
			}
		}
		
		if($class == "") {
			return;
		} elseif($class=="Public") {
			echo $this->createpublicfeed($feed,$class,$property);
			exit();
		} elseif($class=="Private") {
			echo $this->createprivatefeed($feed,$class,$property);
			exit();
		}
	}
	
	function createpublicfeed($type,$class,$property = 0)
	{
		global $wpdb;
		
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$booking->setid($property);
  		
		$feed = "";
		
		// check for property plugin
		if(class_exists('CSproperty')) {
			$csproperty = new CSproperty($wpdb,$wpdb->prefix);
		}
		
		switch(strtolower($type)) {
			case "ical":
				// send ical headers
				header("Content-Type: text/Calendar");
    				header("Content-Disposition: inline; filename=basic.ics");
    				ob_start();
    				echo "BEGIN:VCALENDAR\r\n";
    				echo "VERSION:2.0\r\nPRODID:-//Clearskys.net//Availability Calendar//EN\r\n";
    				echo "X-WR-CALNAME:Availability Calendar for property ";
    				if(isset($csproperty)) {
    					$ref = $csproperty->getrefforproperty($property);
    					echo $ref[0]["reference"];
    					echo "\r\n";
    				} else {
    					echo "$property\r\n";
    				}
    				$row = $booking->getbookings($property);
    				if($row) {
    					for($n=0; $n<count($row);$n++) {
    						echo "BEGIN:VEVENT\r\n";
    						echo "SUMMARY:Booking\r\n";
    						$sdate = strtotime($row[$n]['startdate']);
    						$edate = strtotime($row[$n]['enddate']);
    						$edate = strtotime("+1 day", $edate);
    						echo "DTSTART;VALUE=DATE:" . date("Y",$sdate) . date("m",$sdate). date("d",$sdate) . "\r\n";
    						echo "DTEND;VALUE=DATE:" . date("Y",$edate) . date("m",$edate). date("d",$edate) . "\r\n";
    						echo "END:VEVENT\r\n";
    					}
    				}
    				echo "END:VCALENDAR\r\n";
    				$feed = ob_get_contents();
				ob_end_clean();
				break;
			case "rss":
				// send xml headers
				setlocale(LC_ALL,$cs["clearskys_publiclocale"]);
				$title = "Availability Calendar for property ";
				if(isset($csproperty)) {
   					$ref = $csproperty->getrefforproperty($property);
   					$title .= $ref[0]["reference"];
   				} else {
   					$title .= "$property";
   				}
				header('Content-type: text/xml; charset='.get_option('blog_charset'), true);
				ob_start();
				echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
				?>
				<!-- generator="blog.clearskys.net/plugins/availability-plugin/" -->
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" <?php do_action('rss2_ns'); ?>>
<channel>
	<title><?php 	echo $title; ?></title>
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php 	echo $title; ?></description>
	<pubDate><?php echo date('D, d M Y H:i:s +0000'); ?></pubDate>
	<generator>http://blog.clearskys.net/plugins/availability-plugin/</generator>
	<language><?php echo get_option('rss_language'); ?></language>
<?php 
				do_action('rss2_head'); 
				$row = $booking->getbookings($property);
				//$row = false;
				if($row) {
					for($n=0; $n<count($row);$n++) {
						$sdate = strtotime($row[$n]['startdate']);
    						$edate = strtotime($row[$n]['enddate']);
						echo "<item>\r\n";						
						echo "<title>Booking: " . ucwords(strftime("%a %e %B %Y",$sdate)) . " to " . ucwords(strftime("%a %e %B %Y",$edate)) . "</title>\r\n";		
 						echo "<link>";
 						echo bloginfo_rss('url');
 						echo "\r\n</link>\r\n";
 						echo "<pubDate>" . date('D, d M Y H:i:s +0000',$sdate) . "</pubDate>";
 						echo "<guid isPermaLink=\"false\">";
 						echo $row[$n]['id'] . "-" . $row[$n]['property_id'];
 						echo "</guid>\r\n";
 						echo "<description><![CDATA[";
 						echo "Fully Booked";
 						echo "]]></description>\r\n";
    					echo "</item>\r\n";	
    					}
				}
?>
</channel>
</rss>				
<?php			
				$feed = ob_get_contents();
				ob_end_clean();	
				break;
		}
		return $feed;
	}
	
	function createprivatefeed($type,$class,$property = 0)
	{
		global $wpdb;
		
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		$booking->setid($property);
  		
		$feed = "";
		if (!empty($_GET["sstatus"])) $sstatus = $booking->xss_clean($_GET["sstatus"]);
		if (!empty($_GET["m"])) $smonth = $booking->xss_clean($_GET["m"]); 
		if (!empty($_GET["s"])) $stext = $booking->xss_clean($_GET["s"]);
		if (!empty($_GET["type"])) $stype = $booking->xss_clean($_GET["type"]);
		
		if($stype == "") {
			$row = $booking->getfeedlist($stext, $sstatus, $smonth, $property, True);
		} else {
			$row = $booking->getinitialfeedlist(True);
		}
		// check for property plugin
		if(class_exists('CSproperty')) {
			$csproperty = new CSproperty($wpdb,$wpdb->prefix);
		}
		
		switch(strtolower($type)) {
			case "ical":
				// send ical headers
				header("Content-Type: text/Calendar");
    				header("Content-Disposition: inline; filename=basic.ics");
    				ob_start();
    				echo "BEGIN:VCALENDAR\r\n";
    				echo "VERSION:2.0\r\nPRODID:-//Clearskys.net//Availability Calendar//EN\r\n";
					echo "X-WR-CALNAME:Availability Calendar for property ";
    				if(isset($csproperty) && $property != "") {
    					$ref = $csproperty->getrefforproperty($property);
    					echo $ref[0]["reference"];
    					echo "\r\n";
    				} else {
    					echo "$property\r\n";
    				}
    				if($row) {
    					for($n=0; $n<count($row);$n++) {
    						echo "BEGIN:VEVENT\r\n";
    						
    						echo "SUMMARY:" . $row[$n]['title'] . "\r\n";
    						//=0D=0A=
    						$desc = "DESCRIPTION;ENCODING=QUOTED-PRINTABLE:\\n";
    						
	    					if(isset($csproperty) && $row[$n]['property_id'] != 0) {
		    					$ref = $csproperty->getrefforproperty($row[$n]['property_id']);
		    					$desc .= "Property: " . $ref[0]["reference"];
		    					$desc .= "\\n";
		    				} else {
		    					$desc .= "Property: " . $row[$n]['property_id'] . "\\n";
		    				}
    						
    						
    						$desc .= "Arrival Time: " . $row[$n]['starttime'] . "\\n";
    						$desc .= "Departure Time: " . $row[$n]['endtime'] . "\\n";
    						$desc .= "Status: " . $booking->status($row[$n]['status']) . "\\n";
    						$desc .= "Deposit Amount: " . $row[$n]['depositamount'] . "\\n";
    						$desc .= "Full Amount: " . $row[$n]['fullamount'] . "\\n";
    						$desc .= "Notes: " . $row[$n]['notes'] . "\\n\\n";
    						$desc .= "Guest Name: " . $row[$n]['rentername'] . "\\n";
    						$desc .= "Guest Email: " . $row[$n]['renteremail'] . "\\n";
    						$desc .= "Guest Tel: " . $row[$n]['rentertel'] . "\\n";
    						$desc .= "Guest Notes: " . $row[$n]['renternotes'];
    							
    						$desc = str_replace("\r\n","\\n",$desc);
    						echo $desc . "\r\n";
    						
    						$sdate = strtotime($row[$n]['startdate']);
    						$edate = strtotime($row[$n]['enddate']);
    						$edate = strtotime("+1 day", $edate);
    						echo "DTSTART;VALUE=DATE:" . date("Y",$sdate) . date("m",$sdate). date("d",$sdate) . "\r\n";
    						echo "DTEND;VALUE=DATE:" . date("Y",$edate) . date("m",$edate). date("d",$edate) . "\r\n";
    						echo "END:VEVENT\r\n";
    					}
    				}
    				echo "END:VCALENDAR\r\n";
    				$feed = ob_get_contents();
				ob_end_clean();
				break;
			case "rss":
				// send xml headers
				setlocale(LC_ALL,$cs["clearskys_publiclocale"]);
				$title = "Availability Calendar for property ";
				if(isset($csproperty) && $property != "") {
   					$ref = $csproperty->getrefforproperty($property);
   					$title .= $ref[0]["reference"];
   				} else {
   					$title .= "$property";
   				}
				header('Content-type: text/xml; charset='.get_option('blog_charset'), true);
				ob_start();
				echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
				?>
				<!-- generator="blog.clearskys.net/plugins/availability-plugin/" -->
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" <?php do_action('rss2_ns'); ?>>
<channel>
	<title><?php echo $title; ?></title>
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php echo $title; ?></description>
	<pubDate><?php echo date('D, d M Y H:i:s +0000'); ?></pubDate>
	<generator>http://blog.clearskys.net/plugins/availability-plugin/</generator>
	<language><?php echo get_option('rss_language'); ?></language>
<?php 
				do_action('rss2_head'); 
				//$row = $booking->getbookings($property);
				//$row = false;
				if($row) {
					for($n=0; $n<count($row);$n++) {
						$sdate = strtotime($row[$n]['startdate']);
    						$edate = strtotime($row[$n]['enddate']);
							echo "<item>\r\n";
						
    						echo "<title>" . $row[$n]['title'] . ": " . ucwords(strftime("%a %e %B %Y",$sdate)) . " to " . ucwords(strftime("%a %e %B %Y",$edate)) . "</title>\r\n";
    						echo "<link>";
    						echo bloginfo_rss('url');
    						echo "\r\n</link>\r\n";
    						echo "<pubDate>" . date('D, d M Y H:i:s +0000',$sdate) . "</pubDate>";
    						echo "<guid isPermaLink=\"false\">";
    						echo $row[$n]['id'] . "-" . $row[$n]['property_id'];
    						echo "</guid>\r\n";
    						echo "<description><![CDATA[";
    						$desc = "";
    						$desc .= "<strong>Title:</strong> " . $row[$n]['title'] . "<br/>";
    						
							if(isset($csproperty) && $row[$n]['property_id'] != 0) {
		    					$ref = $csproperty->getrefforproperty($row[$n]['property_id']);
		    					$desc .= "<strong>Property:</strong> " . $ref[0]["reference"];
		    					$desc .= "<br />";
		    				} else {
		    					$desc .= "<strong>Property:</strong> " . $row[$n]['property_id'] . "<br />";
		    				}
    						
    						$desc .= "<strong>Arrival Date:</strong> " . ucwords(strftime("%a %e %B %Y",$sdate)) . "<br/>";
    						$desc .= "<strong>Departure Date:</strong> " . ucwords(strftime("%a %e %B %Y",$edate)) . "<br/>";
    						$desc .= "<strong>Arrival Time:</strong> " . $row[$n]['starttime'] . "<br/>";
    						$desc .= "<strong>Departure Time:</strong> " . $row[$n]['endtime'] . "<br/>";
    						$desc .= "<strong>Status:</strong> " . $booking->status($row[$n]['status']) . "<br/>";
    						$desc .= "<strong>Deposit Amount:</strong> " . $row[$n]['depositamount'] . "<br/>";
    						$desc .= "<strong>Full Amount:</strong> " . $row[$n]['fullamount'] . "<br/>";
    						$desc .= "<strong>Notes:</strong> " . $row[$n]['notes'] . "<br/><br/>";
    						$desc .= "<strong>Guest Name:</strong> " . $row[$n]['rentername'] . "<br/>";
    						$desc .= "<strong>Guest Email:</strong> <a href='mailto:" . $row[$n]['renteremail'] . "'>" . $row[$n]['renteremail'] . "</a><br/>";
    						$desc .= "<strong>Guest Tel:</strong> " . $row[$n]['rentertel'] . "<br/>";
    						$desc .= "<strong>Guest Notes:</strong> " . $row[$n]['renternotes'];
    						$desc = str_replace("\r\n","<br/>",$desc);
    						echo $desc;
    						echo "]]></description>\r\n";
    						
    						echo "</item>\r\n";	
    					}
				}
?>
</channel>
</rss>				
<?php			
				$feed = ob_get_contents();
				ob_end_clean();	
				break;
		}
		return $feed;
	}
	
	function buildmonth($year, $mon, $propertyno = False)
	{
		// Get current database settings
		global $wpdb;
		
		$cs = get_option("clearskys_config"); 
  		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		
  		if(!$propertyno) {
  			$propertyno = $cs["clearskys_propertyno"];
  		}
  		$master = $booking->getmontharray($year,$mon,$propertyno,True);
  		 		
  		$startofmonth = strtotime("$year-$mon-01");
  		$daysinmonth = date("t", $startofmonth);
  		
  		$week = array();
  		$month = array();
  		// set up start of week day
  		if($cs['clearskys_endweek'] == 0) {
  			$startweek = 1;
  		} else {
  			$startweek = 0;
  		}
  		
  		$arrayday = 0;
  		// first week fill initial blanks
  		if((date("w",$startofmonth)  - $startweek) < 0) {
  			$sweek = 6;
  		} else {
  			$sweek = (date("w",$startofmonth)  - $startweek);
  		}
  		for($n = $arrayday; $n < $sweek; $n++) {
  			$week[$n] = str_replace('{day}','&nbsp;',stripslashes($cs['clearskys_calendar_availabledate']));
  		}
  		
  		$arrayday = $n;
  		$m = 0;
  		// build the weeks for the days of the month
  		for($n=1; $n<=$daysinmonth; $n++) {
  			$today = strtotime($year . "-" . $mon. "-" . str_pad($n,2,"00",STR_PAD_LEFT));
  			$yesterday = strtotime("-1 day", $today);
  			$tomorrow = strtotime("+1 day", $today);
  			
  			if(isset($master[date("Ymd",$today)])) {
  				//$cs["clearskys_calendar_bookedstartdate"] = "<td class='booked startday'>{day}</td>";
				//$cs["clearskys_calendar_bookedenddate"] = "<td class='booked endday'>{day}</td>";
				if(!isset($master[date("Ymd",$yesterday)]) && isset($cs["clearskys_calendar_bookedstartdate"])) {
					 $dayhtml = stripslashes($cs['clearskys_calendar_bookedstartdate']);
				} elseif(!isset($master[date("Ymd",$tomorrow)]) && isset($cs["clearskys_calendar_bookedenddate"])) { 
  					$dayhtml = stripslashes($cs['clearskys_calendar_bookedenddate']);
  				} else {
  					$dayhtml = stripslashes($cs['clearskys_calendar_bookeddate']);
  				}
				if(isset($master[date("Ymd",$today)]['status'])) {
					$dayhtml = str_replace("{status}",$master[date("Ymd",$today)]['status'],$dayhtml);
				} else {
					$dayhtml = str_replace("{status}","booked",$dayhtml);
				}
  			} else {
  				$dayhtml = stripslashes($cs['clearskys_calendar_availabledate']);
  			}
  			
  			switch($arrayday) {
  				case 6:
					// end of week so build week for this month
					$week[$arrayday] = str_replace('{day}',$n,$dayhtml);
					$month = $month + array("$m" => $week);
					$m++;
					$arrayday = 0;
  					break;
  				default:
  					// other days
  					$week[$arrayday++] = str_replace('{day}',$n,$dayhtml);
  					break;
  			}			
  		}
  		// last week fill in the end blanks of the last row
  		if($arrayday <= 6 && $arrayday != 0) {
  			for($n = $arrayday; $n <= 6; $n++) {
  			$week[$n] = str_replace('{day}','&nbsp;',stripslashes($cs['clearskys_calendar_availabledate']));
  			}
  			// Add last week to the month
  			$month = $month + array("$m" => $week);
  		} 
  		// Format each week
  		$amonth = "";
  		for($n=0; $n<count($month);$n++) {
  			$aweek = "";
  			for($z=0; $z<count($month[$n]);$z++) {
  				$aweek .= $month[$n][$z]; 
  			}
  			$amonth .= str_replace("{week}",$aweek,stripslashes($cs["clearskys_calendar_weekrow"]));
  		}

  		// add month to month body
  		$amonth = str_replace("{month}",$amonth,stripslashes($cs["clearskys_calendar_datesblock"]));
 					  
  		// Block all in a monthblock
  		$amonth = stripslashes($cs["clearskys_calendar_monthtitle"]) . stripslashes($cs["clearskys_calendar_weekheader"]) . $amonth;

  		$amonth = str_replace("{monthblock}",$amonth,stripslashes($cs["clearskys_calendar_monthblock"]));

  		// final bits
  		setlocale(LC_ALL,$cs["clearskys_publiclocale"]);
  	
  		$amonth = str_replace("{title}",ucwords(strftime("%B %Y",$startofmonth)),$amonth);
  		$amonth = str_replace("{shorttitle}",ucwords(strftime("%b %Y",$startofmonth)),$amonth);
  		
  		$amonth = str_replace("{lowertitle}",strtolower(strftime("%B %Y",$startofmonth)),$amonth);
  		$amonth = str_replace("{lowershorttitle}",strtolower(strftime("%b %Y",$startofmonth)),$amonth);
  		$dateclasses = "";
  		if($year == date("Y")) $dateclasses .= " thisyear";
  		if($year > date("Y")) $dateclasses .= " nextyear";
  		if($mon == date("m")) $dateclasses .= " thismonth";
  		$amonth = str_replace("{monthclass}",ltrim($dateclasses),$amonth);
  		$amonth = str_replace("{property}",$propertyno,$amonth);

  		// and return
  		return $amonth;
	}
	
}	

	$CSbook =new CSbook();
	
?>