<?php
/*
 * License - all content is made available under the Creative Commons License http://creativecommons.org/licenses/by/2.5/.
 */

class CSbooking
	{
		var $db = "";
		var $table = "";
		var $id = 0;
		
		var $statusarray = array( 	"0" => "Pending",
								"1" => "Deposit Paid",
								"2" => "Fully Paid",
								"3" => "Confirmed",
								"9" => "Long Term"
							);
		
		function CSbooking($db, $table)
		{
			$this->db = $db;
			$this->table = $table;
		}
		
		function setid($id)
		{
			$id = $this->xss_clean($id);
			$this->id = $id; 
		}
		
		function getid()
		{
			return $this->id; 
		}
		
		function getfirstdate()
		{
			return $this->db->get_var('SELECT MIN(startdate) FROM ' . $this->table);
		}
		
		function getstartdates()
		{
			$sql = "SELECT LEFT(startdate,7) AS optval, 
					DATE_FORMAT(startdate,'%M %Y') AS optdesc,
					CONCAT(LEFT(startdate,7),'-01') AS optstart,
					COUNT(*) as optnum
					FROM " . $this->table ."
					GROUP BY optval, optdesc, optstart 
					ORDER BY optval DESC";
			$result = $this->db->get_results($sql, ARRAY_A);
			//$return = $this->db->get_var('SELECT MIN(startdate) FROM ' . $this->table);
			
			return $result;
		}
		
		function getinitiallist($full = false)
		{
			if($full === True) {
				$sql = "SELECT * ";
			} else {
				$sql = "SELECT id, property_id,
						title,
						startdate,
						enddate,
						status,
						rentername,
						renteremail,
						rentertel ";
			}
			$sql .= "FROM " . $this->table . " 
					WHERE (startdate <= CURDATE() AND enddate >= CURDATE()) 
					OR (MONTH(startdate) = MONTH(CURDATE()) AND YEAR(startdate) = YEAR(CURDATE())) 
					ORDER BY startdate DESC;";
			$result = $this->db->get_results($sql, ARRAY_A);
			
			return $result;
		}
		
		function getinitialfeedlist($full = false)
		{
			$sql = "SELECT * ";
			
			$sql .= "FROM " . $this->table . " 
					WHERE (startdate <= CURDATE() AND enddate >= CURDATE()) 
					OR (MONTH(startdate) = MONTH(CURDATE()) AND YEAR(startdate) = YEAR(CURDATE())) 
					ORDER BY startdate DESC;";
			$result = $this->db->get_results($sql, ARRAY_A);
			
			return $result;
		}
		
		function getfeedlist($val, $status = "", $month = "", $propertyid = "", $full = false) {
			$val = $this->xss_clean($val);
			$sql = "SELECT * ";
			$sql .= "FROM " . $this->table . " ";

			if($val != "" || $status != "" || $month != "" || $propertyid != "") {
				$sql .= "WHERE id > 0 ";
			}
			
			if($val != "") {
					$sql .= "AND (title LIKE '%" . $val . "%' OR 
							notes LIKE '%" . $val . "%' OR 
							rentername LIKE '%" . $val . "%' OR 
							renteremail LIKE '%" . $val . "%' OR 
							renternotes LIKE '%" . $val . "%') ";
			}
			
			if($status != "") {
					$sql .= "AND status = '" . $status . "' ";
			}
			
			if($month != "") {
				$sql .= "AND DATE_FORMAT(startdate,'%Y-%m') = '" . $month . "' ";
			}
			
			if($propertyid != "") {
				$sql .= "AND property_id = " . $propertyid . " ";
			}
			
			$sql .= "ORDER BY startdate DESC;";
			$result = $this->db->get_results($sql, ARRAY_A);
			
			return $result;
		}
		
		function getsearchlist($val, $status = "", $month = "", $propertyid = "")
		{
			$val = $this->xss_clean($val);
			$sql = "SELECT id, property_id,
					title,
					startdate,
					enddate,
					status,
					rentername,
					renteremail,
					rentertel
					FROM " . $this->table . " ";

			if($val != "" || $status != "" || $month != "" || $propertyid != "") {
				$sql .= "WHERE id > 0 ";
			}
			
			if($val != "") {
					$sql .= "AND (title LIKE '%" . $val . "%' OR 
							notes LIKE '%" . $val . "%' OR 
							rentername LIKE '%" . $val . "%' OR 
							renteremail LIKE '%" . $val . "%' OR 
							renternotes LIKE '%" . $val . "%') ";
			}
			
			if($status != "") {
					$sql .= "AND status = '" . $status . "' ";
			}
			
			if($month != "") {
				$sql .= "AND DATE_FORMAT(startdate,'%Y-%m') = '" . $month . "' ";
			}
			
			if($propertyid != "") {
				$sql .= "AND property_id = " . $propertyid . " ";
			}
			
			$sql .= "ORDER BY startdate DESC;";
			$result = $this->db->get_results($sql, ARRAY_A);
			
			return $result;
		}
		
		function oldgetsearchlist($val)
		{
			$val = $this->xss_clean($val);
			$sql = "SELECT id, property_id,
					title,
					startdate,
					enddate,
					status
					FROM " . $this->table . " 
					WHERE 
					title LIKE '%" . $val . "%' OR 
					notes LIKE '%" . $val . "%' OR 
					rentername LIKE '%" . $val . "%' OR 
					renteremail LIKE '%" . $val . "%' OR 
					renternotes LIKE '%" . $val . "%' 
					ORDER BY startdate DESC;";
			$result = $this->db->get_results($sql, ARRAY_A);
			
			return $result;
		}
		
		function getmonthlist($val)
		{
			$val = $this->xss_clean($val);
			$sql = "SELECT id, property_id,
					title,
					startdate,
					enddate,
					status
					FROM " . $this->table . " 
					WHERE DATE_FORMAT(startdate,'%Y-%m') = '" . $val . "' 
					ORDER BY startdate DESC;";
			$result = $this->db->get_results($sql, ARRAY_A);
			
			return $result;
		}
		
		function getstatuslist($val)
		{
			$val = $this->xss_clean($val);
			$sql = "SELECT id, property_id,
					title,
					startdate,
					enddate,
					status
					FROM " . $this->table . " 
					WHERE status = '" . $val . "' 
					ORDER BY startdate DESC;";
			$result = $this->db->get_results($sql, ARRAY_A);
			
			return $result;
		}
		
		function getpropertylist() {
			$sql = "SELECT property_id FROM " . $this->table . " ";
			$sql .= "GROUP BY property_id ORDER BY property_id";
			$result = $this->db->get_results($sql, ARRAY_A);	
			return $result;
		}
		
		function getbooking($id = 0)
		{
			$lid = $id;
			
			if($lid == 0) 
			{ 
				$lid = $this->id; 
			}
			
			$sql = "SELECT * FROM " . $this->table . " WHERE id = " . $lid . " LIMIT 0,1;";
			
			$result = $this->db->get_results($sql, ARRAY_A);
			if(!empty($result))
			{
				return $result[0];
			} else {
				return NULL;
			}
			
		}
		
		function getbookings($id = 0, $num = 100, $showpending = False)
		{
			$lid = $id;
			
			if($lid == 0) 
			{ 
				$lid = $this->id; 
			}
			
			$sql = "SELECT * FROM " . $this->table . " WHERE property_id = " . $lid;
			if(!$showpending) $sql .= " AND status > 0";
			$sql .= " ORDER BY id DESC LIMIT 0, $num;";
			
			$result = $this->db->get_results($sql, ARRAY_A);
			if(!is_null($result))
			{
				return $result;
			} else {
				return False;
			}
		}
		
		function status($val = 0)
		{
			return $this->statusarray["$val"];
		}
		
		function statuslist()
		{
			return $this->statusarray;
		}
		
		function xss_clean($str, $charset = 'ISO-8859-1')
		{	
			/*
			 * Remove Null Characters
			 *
			 * This prevents sandwiching null characters
			 * between ascii characters, like Java\0script.
			 *
			 */
			$str = preg_replace('/\0+/', '', $str);
			$str = preg_replace('/(\\\\0)+/', '', $str);
	
			/*
			 * Validate standard character entities
			 *
			 * Add a semicolon if missing.  We do this to enable
			 * the conversion of entities to ASCII later.
			 *
			 */
			$str = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u',"\\1;",$str);
			
			/*
			 * Validate UTF16 two byte encoding (x00)
			 *
			 * Just as above, adds a semicolon if missing.
			 *
			 */
			$str = preg_replace('#(&\#x*)([0-9A-F]+);*#iu',"\\1\\2;",$str);
	
			/*
			 * URL Decode
			 *
			 * Just in case stuff like this is submitted:
			 *
			 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
			 *
			 * Note: Normally urldecode() would be easier but it removes plus signs
			 *
			 */	
			$str = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $str);
			$str = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $str);		
					
			/*
			 * Convert character entities to ASCII
			 *
			 * This permits our tests below to work reliably.
			 * We only convert entities that are within tags since
			 * these are the ones that will pose security problems.
			 *
			 */
			
			if (preg_match_all("/<(.+?)>/si", $str, $matches))
			{		
				for ($i = 0; $i < count($matches['0']); $i++)
				{
					$str = str_replace($matches['1'][$i],
										$this->_html_entity_decode($matches['1'][$i], $charset),
										$str);
				}
			}
		
			/*
			 * Convert all tabs to spaces
			 *
			 * This prevents strings like this: ja	vascript
			 * Note: we deal with spaces between characters later.
			 *
			 */		
			$str = preg_replace("#\t+#", " ", $str);
		
			/*
			 * Makes PHP tags safe
			 *
			 *  Note: XML tags are inadvertently replaced too:
			 *
			 *	<?xml
			 *
			 * But it doesn't seem to pose a problem.
			 *
			 */		
			$str = str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
		
			/*
			 * Compact any exploded words
			 *
			 * This corrects words like:  j a v a s c r i p t
			 * These words are compacted back to their correct state.
			 *
			 */		
			$words = array('javascript', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
			foreach ($words as $word)
			{
				$temp = '';
				for ($i = 0; $i < strlen($word); $i++)
				{
					$temp .= substr($word, $i, 1)."\s*";
				}
				
				$temp = substr($temp, 0, -3);
				$str = preg_replace('#'.$temp.'#s', $word, $str);
				$str = preg_replace('#'.ucfirst($temp).'#s', ucfirst($word), $str);
			}
		
			/*
			 * Remove disallowed Javascript in links or img tags
			 */		
			 $str = preg_replace("#<a.+?href=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $str);
			 $str = preg_replace("#<img.+?src=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si", "", $str);
			 $str = preg_replace("#<(script|xss).*?\>#si", "", $str);
	
			/*
			 * Remove JavaScript Event Handlers
			 *
			 * Note: This code is a little blunt.  It removes
			 * the event handler and anything up to the closing >,
			 * but it's unlikely to be a problem.
			 *
			 */		
			 $str = preg_replace('#(<[^>]+.*?)(onblur|onchange|onclick|onfocus|onload|onmouseover|onmouseup|onmousedown|onselect|onsubmit|onunload|onkeypress|onkeydown|onkeyup|onresize)[^>]*>#iU',"\\1>",$str);
		
			/*
			 * Sanitize naughty HTML elements
			 *
			 * If a tag containing any of the words in the list
			 * below is found, the tag gets converted to entities.
			 *
			 * So this: <blink>
			 * Becomes: &lt;blink&gt;
			 *
			 */		
			$str = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $str);
			
			/*
			 * Sanitize naughty scripting elements
			 *
			 * Similar to above, only instead of looking for
			 * tags it looks for PHP and JavaScript commands
			 * that are disallowed.  Rather than removing the
			 * code, it simply converts the parenthesis to entities
			 * rendering the code un-executable.
			 *
			 * For example:	eval('some code')
			 * Becomes:		eval&#40;'some code'&#41;
			 *
			 */
			$str = preg_replace('#(alert|cmd|passthru|eval|exec|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
							
			/*
			 * Final clean up
			 *
			 * This adds a bit of extra precaution in case
			 * something got through the above filters
			 *
			 */	
			$bad = array(
							'document.cookie'	=> '',
							'document.write'	=> '',
							'window.location'	=> '',
							"javascript\s*:"	=> '',
							"Redirect\s+302"	=> '',
							'<!--'				=> '&lt;!--',
							'-->'				=> '--&gt;'
						);
		
			foreach ($bad as $key => $val)
			{
				$str = preg_replace("#".$key."#i", $val, $str);
			}
			
							
			return $str;
		}
		
		function _html_entity_decode($str, $charset='ISO-8859-1')
		{
			if (stristr($str, '&') === FALSE) return $str;
		
			// The reason we are not using html_entity_decode() by itself is because
			// while it is not technically correct to leave out the semicolon
			// at the end of an entity most browsers will still interpret the entity
			// correctly.  html_entity_decode() does not convert entities without
			// semicolons, so we are left with our own little solution here. Bummer.
		
			if (function_exists('html_entity_decode') && (strtolower($charset) != 'utf-8' OR version_compare(phpversion(), '5.0.0', '>=')))
			{
				$str = html_entity_decode($str, ENT_COMPAT, $charset);
				$str = preg_replace('~&#x([0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
				return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
			}
			
			// Numeric Entities
			$str = preg_replace('~&#x([0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $str);
			$str = preg_replace('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $str);
		
			// Literal Entities - Slightly slow so we do another check
			if (stristr($str, '&') === FALSE)
			{
				$str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
			}
			
			return $str;
		}
		
		function overlapsql($id, $startdate, $enddate)
		{
			$sql = "SELECT COUNT(*) FROM " . $this->table . " 
					WHERE property_id = " . $id . " 
					AND id <> " . $this->id . " 
					AND (startdate < '" . $enddate . "' AND enddate > '" . $startdate . "');";
			
			return $sql;
		}
		
		function update($data)
		{
			$error = "";
			// Clean the data of any nastiness
			foreach($data as $key => $value) {
				$data[$key] = $this->xss_clean($value);
			}
			// Check for date conflict
			$sql = $this->overlapsql($data['property_id'], $data['startdate'], $data['enddate']);
			
			$result = $this->db->get_var($sql);
			if($result != '0') {
				$error = "This booking overlaps an existing booking for this property\n";
			}
			// update data
			if($error == "") {
				$sql = "UPDATE " . $this->table . " SET ";
				$isql = "";
				foreach($data as $key => $value) {
					if($isql != "")
					{
						$isql .= ", ";
					}
					if (get_magic_quotes_gpc()) {
	     			  $value = stripslashes($value);
	   				}
					if(is_numeric($value)) {
						$isql .= "$key = $value";
					} else {
						$isql .= "$key = '" . mysql_real_escape_string($value) . "'";
					}
				}
				$sql .= $isql;		
				$sql .= " WHERE id = " . $this->id;
				
				$result = $this->db->query($sql);
				if($result === False) {
					return "Error occured adding booking to database.";
				} else {
					return "";
				}
				
			} else {
				return $error;
			}
		}
		
		function add($data)
		{
			// Clean the data of any nastiness
			foreach($data as $key => $value) {
				$data[$key] = $this->xss_clean($value);
			}
			// Check for date conflict
			$sql = $this->overlapsql($data['property_id'], $data['startdate'], $data['enddate']);
			
			$result = $this->db->get_var($sql);
			if($result != '0') {
				$error = "This booking overlaps an existing booking for this property\n";
			}
			else $error = '';
			// update data
			if($error == "") {
				$sql = "INSERT INTO " . $this->table . " ";
				$isql = "";
				$vsql = "";
				foreach($data as $key => $value) {
					if($isql != "")
					{
						$isql .= ", ";
						$vsql .= ", ";
					}
					if (get_magic_quotes_gpc()) {
	     			  $value = stripslashes($value);
	   				}
	   				$isql .= $key;
					if(is_numeric($value)) {
						$vsql .= $value;
					} else {
						$vsql .= "'" . mysql_real_escape_string($value) . "'";
					}
				}
				$sql .= "(" . $isql . ") ";
				$sql .= "VALUES (" . $vsql . ");";		
				
				$result = $this->db->query($sql);
				if($result === False) {
					return "Error occured adding booking to database.";
				} else {
					return "";
				}
				
			} else {
				return $error;
			}
		}
	
		function delete($id)
		{
			$id = $this->xss_clean($id);
			$sql = "DELETE FROM " . $this->table . " WHERE id=" . $id;
			$result = $this->db->query($sql);
			if($result === False) {
				return "Error occured deleting booking from database.";
			} else {
				return "ok";
			}
		}
		
		function getmonth($year, $month, $property = 0, $prepostpack = False)
		{
			$firstdate = $year . "-" . $month ."-01";
			$numdays = date('t', strtotime($firstdate));
			$lastdate = $year . "-" . $month ."-" . $numdays;
			
			if($prepostpack) {
				$newfirstdate = strtotime("-1 day", strtotime($firstdate));
				$newlastdate = strtotime("+1 day", strtotime($lastdate));
				$firstdate = date("Y-m-d", $newfirstdate);
				$lastdate = date("Y-m-d", $newlastdate);
			}
			
			$sql = "SELECT id, startdate, enddate, title, status 
					FROM " . $this->table . " 
					WHERE  
					(startdate <= '" . $lastdate . "' AND enddate >= '" . $firstdate . "') 
					AND status >= 0 ";
			if($property != 0) {
				$sql .= "AND property_id=" . $property . " ";
			}
			$sql .= "ORDER BY startdate ASC;";
			
			$result = $this->db->get_results($sql, ARRAY_A);
			if(!is_null($result))
			{
				return $result;
			} else {
				return False;
			}
		}
		
		function getmontharray($year, $month, $property = 0, $prepostpack = False)
		{
			// Get the main events array
			$events = $this->getmonth($year,$month,$property,$prepostpack);
			// Process into new format.
			$master = array();
			
			$mbegin = strtotime($year . "-" . $month . "-01");
			$mend = strtotime($year . "-" . $month . "-" . date("t",$mbegin));
			if($prepostpack) {
				$newfirstdate = strtotime("-1 day", $mbegin);
				$newlastdate = strtotime("+1 day", $mend);
				$mbegin = $newfirstdate;
				$mend = $newlastdate;
			}
			
			if($events) {
				foreach($events as $event) {
					$today = strtotime($event['startdate']);
					$end = strtotime($event['enddate']);
					while($today <= $end) {
						if($today >= $mbegin && $today <= $mend) {
							// return only dates in this month
							$key = date("Ymd", $today);
							$master["$key"] = array(	"title" => $event['title'],
														"status" => strtolower(str_replace(" ","",$this->statusarray[$event['status']]))
													);
						}
						$today = strtotime("+1 day", $today);
					}
				}
			}
			return $master;
		}
		
		
	}
	
?>