<?php
/*
 * Plugin Name: lobbycal2press for wordpress
 * Plugin URI: http://lobbycal.greens-efa-service.eu
 * Description: Plugin to display meetings from lobbycal.greens-efa-service.eu. Based on http://datatables.net/dev/knockout/
 * Version: 1.1
 * Author: GREENS EFA EU
 * Author URI: http://lobbycal.greens-efa-service.eu
 * License: GPL
 */
function lobbycal2press_scripts() {
	
	// Load custom scripts
	wp_enqueue_script ( 'jquery' );
	wp_enqueue_script ( 'jquery.datatables', plugin_dir_url ( __FILE__ ) . 'jquery.datatables.js', array (
			'jquery' 
	), '1.0', true );
	wp_enqueue_script ( 'knockout-min', plugin_dir_url ( __FILE__ ) . 'knockout-min.js', array (
			'jquery' 
	), '1.0', true );
	wp_enqueue_script ( 'knockout.mapping', plugin_dir_url ( __FILE__ ) . 'knockout.mapping.js', array (
			'jquery' 
	), '1.0', true );
	wp_enqueue_script ( 'moment.min', plugin_dir_url ( __FILE__ ) . 'moment.min.js', array (
			'jquery' 
	), '1.0', true );
	
	wp_enqueue_script ( 'lobbycal2press', plugin_dir_url ( __FILE__ ) . 'lobbycal2press.js', array (
			'jquery' 
	), '1.0', true );
	
	// Load custom styles
	
	wp_enqueue_style ( 'lobbycal2press', plugin_dir_url ( __FILE__ ) . 'custom.css' );
	wp_enqueue_style ( 'lobbycal2press-jqdt', plugin_dir_url ( __FILE__ ) . 'jquery.datatables.css' );
	
	// Configure according to settings
	function lc2p_js_variables() {
		$options = get_option ( 'lobbycal2press_settings' );
		?>
<script type="text/javascript">
			var lc2pUrl = '<?php echo $options['lobbycal2press_text_field_apiURL']; ?>';

			var lc2pMax = '<?php echo $options['lobbycal2press_text_field_max']; ?>';

	        var lc2pShowStart  = '<?php echo $options['lobbycal2press_checkbox_field_start']; ?>';

	        var lc2pShowEnd = '<?php echo $options['lobbycal2press_checkbox_field_end']; ?>';
	        
	        var lc2pDateFormat = '<?php echo $options['lobbycal2press_text_field_dateFormat']; ?>';

	        var lc2pShowFirstName  = '<?php echo $options['lobbycal2press_checkbox_field_firstname']; ?>';

	        var lc2pShowLastName  = '<?php echo $options['lobbycal2press_checkbox_field_lastname']; ?>';

	        var lc2pShowTitle = '<?php echo $options['lobbycal2press_checkbox_field_title']; ?>';

	        var lc2pShowPartner  = '<?php echo $options['lobbycal2press_checkbox_field_partner']; ?>';

	        var lc2pShowTags  = '<?php echo $options['lobbycal2press_checkbox_field_tags']; ?>';

	        var lc2pShowTagsTitle  = '<?php echo $options['lobbycal2press_checkbox_field_tagsTitle']; ?>';
	        var lc2pPerPage  = '<?php echo $options['lobbycal2press_text_field_perPage']; ?>';
	        lc2pPerPage = (lc2pPerPage === undefined) ? 10 : lc2pPerPage;
	        lc2pPerPage = (lc2pPerPage === '') ? 10 : lc2pPerPage;
		</script>
<?php
	}
	add_action ( 'wp_head', 'lc2p_js_variables' );
}

add_action ( 'wp_enqueue_scripts', 'lobbycal2press_scripts' );

add_action ( 'admin_menu', 'lobbycal2press_add_admin_menu' );
add_action ( 'admin_init', 'lobbycal2press_settings_init' );
function lobbycal2press_add_admin_menu() {
	add_options_page ( 'lobbycal2press', 'lobbycal2press', 'manage_options', 'lobbycal2press', 'lobbycal2press_options_page' );
}
function lobbycal2press_settings_init() {
	register_setting ( 'pluginPage', 'lobbycal2press_settings' );
	
	add_settings_section ( 'lobbycal2press_pluginPage_section', __ ( 'Settings for lobbycal2press plugin', 'lobbycal2press' ), 'lobbycal2press_settings_section_callback', 'pluginPage' );
	
	add_settings_field ( 'lobbycal2press_text_field_apiURL', __ ( 'API URL for your MEP ', 'lobbycal2press' ), 'lobbycal2press_text_field_apiURL_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	add_settings_field ( 'lobbycal2press_text_field_max', __ ( 'Number of meetings to load', 'lobbycal2press' ), 'lobbycal2press_text_field_max_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	add_settings_field ( 'lobbycal2press_text_field_perPage', __ ( 'Number of meetings to display on a table page', 'lobbycal2press' ), 'lobbycal2press_text_field_perPage_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	
	add_settings_field ( 'lobbycal2press_checkbox_field_start', __ ( 'Display column for start date?', 'lobbycal2press' ), 'lobbycal2press_checkbox_field_start_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	add_settings_field ( 'lobbycal2press_checkbox_field_title', __ ( 'Display column for title', 'lobbycal2press' ), 'lobbycal2press_checkbox_field_title_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	add_settings_field ( 'lobbycal2press_checkbox_field_partner', __ ( 'Display column for partner?', 'lobbycal2press' ), 'lobbycal2press_checkbox_field_partner_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	
	add_settings_field ( 'lobbycal2press_checkbox_field_tags', __ ( 'Display column for tags?', 'lobbycal2press' ), 'lobbycal2press_checkbox_field_tags_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	add_settings_field ( 'lobbycal2press_checkbox_field_tagsTitle', __ ( 'Display tags with title?', 'lobbycal2press' ), 'lobbycal2press_checkbox_field_tagsTitle_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	
	add_settings_field ( 'lobbycal2press_checkbox_field_end', __ ( 'Display column for end date?', 'lobbycal2press' ), 'lobbycal2press_checkbox_field_end_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	add_settings_field ( 'lobbycal2press_text_field_dateFormat', __ ( 'Date format <br/> see <a href="http://momentjs.com/"> here </a> for options', 'lobbycal2press' ), 'lobbycal2press_text_field_dateFormat_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	
	add_settings_field ( 'lobbycal2press_checkbox_field_firstname', __ ( 'Display column for MEP first name?', 'lobbycal2press' ), 'lobbycal2press_checkbox_field_firstname_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	
	add_settings_field ( 'lobbycal2press_checkbox_field_lastname', __ ( 'Display column for MEP last name?', 'lobbycal2press' ), 'lobbycal2press_checkbox_field_lastname_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	
	add_settings_field ( 'lobbycal2press_textarea_field_example', __ ( 'Use this code to place the calendar in a post or page', 'lobbycal2press' ), 'lobbycal2press_textarea_field_example_render', 'pluginPage', 'lobbycal2press_pluginPage_section' );
	
	/*ical*/
	add_settings_section ( 'lobbycal2press_pluginPage_section_ical', __ ( 'iCal Settings for lobbycal2press plugin', 'lobbycal2press' ), 'lobbycal2press_settings_section_ical_callback', 'pluginPage' );

	add_settings_field ( 'lobbycal2press_ical_calendar_name', __ ( 'calendar name', 'lobbycal2press' ), 'lobbycal2press_ical_calendar_name_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
	add_settings_field ( 'lobbycal2press_ical_calendar_description', __ ( 'calendar description', 'lobbycal2press' ), 'lobbycal2press_ical_calendar_description_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
	add_settings_field ( 'lobbycal2press_ical_title_prefix', __ ( 'title prefix for all meetings', 'lobbycal2press' ), 'lobbycal2press_ical_title_prefix_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
	add_settings_field ( 'lobbycal2press_ical_static_content', __ ( 'static content to be added at the end of each meeting description', 'lobbycal2press' ), 'lobbycal2press_ical_static_content_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
	add_settings_field ( 'lobbycal2press_ical_static_url', __ ( 'static url for all meetings', 'lobbycal2press' ), 'lobbycal2press_ical_static_url_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
	add_settings_field ( 'lobbycal2press_ical_filename', __ ( 'filename of iCal file', 'lobbycal2press' ), 'lobbycal2press_ical_filename_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
	add_settings_field ( 'lobbycal2press_ical_fixed_location', __ ( 'geolocation 1: use fixed location for all meetings (eg EU Parliament)', 'lobbycal2press' ), 'lobbycal2press_ical_fixed_location_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
	add_settings_field ( 'lobbycal2press_ical_fixed_location_lat', __ ( 'geolocation 2a: use fixed latitude value for all meetings (eg 50.838908)', 'lobbycal2press' ), 'lobbycal2press_ical_fixed_location_latitude_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
	add_settings_field ( 'lobbycal2press_ical_fixed_location_lon', __ ( 'geolocation 2b: use fixed longitude value for all meetings (eg 4.373942)', 'lobbycal2press' ), 'lobbycal2press_ical_fixed_location_longitude_render', 'pluginPage', 'lobbycal2press_pluginPage_section_ical' );
}

function lobbycal2press_ical_calendar_name_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_calendar_name'] == NULL) {
		$ical_calendar_name = 'LobbyCalendar for ';
	} else {
		$ical_calendar_name = $options['lobbycal2press_ical_calendar_name'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_calendar_name]'
	size="60"
	value='<?php echo $ical_calendar_name;  ?>'>
<?php }

function lobbycal2press_ical_calendar_description_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_calendar_description'] == NULL) {
		$ical_calendar_description = 'This calendar shows information about meetings held with lobbyists and interest representatives such as civil society organisations.';
	} else {
		$ical_calendar_description = $options['lobbycal2press_ical_calendar_description'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_calendar_description]'
	size="60"
	value='<?php echo $ical_calendar_description;  ?>'>
<?php }

function lobbycal2press_ical_filename_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_filename'] == NULL) {
		$ical_filename = 'lobbycal2press.ics';
	} else {
		$ical_filename = $options['lobbycal2press_ical_filename'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_filename]'
	size="60"
	value='<?php echo $ical_filename;  ?>'>
<?php }

function lobbycal2press_ical_title_prefix_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_title_prefix'] == NULL) {
		$ical_title_prefix = 'GreensEP LobbyCal:';
	} else {
		$ical_title_prefix = $options['lobbycal2press_ical_title_prefix'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_title_prefix]'
	size="60"
	value='<?php echo $ical_title_prefix;  ?>'>
<?php }

function lobbycal2press_ical_static_content_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_static_content'] == NULL) {
		$ical_static_content = '';
	} else {
		$ical_static_content = $options['lobbycal2press_ical_static_content'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_static_content]'
	size="60"
	value='<?php echo $ical_static_content;  ?>'>
<?php }

function lobbycal2press_ical_static_url_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_static_url'] == NULL) {
		$ical_static_url = '';
	} else {
		$ical_static_url = $options['lobbycal2press_ical_static_url'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_static_url]'
	size="60"
	value='<?php echo $ical_static_url;  ?>'>
<?php }

function lobbycal2press_ical_fixed_location_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_fixed_location'] == NULL) {
		$fixed_location = '';
	} else {
		$fixed_location = $options['lobbycal2press_ical_fixed_location'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_fixed_location]'
	size="60"
	value='<?php echo $fixed_location;  ?>'>
<?php }

function lobbycal2press_ical_fixed_location_latitude_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_fixed_location_latitude'] == NULL) {
		$fixed_location_latitude = '';
	} else {
		$fixed_location_latitude = $options['lobbycal2press_ical_fixed_location_latitude'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_fixed_location_latitude]'
	size="60"
	value='<?php echo $fixed_location_latitude;  ?>'>
<?php }

function lobbycal2press_ical_fixed_location_longitude_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	if ($options['lobbycal2press_ical_fixed_location_longitude'] == NULL) {
		$fixed_location_longitude = '';
	} else {
		$fixed_location_longitude = $options['lobbycal2press_ical_fixed_location_longitude'];
	}	
	?>
	<input type='text'
	name='lobbycal2press_settings[lobbycal2press_ical_fixed_location_longitude]'
	size="60"
	value='<?php echo $fixed_location_longitude;  ?>'>
<?php }

function lobbycal2press_text_field_apiURL_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='text'
	name='lobbycal2press_settings[lobbycal2press_text_field_apiURL]'
	size="60"
	value='<?php echo $options['lobbycal2press_text_field_apiURL']; ?>'>
<?php
}
function lobbycal2press_textarea_field_example_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?><textarea id="textarea_example"
	name="lobbycal2press_settings[lobbycal2press_textarea_field_example]"
	rows="14" cols="50">&lt;p&gt;&lta href="<?php echo plugin_dir_url(__FILE__) . 'ical.php' ;?>" target="_blank"&gt;Subscribe to LobbyCal via iCal&lt;/a&gt; (copy link and use it within Thunderbird, Outlook, Google Calendar etc. to automatically receive infos about new meetings in your calendar - &lt;a href="https://www.webtermine.at/abo/abo-ical/" target="_blank"&gt;tutorials in German&lt;/a&gt;)&lt;/p&gt;
&lt;table id=&quot;lobbycal&quot; aria-describedby=&quot;lobbycal_info&quot;&gt;
	&lt;thead&gt;
		&lt;tr&gt;
			&lt;th&gt;Date&lt;/th&gt;
			&lt;th&gt;End&lt;/th&gt;
			&lt;th&gt;FirstName&lt;/th&gt;
			&lt;th&gt;LastName&lt;/th&gt;
			&lt;th&gt;Partners&lt;/th&gt;
			&lt;th&gt;Title&lt;/th&gt;
			&lt;th&gt;Tags&lt;/th&gt;
		&lt;/tr&gt;
	&lt;/thead&gt;
&lt;/table&gt; 	
</textarea>
<?php
}
function lobbycal2press_text_field_max_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='text'
	name='lobbycal2press_settings[lobbycal2press_text_field_max]' size="60"
	value='<?php echo $options['lobbycal2press_text_field_max']; ?>'>
<?php
}
function lobbycal2press_text_field_perPage_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='text'
	name='lobbycal2press_settings[lobbycal2press_text_field_perPage]'
	size="60"
	value='<?php echo $options['lobbycal2press_text_field_perPage'];  ?>'>
<?php
}
function lobbycal2press_checkbox_field_start_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='checkbox'
	name='lobbycal2press_settings[lobbycal2press_checkbox_field_start]'
	<?php checked( $options['lobbycal2press_checkbox_field_start'], 1 ); ?>
	value='1'>
<?php
}
function lobbycal2press_checkbox_field_end_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='checkbox'
	name='lobbycal2press_settings[lobbycal2press_checkbox_field_end]'
	<?php checked( $options['lobbycal2press_checkbox_field_end'], 1 ); ?>
	value='1'>
<?php
}
function lobbycal2press_text_field_dateFormat_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='text'
	name='lobbycal2press_settings[lobbycal2press_text_field_dateFormat]'
	size="60"
	value='<?php echo $options['lobbycal2press_text_field_dateFormat'];  ?>'>
<?php
}
function lobbycal2press_checkbox_field_title_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='checkbox'
	name='lobbycal2press_settings[lobbycal2press_checkbox_field_title]'
	<?php checked( $options['lobbycal2press_checkbox_field_title'], 1 ); ?>
	value='1'>
<?php
}
function lobbycal2press_checkbox_field_partner_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='checkbox'
	name='lobbycal2press_settings[lobbycal2press_checkbox_field_partner]'
	<?php checked( $options['lobbycal2press_checkbox_field_partner'], 1 ); ?>
	value='1'>
<?php
}
function lobbycal2press_checkbox_field_firstname_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='checkbox'
	name='lobbycal2press_settings[lobbycal2press_checkbox_field_firstname]'
	<?php checked( $options['lobbycal2press_checkbox_field_firstname'], 1 ); ?>
	value='1'>
<?php
}
function lobbycal2press_checkbox_field_lastname_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='checkbox'
	name='lobbycal2press_settings[lobbycal2press_checkbox_field_lastname]'
	<?php checked( $options['lobbycal2press_checkbox_field_lastname'], 1 ); ?>
	value='1'>
<?php
}
function lobbycal2press_checkbox_field_tags_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='checkbox'
	name='lobbycal2press_settings[lobbycal2press_checkbox_field_tags]'
	<?php checked( $options['lobbycal2press_checkbox_field_tags'], 1 ); ?>
	value='1'>
<?php
}
function lobbycal2press_checkbox_field_tagsTitle_render() {
	$options = get_option ( 'lobbycal2press_settings' );
	?>
<input type='checkbox'
	name='lobbycal2press_settings[lobbycal2press_checkbox_field_tagsTitle]'
	<?php checked( $options['lobbycal2press_checkbox_field_tagsTitle'], 1 ); ?>
	value='1'>
<?php
}
function lobbycal2press_settings_section_callback() {
	echo __ ( 'The URL and at least one field are mandatory for the plugin to work. <br/> Make sure to use the https protocol here if your websites are accessed via https themselves.<br/> The default sorting of meetings is latest first.', 'lobbycal2press' );
}
function lobbycal2press_settings_section_ical_callback() {
	echo __ ( 'Each calendar also automatically generates an iCal feed which can be used to automatically integrate the meetings into calendar programs like Outlook or Google Calendar for example. Some supported iCal metadata information is not available (yet?) from the API - anyway those info can be set globally below for all events. For tutorials on how to integrate an iCal feed with popular calendar clients, please have a look at <a href="https://www.webtermine.at/abo/abo-ical/" target="_blank">https://www.webtermine.at/abo/abo-ical/</a> (German)', 'lobbycal2press' );
}
function lobbycal2press_options_page() {
	?>
<form action='options.php' method='post'>

	<h2>lobbycal2press</h2>
		
		<?php
	settings_fields ( 'pluginPage' );
	do_settings_sections ( 'pluginPage' );
	submit_button ();
	?>
		
	</form>
<?php
}

?>