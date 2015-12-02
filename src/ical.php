<?php
/*
     iCAL-Extension for lobbycal2press
	 json example full: https://lobbycal.greens-efa-service.eu/api/meetings/mep/
	 json example detail: https://lobbycal.greens-efa-service.eu/api/meetings/mep/8
	 provided by @RobertHarm, re-using work from webtermine.at
*/
//info: construct path to wp-load.php and initialize WordPress
while(!is_file('wp-load.php')) {
	if(is_dir('..' . DIRECTORY_SEPARATOR)) chdir('..' . DIRECTORY_SEPARATOR);
	else die('Error: Could not construct path to wp-load.php');
}
include( 'wp-load.php' );

/**HELPER FUNCTIONS**/
/** Quotes iCalendar special characters. */
function ec3_ical_quote($string) {
	$s = preg_replace('/([\\,;])/','\\\\$1',$string);
	$s = preg_replace('/\r?\n/','\\\\n',$s);
	$s = preg_replace('/\r/',' ',$s);
	return $s;
}
/** Folds an iCalendar content-line so that it does not exceed
*  75 characters. Appends CRLF to the end of the line. */
function ec3_ical_fold($string, $max = 75) {
	$result = '';
	$s = $string;
	while(strlen($s)>$max)
	{
	  $len = $max;
	  while( $len>($max-10) && substr($s,$len-1,1) == '\\' )
		$len --;
	  $result .= substr($s,0,$len) . "\r\n\t";
	  $s = substr($s,$len);
	}
	$result .= $s;
	//    return $result."\r\n";
	return $result."\r\n";
}
/** Folds an iCalendar content-line and echos it to stdout */
function ec3_ical_echo($string, $max = 75) {
	echo utf8_encode(ec3_ical_fold($string, $max));
}

/*DATE TIME HELPER*/
function ec3_tz_push($tz) {
	$old_tz=date_default_timezone_get();
	date_default_timezone_set($tz);
	return $old_tz;
}
function ec3_tz_pop($tz) {
	date_default_timezone_set($tz);
}
/** Converts a WordPress timestamp (string in local time) to Unix time. */
function ec3_to_time($timestamp) {
	//global $ec3;
	// Parse $timestamp and extract the Unix time.
	$old_tz=ec3_tz_push($ec3->tz);
	$unix_time = strtotime($timestamp);
	ec3_tz_pop($old_tz);
	// Unix time is seconds since the epoch (in UTC).
	return $unix_time;
}
/** Converts a WordPress timestamp (string in local time) to
 *  string in formatted UTC. */
function ec3_to_utc($timestamp,$fmt='%Y%m%dT%H%M00Z') {
	$result = gmstrftime($fmt,ec3_to_time($timestamp));
	return $result;
}

$options = get_option ( 'lobbycal2press_settings' );
$api_url = $options['lobbycal2press_text_field_apiURL'];

//info: override $api_url for testing purposes
if (isset($_GET['full'])) {
	$api_url = 'https://lobbycal.greens-efa-service.eu/api/meetings/';
}

$result = wp_remote_get($api_url, array( 'sslverify' => true, 'timeout' => 10 ) );

if ( !is_wp_error($result) && isset($result['response']['code']) && ($result['response']['code'] == 200) ){
	$json = json_decode($result['body']);
	
	//header('Content-type: application/json; charset=utf-8');
	header('Content-Type: text/calendar; charset=' . get_option('blog_charset'));
	header('Content-Disposition: inline; filename=' . $options['lobbycal2press_ical_filename']);
	header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	ec3_ical_echo('BEGIN:VCALENDAR').PHP_EOL;
	ec3_ical_echo('VERSION:2.0').PHP_EOL;
	$ical_calendar_name = ec3_ical_echo('X-WR-CALNAME:' . $options['lobbycal2press_ical_calendar_name']);
	echo $ical_calendar_name; 
	ec3_ical_echo("X-WR-TIMEZONE:Europe/Brussels").PHP_EOL;
	$ical_calendar_description = ec3_ical_echo('X-WR-CALDESC:' . $options['lobbycal2press_ical_calendar_description']);
	echo $ical_calendar_description; 
	ec3_ical_echo("CALSCALE:GREGORIAN").PHP_EOL;

	$first = true;
	foreach($json as $detail) {
		if ($first) $first = false;

			ec3_ical_echo('BEGIN:VEVENT');			
			ec3_ical_echo('UID:'.md5("$detail->id . $detail->userid")."@lobbycal2press");
			$title_prefix = '';
			if ($options['lobbycal2press_ical_title_prefix'] != NULL) {
				$title_prefix .= $options['lobbycal2press_ical_title_prefix'];
			} 
			$the_title_ical = $title_prefix .  ' ' . str_replace("&#8211;","-",str_replace("&#8220;","\"",str_replace("&#8221;","\"",str_replace("&#038;","&",$detail->title))));
			ec3_ical_echo('SUMMARY:'.utf8_decode(ec3_ical_quote($the_title_ical)));
			
			//info: static url for all meetings
			if ($options['lobbycal2press_ical_static_url'] != NULL) {
				$static_url_raw = $options['lobbycal2press_ical_static_url'];
				$static_url = ec3_ical_echo("URL;VALUE=URI:$static_url_raw");
				echo $static_url;
			}
			
			//info: add infos about meeting partners
			$description = 'Participants:\n' . $detail->userFirstName . ' ' . $detail->userLastName . ' and ';
			$first_partner = true;
			foreach($detail->partners as $partner) {
				if ($first_partner) { $first_partner = false; } else { $description .= ', '.PHP_EOL; }
				$description .= $partner->name;
				if ($partner->transparencyRegisterID != NULL) {
					$description .= ' (transparencyRegisterID: '. $partner->transparencyRegisterID;
				} else {
					$description .= ' (no transparencyRegisterID available)';
				}
			}
			
			//info: add tags
			if ($detail->tags != NULL) {
				$description .= '\n\nTags:\n';
				$first_tag = true;
				foreach($detail->tags as $tag) {
					if ($first_tag) { $first_tag = false; } else { $description .= ', '; }
					$description .= $tag->i18nKey;
				}
			} else {
				$description .= '\n\nNo tags available for this meeting.';
			}
			
			//info: add static content for all meetings
			if ($options['lobbycal2press_ical_static_content'] != NULL) {
				$description .= '\n\n---\n' . $options['lobbycal2press_ical_static_content'];
			}
						
			ec3_ical_echo('DESCRIPTION:'.utf8_decode(ec3_ical_quote($description)));

			//info: ec3_ical_echo('TRANSP:OPAQUE'); // for availability - removed by request from @mleyrer
			//info: Convert timestamps to UTC
			ec3_ical_echo('DTSTART;VALUE=DATE-TIME:'.ec3_to_utc($detail->startDate));
			ec3_ical_echo('DTEND;VALUE=DATE-TIME:'.ec3_to_utc($detail->endDate));
			
			//info: fixed location for all meetings
			if ($options['lobbycal2press_ical_fixed_location'] != NULL) {
				$fixed_location = ec3_ical_echo('LOCATION:' . $options['lobbycal2press_ical_fixed_location']);
				echo $fixed_location;
			}

			//info: fixed lat+lon values for all meetings
			if ( ($options['lobbycal2press_ical_fixed_location_latitude'] != NULL) && ($options['lobbycal2press_ical_fixed_location_longitude'] != NULL) ) {
				$geo = ec3_ical_echo('GEO:' . floatval($options['lobbycal2press_ical_fixed_location_latitude']) . ';' . floatval($options['lobbycal2press_ical_fixed_location_longitude']));
				echo $geo;
			}
			 
			ec3_ical_echo('END:VEVENT');
	} 
	ec3_ical_echo('END:VCALENDAR');

} else if ( is_wp_error($result) ) {
	echo "WP HTTP error: " . $result->get_error_message();
}