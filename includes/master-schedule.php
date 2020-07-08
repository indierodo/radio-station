<?php

/*
 * Master Show schedule
 * Author: Nikki Blight
 * @Since: 2.1.1
 */

add_shortcode( 'master-schedule', 'radio_station_master_schedule' );
function radio_station_master_schedule( $atts ) {

	// --- make attributes backward compatible ---
	// 2.3.0: convert old list attribute to view
	if ( !isset( $atts['view'] ) && isset( $atts['list'] ) ) {
		if ( 1 === (int) $atts['list'] ) {
			$atts['list'] = 'list';
		}
		$atts['view'] = $atts['list'];
		unset( $atts['list'] );
	}
	// 2.3.0: convert show_djs attribute to show_hosts
	if ( !isset( $atts['show_hosts'] ) && isset( $atts['show_djs'] ) ) {
		$atts['show_hosts'] = $atts['show_djs'];
		unset( $atts['show_djs'] );
	}
	// 2.3.0: convert display_show_time attribute to show_times
	if ( !isset( $atts['show_times'] ) && isset( $atts['display_show_time'] ) ) {
		$atts['show_times'] = $atts['display_show_time'];
		unset( $atts['display_show_time'] );
	}
	// 2.3.0: convert single_day attribute to days
	if ( !isset( $atts['days'] ) && isset( $atts['single_day'] ) ) {
		$atts['days'] = $atts['single_day'];
		unset( $atts['single_day'] );
	}

	// --- get default clock display setting ---
	$clock = radio_station_get_setting( 'schedule_clock' );

	// --- merge shortcode attributes with defaults ---
	// 2.3.0: added show_desc (default off)
	// 2.3.0: added show_hosts (alias of show_djs)
	// 2.3.0: added show_file attribute (default off)
	// 2.3.0: added show_encore attribute (default on)
	// 2.3.0: added display clock attribute (default on)
	// 2.3.0: added display selector attribute (default on)
	// 2.3.0: added link_hosts attribute (default off)
	// 2.3.0: set default time format according to plugin setting
	// 2.3.0: set default table display to new table formatting
	// 2.3.2: added start_day attribute (for use width days)
	// 2.3.2: added display_day, display_date and display_month attributes
	$time_format = (int) radio_station_get_setting( 'clock_time_format' );
	$defaults = array(

		// --- control display options ---
		'selector'          => 1,
		'clock'             => $clock,
		'timezone'			=> 1,
	
		// --- schedule display options ---
		'time'              => $time_format,
		'show_times'		=> 1,
		'show_link'         => 1,
		'view'              => 'table',
		'days'				=> false,
		'start_day'			=> false,
		'display_day'		=> 'short',
		'display_date'		=> 'jS',
		'display_month'		=> 'short',
		'divheight'         => 45,

		// --- converted and deprecated ---
		// 'list'              => 0, 
		// 'show_djs'          => 0,
		// 'display_show_time' => 1,

		// --- show display options ---
		'show_image'        => 0,
		'show_desc'			=> 0,
		'show_hosts'        => 0,
		'link_hosts'        => 0,
		'show_genres'       => 0,
		'show_encore'       => 1,
		'show_file'         => 0,
	);
	// 2.3.0: change some defaults for tabbed and list view
	// 2.3.2: check for comma separated view list
	if ( isset( $atts['view'] ) ) {
		// 2.3.2: view value to lowercase to be case insensitive
		$atts['view'] = strtolower( $atts['view'] );
		$views = explode( ',', $atts['view'] );
		if ( ( 'tabs' == $atts['view'] ) || in_array( 'tabs', $views ) ) {
			// 2.3.2: add show descriptions default for tabbed view
			// 2.3.2: add display_ and display_date attributes
			$defaults['show_image'] = 1;
			$defaults['show_hosts'] = 1;
			$defaults['show_genres'] = 1;
			$defaults['show_desc'] = 1;
			$defaults['display_day'] = 'full';
			$defaults['display_date'] = false;
		} elseif ( ( 'list' == $atts['view'] ) || in_array( 'list', $views ) ) {
			// 2.3.2: add display date attribute
			$defaults['show_genres'] = 1;
			$defaults['display_date'] = false;
		}
	}

	// --- merge attributes with defaults ---
	$atts = shortcode_atts( $defaults, $atts, 'master-schedule' );
		
	// --- enqueue schedule stylesheet ---
	// 2.3.0: use abstracted method for enqueueing widget styles
	radio_station_enqueue_style( 'schedule' );

	// --- set initial empty output string ---
	$output = '';

	// --- disable clock if feature is not present ---
	// (temporarily while clock is in development)
	if ( !function_exists( 'radio_station_clock_shortcode' ) ) {
		$atts['clock'] = 0;
	}

	// --- table for selector and clock  ---
	// 2.3.0: moved out from templates to apply to all views
	// 2.3.2: moved shortcode calls inside and added filters
	$output .= '<div id="master-schedule-controls-wrapper">';

		$controls = array();
		
		// --- display radio clock or timezone (or neither)
		if ( $atts['clock'] ) {

			// --- radio clock ---
			$controls['clock'] = '<div id="master-schedule-clock-wrapper">';
			$clock_atts = apply_filters( 'radio_station_schedule_clock', array(), $atts );
			$controls['clock'] .= radio_station_clock_shortcode( $clock_atts );
			$controls['clock'] .= '</div>';

		} elseif ( $atts['timezone'] ) {

			// --- radio timezone ---
			$controls['timezone'] = '<div id="master-schedule-timezone-wrapper">';
			$timezone_atts = apply_filters( 'radio_station_schedule_clock', array(), $atts );
			$controls['timezone'] .= radio_station_timezone_shortcode( $timezone_atts );
			$controls['timezone'] .= '</div>';

		}

		// --- genre selector ---
		if ( $atts['selector'] ) {
			$controls['selector'] = '<div id="master-schedule-selector-wrapper">';
			$controls['selector'] .= radio_station_master_schedule_selector();
			$controls['selector'] .= '</div>';
		}

		// 2.3.1: add filters for control order
		$control_order = array( 'clock', 'timezone', 'selector' );
		$control_order = apply_filters( 'radio_station_schedule_control_order', $control_order, $atts );
		
		// 2.3.1: add filter for controls HTML
		$controls = apply_filters( 'radio_station_schedule_controls', $controls, $atts );

		// --- add ordered controls to output ---		
		if ( is_array( $control_order ) && ( count( $control_order ) > 0 ) ) {
			foreach ( $control_order as $control ) {
				if ( isset( $controls[$control] ) && ( '' != $control ) ) {
					$output .= $controls[$control];
				}
			}
		}
	
	$output .= '<br></div><br>';

	// --- schedule display override ---
	// 2.3.1: add full schedule override filter
	$override = apply_filters( 'radio_station_schedule_override', '', $atts );
	if ( ( '' != $override ) && strstr( $override, '<!-- OVERRIDE -->' ) ) {
		$override = str_replace( '<!-- OVERRIDE -->', '', $override );
		return $output . $override;
	}

	// -------------------------
	// New Master Schedule Views
	// -------------------------

	// --- load master schedule template ---
	// 2.2.7: added tabbed master schedule template
	// 2.3.0: use new data model for table and tabs view
	// 2.3.0: check for user theme templates
	if ( 'table' == $atts['view'] ) {
		add_action( 'wp_footer', 'radio_station_master_schedule_table_js' );
		$template = radio_station_get_template( 'file', 'master-schedule-table.php' );
		require $template;

		$output = apply_filters( 'master_schedule_table_view', $output, $atts );
		return $output;
	} elseif ( 'tabs' == $atts['view'] ) {
		// 2.2.7: add tabbed view javascript to footer
		add_action( 'wp_footer', 'radio_station_master_schedule_tabs_js' );
		$template = radio_station_get_template( 'file', 'master-schedule-tabs.php' );
		require $template;

		$output = apply_filters( 'master_schedule_tabs_view', $output, $atts );
		return $output;
	} elseif ( 'list' == $atts['view'] ) {
		add_action( 'wp_footer', 'radio_station_master_schedule_list_js' );
		$template = radio_station_get_template( 'file', 'master-schedule-list.php' );
		require $template;

		$output = apply_filters( 'master_schedule_list_view', $output, $atts );
		return $output;
	}

	// ----------------------
	// Legacy Master Schedule
	// ----------------------

	global $wpdb;

	// 2.3.0: remove unused default DJ name option
	// $default_dj = get_option( 'dj_default_name' );

	// --- check to see what day of the week we need to start on ---
	$start_of_week = get_option( 'start_of_week' );
	$days_of_the_week = array(
		'Sunday'    => array(),
		'Monday'    => array(),
		'Tuesday'   => array(),
		'Wednesday' => array(),
		'Thursday'  => array(),
		'Friday'    => array(),
		'Saturday'  => array(),
	);
	$week_start = array_slice( $days_of_the_week, $start_of_week );
	foreach ( $days_of_the_week as $i => $weekday ) {
		if ( $start_of_week > 0 ) {
			$add = $days_of_the_week[$i];
			unset( $days_of_the_week[$i] );
			$days_of_the_week[$i] = $add;
		}
		$start_of_week --;
	}

	// --- create the master_list array based on the start of the week ---
	$master_list = array();
	for ( $i = 0; $i < 24; $i ++ ) {
		$master_list[$i] = $days_of_the_week;
	}

	// --- get the show schedules, excluding shows marked as inactive ---
	$show_shifts = $wpdb->get_results(
		"SELECT meta.post_id, meta.meta_value
		FROM {$wpdb->postmeta} AS meta
		JOIN {$wpdb->postmeta} AS active
			ON meta.post_id = active.post_id
		JOIN {$wpdb->posts} as posts
			ON posts.ID = meta.post_id
		WHERE meta.meta_key = 'show_sched' AND
			posts.post_status = 'publish' AND
			(
				active.meta_key = 'show_active' AND
				active.meta_value = 'on'
			)"
	);

	// --- insert scheduled shifts into the master list ---
	foreach ( $show_shifts as $shift ) {
		$shift->meta_value = maybe_unserialize( $shift->meta_value );

		// if a show is not scheduled yet, unserialize will return false... fix that.
		if ( !is_array( $shift->meta_value ) ) {
			$shift->meta_value = array();
		}

		foreach ( $shift->meta_value as $time ) {

			// 2.3.0: added check for show disabled switch
			if ( !isset( $time['disabled'] ) || ( 'yes' == $time['disabled'] ) ) {

				// --- switch to 24-hour time ---
				if ( 'pm' === $time['start_meridian'] && 12 !== (int) $time['start_hour'] ) {
					$time['start_hour'] += 12;
				}
				if ( 'am' === $time['start_meridian'] && 12 === (int) $time['start_hour'] ) {
					$time['start_hour'] = 0;
				}

				if ( 'pm' === $time['end_meridian'] && 12 !== (int) $time['end_hour'] ) {
					$time['end_hour'] += 12;
				}
				if ( 'am' === $time['end_meridian'] && 12 === (int) $time['end_hour'] ) {
					$time['end_hour'] = 0;
				}

				// --- check if we are spanning multiple days ---
				$time['multi-day'] = 0;
				if ( $time['start_hour'] > $time['end_hour'] || $time['start_hour'] === $time['end_hour'] ) {
					$time['multi-day'] = 1;
				}

				$master_list[$time['start_hour']][$time['day']][$time['start_min']] = array(
					'id'   => $shift->post_id,
					'time' => $time,
				);
			}
		}
	}

	// --- sort the array by time ---
	foreach ( $master_list as $hour => $days ) {
		foreach ( $days as $day => $min ) {
			ksort( $min );
			$master_list[$hour][$day] = $min;

			// we need to take into account shows that start late at night and end the following day
			foreach ( $min as $i => $time ) {

				// if it ends at midnight, we don't need to worry about carry-over
				if ( 0 === (int) $time['time']['end_hour'] && 0 === (int) $time['time']['end_min'] ) {
					continue;
				}

				// if it ends after midnight, fix it
				// if it starts at night and ends in the morning, end hour is on the following day
				if ( ( 'pm' === $time['time']['start_meridian'] && 'am' === $time['time']['end_meridian'] ) ||
				     // if the start and end times are identical, assume the end time is the following day
				     ( $time['time']['start_hour'] . $time['time']['start_min'] . $time['time']['start_meridian'] === $time['time']['end_hour'] . $time['time']['end_min'] . $time['time']['end_meridian'] ) ||
				     // if the start hour is in the morning, and greater than the end hour, assume end hour is the following day
				     ( 'am' === $time['time']['start_meridian'] && $time['time']['start_hour'] > $time['time']['end_hour'] )
				) {

					if ( 12 === (int) $atts['time'] ) {
						$time['time']['real_start'] = ( $time['time']['start_hour'] - 12 ) . ':' . $time['time']['start_min'];
					} else {
						$pad_hour = '';
						if ( $time['time']['start_hour'] < 10 ) {
							$pad_hour = '0';
						}
						$time['time']['real_start'] = $pad_hour . $time['time']['start_hour'] . ':' . $time['time']['start_min'];
					}
					$time['time']['rollover'] = 1;

					// 2.3.0: use new get next day function
					$nextday = radio_station_get_next_day( $day );

					$master_list[0][$nextday]['00'] = $time;

				}
			}
		}
	}

	// --- check for schedule overrides ---
	// ? TODO - check/include schedule overrides in legacy template views
	// $overrides = radio_station_master_get_overrides( true );

	// --- include the specified master schedule output template ---
	// 2.3.0: check for user theme templates
	if ( 'divs' == $atts['view'] ) {
		$output = ''; // no selector / clock support yet
		$template = radio_station_get_template( 'file', 'master-schedule-div.php' );
		require $template;
	} elseif ( 'legacy' == $atts['view'] ) {
		$template = radio_station_get_template( 'file', 'master-schedule-legacy.php' );
		require $template;
	}

	return $output;
}

// ----------------------
// Show  / Genre Selector
// ----------------------
function radio_station_master_schedule_selector() {

	// --- get genres ---
	$args = array(
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);
	$genres = get_terms( RADIO_STATION_GENRES_SLUG, $args );
	// 2.3.2: bug out if there are no genre terms
	if ( !$genres || !is_array( $genres ) ) {
		return '';
	}

	// --- open genre highlighter div ---
	$html = '<div id="master-genre-list">';
	$html .= '<span class="heading">' . esc_html( __( 'Genres', 'radio-station' ) ) . ': </span>';

	// --- genre highlight links ---
	// 2.3.0: fix by imploding with genre link spacer
	$genre_links = array();
	foreach ( $genres as $i => $genre ) {
		$slug = sanitize_title_with_dashes( $genre->name );
		$javascript = 'javascript:radio_genre_highlight(\'' . $slug . '\')';
		$title = __( 'Click to toggle Highlight of Shows with this Genre.', 'radio-station' );
		$genre_link = '<a id="genre-highlight-' . esc_attr( $slug ) . '" class="genre-highlight" href="' . $javascript . '" title="' . esc_attr( $title ) . '">';
		$genre_link .= esc_html( $genre->name ) . '</a>';
		$genre_links[] = $genre_link;
	}
	$html .= implode( ' | ', $genre_links );

	$html .= '</div>';

	// --- genre highlighter script ---
	// 2.3.0: improved to highlight / unhighlight multiple genres
	// 2.3.0: improved to work with table, tabs or list view
	$js = "var highlighted_genres = new Array();
	function radio_genre_highlight(genre) {
		if (jQuery('#genre-highlight-'+genre).hasClass('highlighted')) {
			jQuery('#genre-highlight-'+genre).removeClass('highlighted');

			jQuery('.master-show-entry').each(function() {jQuery(this).removeClass('highlighted');});
			jQuery('.master-schedule-tabs-show').each(function() {jQuery(this).removeClass('highlighted');});
			jQuery('.master-list-day-item').each(function() {jQuery(this).removeClass('highlighted');});

			j = 0; new_genre_highlights = new Array();
			for (i = 0; i < highlighted_genres.length; i++) {
				if (highlighted_genres[i] != genre) {
					jQuery('.'+highlighted_genres[i]).addClass('highlighted');
					new_genre_highlights[j] = highlighted_genres[i]; j++;
				}
			}
			highlighted_genres = new_genre_highlights;

		} else {
			jQuery('#genre-highlight-'+genre).addClass('highlighted');
			highlighted_genres[highlighted_genres.length] = genre;
			jQuery('.'+genre).each(function () {
				jQuery(this).addClass('highlighted');
			});
		}
	}";

	// --- enqueue script ---
	// 2.3.0: add script code to existing handle
	wp_add_inline_script( 'radio-station', $js );

	return $html;
}

// ---------------------
// Table View Javascript
// ---------------------
// 2.3.0: added for table responsiveness
function radio_station_master_schedule_table_js() {

	// 2.3.2: added current show highlighting cycle
	// 2.3.2: fix to currenthour substr
	$js = "/* Initialize Table */
	jQuery(document).ready(function() {
		radio_table_responsive();
		radio_times_highlight();
		setTimeout(radio_times_highlight, 60000);
	});
	jQuery(window).resize(function () {
		radio_resize_debounce(radio_table_responsive, 500, 'scheduletable');
	});

	/* Current Time Highlighting */
	function radio_times_highlight() {
		radio.current_time = Math.floor( (new Date()).getTime() / 1000 );
		radio.offset_time = radio.current_time + radio.timezone_offset;
		if (radio.debug) {console.log(radio.current_time+' - '+radio.offset_time);}
		if (radio.timezone_adjusted) {radio.offset_time = radio.current_time;}
		jQuery('.master-program-day').each(function() {
			start = parseInt(jQuery(this).find('.rs-start-time').attr('data'));
			if (start < radio.offset_time) {
				end = parseInt(jQuery(this).find('.rs-end-time').attr('data'));
				if (end > radio.offset_time) {jQuery(this).addClass('current-day');}
				else {jQuery(this).removeClass('current-day');}
			} else {jQuery(this).removeClass('current-day');}		
		});
		jQuery('.master-program-hour').each(function() {
			hour = parseInt(jQuery(this).find('.master-program-server-hour').attr('data'));
			offset_time = radio.current_time + radio.timezone_offset;
			current = new Date(offset_time * 1000).toISOString();
			currenthour = current.substr(11, 2);
    		if (currenthour.substr(0,1) == '0') {currenthour = currenthour.substr(1,1);}
			if (hour == currenthour) {jQuery(this).addClass('current-hour');}
			else {jQuery(this).removeClass('current-hour');}
		});
		jQuery('.master-show-entry').each(function() {
			start = parseInt(jQuery(this).find('.rs-start-time').attr('data'));
			if (radio.debug) {console.log(start);}
			if (start < radio.offset_time) {
				end = parseInt(jQuery(this).find('.rs-end-time').attr('data'));
				if (end > radio.offset_time) {
					jQuery(this).addClass('nowplaying');
					if (radio.debug) {console.log('^^^^^^^');}
				} else {jQuery(this).removeClass('nowplaying');}
			} else {jQuery(this).removeClass('nowplaying');}
		});
	}

	/* Make Table Responsive */
	function radio_table_responsive() {
		tablewidth = jQuery('#master-program-schedule').width();
		daycolumns = Math.floor(tablewidth / 100) - 1;
		if (daycolumns < 1) {daycolumns = 1;} else if (daycolumns > 7) {daycolumns = 7;}
		for (i = 0; i < 7; i++) {
			if (jQuery('.master-program-day.day-'+i).hasClass('selected-day')) {selected = i;}
		}
		startcolumn = selected;
		if ((selected + daycolumns) > 6) {startcolumn = 7 - daycolumns;}

		columns = 0; firstcolumn = -1;
		for (i = 0; i < 7; i++) {
			jQuery('.master-program-day.day-'+i).removeClass('first-column').removeClass('last-column');
			if ( ((i + 1) > startcolumn) && (columns < daycolumns) ) {
				jQuery('.master-program-day.day-'+i+', .show-info.day-'+i).show();
				if (firstcolumn < 0) { 
					if (i > 0) {jQuery('.master-program-day.day-'+i).addClass('first-column');}
					firstcolumn = 0;
				}
				lastcolumn = i; columns++;
			} else {
				jQuery('.master-program-day.day-'+i+', .show-info.day-'+i).hide();
			}
		}
		if (lastcolumn < 6) {jQuery('.master-program-day.day-'+lastcolumn).addClass('last-column');}

		if (radio.debug) {
			console.log('Day Columns:' +daycolumns);
			console.log('Selected Column: '+selected);
			console.log('Start Column: '+startcolumn);
			console.log('Last Column: '+lastcolumn);
		}
	}
	
	/* Shift Day Left /  Right */
	function radio_shift_day(leftright) {
		tablewidth = jQuery('#master-program-schedule').width();
		daycolumns = Math.floor(tablewidth / 100) - 1;
		if (daycolumns < 1) {daycolumns = 1;} else if (daycolumns > 7) {daycolumns = 7;}
		for (i = 0; i < 7; i++) {
			if (jQuery('.master-program-day.day-'+i).hasClass('selected-day')) {selected = i;}
		}
		if ((selected + daycolumns) > 6) {selected = 7 - daycolumns;}
		if (leftright == 'left') {selected--;} else if (leftright == 'right') {selected++;} 
		for (i = 0; i < 7; i++) {
			if (i == selected) {jQuery('.master-program-day.day-'+i).addClass('selected-day');}
			else {jQuery('.master-program-day.day-'+i).removeClass('selected-day');}
		}
		radio_table_responsive();
		return false;
	}";

	// --- enqueue script inline ---
	wp_add_inline_script( 'radio-station', $js );
}

// ----------------------
// Tabbed View Javascript
// ----------------------
// 2.2.7: added for tabbed schedule view
function radio_station_master_schedule_tabs_js() {

	// --- tab switching function ---
	// 2.3.2: added fallback if current day is not viewed
	// TODO: check current server time for onload display
	/* date = new Date(); dayweek = date.getDay(); day = radio_get_weekday(dayweek);
	if (jQuery('#master-schedule-tabs-header-'+day).length) {
		id = jQuery('.master-schedule-tabs-day.selected-day').first().attr('id');
		day = id.replace('master-schedule-tabs-header-','');
		jQuery('#master-schedule-tabs-header-'+day).addClass('active-day-tab');
		jQuery('#master-schedule-tabs-day-'+day).addClass('active-day-panel');
	} else {
		jQuery('.master-schedule-tabs-day').first().addClass('active-day-tab');
		jQuery('.master-schedule-tabs-panel').first().addClass('active-day-panel');
	} */	
	$js = "jQuery(document).ready(function() {
		jQuery('.master-schedule-tabs-day-name').bind('click', function (event) {
			headerID = jQuery(event.target).closest('li').attr('id');
			panelID = headerID.replace('header', 'day');
			jQuery('.master-schedule-tabs-day').removeClass('active-day-tab');
			jQuery('#'+headerID).addClass('active-day-tab');
			jQuery('.master-schedule-tabs-panel').removeClass('active-day-panel');
			jQuery('#'+panelID).addClass('active-day-panel');
		});
	});";

	// --- tabbed view responsiveness ---
	// 2.3.0: added for tabbed responsiveness
	// 2.3.2: display selected day message if outside view
	$js .= "/* Initialize Tabs */
	jQuery(document).ready(function() {
		radio.schedule_tabinit = false;
		radio_tabs_responsive();
		radio_show_highlight();
		setTimeout(radio_show_highlight, 60000);
	});
	jQuery(window).resize(function () {
		radio_resize_debounce(radio_tabs_responsive, 500, 'scheduletabs');
	});

	/* Set Day Tab on Load */
	function radio_set_active_tab(day) {
		if (radio.schedule_tabinit) {return;}
		jQuery('.master-schedule-tabs-day').removeClass('active-day-tab');
		jQuery('.master-schedule-tabs-panel').removeClass('active-day-panel');
		jQuery('#master-schedule-tabs-header-'+day).addClass('active-day-tab');
		jQuery('#master-schedule-tabs-day-'+day).addClass('active-day-panel');
		radio.schedule_tabinit = true;
	}

	/* Current Show Highlighting */
	function radio_show_highlight() {
		radio.current_time = Math.floor( (new Date()).getTime() / 1000 );
		radio.offset_time = radio.current_time + radio.timezone_offset;
		if (radio.debug) {console.log(radio.current_time+' - '+radio.offset_time);}
		if (radio.timezone_adjusted) {radio.offset_time = radio.current_time;}
		jQuery('.master-schedule-tabs-day').each(function() {
			start = parseInt(jQuery(this).find('.rs-start-time').attr('data'));
			if (start < radio.offset_time) {
				end = parseInt(jQuery(this).find('.rs-end-time').attr('data'));
				if (end > radio.offset_time) {
					jQuery(this).addClass('current-day');
					day = jQuery(this).attr('id').replace('master-schedule-tabs-header-', '');
					radio_set_active_tab(day);
				} else {jQuery(this).removeClass('current-day');}
			} else {jQuery(this).removeClass('current-day');}		
		});		
		jQuery('.master-schedule-tabs-show').each(function() {
			start = parseInt(jQuery(this).find('.rs-start-time').attr('data'));
			if (radio.debug) {console.log(start);}
			if (start < radio.offset_time) {
				if (radio.debug) {console.log('^^^^^^^');}
				end = parseInt(jQuery(this).find('.rs-end-time').attr('data'));
				if (end > radio.offset_time) {jQuery(this).addClass('nowplaying');}
				else {jQuery(this).removeClass('nowplaying');}
			} else {jQuery(this).removeClass('nowplaying');}
		});
	}
	
	/* Make Tabs Responsive */
	function radio_tabs_responsive() {
	
		jQuery('.master-schedule-tabs-selected').hide();
		tabswidth = jQuery('#master-schedule-tabs').width();
		daycolumns = Math.floor(tabswidth / 125);
		if (daycolumns < 1) {daycolumns = 1;} else if (daycolumns > 7) {daycolumns = 7;}
		fallback = false; selected = false;
		for (i = 0; i < 7; i++) {
			if (!fallback && jQuery('.master-schedule-tabs-day.day-'+i)) {fallback = i;}
			if (jQuery('.master-schedule-tabs-day.day-'+i).hasClass('selected-day')) {selected = i;}
		}
		if (selected) {startcolumn = selected;} else {selected = startcolumn = fallback;}
		if ((selected + daycolumns) > 6) {startcolumn = 7 - daycolumns;}

		activeday = false; columns = 0; firstcolumn = -1;
		for (i = 0; i < 7; i++) {
			if (jQuery('.master-schedule-tabs-day.day-'+i).hasClass('active-day-tab')) {
				activeday = i;
			}
		}
		for (i = 0; i < 7; i++) {
			jQuery('.master-schedule-tabs-day.day-'+i).removeClass('first-tab');
			jQuery('.master-schedule-tabs-day.day-'+i).removeClass('last-tab');
			if ( ((i + 1) > startcolumn) && (columns < daycolumns) ) {
				jQuery('.master-schedule-tabs-day.day-'+i).show();
				if (firstcolumn < 0) { 
					firstcolumn = 0;
					jQuery('.master-schedule-tabs-day.day-'+i).addClass('first-tab');
				}
				lastcolumn = i; columns++;
			} else {
				jQuery('.master-schedule-tabs-day.day-'+i).hide();
			}
		}

		if (lastcolumn < 6) {
			jQuery('.master-schedule-tabs-day.day-'+lastcolumn).addClass('last-tab');
		}
		if (radio.debug) {
			console.log('Active Day: '+activeday);
			console.log('Day Columns:'+daycolumns);
			console.log('Start Column: '+startcolumn);
			console.log('Selected Column: '+selected);
			console.log('Last Column: '+lastcolumn);
		}

		/* display selected day message if outside view */
		if ( activeday && ( (activeday > lastcolumn) || (activeday < startcolumn ) ) ) {
			weekday = radio_get_weekday(activeday);
			jQuery('#master-schedule-tabs-selected-'+weekday).show();
		}		
	}
	
	/* Shift Day Left /  Right */
	function radio_shift_tab(leftright) {
		tabswidth = jQuery('#master-schedule-tabs').width();
		daycolumns = Math.floor(tabswidth / 120);
		if (daycolumns < 1) {daycolumns = 1;} else if (daycolumns > 7) {daycolumns = 7;}
		for (i = 0; i < 7; i++) {
			if (jQuery('.master-schedule-tabs-day.day-'+i).hasClass('selected-day')) {selected = i;}
		}
		if (radio.debug) {
			console.log('Tabs Width: '+tabswidth+' - Day Columns: '+daycolumns);
			console.log('Selected Day: '+selected);
		}
		if ((selected + daycolumns) > 6) {
			selected = 7 - daycolumns;
			if (radio.debug) {console.log('Adjusted Select: '+selected);}
		}
		if (leftright == 'left') {selected--;} else if (leftright == 'right') {selected++;} 
		for (i = 0; i < 7; i++) {
			if (i == selected) {jQuery('.master-schedule-tabs-day.day-'+i).addClass('selected-day');}
			else {jQuery('.master-schedule-tabs-day.day-'+i).removeClass('selected-day');}
		}
		radio_tabs_responsive();
		return false;
	}";

	// --- enqueue script inline ---
	// 2.3.0: enqueue instead of echoing
	wp_add_inline_script( 'radio-station', $js );
}

// --------------------
// List View Javascript
// --------------------
// 2.3.2: added for list schedule view
function radio_station_master_schedule_list_js() {

	// --- list view javascript ---
	$js = "/* Initialize List */
	jQuery(document).ready(function() {
		radio_list_highlight();
		setTimeout(radio_list_highlight, 60000);
	});
	/* Current Show Highlighting */
	function radio_list_highlight() {
		radio.current_time = Math.floor( (new Date()).getTime() / 1000 );
		radio.offset_time = radio.current_time + radio.timezone_offset;
		if (radio.timezone_adjusted) {radio.offset_time = radio.current_time;}
		jQuery('.master-list-day').each(function() {
			start = parseInt(jQuery(this).find('.rs-start-time').first().attr('data'));
			if (start < radio.offset_time) {
				end = parseInt(jQuery(this).find('.rs-end-time').first().attr('data'));
				if (end > radio.offset_time) {jQuery(this).addClass('current-day');}
				else {jQuery(this).removeClass('current-day');}
			} else {jQuery(this).removeClass('current-day');}		
		});		
		jQuery('.master-list-day-item').each(function() {
			start = parseInt(jQuery(this).find('.rs-start-time').attr('data'));
			if (start < radio.offset_time) {	
				end = parseInt(jQuery(this).find('.rs-end-time').attr('data'));
				if (end > radio.offset_time) {jQuery(this).addClass('nowplaying');}
				else {jQuery(this).removeClass('nowplaying');}
			} else {jQuery(this).removeClass('nowplaying');}
		});
	}";
	
	// --- enqueue script inline ---
	wp_add_inline_script( 'radio-station', $js );
}	
	