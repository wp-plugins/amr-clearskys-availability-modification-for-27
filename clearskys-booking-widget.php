<?php
/*
Plugin Name: Clearskys.net Availability calendar widget
Plugin URI: http://blog.clearskys.net/plugins/availability-widget/
Description: This plugin provides a widget availability calendar for your site. It relies on the existance of the <a href="http://blog.clearskys.net/plugins/availability-plugin/">Clearskys.net Booking manager</a> plugin to function correctly.
Version: 1.1
Author: clearskys.net
Author URI: http://blog.clearskys.net
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

define('WPCPLUGINPATH', (DIRECTORY_SEPARATOR != '/') ? str_replace(DIRECTORY_SEPARATOR, '/', dirname(__FILE__)) : dirname(__FILE__));


include_once('includes/csbooking.php');

class CSbookwidget
{
	function isquerystring($url)
	{
		return stristr($url,'?');
	}
	 
	function CSbookwidget() 
	{ 
		// Add the installation and uninstallation hooks 
		$file = WPCPLUGINPATH . '/' . basename(__FILE__); 
		register_activation_hook($file, array(&$this, 'install')); 
		register_deactivation_hook($file, array(&$this, 'uninstall'));  
		
		add_action('plugins_loaded', array(&$this,'initcalendar'));
	}
	
	function install() {
		
	}
	
	function uninstall() {
		
	}

	function initcalendar() {
		if (!function_exists('register_sidebar_widget')) {
                return;
        }
        
        $csw = get_option('clearskys_calendar_widget_config');
		$number = $csw['clearskys_calwidget']['number'];
		if ( $number < 1 ) $number = 1;
		if ( $number > 99 ) $number = 99;
		for ($i = 1; $i <= 99; $i++) {
			$name = array('Availability Calendar %s', 'widgets', $i);
			//change next line
			register_sidebar_widget($name, $i <= $number ? array(&$this,'showcalendar') : /* unregister */ '', $i);
			register_widget_control($name, $i <= $number ? array(&$this,'setupcalendarcontrol') : /* unregister */ '',300,425, $i);
		}
		add_action('sidebar_admin_setup', array(&$this,'calendar_setup'));
		add_action('sidebar_admin_page', array(&$this,'calendar_admin_page'));
        
        //register_sidebar_widget('Availability Calendar', array('CSbookwidget','showcalendar'));
        //register_widget_control('Availability Calendar', array('CSbookwidget','setupcalendar'),300,425);
	}
	
	function calendar_setup() {
		$ocsw = $csw = get_option('clearskys_calendar_widget_config');
		$number = $csw['clearskys_calwidget']['number'];
		
		//$options = $newoptions = get_option('widget_text');
		if ( isset($_POST['clearskys_calwidget_number_submit']) ) {
			$number = (int) $_POST['clearskys_calwidget_number'];
			if ( $number > 99 ) $number = 99;
			if ( $number < 1 ) $number = 1;
			$csw['clearskys_calwidget']['number'] = $number;
		}
		if ($ocsw != $csw ) {
			update_option('clearskys_calendar_widget_config', $csw);
			$this->initcalendar();
		}
	}
	
	function calendar_admin_page() {
		$csw = get_option('clearskys_calendar_widget_config');
		$number = $csw['clearskys_calwidget']['number'];
	?>
		<div class="wrap">
			<form method="POST">
				<h2><?php _e('Availability Calendars', 'widgets'); ?></h2>
				<p style="line-height: 30px;"><?php _e('How many availability calendar widgets would you like?', 'widgets'); ?>
				<select id="clearskys_calwidget_number" name="clearskys_calwidget_number" value="<?php echo $number; ?>">
	<?php for ( $i = 1; $i < 100; ++$i ) echo "<option value='$i' ".($number==$i ? "selected='selected'" : '').">$i</option>"; ?>
				</select>
				<span class="submit"><input type="submit" name="clearskys_calwidget_number_submit" id="clearskys_calwidget_number_submit" value="<?php _e('Save'); ?>" /></span></p>
			</form>
		</div>
	<?php
	}
	
	function setupcalendarcontrol($number) {
		//echo "<div style='padding: 5px; background: #FFFFFF; border: 1px solid #BBBBBB; width: 300px; height: 200px; overflow: auto; text-align: right; padding-left: 10px;'>\n";
		// Get our options and see if we're handling a form submission.
		$csw = get_option('clearskys_calendar_widget_config');
		$cs = get_option("clearskys_config"); 
  		
		if ( !is_array($csw[$number]) ) {
			$sstyle = ".cs_availabilitycalendar_container 
{
float:left;
padding:5px;
background-color:#F7F9FB;
border:1px solid #7B9EBD;
margin-bottom:10px;
}

.cs_availabilitycalendar  caption {
border:1px solid #E0E0E0;
vertical-align:middle;
background-color:#FFF;
position:relative;
width:100%;
text-align:center;
}

.cs_availabilitycalendar
{
font:100% sans-serif;
text-align:center;
border-spacing:0;
border-collapse:separate;
}

.cs_availabilitycalendar tbody td
{
padding:.1em .2em;
border:1px solid #E0E0E0;
background-color:#FFF;
}

.cs_availabilitycalendar tbody td.booked
{
background-color:#F66;
}

.cs_availabilitycalendar_feedcontainer
{
float:left;
padding-left:5px;
padding-right:15px;
}

.cs_availabilitycalendar_feedcontainer a.icalfeedlink
{
float:left;
}

.cs_availabilitycalendar_feedcontainer a.icalfeedlink img
{
border:none;
}

.cs_availabilitycalendar_feedcontainer a.rssfeedlink
{
float:right;
}

.cs_availabilitycalendar_feedcontainer a.rssfeedlink img
{
border:none;
}
";
			$csw[$number] = array('clearskys_calwidget_title'=>'Availability', 
						 'clearskys_calwidget_getpropertyno'=>'propertyno',
						 'clearskys_calwidget_getpropertynosetting' => '1',
						 'clearskys_calwidget_numberofmonths'=>'6',
						 'clearskys_calwidget_showical'=>'',
						 'clearskys_calwidget_showrss'=>'',
						 'clearskys_calwidget_jlink'=>'',
						 'clearskys_calwidget_style'=>$sstyle);
		}
		
		if ( $_POST['clearskys_calwidget_submit_' . $number] ) {

			// Remember to sanitize and format use input appropriately.
			$csw[$number]['clearskys_calwidget_title'] = strip_tags(stripslashes($_POST['clearskys_calwidget_title_' . $number]));
			$csw[$number]['clearskys_calwidget_getpropertyno'] = strip_tags(stripslashes($_POST['clearskys_calwidget_getpropertyno_' . $number]));
			$csw[$number]['clearskys_calwidget_getpropertynosetting'] = strip_tags(stripslashes($_POST['clearskys_calwidget_getpropertynosetting_' . $number]));
			$csw[$number]['clearskys_calwidget_numberofmonths'] = strip_tags(stripslashes($_POST['clearskys_calwidget_numberofmonths_' . $number]));
			$csw[$number]['clearskys_calwidget_style'] = strip_tags(stripslashes($_POST['clearskys_calwidget_style_' . $number]));
			$csw[$number]['clearskys_calwidget_showical'] = strip_tags(stripslashes($_POST['clearskys_calwidget_showical_' . $number]));
			$csw[$number]['clearskys_calwidget_showrss'] = strip_tags(stripslashes($_POST['clearskys_calwidget_showrss_' . $number]));
			$csw[$number]['clearskys_calwidget_jlink'] = strip_tags(stripslashes($_POST['clearskys_calwidget_jlink_' . $number]));
			
			
			update_option('clearskys_calendar_widget_config', $csw);
		}

		// Be sure you format your options to be valid HTML attributes.
		$clearskys_calwidget_title = htmlspecialchars($csw[$number]['clearskys_calwidget_title'], ENT_QUOTES);
		$clearskys_calwidget_getpropertyno = htmlspecialchars($csw[$number]['clearskys_calwidget_getpropertyno'], ENT_QUOTES);
		$clearskys_calwidget_getpropertynosetting = htmlspecialchars($csw[$number]['clearskys_calwidget_getpropertynosetting'], ENT_QUOTES);
		$clearskys_calwidget_numberofmonths = htmlspecialchars($csw[$number]['clearskys_calwidget_numberofmonths'], ENT_QUOTES);
		$clearskys_calwidget_style = htmlspecialchars($csw[$number]['clearskys_calwidget_style'], ENT_QUOTES);
		$clearskys_calwidget_showical = htmlspecialchars($csw[$number]['clearskys_calwidget_showical'], ENT_QUOTES);
		$clearskys_calwidget_showrss = htmlspecialchars($csw[$number]['clearskys_calwidget_showrss'], ENT_QUOTES);
		$clearskys_calwidget_jlink = htmlspecialchars($csw[$number]['clearskys_calwidget_jlink'], ENT_QUOTES);
		
		echo '<p style="padding: 5px; background: #FFFFFF; border: 1px solid #BBBBBB; width: 300px; text-align:left;"><label for="clearskys_calwidget_title_' . $number . '" style="display: block;">' . __('Title:') . ' <input style="width: 250px; display: block;" id="clearskys_calwidget_title_' . $number . '" name="clearskys_calwidget_title_' . $number . '" type="text" value="'.$clearskys_calwidget_title.'" /></label></p>';
		echo '<p style="padding: 5px; background: #FFFFFF; border: 1px solid #BBBBBB; width: 300px; text-align:left;"><label for="clearskys_calwidget_getpropertyno_' . $number . '" style="display: block;">' . __('Show calendar for:');
		echo '</label>';
		echo '<select id="clearskys_calwidget_getpropertyno_' . $number . '" name="clearskys_calwidget_getpropertyno_' . $number . '" style="width: 200px;">';
		echo '<option value="propertyno"';
		if($clearskys_calwidget_getpropertyno == 'propertyno') echo ' selected';
		echo '>' . __('Property number') . '</option>';
		echo '<option value="query"';
		if($clearskys_calwidget_getpropertyno == 'query') echo ' selected';
		echo '>' . __('Query Attribute') . '</option>';
		echo '<option value="uri"';
		if($clearskys_calwidget_getpropertyno == 'uri') echo ' selected';
		echo '>' . __('URI permalink') . '</option>';
		
		if(class_exists('CSproperty')) {
			echo '<option value="ref"';
			if($clearskys_calwidget_getpropertyno == 'ref') echo ' selected';
			echo '>' . __('Property Ref') . '</option>';
			echo '<option value="uriref"';
			if($clearskys_calwidget_getpropertyno == 'uriref') echo ' selected';
			echo '>' . __('Ref URI permalink') . '</option>';
			echo '<option value="queryref"';
			if($clearskys_calwidget_getpropertyno == 'queryref') echo ' selected';
			echo '>' . __('Ref Query Attribute') . '</option>';
		}
		
		echo '</select>';
		echo ' <input style="width: 50px;" id="clearskys_calwidget_getpropertynosetting_' . $number . '" name="clearskys_calwidget_getpropertynosetting_' . $number . '" type="text" value="'.$clearskys_calwidget_getpropertynosetting.'" />';
		echo '</p>';
		echo '<p style="padding: 5px; background: #FFFFFF; border: 1px solid #BBBBBB; width: 300px; text-align:left;"><label for="clearskys_calwidget_numberofmonths_' . $number . '" style="display: block;">' . __('Number of months:');
		echo '</label>';
		echo '<select id="clearskys_calwidget_numberofmonths_' . $number . '" name="clearskys_calwidget_numberofmonths_' . $number . '" style="width: 200px;">';
		for($n=1;$n<=18;$n++) {
			echo '<option value="' . $n . '"';
			if($clearskys_calwidget_numberofmonths == $n) echo ' selected';
			echo '>' . $n . __(' month(s)') . '</option>';
		}
		echo '</select>';
		echo '</p>';
		echo '<p style="padding: 5px; background: #FFFFFF; border: 1px solid #BBBBBB; width: 300px; text-align:left;"><label for="clearskys_calwidget_showrss_' . $number . '">' . __('Show RSS feed: ') . ' <input id="clearskys_calwidget_showrss_' . $number . '" name="clearskys_calwidget_showrss_' . $number . '" type="checkbox" value="1"';
		if($clearskys_calwidget_showrss != "") echo " checked";
		echo ' /></label>';
		echo '&nbsp;&nbsp;&nbsp;<label for="clearskys_calwidget_showical_' . $number . '">' . __('Show iCal feed: ') . ' <input id="clearskys_calwidget_showical_' . $number . '" name="clearskys_calwidget_showical_' . $number . '" type="checkbox" value="1"';
		if($clearskys_calwidget_showical != "") echo " checked";
		echo ' /></label>';
		echo '<br />';
		echo '<label for="clearskys_calwidget_jlink_' . $number . '">' . __('Use a Javascript Pop-up feed link: ') . ' <input id="clearskys_calwidget_jlink_' . $number . '" name="clearskys_calwidget_jlink_' . $number . '" type="checkbox" value="1"';
		if($clearskys_calwidget_jlink != "") echo " checked";
		echo ' /></label>';
		echo '</p>';
		
		
		
		echo '<p style="padding: 5px; background: #FFFFFF; border: 1px solid #BBBBBB; width: 300px; text-align:left;"><label for="clearskys_calwidget_style_' . $number .'" style="display: block;">' . __('Calendar style:') . ' <textarea cols="30" rows="5" style="width: 250px; display: block;" id="clearskys_calwidget_style_' . $number .'" name="clearskys_calwidget_style_' . $number .'">'.$clearskys_calwidget_style.'</textarea></label></p>';
		echo '<input type="hidden" id="clearskys_calwidget_submit_' . $number .'" name="clearskys_calwidget_submit_' . $number .'" value="1" />';
	}
	
	function showcalendar($args, $number = 1) {
		global $wpdb;
		
		$csw = get_option('clearskys_calendar_widget_config');
		$cs = get_option("clearskys_config");
		$tblbooking = $wpdb->prefix . "cs_booking";
  		$booking = new CSbooking($wpdb,$tblbooking);
  		
  		//set property number
  		$propertyid = False;
  		$csw = $csw[$number];
  		
  		switch($csw['clearskys_calwidget_getpropertyno']) {
  			case "propertyno":
  				$number = $csw['clearskys_calwidget_getpropertynosetting'];
				if(is_numeric($number)) {
					$propertyid = $number;
				}
  				break;
  			case "query":
  				$attr = $csw['clearskys_calwidget_getpropertynosetting'];
				if($attr != "") {
					$propertyid = $booking->xss_clean($_GET[$attr]);
					if(!is_numeric($propertyid)) {
						$propertyid = False;
					} 
				}
  				break;
  			case "uri":
  				$number = $csw['clearskys_calwidget_getpropertynosetting'];
				if(is_numeric($number)) {
					$uri = $_SERVER['REQUEST_URI'];
					$urisplit = explode("/",$uri);
					if(count($urisplit) > $number && is_numeric($urisplit[$number])) {
						$propertyid = $urisplit[$number];
					} else {
						$propertyid = False;
					}
				}
  				break;
  			case "ref":
  				if(class_exists('CSproperty')) {
				$csprop = new CSproperty($wpdb, $wpdb->prefix);
  				$number = $csw['clearskys_calwidget_getpropertynosetting'];
  				$rows = $csprop->getpropertyforref($number);
  				if($rows) {
  					$propertyid = $rows[0]['id'];
  				}
				}
  				break;
  			case "uriref":
  				if(class_exists('CSproperty')) {
				$number = $csw['clearskys_calwidget_getpropertynosetting'];
				if(is_numeric($number)) {
					$uri = $_SERVER['REQUEST_URI'];
					$urisplit = explode("/",$uri);
					if(count($urisplit) > $number) {
						$csprop = new CSproperty($wpdb, $wpdb->prefix);
						$rows = $csprop->getpropertyforref($urisplit[$number]);
						if($rows) {
		  					$propertyid = $rows[0]['id'];
		  				}
					} else {
						$propertyid = False;
					}
				}
				}
  				break;
  			case "queryref":
  				if(class_exists('CSproperty')) {
				$attr = $csw['clearskys_calwidget_getpropertynosetting'];
				if($attr != "") {
					$csprop = new CSproperty($wpdb, $wpdb->prefix);
					$rows = $csprop->getpropertyforref($booking->xss_clean($_GET[$attr]));
					if($rows) {		
		  				$propertyid = $rows[0]['id'];
		  			}
				}
				}
  				break;
  			default:
  				break;
  		}
  		$booking->setid($propertyid);
  		// output the standard widget stuff
		echo $args['before_widget'];
        echo $args['before_title'] . $csw['clearskys_calwidget_title'] . $args['after_title'];
        // output the styles
        if($csw['clearskys_calwidget_style'] != "") {
        	echo '<style type="text/css">';
       	 	echo $csw['clearskys_calwidget_style'];
       		 echo '</style>';
        }
        // output the calendar
		$thedate = strtotime(date("Y-m-01"));
		$month = "";
		$number = $csw['clearskys_calwidget_numberofmonths'];
		if(is_numeric($number)) { 
			for($z=1; $z<=$number; $z++) {
				// add a month to the output
				$month .= $this->buildmonth(date("Y", $thedate),date("m",$thedate),$propertyid);
				$thedate = strtotime("+1 month", $thedate);
			}
		} 
		echo $month;
		
		// show feed links
		if($csw['clearskys_calwidget_showical'] == '1' || $csw['clearskys_calwidget_showrss'] == '1') {
			echo '<div class="cs_availabilitycalendar_feedcontainer">';
			if($propertyid) {
				$useprop = $propertyid;
			} else {
				$useprop = $cs["clearskys_propertyno"];
			}
			$link = "<a href='";
			$useurl =  get_option('siteurl')  . $cs["clearskys_publicpath"];
			
			if($this->isquerystring($cs["clearskys_publicpath"])) {
				$useurl .= "&property=" . $useprop . "&feed=";
			} else {
				$useurl .=  "/" . $useprop . "?feed=";
			}
			$link .= $useurl;

			if($csw['clearskys_calwidget_showical'] == '1') {
				$ilink = "' title='Subscribe to iCal feed' class='icalfeedlink'";
				if($csw['clearskys_calwidget_jlink'] == '1') {
					$ilink .= " onclick='prompt(\"To subscribe to this feed, cut and paste the URL below into your Calendar application when asked for subscribe details\",\"";
					$ilink .= $useurl . "ical\"); return false;'";
				}
				$ilink .= ">";
//amr				$ilink .= "<img src='" .  get_option('siteurl')  . "/wp-content/plugins/clearskys/includes/images/date22x22.png' alt='iCal feed' width='22' height='22' style='vertical-align: bottom;' />";
			$ilink .= "<img src='" . WPCPLUGINURL ."/includes/images/date22x22.png' alt='iCal feed' width='22' height='22' style='vertical-align: bottom;' />";
				echo $link . "ical" . $ilink;
				echo "</a>";
			}
			if($csw['clearskys_calwidget_showrss'] == '1') {
				$ilink = "' title='Subscribe to RSS feed' class='rssfeedlink'";
				if($csw['clearskys_calwidget_jlink'] == '1') {
					$ilink .= " onclick='prompt(\"To subscribe to this feed, cut and paste the URL below into your Feed Reader when asked for subscribe details\",\"";
					$ilink .= $useurl . "rss\"); return false;'";
				}
				$ilink .= ">";
//amr				$ilink .= "<img src='" .  get_option('siteurl')  . "/wp-content/plugins/clearskys/includes/images/feed-icon16x16.png' alt='RSS feed' width='16' height='16' style='vertical-align: text-top;' />";
	            $ilink .= "<img src='" .  WPCPLUGINURL ."/includes/images/feed-icon16x16.png' alt='RSS feed' width='16' height='16' style='vertical-align: text-top;' />";
				echo $link . "rss" . $ilink;
				echo "</a>";
			}
			
			echo '</div>';
		}
        
        
        // end the widget
        echo $args['after_widget'];
	}
	
	function buildmonth($year, $mon, $propertyno = False)
	{
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
  		for($n=1; $n<=$daysinmonth; $n++) {
  			$today = strtotime($year . "-" . $mon. "-" . str_pad($n,2,"00",STR_PAD_LEFT));
  			$yesterday = strtotime("-1 day", $today);
  			$tomorrow = strtotime("+1 day", $today);
  			
  			if(isset($master[date("Ymd",$today)])) {
  				$dayhtml = "<td class='";
				if(isset($master[date("Ymd",$today)]['status'])) {
					$dayhtml .= $master[date("Ymd",$today)]['status'];
				} else {
					$dayhtml .= "booked";
				}
  				if(!isset($master[date("Ymd",$yesterday)])) $dayhtml .= " startday";
  				if(!isset($master[date("Ymd",$tomorrow)])) $dayhtml .= " endday";
  				$dayhtml .= "'>{day}</td>";
  			} else {
  				$dayhtml = "<td>{day}</td>";
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
  			$week[$n] = str_replace('{day}','&nbsp;','<td>{day}</td>');
  			}
  			// Add last week to the month
  			$month = $month + array("$m" => $week);
  		}
  		
  		//Build the month table and return the html
  		setlocale(LC_ALL,$cs["clearskys_publiclocale"]);
  		$amonth="";
  		foreach($month as $week) {
  			$amonth .= "<tr>" . implode('',$week) . "</tr>\n";
  		}
  		$amonth = "<tbody" . $amonth . "</tbody>";
  		$amonth = stripslashes($cs["clearskys_calendar_weekheader"]) . $amonth;
  		$bmonth = "<div class='cs_availabilitycalendar_container " . strtolower(strftime("%B %Y",$startofmonth));
  		if($year == date("Y")) $bmonth .= " thisyear";
  		if($year > date("Y")) $bmonth .= " nextyear";
  		if($mon == date("m")) $bmonth .= " thismonth";
  		$amonth = $bmonth . "'><table class='cs_availabilitycalendar'>\n<caption>" . ucwords(strftime("%B %Y",$startofmonth)) . "</caption>\n" . $amonth . "\n</table></div>\n";
  		//print_r($amonth);
		return $amonth;
	}
	
	
}

//CSbookwidget::bootstrap();
$CSbw =& new CSbookwidget();

?>