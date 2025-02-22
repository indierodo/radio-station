<?php

// ====================
// === RADIO PLAYER ===
// ====================
// === by Tony Hayes ==
// ====================

// === Radio Player ===
// - Player Output
// - Player Shortcode
// - Player AJAX Display
// - Sanitize Shortcode Values
// - Sanitize Values
// - Media Elements Interface
// === Player Scripts ===
// - Enqueue Player Javasscripts
// - Enqueue Player Script
// - Lazy Load Audio Script Fallbacks
// - Enqueue Amplitude Javascript
// - Enqueue JPlayer Javascript
// - Enqueue Howler Javascript
// * Enqueue Media Element Javascript
// - Dynamic Load Script via AJAX
// - Get Player Settings
// - User State Iframe
// - AJAX Update User State
// - Load Amplitude Function
// - Load JPlayer Function
// - Load Howler Function
// * Load Media Element Function
// - Get Default Player Script
// - Enqueue Player Styles
// - Player Control Styles
// === Standalone Compatibility ===
// - Output Script Tag
// - Output Style Tag
// - Validate Boolean
// - Escape JS
// - Escape HTML
// - Escape URL


// -------------------------
// Audio/Video Support Notes
// -------------------------
//
// Script Library Support
// ----------------------
// [Amplitude] HTML5 Support - mp3, aac ...?
// ref: https://en.wikipedia.org/wiki/HTML5_audio#Supporting_browsers
// [JPlayer] Audio: mp3, m4a - Video: m4v
// +Audio: webma, oga, wav, fla, rtmpa +Video: webmv, ogv, flv, rtmpv
// [Howler] mp3, opus, ogg, wav, aac, m4a, mp4, webm
// +mpeg, oga, caf, weba, webm, dolby, flac
// [Media Elements] Audio: mp3, wma, wav +Video: mp4, ogg, webm, wmv

// Streaming Server Support
// ------------------------
// [Icecast] Ogg (Vorbis, Theora), Opus, FLAC and WebM (VP8/VP9),
// "nonfree codecs/formats like MP4 (H. 264, MPEG4), M4A, NSV, AAC and MP3 might work,
// but we do not officially support those."
// [Shoutcast]
// ref: https://help.shoutcast.com/hc/en-us/articles/115004705393-Recommended-bitrate-and-format
// MP3 320, MP3 256, MP3 192, MP3 128, MP3 64, MP3 32
// AAC4 128, AAC4 96, AAC4 64, AAC4 32
// [Azuracast]
// OGG, MP3, AAC, OPUS .. ?
// [LibreTime] (supports Shoutcast and Icecast)

// Note: MP4 vs AAC
// ----------------
// An MPEG-4 file contains a header that includes metadata followed by "tracks" which can include
// video as well as audio data, for example, H.264 encoded Video and AAC encoded Audio.
// ADTS in contrast is a streaming format consisting of a series of frames,
// each frame having a header followed by the AAC data.


// ----------------------
// Player Constants Notes
// ----------------------
//
// --- player resource URL ---
// RADIO_PLAYER_URL - define player URL path for standalone compatible version
// (note: should have a trailing slash!) eg. to use as a WordPress mu-plugins dropin:
// define( 'RADIO_PLAYER_URL', 'https://example.com/wp-content/mu-plugins/player/');
// (then include /mu-plugins/player/radio-player.php from a file in /mu-plugins/)

// --- player script and skin ---
// RADIO_PLAYER_SCRIPT - default player script (amplitude, jplayer, howler)
// RADIO_PLAYER_FORCE_SCRIPT - force override any use of other player script
// RADIO_PLAYER_SKIN - default player skin (must match script used)
// RADIO_PLAYER_FORCE_SKIN - force override any use of other player skin

// --- player display values ---
// RADIO_PLAYER_TITLE - title of station/player
// RADIO_PLAYER_IMAGE - URL of station/player image
// RADIO_PLAYER_VOLUME - initial player volume (0 to 100)

// --- user state saving ---
// RADIO_PLAYER_AJAX_URL - destination for user saving (default: WordPress admin-ajax.php)
// RADIO_PLAYER_SAVE_INTERVAL - seconds between user state saving (default: 60)


// -----------------------
// Original JPlayer Markup
// -----------------------
/* <div id="jquery_jplayer_1" class="jp-jplayer"></div>
<div id="jp_container_1" class="jp-audio" role="application" aria-label="media player">
  <div class="jp-type-single">
	<div class="jp-gui jp-interface">
	  <div class="jp-volume-controls">
		<button class="jp-mute" role="button" tabindex="0">mute</button>
		<button class="jp-volume-max" role="button" tabindex="0">max volume</button>
		<div class="jp-volume-bar">
		  <div class="jp-volume-bar-value"></div>
		</div>
	  </div>
	  <div class="jp-controls-holder">
		<div class="jp-controls">
		  <button class="jp-play" role="button" tabindex="0">play</button>
		  <button class="jp-stop" role="button" tabindex="0">stop</button>
		</div>
		<div class="jp-progress">
		  <div class="jp-seek-bar">
			<div class="jp-play-bar"></div>
		  </div>
		</div>
		<div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
		<div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
		<div class="jp-toggles">
		  <button class="jp-repeat" role="button" tabindex="0">repeat</button>
		</div>
	  </div>
	</div>
	<div class="jp-details">
	  <div class="jp-title" aria-label="title">&nbsp;</div>
	</div>
	<div class="jp-no-solution">
	  <span>Update Required</span>
	  To play the media you will need to either update your browser to a recent version or update your <a href="https://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
	</div>
  </div>
</div> */

// -----------------------------
// Original Media Element Markup
// -----------------------------
/* <div class="mejs-container">
	<div class="mejs-inner">
		<div class="mejs-mediaelement">
			<audio class="wp-audio-shortcode">...</audio>
		</div>
		<div class="mejs-layers">
			<div class="mejs-layer">...</div>
		</div>
		<div class="mejs-controls">
			<div class="mejs-button"></div>
		</div>
		<div class="mejs-clear">...</div>
	</div>
</div> */


// --------------------
// === Radio Player ===
// --------------------

// -------------
// Player Output
// -------------
// Accepts: $args (Array)
// Array Key    | Accepts
// 'script'	| 'amplitude' (default), 'jplayer', 'howler', // 'mediaelements'
// 'layout'	| 'horizontal', 'vertical
// 'theme'      | 'light', 'dark'
// 'buttons'	| 'circular', 'rounded', 'square'
// 'skin'	| // (Media Elements: 'wordpress', 'minimal');
// 'title'	| [String]: Player/Station Title - 0 for none
// 'image'	| [URL]: Player/Station Image  (eg. Logo) - recommended size 256x256
// 'volume'	| [Integer: 0 to 100]: Initial Player Volume - default: 77
function radio_station_player_output( $args = array() ) {

	global $radio_player;

	// --- maybe debug output arguments ---
	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		echo '<span style="display:none;">Passed Radio Player Output Arguments: ';
		echo print_r( $args, true ) . '</span>';
	}

	// --- settings defaults ---
	$defaults = array(
		'url'      => '',
		'format'   => '',
		'fallback' => '',
		'fformat'  => '',
		'title'    => '',
		'image'    => '',
		'script'   => 'amplitude',
		'layout'   => 'vertical',
		'theme'    => 'light',
		'buttons'  => 'rounded',
		'volume'   => 77,
		'default'  => false,
	);

	// --- ensure all arguments are set ---
	foreach ( $defaults as $key => $value ) {
		if ( !isset( $args[$key] ) ) {
			$args[$key] = $value;
		}
	}

	// --- maybe set player instance ---
	// 2.4.0.1: fix for storing multiple instance IDs
	if ( !isset( $radio_player['instances'] ) ) {
		$radio_player['instances'] = array();
	}
	$instance = 0;
	if ( isset( $args['id'] ) && ( '' != $args['id'] ) ) {
		$id = abs( intval( $args['id'] ) ) ;
		if ( $instance > 0 ) {
			$instance = $id; 
		}
	}		
	if ( in_array( $instance, $radio_player['instances'] ) ) {
		while ( in_array( $instance, $radio_player['instances'] ) ) {
			$instance++;
		}
	}
	$radio_player['instances'][] = $instance;
	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		echo '<span style="display:none;">Player Instance: ' . $instance . ' - Instances: ' . print_r( $radio_player['instances'], true ) . '</span>';
	}

	// --- filter player output args ---
	// 2.4.0.3: added missing function_exists wrapper
	if ( function_exists( 'apply_filters' ) ) {
		$args = apply_filters( 'radio_station_player_output_args', $args, $instance );
	}

	// --- maybe debug output arguments ---
	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		echo '<span style="display:none;">Parsed Radio Player Output Arguments: ';
		echo print_r( $args, true ) . '</span>';
	}
		
	// --- set instanced container IDs ---
	$player_id = 'radio_player_' . $instance;
	$container_id = 'radio_container_' . $instance;

	// --- set Player div ---
	$classes = array( 'radio-player', 'rp-player', 'script-' . $args['script'] );
	if ( $args['default'] ) {
		$classes[] = 'default-player';
	}
	$class_list = implode( ' ', $classes );
	$html['player_open'] = '<div id="' . esc_attr( $player_id ) . '" class="' . esc_attr( $class_list ) . '"></div>' . PHP_EOL;
	
	// --- set Player container ---
	$classes = array( 'radio-container', 'rp-audio', 'rp-audio-stream' );
	$classes[] = $args['layout'];
	$classes[] = $args['theme'];
	$classes[] = $args['buttons'];
	$class_list = implode( ' ', $classes );
	$html['player_open'] .= '<div id="' . esc_attr( $container_id ) . '" class="' . esc_attr( $class_list ) . '" role="application" aria-label="media player" data-href="' . esc_url( $args['url'] ) . '" data-format="' . esc_attr( $args['format'] ) . '" data-fallback="' . esc_url( $args['fallback'] ) . '" data-fformat="' . esc_attr( $args['fformat'] ) . '">' . PHP_EOL;
	$html['player_open'] .= '	<div class="rp-type-single">' . PHP_EOL;
    	$html['player_close'] = '</div></div>' . PHP_EOL;

	// --- set interface wrapper ---
	$html['interface_open'] = '<div class="rp-gui rp-interface">' . PHP_EOL;
	$html['interface_close'] = '</div>' . PHP_EOL;

	// --- Station Info ---
	$html['station'] = '<div class="rp-station-info">' . PHP_EOL;

		// --- station logo image ---
		$image = '';
		$classes = array( 'rp-station-image' );
		if ( ( '0' != (string)$args['image'] ) && ( 0 !== $args['image'] ) && ( '' != $args['image'] ) ) {
			$image = '<img id="rp-station-default-image-' . esc_attr( $instance ) . '" class="rp-station-default-image" src="' . esc_url( $args['image'] ) . '" width="100%" height="100%" border="0" aria-label="' . esc_attr( __( 'Station Logo Image' ) ) . '">' . PHP_EOL;
			if ( function_exists( 'apply_filters' ) ) {
				// 2.4.0.3: fix atts to args in third filter argument
				$image = apply_filters( 'radio_station_player_station_image_tag', $image, $args['image'], $args, $instance );
			}
			if ( !is_string( $image ) ) {
				$image = '';
				$classes[] = 'no-image';
			}
		} else {
			$classes[] = 'no-image';
		}
		$class_list = implode( ' ', $classes );
		$html['station'] .= '	<div id="rp-station-image-' . esc_attr( $instance ) . '" class="' . esc_attr( $class_list ) . '">';
		$html['station'] .= $image . '</div>' . PHP_EOL;

		// --- station text display ---
		$html['station'] .= '	<div class="rp-station-text">';

			// --- station title ---
			$station_text_html = '		<div class="rp-station-title" aria-label="' . esc_attr( __( 'Station Name' ) ) . '">';
			if ( ( '0' != (string)$args['title'] ) && ( 0 !== $args['title'] ) && ( '' != $args['title'] ) ) {
				$station_text_html .= esc_html( $args['title'] );
			}
			$station_text_html .= '		</div>' . PHP_EOL;

			// --- station timezone / location / frequency ---
			// TODO: add timezone and/or frequency display ?
			$station_text_html .= '		<div class="rp-station-timezone"></div>' . PHP_EOL;
			$station_text_html .= '		<div class="rp-station-frequency"></div>' . PHP_EOL;
			
			$html['station'] .= $station_text_html;

		$html['station'] .= '	</div>' . PHP_EOL;

	$html['station'] .= '</div>' . PHP_EOL;

	// --- Stream Play/Pause Control ---
	$html['controls'] = '<div class="rp-controls-holder">' . PHP_EOL;
	$html['controls'] .= '	<div class="rp-controls">' . PHP_EOL;
	$html['controls'] .= '		<div class="rp-play-pause-button-bg">' . PHP_EOL;
	$html['controls'] .= '			<div class="rp-play-pause-button" role="button" aria-label="' . esc_attr( __( 'Play Radio Stream' ) ) . '"></div>' . PHP_EOL;
	$html['controls'] .= '		</div>' . PHP_EOL;
	// $html['controls'] .= '		<button class="rp-stop" role="button" tabindex="0">' . esc_html( __( 'Stop', 'radio-player' ) ) . '</button>' . PHP_EOL;
	$html['controls'] .= '	</div>' . PHP_EOL;
	$html['controls'] .= '</div>' . PHP_EOL;

	// --- Volume Controls ---
	$html['volume'] = '<div class="rp-volume-controls">' . PHP_EOL;

		// --- Volume Mute ---
		// amplitude-mute
		$html['volume'] .= '	<button class="rp-mute" role="button" tabindex="0">' . esc_html( __( 'Mute', 'radio-player' ) ) . '</button>' . PHP_EOL;

		// --- Volume Decrease ---
		$html['volume'] .= '	<button class="rp-volume-down" role="button" area-label="' . esc_attr( __( 'Volume Down' ) ) . '">-</button>' . PHP_EOL;

		// --- Custom Range volume slider ---
		$html['volume'] .= '	<div class="rp-volume-slider-container">' . PHP_EOL;
		$html['volume'] .= '		<div class="rp-volume-slider-bg" style="width: 0; border: none;"></div>' . PHP_EOL;
		$html['volume'] .= '		<input type="range" class="rp-volume-slider" value="' . esc_attr( $args['volume'] ) . '" max="100" min="0" aria-label="' . esc_attr( __( 'Volume Slider' ) ) . '">' . PHP_EOL;
		$html['volume'] .= '		<div class="rp-volume-thumb"></div>' . PHP_EOL;
		$html['volume'] .= '	</div>' . PHP_EOL;

		// --- jPlayer/Howler volume bar slider ---
		// $html['volume'] .= '	<div class="rp-volume-bar volume-bar"';
		// $html['volume'] .= '>' . PHP_EOL;
		// $html['volume'] .= '		<div class="rp-volume-bar-value"></div>' . PHP_EOL;
		// $html['volume'] .= '	</div>' . PHP_EOL;

		// --- Volume Increase ---
		$html['volume'] .= '	<button class="rp-volume-up" role="button" aria-label="' . esc_attr( __( 'Volume Up' ) ) . '">+</button>' . PHP_EOL;

		// --- Volume Max ---
		$html['volume'] .= '	<button class="rp-volume-max" role="button" tabindex="0">' . esc_html( __( 'Max', 'radio-player' ) ) . '</button>' . PHP_EOL;

	$html['volume'] .= '</div>' . PHP_EOL;

	// --- dropdown script switcher for testing ---
	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		$html['switcher'] = '<div class="rp-script-switcher">' . PHP_EOL;
			$html['switcher'] .= '<div class="rp-show-switcher" onclick="radio_player_show_switcher(' . esc_js( $instance ) . ');">*</div>';
			$html['switcher'] .= '<select class="rp-script-select" name="rp-script-select" style="display:none;">' . PHP_EOL;
			$scripts = array( 'amplitude' => 'Amplitude', 'jplayer' => 'jPlayer', 'howler' => 'Howler' );
			foreach ( $scripts as $script => $label ) {
				$html['switcher'] .= '<option value="' . esc_attr( $script ) . '"';
				if ( $script == $args['script'] ) {
					$html['switcher'] .= ' selected="selected"';
				}
				$html['switcher'] .= '>' . esc_html( $label ) . '</option>' . PHP_EOL;
			}
			$html['switcher'] .= '</select>' . PHP_EOL;
		$html['switcher'] .= '</div>' . PHP_EOL;
	}

	// --- Current Show Texts ---
	// TODO: add other show info divs ( with expander ) ?
	$show_text_html = '<div class="rp-show-text">' . PHP_EOL;
	$show_text_html .= '	<div class="rp-show-title" aria-label="' . esc_attr( __( 'Show Title', 'radio-player' ) ) . '"></div>' . PHP_EOL;
	$show_text_html .= '	<div class="rp-show-hosts"></div>' . PHP_EOL;
	$show_text_html .= '	<div class="rp-show-producers"></div>' . PHP_EOL;
	$show_text_html .= '	<div class="rp-show-shift"></div>' . PHP_EOL;
	$show_text_html .= '	<div class="rp-show-remaining"></div>' . PHP_EOL;
	$show_text_html .= '</div>' . PHP_EOL;
	$show_text_html .= '<div id="rp-show-image-' . esc_attr( $instance ) . '" class="rp-show-image no-image" aria-label="' . esc_attr( __( 'Show Logo Image', 'radio-player' ) ) . '"></div>' . PHP_EOL;

	$html['show'] = '<div class="rp-show-info">' . PHP_EOL;
	$html['show'] .= $show_text_html;
	$html['show'] .= '	</div>' . PHP_EOL;

	// --- Progress Bar ---
	// (for files - not implemented yet)
	/* $html['progress'] = '<div class="rp-progress">';
	$html['progress'] .= '	<div class="rp-seek-bar">';
	$html['progress'] .= '		<div class="rp-play-bar"></div>';
	$html['progress'] .= '	</div>';
	$html['progress'] .= '</div>';
	$html['progress'] .= '<div class="rp-current-time" role="timer" aria-label="time">&nbsp;</div>' . PHP_EOL;
	$html['progress'] .= '<div class="rp-duration" role="timer" aria-label="duration">&nbsp;</div>' . PHP_EOL;
	$html['progress'] .= '<div class="rp-toggles">';
	$html['progress'] .= '	<button class="rp-repeat" role="button" tabindex="0">Repeat</button>';
	$html['progress'] .= '	<button class="rp-shuffle" role="button" tabindex="0">Shuffle</button>';
	$html['progress'] .= '</div>' . PHP_EOL; */

	// --- no solution section ---
	// $html['no-solution'] = '<div class="rp-no-solution">' . PHP_EOL;
	// $html['no-solution'] .= '<span>' . esc_html( __( 'Update Required' ) ) . '</span>' . PHP_EOL;
	// $html['no-solution'] .= 'To play the media you will need to either update your browser to a recent version or update your <a href="https://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.' . PHP_EOL;
	// $html['no-solution'] .= '</div>' . PHP_EOL;

	// --- Current Track ---
	$html['track'] = '<div class="rp-now-playing">' . PHP_EOL;
	$html['track'] .= '	<div class="rp-now-playing-item rp-now-playing-title"></div>' . PHP_EOL;
	$html['track'] .= '	<div class="rp-now-playing-item rp-now-playing-artist"></div>' . PHP_EOL;
	$html['track'] .= '	<div class="rp-now-playing-item rp-now-playing-album"></div>' . PHP_EOL;
	$html['track'] .= '</div>' . PHP_EOL;

	// --- set section order ---
	$section_order = array( 'station', 'interface', 'show' );
	if ( isset( $args['section_order'] ) ) {
		if ( is_array( $args['section_order'] ) ) {
			$section_order = $args['section_order'];
		} else {
			$section_order = explode( ',', $args['section_order'] );
		}
	}
	if ( function_exists( 'apply_filters' ) ) {
		$section_order = apply_filters( 'radio_station_player_section_order', $section_order, $args );
	}

	// --- set interface order ---
	// if ( 'mediaelements' == $args['script'] ) {
	//	$html['mediaelements'] = radio_station_player_mediaelements_interface( $args );
	//	$control_order = array( 'mediaelements' );
	// } else {
		$control_order = array( 'controls', 'volume', 'switcher', 'track' );
		if ( isset( $args['control_order'] ) ) {
			if ( is_array( $args['control_order'] ) ) {
				$control_order = $args['control_order'];
			} else {
				$control_order = explode( ',', $args['control_order'] );
			}
		}
	// }
	if ( function_exists( 'apply_filters' ) ) {
		$control_order = apply_filters( 'radio_station_player_control_order', $control_order, $args, $instance );
	}

	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		echo '<!-- Section Order: ' . print_r( $section_order, true ) . ' -->';
		echo '<!-- Control Order: ' . print_r( $control_order, true ) . ' -->';
	}

	// --- set alternative text sections ---
	// 2.4.0.2: added for alternative display methods
	// 2.4.0.3: added missing function_exists wrappers
	$station_text_alt = '<div class="rp-station-text-alt">' . $station_text_html . '</div>' . PHP_EOL;
	if ( function_exists( 'apply_filters' ) ) {
		$station_text_alt = apply_filters( 'radio_station_player_station_text_alt', $station_text_alt, $args, $instance );
	}
	$show_text_alt = '<div class="rp-station-text-alt">' . $show_text_html . '</div>' . PHP_EOL;
	if ( function_exists( 'apply_filters' ) ) {
		$show_text_alt = apply_filters( 'radio_station_player_show_text_alt', $show_text_alt, $args, $instance );
	}

	// --- create player from sections ---
	$player = $html['player_open'];
	foreach ( $section_order as $section ) {
		if ( 'interface' == $section ) {

			// --- create control interface ---
			// 2.4.0.2: added alternative text sections
			// 2.4.0.3: fix to alternative text variables
			$player .= $html['interface_open'];
			$player .= $station_text_alt;
			foreach ( $control_order as $control ) {
				if ( isset( $html[$control] ) ) {
					$player .= $html[$control];
				}
			}
			$player .= $show_text_alt;
			$player .= $html['interface_close'];

		} elseif ( isset( $html[$section] ) ) {
			$player .= $html[$section];
		}
	}
	// if ( 'jplayer' == $args['script'] ) {
	//	$player .= $html['no-solution'];
	// }
	$player .= $html['player_close'];

	// --- filter and return ---
	// 2.4.0.3: added missing function_exists wrappers
	if ( function_exists( 'apply_filters' ) ) {
		$player = apply_filters( 'radio_station_player_html', $player, $args, $instance );
	}
	return $player;
}

// ----------------
// Player Shortcode
// ----------------
// note: this Shortcode is WordPress / Radio Station plugin usage
if ( function_exists( 'add_shortcode' ) ) {
	add_shortcode( 'radio-player', 'radio_station_player_shortcode' );
}
function radio_station_player_shortcode( $atts ) {

	// 2.4.0.3: fix for when no attributes passed
	if ( !is_array( $atts ) ) {
		$atts = array();
	}

	// --- maybe debug shortcode attributes --
	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		echo '<span style="display:none;">Passed Radio Player Shortcode Attributes: ';
		echo print_r( $atts, true ) . '</span>';
	}

	// --- set base defaults ---
	$title = $image = $image_url = '';
	$script = 'amplitude';
	$layout = 'horizontal';
	$theme = 'light';
	$buttons = 'rounded';
	$volume = 77;

	// --- set default player title ---
	if ( defined( 'RADIO_PLAYER_TITLE_DISPLAY' ) ) {
		$title = RADIO_PLAYER_TITLE_DISPLAY;
	} elseif ( function_exists( 'radio_station_get_setting' ) ) {
		$title = radio_station_get_setting( 'player_title' );
		$title = ( 'yes' == $title ) ? '' : 0;
	} elseif ( function_exists( 'apply_filters' ) ) {
		$title = apply_filters( 'radio_station_player_default_title_display', $title );
	}

	// --- set default player image ---
	if ( defined( 'RADIO_PLAYER_IMAGE_DISPLAY' ) ) {
		$image = RADIO_PLAYER_IMAGE_DISPLAY;
	} elseif ( function_exists( 'radio_station_get_setting' ) ) {
		$image = radio_station_get_setting( 'player_image' );
		$image = ( 'yes' == $image ) ? 1 : 0;
	} elseif ( function_exists( 'apply_filters' ) ) {
		$image = apply_filters( 'radio_station_player_default_image_display', $image );
	}

	// --- set default player script ---
	$scripts = array( 'amplitude', 'howler', 'jplayer' ); // 'mediaelements'
	if ( defined( 'RADIO_PLAYER_SCRIPT' ) && in_array( RADIO_PLAYER_SCRIPT, $scripts ) ) {
		$script = RADIO_PLAYER_SCRIPT;
	} elseif ( function_exists( 'radio_station_get_setting' ) ) {
		$script = radio_station_get_setting( 'player_script' );
	} elseif ( function_exists( 'apply_filters' ) ) {
		$script = apply_filters( 'radio_station_player_default_script', $script );
	}

	// --- set default player layout ---
	if ( defined( 'RADIO_PLAYER_LAYOUT' ) ) {
		$layout = RADIO_PLAYER_LAYOUT;
	} elseif ( function_exists( 'apply_filters' ) ) {
		$layout = apply_filters( 'radio_station_player_default_layout', $layout );
	}

	// --- set default player volume ---
	if ( defined( 'RADIO_PLAYER_VOLUME' ) ) {
		$volume = RADIO_PLAYER_VOLUME;
	} elseif ( function_exists( 'radio_station_get_setting' ) ) {
		$volume = radio_station_get_setting( 'player_volume' );
	} elseif ( function_exists( 'apply_filters' ) ) {
		$volume = apply_filters( 'radio_station_player_default_volume', $volume );
	}

	// --- set default player theme ---
	if ( defined( 'RADIO_PLAYER_THEME' ) ) {
		$theme = RADIO_PLAYER_THEME;
	} elseif ( function_exists( 'radio_station_get_setting' ) ) {
		$theme = radio_station_get_setting( 'player_theme' );
	} elseif ( function_exists( 'apply_filters' ) ) {
		$theme = apply_filters( 'radio_station_player_default_theme', $theme );
	}

	// --- set default player button shape ---
	if ( defined( 'RADIO_PLAYER_BUTTONS' ) ) {
		$buttons = RADIO_PLAYER_BUTTONS;
	} elseif ( function_exists( 'radio_station_get_setting' ) ) {
		$buttons = radio_station_get_setting( 'player_buttons' );
	} elseif ( function_exists( 'apply_filters' ) ) {
		$buttons = apply_filters( 'radio_station_player_default_buttons', $buttons );
	}

	// --- set default atts ---
	// 2.4.0.1: add player ID attribute
	$defaults = array(
		'url'       => '',
		'format'    => '',
		'fallback'  => '',
		'fformat'   => '',
		'title'	    => $title,
		'image'	    => $image,
		'script'    => $script,
		'layout'    => $layout,
		'theme'     => $theme,
		'buttons'   => $buttons,
		// 'skin'   => $skin,
		'volume'    => $volume,
		'default'   => false,
		'widget'    => 0,
		'id'        => '',
	);

	// --- unset attribites set to default ---
	foreach ( $atts as $key => $value ) {
		if ( 'default' == $value ) {
			unset( $atts[$key] );
		}
	}

	// --- filter attributes ---
	// 2.4.0.1: move filter to before merging
	if ( function_exists( 'apply_filters' ) ) {
		$atts = apply_filters( 'radio_station_player_shortcode_attributes', $atts );
	}

	// --- merge attribute values ---
	if ( function_exists( 'shortcode_atts' ) ) {
		$atts = shortcode_atts( $defaults, $atts, 'radio-player' );
	}
	foreach ( $defaults as $key => $value ) {
		if ( !isset( $atts[$key] ) ) {
			$atts[$key] = $value;
		}
	}

	// --- maybe debug shortcode attributes --
	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		echo '<span style="display:none;">Combined Radio Player Shortcode Attributes: ';
		echo print_r( $atts, true ) . '</span>';
	}

	// --- maybe get station title ---
	if ( '' == $atts['title'] ) {
		if ( defined( 'RADIO_PLAYER_TITLE' ) ) {
			$atts['title'] = RADIO_PLAYER_TITLE;
		} elseif ( function_exists( 'radio_station_get_setting' ) ) {
			$atts['title'] = radio_station_get_setting( 'station_title' );
		} elseif ( function_exists( 'apply_filters' ) ) {
			$atts['title'] = apply_filters( 'radio_station_player_default_title', '' );
		}
	} elseif ( ( '0' == $atts['title'] ) || ( 0 === $atts['title'] ) ) {
		// --- allows disabling via 0 attribute value ---
		// 2.4.0.3: allow for string or integer value match
		$atts['title'] = '';
	}

	// --- maybe get station image ---
	if ( $atts['image'] ) {
		// note: converts attribute switch to URL
		if ( ( '1' == $atts['image'] ) || ( 1 == $atts['image'] ) ) {
			if ( defined( 'RADIO_PLAYER_IMAGE' ) ) {
				$atts['image'] = RADIO_PLAYER_IMAGE;
			} elseif ( function_exists( 'radio_station_get_setting' ) ) {
				$station_image = radio_station_get_setting( 'station_image' );
				if ( $station_image ) {
					$attachment = wp_get_attachment_image_src( $station_image, 'full' );
					if ( is_array( $attachment ) ) {
						$atts['image'] = $attachment[0];
					} else {
						$atts['image'] = 0;
					}
				} else {
					$atts['image'] = 0;
				}
			} elseif ( function_exists( 'apply_filters' ) ) {
				$atts['image'] = apply_filters( 'radio_station_player_default_image', '' );
			}
		}
	}

	// DEV TEMP: allow default script override via querystring
	// if ( isset( $_REQUEST['script'] ) && in_array( $_REQUEST['script'], $scripts ) ) {
	//	$atts['script'] = $_REQUEST['script'];
	// }

	// --- check script override constant ---
	if ( defined( 'RADIO_PLAYER_FORCE_SCRIPT' ) && in_array( RADIO_PLAYER_FORCE_SCRIPT, $scripts ) ) {
		$atts['script'] = RADIO_PLAYER_FORCE_SCRIPT;
	}

	// --- check for full player output override ---
	$player = $override = '';
	if ( function_exists( 'apply_filters' ) ) {
		$override = apply_filters( 'radio_station_player_output', $override, $atts );
	}
	if ( '' != $override ) {

		// --- use full override for output ---
		$player = $override;

	} else {

		// --- maybe open shortcode wrapper ---
		if ( !$atts['widget'] ) {
			$player .= '<div class="radio-player-shortcode">' . PHP_EOL;
		}

		// --- maybe debug shortcode attributes --
		if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
			echo '<span style="display:none;">Parsed Radio Player Shortcode Attributes: ';
			echo print_r( $atts, true ) . '</span>';
		}

		// --- get player HTML ---
		$player .= radio_station_player_output( $atts );

		// -- maybe close shortcode wrapper ---
		if ( !$atts['widget'] ) {
			$player .= '</div>' . PHP_EOL;
		}
	}

	// --- enqueue player script in footer ---
	radio_station_player_core_scripts();
	radio_station_player_enqueue_script( $atts['script'] );

	// --- enqueue player styles ---
	radio_station_player_enqueue_styles( $atts['script'], false ); // $atts['skin']

	// --- add update iframe to footer ---
	// (for saving WordPress logged in user states)
	if ( function_exists( 'add_action' ) ) {
		add_action( 'wp_footer', 'radio_station_player_iframe', 20 );
	}

	return $player;
}

// -------------------
// Player AJAX Display
// -------------------
add_action( 'wp_ajax_radio_player', 'radio_station_player_ajax' );
add_action( 'wp_ajax_nopriv_radio_player', 'radio_station_player_ajax' );
function radio_station_player_ajax() {

	// --- sanitize shortcode attributes ---
	$atts = radio_station_player_sanitize_shortcode_values();
	if ( defined( 'RADIO_PLAYER_DEBUG' ) && RADIO_PLAYER_DEBUG ) {
		echo '<span style="display:none;">';
		echo 'Radio Player Shortcode Attributes: ' . print_r( $atts, true );
		echo '</span>';
	}

	// --- output head ---
	echo '<head>';
		wp_head();
	echo '</head><body>';

	// --- output widget contents ---
	echo '<div id="player-contents">';
		echo radio_station_player_shortcode( $atts );
	echo '</div>';

	// --- output (hidden) footer for scripts ---
	echo '<div style="display:none;">';
		wp_footer();
	echo '</div>';

	// --- maybe add background color ---
	if ( isset( $atts['background'] ) ) {
		echo '<style>#player-contents {background: #' . esc_attr( $atts['background'] ) . ';}</style>';
	}

	echo '</body>';
	exit;
}

// -------------------------
// Sanitize Shortcode Values
// -------------------------
function radio_station_player_sanitize_shortcode_values() {

	// --- current show attribute keys ---
	$keys = array(
		'url'        => 'url',
		'title'	     => 'text',
		'image'	     => 'url',
		'script'     => 'howler/amplitude/jplayer',
		'layout'     => 'text',
		'theme'      => 'text',
		'buttons'    => 'text',
		'volume'     => 'integer',
		'default'    => 'boolean',
		'widget'     => 'boolean',
		'background' => 'text',
	);

	// --- sanitize values by key type ---
	$atts = radio_station_player_sanitize_values( $_REQUEST, $keys );
	return $atts;

}

// ---------------
// Sanitize Values
// ---------------
function radio_station_player_sanitize_values( $data, $keys ) {

	$sanitized = array();
	foreach ( $keys as $key => $type ) {
		if ( isset( $data[$key] ) ) {
			if ( 'boolean' == $type ) {
				if ( ( 0 == $data[$key] ) || ( 1 == $data[$key] ) ) {
					$sanitized[$key] = $data[$key];
				}
			} elseif ( 'integer' == $type ) {
				$sanitized[$key] = absint( $data[$key] );
			} elseif ( 'alphanumeric' == $type ) {
				$value = preg_match( '/^[a-zA-Z0-9_]+$/', $data[$key] );
				if ( $value ) {
					$sanitized[$key] = $value;
				}
			} elseif ( 'text' == $type ) {
				$sanitized[$key] = sanitize_text_field( $data[$key] );
			} elseif ( 'slug' == $type ) {
				$sanitized[$key] = sanitize_title( $data[$key] );
			} elseif ( strstr( $type, '/' ) ) {
				$options = explode( '/', $type );
				if ( in_array( $data[$key], $options ) ) {
					$sanitized[$key] = $data[$key];
				}
			}
		}
	}
	return $sanitized;
}

// ------------------------
// Media Elements Interface
// ------------------------
// note: exception to the main interface used by all other scripts
/* function radio_station_player_mediaelements_interface( $atts ) {

	global $radio_player;

	$post_id = 0;
	if ( function_exists( 'get_post' ) && function_exists( 'get_the_ID' ) ) {
		$post_id = get_post() ? get_the_ID() : 0;
	}

	// --- set player instance ---
	$instance = 0;
	if ( isset( $radio_player['me_instance'] ) ) {
		$instance = $radio_player['me_instance'];
	}
	$instance++;

	// --- get shortcode attributes ---
	$defaults_atts = array(
		'src'      => '',
		'loop'     => '',
		'autoplay' => '',
		'preload'  => 'none',
		'class'    => 'rp-audio', // 'mejs-audio'
		'style'    => 'width: 100%;'
	);
	if ( function_exists( 'shortcode_atts' ) ) {
		$atts = shortcode_atts( $defaults_atts, $atts, 'radio-player-mediaelements' );
	} else {
		foreach ( $defaults as $key => $value ) {
			if ( !isset( $atts[$key] ) ) {
				$atts[$key] = $value;
			}
		}
	}
	if ( function_exists( 'apply_filters' ) ) {
		$atts = apply_filters( 'radio_station_player_atts', $atts );
	}

	// --- set HTML attributes ---
	// TODO: replace radio_station_player_validate_boolean ?
	// TODO: replace and store player ID ?
	$html_atts = array(
		'class'    => $atts['class'],
		'id'       => sprintf( 'audio-%d-%d', $post_id, $instance ),
		'loop'     => radio_station_player_validate_boolean( $atts['loop'] ),
		'autoplay' => radio_station_player_validate_boolean( $atts['autoplay'] ),
		'preload'  => $atts['preload'],
		'style'    => $atts['style'],
	);
	foreach ( array( 'loop', 'autoplay', 'preload' ) as $a ) {
		if ( empty( $html_atts[$a] ) ) {
			unset( $html_atts[$a] );
		}
	}

	// --- set audio attributes ---
	$attr_strings = array();
	foreach ( $html_atts as $k => $v ) {
		$attr_strings[] = $k . '="' . esc_attr( $v ) . '"';
	}

	// --- open audio element ---
	$html = '';
	if ( 1 === $instance ) {
		$html .= "<!--[if lt IE 9]><script>document.createElement('audio');</script><![endif]-->\n";
	}
	$html .= sprintf( '<audio %s controls="controls">', join( ' ', $attr_strings ) );

	// --- set audio source ---
	$source = '<source type="%s" src="%s" />';
	$html .= sprintf( $source, $stream_format, $streaming_url );
	if ( $fallback_format && $fallback_url ) {
		$html .= sprintf( $source, $fallback_format, $fallback_url );
	}

	// --- close audio element ---
	$html .= '</audio>';

	// --- filter and return ---
	if ( function_exists( 'apply_filters' ) ) {
		$html = apply_filters( 'radio_station_player_mediaelements_interface', $html, $atts, $post_id );
	}
	return $html;
} */


// ----------------------
// === Player Scripts ===
// ----------------------

// -------------------------
// Enqueue Player Javascript
// -------------------------
function radio_station_player_core_scripts() {

	global $radio_player;
	if ( !isset( $radio_player ) ) {
		$radio_player = array();
	}

	// --- enqueue sysend message script ---
	$version = '1.3.3';
	if ( function_exists( 'wp_enqueue_script' ) ) {

		// --- enqueue player script ---
		if ( defined( 'RADIO_PLAYER_URL' ) ) {
			$url = RADIO_PLAYER_URL . 'js/sysend.js';
		} elseif ( defined( 'RADIO_STATION_FILE ' ) ) {
			$url = plugins_url( 'player/js/sysend.js', RADIO_STATION_FILE );
		} else {
			$url = plugins_url( 'js/sysend.js', __FILE__ );
		}
		wp_enqueue_script( 'sysend', $url, array(), $version, true );

	} elseif ( !isset( $radio_player['printed_sysend'] ) ) {

		// --- output script tag directly ---
		$url = 'js/sysend.js';
		if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
		echo radio_station_player_script_tag( $url, $version );
		$radio_player['printed_sysend'] = true;

	}

	// --- enqueue radio player script ---
	// TODO: add minimized version of player script ?
	// $suffix = '.min';
	// if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$suffix = '';
	// }
	if ( defined( 'RADIO_STATION_DIR' ) ) {
		$version = filemtime( RADIO_STATION_DIR . '/player/js/radio-player' . $suffix . '.js' );
	} else {
		$version = filemtime( dirname( __FILE__ ) . '/js/radio-player' . $suffix . '.js' );
	}
	if ( function_exists( 'wp_enqueue_script' ) ) {

		// --- enqueue player script ---
		if ( defined( 'RADIO_PLAYER_URL' ) ) {
			$url = RADIO_PLAYER_URL . 'js/radio-player' . $suffix . '.js';
		} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
			$url = plugins_url( 'player/js/radio-player' . $suffix . '.js', RADIO_STATION_FILE );
		} else {
			$url = plugins_url( 'js/radio-player' . $suffix . '.js', __FILE__ );
		}
		wp_enqueue_script( 'radio-player', $url, array( 'jquery' ), $version, true );

	} elseif ( !isset( $radio_player['printed_player'] ) ) {

		// note: jQuery should be enqueued for standalone version

		// --- output script tag directly ---
		$url = 'js/radio-player' . $suffix . '.js';
		if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
		echo radio_station_player_script_tag( $url, $version );
		$radio_player['printed_player'] = true;

	}

	// --- set minified script suffix ---
	$suffix = '.min';
	if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
	  || ( defined( 'RADIO_STATION_DEBUG') && RADIO_STATION_DEBUG ) ) {$suffix = '';}

	// --- set amplitude script ---
	$path = dirname( __FILE__ ) . '/js/amplitude' . $suffix . '.js';
	if ( function_exists( 'wp_enqueue_script' ) ) {
		if ( defined( 'RADIO_PLAYER_URL' ) ) {
			$url = RADIO_PLAYER_URL . 'js/amplitude' . $suffix . '.js';
		} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
			$url = plugins_url( 'player/js/amplitude' . $suffix . '.js', RADIO_STATION_FILE );
		} else {
			$url = plugins_url( 'js/amplitude' . $suffix . '.js', __FILE__ );
		}

	} else {
		$url = 'js/amplitude' . $suffix . '.js';
		if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
	}
	$radio_player['amplitude_script'] = array( 'version' => '5.0.3', 'url' => $url, 'path' => $path );

	// --- set jplayer script ---
	$path = dirname( __FILE__ ) . '/js/jplayer' . $suffix . '.js';
	if ( function_exists( 'wp_enqueue_script' ) ) {
		if ( defined( 'RADIO_PLAYER_URL' ) ) {
			$url = RADIO_PLAYER_URL . 'js/jplayer' . $suffix . '.js';
		} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
			$url = plugins_url( 'player/js/jplayer' . $suffix . '.js', RADIO_STATION_FILE );
		} else {
			$url = plugins_url( 'js/jplayer' . $suffix . '.js', __FILE__ );
		}
	} else {
		echo radio_station_player_script_tag( $url, $version );
		$radio_player['printed_jplayer'] = true;
	}
	$radio_player['jplayer_script'] = array( 'version' => '2.9.2', 'url' => $url, 'path' => $path );

	// --- set howler script ---
	$path = dirname( __FILE__ ) . '/js/howler' . $suffix . '.js';
	if ( function_exists( 'wp_enqueue_script' ) ) {
		if ( defined( 'RADIO_PLAYER_URL' ) ) {
			$url = RADIO_PLAYER_URL . 'js/howler' . $suffix . '.js';
		} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
			$url = plugins_url( 'player/js/howler' . $suffix . '.js', RADIO_STATION_FILE );
		} else {
			$url = plugins_url( 'js/howler' . $suffix . '.js', __FILE__ );
		}
	} else {
		$url = 'js/howler' . $suffix . '.js';
		if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
	}
	$radio_player['howler_script'] = array( 'version' => '2.3.1', 'url' => $url, 'path' => $path );

	// --- set core media elements script ---
	/* $version = '4.2.6'; // as of WP 4.9
	$version = filemtime( dirname( __FILE__ ) . '/js/mediaelement-and-player' . $suffix . '.js' );
	$url = 'js/mediaelement-and-player' . $suffix . '.js';
	if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
	$radio_player['media_script'] = array( 'version' => $version, 'url' => $url, 'path' => $path );

	// --- set media elements player script ---
	$path = dirname( __FILE__ ) . '/js/rp-mediaelement' . $suffix . '.js';
	if ( function_exists( 'wp_enqueue_script' ) ) {
		if ( defined( 'RADIO_PLAYER_URL' ) ) {
			$url = RADIO_PLAYER_URL . 'js/rp-mediaelement.js';
			$version = filemtime( dirname( __FILE__ ) . '/js/rp-mediaelement.js' );
		} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
			$url = plugins_url( 'player/js/rp-mediaelement.js', RADIO_STATION_FILE );
			$version = filemtime( RADIO_STATION_DIR . '/player/js/rp-mediaelement.js' );
		} else {
			$url = plugins_url( 'js/rp-mediaelement.js', __FILE__ );
			$version = filemtime( dirname( __FILE__ ) . '/js/rp-mediaelement.js' );
		}
	} elseif ( !isset( $radio_player['printed_mediaelement'] ) ) {
		// note: no minified version here yet ?
		$version = filemtime( dirname( __FILE__ ) . '/js/rp-mediaelement.js' );
		$url = 'js/rp-mediaelement.js';
		if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
	}
	$radio_player['elements_script'] = array( 'version' => $version, 'url' => $url, 'path' => $path );
	*/

	// --- add radio player settings (once only) ---
	// note: intentionally here after player scripts are set
	if ( !isset( $radio_player['enqeued_player'] ) ) {
		$js = radio_station_player_get_settings();
		if ( function_exists( 'wp_add_inline_script' ) ) {
			// --- add inline script ---
			wp_add_inline_script( 'radio-player', $js );
		} else {
			// --- print settings directly ---
			echo "<script>" . $js . "</script>";
		}
		$radio_player['enqueued_player'] = true;
	}
}

// ---------------------
// Enqueue Player Script
// ---------------------
function radio_station_player_enqueue_script( $script ) {

	global $radio_player;
	if ( !isset( $radio_player ) ) {
		$radio_player = array();
	}

	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		echo '<span style="display:none;">Default Player Script: ' . $script . '</span>';
	}

	// --- add player specific functions (once only ) ---
	$js = '';
	if ( ( 'amplitude' == $script ) && !isset( $radio_player['enqeued_amplitude'] ) ) {

		radio_station_player_enqueue_amplitude( true );

	} elseif ( ( 'jplayer' == $script ) && !isset( $radio_player['enqeued_jplayer'] ) ) {

		radio_station_player_enqueue_jplayer( true );

	} elseif ( ( 'howler' == $script ) &&  !isset( $radio_player['enqeued_howler'] ) ) {

		radio_station_player_enqueue_howler( true );

	}
	// elseif ( ( 'mediaelements' == $script ) &&  !isset( $radio_player['enqeued_mediaelements'] ) ) {
	//	radio_station_player_enqueue_mediaelements( true );
	//	$js = radio_station_player_script_mediaelements();
	// }

	// 2.4.0.3: load all player scripts regardless
	$js .= radio_station_player_script_howler();
	$js .= radio_station_player_script_jplayer();
	$js .= radio_station_player_script_amplitude();

	// --- append any pageload scripts ---
	if ( function_exists( 'apply_filters') ) {
		$pageload = apply_filters( 'radio_station_player_pageload_script', '' );
		if ( '' != $pageload ) {
			$js .= "jQuery(document).ready(function() {" . PHP_EOL . $pageload . PHP_EOL . "});" . PHP_EOL;
		}
	}

	// --- maybe filter the full script output ---
	if ( function_exists( 'apply_filters' ) ) {
		$js = apply_filters( 'radio_station_player_scripts', $js );
	}

	// --- output script tag ---
	if ( '' != $js ) {
		if ( function_exists( 'wp_add_inline_script' ) ) {
			// --- add inline script ---
			wp_add_inline_script( 'radio-player', $js );
		} else {
			// --- print script directly ---
			echo "<script>" . $js . "</script>";
		}
	}

	// --- set specific script as enqueued ---
	$radio_player['enqeued_' . $script] = true;
}

// --------------------------------
// Lazy Load Audio Script Fallbacks
// --------------------------------
// 2.4.0.3: lazy load fallback scripts on pageload to cache them
add_filter( 'radio_station_player_pageload_script', 'radio_station_player_load_script_fallbacks' );
function radio_station_player_load_script_fallbacks( $js ) {

	global $radio_player;

	// 2.4.0.3: check for fallback selection (default all)
	$fallbacks = array( 'jplayer', 'howler', 'amplitude' );
	if ( function_exists( 'radio_station_get_setting' ) ) {
		$fallbacks = radio_station_get_setting( 'player_fallbacks' );
	} elseif ( function_exists( 'apply_filters' ) ) {
		$fallbacks = apply_filters( 'radio_station_player_fallbacks', $fallbacks );
	}
	if ( defined( 'RADIO_PLAYER_FALLBACKS' ) ) {
		$fallbacks = explode( ',', RADIO_PLAYER_FALLBACKS );
	}

	// --- load fallback audio scripts ---
	if ( count( $fallbacks ) > 0 ) {
		$js .= "head = document.getElementsByTagName('head')[0]; ";
		if ( !isset( $radio_player['enqueued_howler'] ) && in_array( 'howler', $fallbacks ) ) {
			$js .= "el = document.createElement('script'); el.src = radio_player.scripts.howler; head.appendChild(el);";
		}
		if ( !isset( $radio_player['enqueued_jplayer'] )  && in_array( 'jplayer', $fallbacks ) ) {
			$js .= "el = document.createElement('script'); el.src = radio_player.scripts.jplayer; head.appendChild(el);";
		}
		if ( !isset( $radio_player['enqueued_amplitude'] )  && in_array( 'amplitude', $fallbacks ) ) {
			$js .= "el = document.createElement('script'); el.src = radio_player.scripts.amplitude; head.appendChild(el);";
		}
		$js .= PHP_EOL;
	}
	
	return $js;
}

// ----------------------------
// Enqueue Amplitude Javascript
// ----------------------------
function radio_station_player_enqueue_amplitude( $infooter ) {
	global $radio_player;
	if ( function_exists( 'wp_enqueue_script' ) ) {
		// note: jquery dependency not required
		wp_enqueue_script( 'amplitude', $radio_player['amplitude_script']['url'], array(), $radio_player['amplitude_script']['version'], $infooter );
	} elseif ( !isset( $radio_player['printed_amplitude'] ) ) {
		echo radio_station_player_script_tag( $radio_player['amplitude_script']['url'], $radio_player['amplitude_script']['version'] );
		$radio_player['printed_amplitude'] = true;
	}
}

// --------------------------
// Enqueue JPlayer Javascript
// --------------------------
function radio_station_player_enqueue_jplayer( $infooter ) {
	global $radio_player;
	if ( function_exists( 'wp_enqueue_script' ) ) {
		wp_enqueue_script( 'jplayer', $radio_player['jplayer_script']['url'], array( 'jquery' ), $radio_player['jplayer_script']['version'], $infooter );
	} elseif ( !isset( $radio_player['printed_jplayer'] ) ) {
		echo radio_station_player_script_tag( $radio_player['jplayer_script']['url'], $radio_player['jplayer_script']['version'] );
		$radio_player['printed_jplayer'] = true;
	}
}

// -------------------------
// Enqueue Howler Javascript
// -------------------------
// TODO: maybe test use of howler.core.min.js instead ?
function radio_station_player_enqueue_howler( $infooter ) {
	global $radio_player;
	if ( function_exists( 'wp_enqueue_script' ) ) {
		wp_enqueue_script( 'howler', $radio_player['howler_script']['url'], array( 'jquery' ), $radio_player['howler_script']['version'], $infooter );
	} elseif ( !isset( $radio_player['printed_howler'] ) ) {
		echo radio_station_player_script_tag( $radio_player['howler_script']['url'], $radio_player['howler_script']['version'] );
		$radio_player['printed_howler'] = true;
	}
}

// --------------------------------
// Enqueue Media Element Javascript
// --------------------------------
/* function radio_station_player_enqueue_mediaelements( $infooter ) {
	global $radio_player;

	// --- enqueue media element javascript ---
	if ( function_exists( 'wp_enqueue_script' ) ) {
		// note: media player script enqueued via dependency
		wp_enqueue_script( 'rp-mediaelement', $radio_player['elements_script']['url'], array( 'mediaelement' ), $radio_player['elements_script']['version'], $infooter );
	} elseif ( !isset( $radio_player['printed_mediaelement'] ) ) {
		// --- output core media element script ---
		echo radio_station_player_script_tag( $radio_player['media_script']['url'], $radio_player['media_script']['version'] );

		// --- output media element player script ---
		echo radio_station_player_script_tag( $radio_player['elements_script']['url'], $radio_player['elements_script']['version'] );
		$radio_player['printed_mediaelement'] = true;
	}


	// --- localize settings ---
	// TODO: move this code block
	if ( function_exists( 'plugins_url' ) ) {
		$url = plugins_url( 'js/', __FILE__ );
	} else {
		$url = 'js/';
	}
	$player_settings = array(
		'pluginPath'    => $url,
		'classPrefix'   => 'rp-', // 'mejs-'
		'stretching'    => 'responsive',
		'forceLive'		=> true,
	);
	if ( function_exists( 'apply_filters' ) ) {
		$player_settings = apply_filters( 'radio_station_player_mediaelement_settings', $player_settings );
	}
	if ( function_exists( 'wp_localize_script') ) {
		// --- localize script output ---
		wp_localize_script( 'rp-mediaelement', 'rpSettings', $player_settings );
	} else {
		// --- output script settings variable directly ---
		echo "<script>var rpSettings; ";
		foreach ( $player_settings as $key => $value ) {
			if ( is_string( $value ) ) {
				echo "rpSettings[" . $key . "] = '" . $value . "'; ";
			} else {
				echo "rpSettings[" . $key . "] = " . $value . "; ";
			}
		}
		echo "</script>";
	}
} */

// ----------------------------
// Dynamic Load Script via AJAX
// ----------------------------
if ( function_exists( 'add_action' ) ) {
	add_action( 'wp_ajax_radio_station_player_script', 'radio_station_player_script' );
	add_action( 'wp_ajax_nopriv_radio_station_player_script', 'radio_station_player_script' );
} elseif ( isset( $_REQUEST['action'] ) && ( 'radio_station_player_script' == $_REQUEST['action'] ) ) {
	radio_station_player_script();
}
function radio_station_player_script() {
	$script = $_REQUEST['script'];
	$js = '';
	if ( 'amplitude' == $script ) {
		// $js = file_get_contents( dirname( __FILE__ ) . '/js/amplitude' . $suffix . '.js' );
		$js .= radio_station_player_script_amplitude();
	} elseif ( 'jplayer' == $script ) {
		// $js = file_get_contents( dirname( __FILE__ ) . '/js/jplayer' . $suffix . '.js';
		$js .= radio_station_player_script_jplayer();
	} elseif ( 'howler' == $script ) {
		// $js = file_get_contents( dirname( __FILE__ ) . '/js/howler' . $suffix . '.js';
		$js .= radio_station_player_script_howler();
	} // elseif ( 'elements' == $script ) {
		// TODO: combine both media elements scripts
		// $js = file_get_contents( dirname( __FILE__ ) . '/js/mediaelements' . $suffix . '.js';
		// $js .= radio_station_player_script_mediaelements();
		// TODO: localize script settings ?
	// }
	else {
		exit;
	}

	if ( isset( $js ) ) {
		header( 'Content-type: application/javascript' );
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo $js;
	}
	exit;
}

// -------------------
// Get Player Settings
// -------------------
function radio_station_player_get_settings() {

	global $radio_player;
	$js = '';

	// ---- set AJAX URL ---
	$admin_ajax = '';
	if ( defined( 'RADIO_PLAYER_AJAX_URL' ) ) {
		$admin_ajax = RADIO_PLAYER_AJAX_URL;
	} elseif ( function_exists( 'admin_url' ) ) {
		$admin_ajax = admin_url( 'admin-ajax.php' );
	}

	// --- set save interval ---
	$save_interval = 60;
	if ( defined( 'RADIO_PLAYER_SAVE_INTERVAL' ) ) {
		$save_interval = RADIO_PLAYER_SAVE_INTERVAL;
	} elseif ( function_exists( 'apply_filters' ) ) {
		apply_filters( 'radio_station_player_save_interval', $save_interval );
	}
	$save_interval = abs( intval( $save_interval ) );
	if ( $save_interval < 1 ) {$save_interval = 60;}

	// --- set jPlayer Flash path ---
	if ( defined( 'RADIO_PLAYER_URL' ) ) {
		$swf_path = RADIO_PLAYER_URL . 'js';
	} elseif ( function_exists( 'plugins_url' ) ) {
		if ( defined( 'RADIO_STATION_FILE' ) ) {
			$swf_path = plugins_url( 'player/js', RADIO_STATION_FILE );
		} else {
			$swf_path = plugins_url( 'js', __FILE__ );
		}
	} elseif ( function_exists( 'apply_filters' ) ) {
		$swf_path = apply_filters( 'radio_station_player_jplayer_swf_path', '' );
	} else {
		// TODO: check fallback to SWF (URL) relative path js/ ?
		$swf_path = '';
	}

	// --- set default stream settings ---
	$player_script = radio_station_player_get_default_script();
	if ( function_exists( 'radio_station_get_setting' ) ) {

		// --- get player settings ---
		$player_script = radio_station_get_setting( 'player_script' );
		$player_title = radio_station_get_setting( 'player_title' );
		$player_image = radio_station_get_setting( 'player_image' );
		$player_volume = radio_station_get_setting( 'player_volume' );
		$player_single = radio_station_get_setting( 'player_single' );

	} else {

		// --- get player settings ---
		$player_title = '';
		$player_image = '';
		$player_volume = 77;
		$player_single = true;

		if ( function_exists( 'apply_filters' ) ) {
			$player_script = apply_fitlers( 'radio_station_player_script', $player_script );
			$player_title = apply_filters( 'radio_station_player_title', $player_title );
			$player_image = apply_filters( 'radio_station_player_image', $player_image );
			$player_volume = abs( intval( apply_filters( 'radio_station_player_volume', $player_volume ) ) );
			$player_single = apply_filters( 'radio_station_player_single', $player_single );
		}
	}
	
	// 2.4.0.3: move constant checks out
	if ( defined( 'RADIO_PLAYER_SCRIPT' ) ) {
		$player_script = RADIO_PLAYER_SCRIPT;
	}
	if ( defined( 'RADIO_PLAYER_TITLE' ) ) {
		$player_title = RADIO_PLAYER_TITLE;
	}
	if ( defined( 'RADIO_PLAYER_IMAGE' ) ) {
		$player_image = RADIO_PLAYER_IMAGE;
	}
	if ( defined( 'RADIO_PLAYER_VOLUME' ) ) {
		$player_volume = abs( intval( RADIO_PLAYER_VOLUME ) );
	}
	if ( defined( 'RADIO_PLAYER_SINGLE' ) ) {
		$player_single = RADIO_PLAYER_SINGLE;
	}

	// --- set script suffix ---
	// $suffix = '.min';
	// if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
	// 	$suffix = '';
	// }

	// --- convert player behaviour settings to boolean string ---
	$player_single = $player_single ? 'true' : 'false';

	// --- set radio player settings ---
	$js .= "player_settings = {";
		$js .= "'ajaxurl': '" . esc_url( $admin_ajax ) . "', ";
		$js .= "'saveinterval':" . esc_js( $save_interval ) . ", ";
		$js .= "'swf_path': '" . esc_url( $swf_path ) . "', ";
		$js .= "'script': '" . esc_js( $player_script ). "', ";
		$js .= "'title': '" . esc_js( $player_title ) . "', ";
		$js .= "'image': '" . esc_url( $player_image ) . "', ";
		$js .= "'singular': " . esc_js( $player_single ) . ", ";
		// $js .= "'suffix': '" . esc_js( $suffix ) . "', ";
	$js .= "}" . PHP_EOL;

	// --- maybe limit available scripts for testing purposes ---
	$valid_scripts = array( 'amplitude', 'howler', 'jplayer' );
	// 2.4.0.3: set single script override only
	if ( isset( $_REQUEST['player-script'] ) && in_array( $_REQUST['player-script'], $valid_scripts ) ) {
		// 2.4.0.3: only allow admin to override script for testing purposes
		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			$player_script = $_REQUEST['player-script'];
		}
	}
	$scripts = array( $player_script );

	// --- set script URL ouput ---
	// 2.4.0.3: check for fallback script settings
	$fallbacks = array( 'jplayer', 'howler', 'amplitude' );
	if ( function_exists( 'radio_station_get_setting' ) ) {
		$fallbacks = radio_station_get_setting( 'player_fallbacks' );
	} elseif ( function_exists( 'apply_filters' ) ) {
		$fallbacks = apply_filters( 'radio_station_player_fallbacks', $fallbacks );
	}
	if ( defined( 'RADIO_PLAYER_FALLBACKS' ) ) {
		$fallbacks = explode( ',', RADIO_PLAYER_FALLBACKS );
	}
	// 2.4.0.3: allow for admin-only fallback script override
	if ( isset( $_REQUEST['fallback-scripts'] ) ) {
		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			$fallback_scripts = explode( ',', $_REQUEST['fallback-scripts'] );
			if ( count( $fallback_scripts ) > 0 ) {
				foreach ( $fallback_scripts as $i => $fallback_script ) {
					if ( !in_array( $fallback_script, $valid_scripts ) ) {
						unset( $fallback_scripts[$i] );
					}
				}
			}
			if ( count( $fallback_scripts ) > 0 ) {
				$fallbacks = $fallback_scripts;
			}
		}
	}
	
	// 2.4.0.3: merge fallbacks with current script
	if ( is_array( $fallbacks ) && ( count( $fallbacks ) > 0 ) ) {
		$scripts = array_merge( $scripts, $fallbacks );
	}
	$js .= "scripts = {";
		if ( in_array( 'amplitude', $scripts ) ) {
			$js .= "'amplitude': '" . $radio_player['amplitude_script']['url'] . '?version=' . $radio_player['amplitude_script']['version'] . "', ";
		}
		if ( in_array( 'howler', $scripts ) ) {
			$js .= "'howler': '" . $radio_player['howler_script']['url'] . '?version=' . $radio_player['howler_script']['version'] . "', ";
		}
		if ( in_array( 'jplayer', $scripts ) ) {
			$js .= "'jplayer': '" . $radio_player['jplayer_script']['url'] . '?version=' . $radio_player['jplayer_script']['version'] . "', ";
		}
		// $js .= "'media': '" . $radio_player['media_script']['url'] . '?version=' . $radio_player['media_script']['version'] . "', "
		// $js .= "'elements': '" . $radio_player['elements_script']['url'] . '?version=' . $radio_player['elements_script']['version'] . "', ";
	$js .= "}" . PHP_EOL;

	// --- set player script supported formats ---
	// TODO: recheck supported amplitude formats ?
	// [JPlayer] Audio: mp3, m4a - Video: m4v
	// +Audio: webma, oga, wav, fla, rtmpa +Video: webmv, ogv, flv, rtmpv
	// [Howler] mp3, opus, ogg, wav, aac, m4a, mp4, webm
	// +mpeg, oga, caf, weba, webm, dolby, flac
	// [Amplitude] HTML5 Support - mp3, aac ...?
	// ref: https://en.wikipedia.org/wiki/HTML5_audio#Supporting_browsers
	// [Media Elements] Audio: mp3, wma, wav +Video: mp4, ogg, webm, wmv
	$js .= "formats = {";
		$js .= "'howler': ['mp3','opus','ogg','oga','wav','aac','m4a','mp4','webm','weba','flac'], ";
		$js .= "'jplayer': ['mp3','m4a','webm','oga','rtmpa','wav','flac'], ";
		$js .= "'amplitude': ['mp3','aac'], ";
		// $js .= "'mediaelements': ['mp3','wma','wav'], ";
	$js .= "}" . PHP_EOL;

	// --- set debug mode ---
	$debug = false; 
	if ( function_exists( 'radio_station_get_setting' ) ) {
		$debug = radio_station_get_setting( 'player_debug' );
	} elseif ( function_exists( 'apply_filters' ) ) {
		$debug = apply_filters( 'radio_station_player_debug', $debug );
	}
	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		$debug = true;
	}
	if ( defined( 'RADIO_PLAYER_DEBUG' ) ) {
		$debug = RADIO_PLAYER_DEBUG;
	}
	if ( $debug ) {
		$debug = 'true';
	} else {
		$debug = 'false';
	}

	// --- set radio player settings and radio data objects ---
	// (with empty arrays for instances, script types, failbacks, audio targets and stream data)
	$js .= "var radio_player = {settings:player_settings, scripts:scripts, formats:formats, loading:false, debug:" . esc_js( $debug ) . "}" . PHP_EOL;
	$js .= "var radio_data = {state:{}, players:[], scripts:[], failed:[], data:[], types:[], channels:[], faders:[]}" . PHP_EOL;

	// --- logged in / user state settings ---
	$loggedin = 'false';
	if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
		$loggedin = 'true';
		$user_id = get_current_user_id();
		$state = get_user_meta( $user_id, 'radio_player_state', true );
	}
	$js .= "radio_data.state.loggedin = " . esc_js( $loggedin ) . ";" . PHP_EOL;

	// ---- maybe set play state ---
	$playing = 'false';
	if ( isset( $state['playing'] ) && $state['playing'] ) {
		$playing = 'true';
	}
	$js .= "radio_data.state.playing = " . esc_js( $playing ) . "; " . PHP_EOL;

	// --- maybe set station ID ---
	if ( isset( $state ) && isset( $state['station'] ) ) {
		$station = abs( intval( $state['station'] ) );
	}
	if ( isset( $station ) && $station && ( $station > 0 ) ) {
		$js .= "radio_data.state.station = " . esc_js( $station ) . ";" . PHP_EOL;
	} else {
		$js .= "radio_data.state.station = 0;" . PHP_EOL;
	}

	// --- maybe set user volume ---
	// note: default already set above
	if ( isset( $state ) && isset( $state['volume'] ) ) {
		$player_volume = abs( intval( $state['volume'] ) );
	}
	$js .= "radio_data.state.volume = " . esc_js( $player_volume ) . "; " . PHP_EOL;

	// --- maybe set user mute ---
	$player_mute = 'false';
	if ( isset( $state ) && isset( $state['mute'] ) && ( $state['mute'] ) ) {
		$player_mute = 'true';
	}
	$js .= "radio_data.state.mute = " . esc_js( $player_mute ) . "; " . PHP_EOL;

	// --- set main radio stream data ---
	$js .= "radio_data.state.data = {};" . PHP_EOL;
	if ( function_exists( 'apply_filters' ) ) {
		$station = ( isset( $state['station'] ) ) ? $state['station'] : 0;
		// note: this is the main stream data filter hooked into by Radio Station plugin
		// 2.4.0.3: fix for uninitialized string offset
		$data = apply_filters( 'radio_station_player_data', false, $station );
	}
	if ( $data && is_array( $data ) ) {
		foreach ( $data as $key => $value ) {
			$js .= "radio_data.state.data['" . $key . "'] = '" . $value . "';" . PHP_EOL;
		}
	}
	$js .= "radio_player.stream_data = radio_data.state.data;" . PHP_EOL;

	// --- attempt to set player state from cookies ---
	$js .= "var radio_player_state_loaded = false;
	var radio_player_state_loader = setInterval(function() {
		if (!radio_player_state_loaded && (typeof radio_player_load_state != 'undefined')) {
			radio_player_load_state(); radio_player_state_loaded = true;
			radio_player_custom_event('rp-state-loaded', false);			
			clearInterval(radio_player_state_loader);
		}
	}, 1000);" . PHP_EOL;

	/* --- periodic save to user meta --- */
	$js .= "rp_save_interval = radio_player.settings.saveinterval * 1000;
	var radio_player_state_saver = setInterval(function() {
		if (typeof radio_data.state != 'undefined') {
			if (!radio_data.state.loggedin) {clearInterval(radio_player_state_saver); return;}
			radio_player_save_user_state();
		}
	}, rp_save_interval);" . PHP_EOL;

	return $js;
}

// -----------------
// User State Iframe
// -----------------
// note: only triggered for WordPress logged in users
function radio_station_player_iframe() {
	// echo '<span style="display:none;">FRAME TEST</span>';
	if ( function_exists( 'is_user_logged_in') && is_user_logged_in() ) {
		echo "<iframe src='about:blank' id='radio-player-state-iframe' name='radio-player-state-iframe' style='display:none;'></iframe>";
	}
}

// ----------------------
// AJAX Update User State
// ----------------------
// note: only triggered for WordPress logged in users
if ( function_exists( 'add_action' ) ) {
	add_action( 'wp_ajax_radio_station_player_state', 'radio_station_player_state' );
	// note: non-logged in user action still added to prevent 400 bad request
	add_action( 'wp_ajax_nopriv_radio_station_player_state', 'radio_station_player_state' );
}
function radio_station_player_state() {

	// --- reset saving state in parent frame ---
	echo "<script>parent.radio_data.state.saving = false;</script>";

	if ( !function_exists( 'get_current_user_id' ) || !function_exists( 'update_user_meta' ) ) {
		exit;
	}

	// --- get current user ID ---
	$user_id = get_current_user_id();
	if ( 0 == $user_id ) {exit;}

	// --- get user state values ---
	$playing = $_REQUEST['playing'];
	$volume = $_REQUEST['volume'];
	$station = $_REQUEST['station'];
	$mute = $_REQUEST['mute'];

	// --- sanitize user state values ---
	if ( $playing ) {$playing = true;} else {$playing = false;}
	$volume = abs( intval( $volume ) );
	if ( $volume < 1 ) {$volume = false;} elseif ( $volume > 100 ) {$volume = 100;}
	$station = abs( intval( $station ) );
	if ( $station < 1 ) {$station = false;}
	if ( $mute ) {$mute = true;} else {$mute = false;}

	// --- save player state to user meta ---
	$state = array(
		'playing'	=> $playing,
		'volume'	=> $volume,
		'station'	=> $station,
		'mute'		=> $mute,
	);
	update_user_meta( $user_id, 'radio_player_state', $state );
	exit;
}

// -----------------------
// Load Amplitude Function
// -----------------------
// "mp3" "aac" ... (+HTML5 Browser Supported Sources)
function radio_station_player_script_amplitude() {

	// --- load amplitude player ---
	$js = "function radio_player_amplitude(instance, url, format, fallback, fformat) {

		player_id = 'radio_player_'+instance;
		container_id = 'radio_container_'+instance;
		if (url == '') {url = radio_player.settings.url;}
		if (url == '') {return;}
		if (!format || (format == '')) {format = 'aac';}
		if (fallback == '') {fallback = radio_player.settings.fallback;}
		if (!fallback || !fformat || (fformat == '')) {fallback = ''; fformat = '';}

		/* check if already loaded with same source 
		if ( (typeof radio_data.scripts[instance] != 'undefined') && (radio_data.scripts[instance] == 'amplitude')
		  && (typeof radio_player.previous_data != 'undefined') ) {
			pdata = radio_player.previous_data;
			if ( (pdata.url == url) && (pdata.format == format) && (pdata.fallback == fallback) && (pdata.fformat == fformat) ) {
				if (radio_player.debug) {console.log('Player already loaded with this stream data.');}
				return radio_data.players[instance];
			}
		} */

		/* set song streams */
		songs = new Array();
		songs[0] = {'name': '',	'artist': '', 'album': '', 'url': url, 'cover_art_url': '',	'live': true};
		/* if ('' != fallback) {songs[1] = {'name': '', 'artist': '', 'album': '', 'url': fallback, 'cover_art_url': '', 'live': true};} */

		/* set volume */
		if (jQuery('#'+container_id+' .rp-volume-slider').hasClass('changed')) {
			volume = jQuery('#'+container_id+' .rp-volume-slider').val();
		} else if (typeof radio_data.state.volume != 'undefined') {volume = radio_data.state.volume;}
		else {volume = radio_player.settings.volume;}
		radio_player_volume_slider(instance, volume);
		if (radio_player.debug) {console.log('Amplitude init Volume: '+volume);}

		/* initialize player */
		if (radio_player.debug) {console.log('Init Amplitude: '+instance+' : '+url+' : '+format+' : '+fallback+' : '+fformat);}
		radio_player_instance = Amplitude;
		radio_player_instance.init({
			'songs': songs,
			'volume': volume,
			'volume_increment': 5,
			'volume_decrement': 5,
			'continue_next': false,
			'preload': 'none',
			'callbacks': {
				/* bug: initialized callback is not being triggered
				'initialized': function(e) {
					radio_player.loading = false;
					instance = radio_player_event_instance(e, 'Loaded', 'amplitude');
					radio_player_event_handler('loaded', {instance:instance, script:'amplitude'});

					channel = radio_data.channels[instance];
					if (channel) {radio_player_set_state('channel', channel);}
					station = jQuery('#radio_player_'+instance).attr('station-id');
					if (station) {radio_player_set_state('station', station);}
				}, */
				/* bug: play callback event is not being triggered 
				'play': function(e) {
					radio_player.loading = false;
					instance = radio_player_event_instance(e, 'Playing', 'amplitude');
					radio_player_event_handler('playing', {instance:instance, script:'amplitude'});
					radio_player_pause_others(instance);
				}, */
				'pause': function(e) {
					instance = radio_player_event_instance(e, 'Paused', 'amplitude');
					radio_player_event_handler('paused', {instance:instance, script:'amplitude'});
				},
				'stop': function(e) {
					instance = radio_player_event_instance(e, 'Stopped', 'amplitude');
					radio_player_event_handler('stopped', {instance:instance, script:'amplitude'});
				},
				'volumechange': function(e) {
					instance = radio_player_event_instance(e, 'Volume', 'amplitude');
					if (instance && (radio_data.scripts[instance] == 'amplitude')) {
						volume = radio_data.players[instance].getConfig().volume;
						radio_player_player_volume(instance, 'amplitude', volume);
					}
				},
				/* bug: no event is being passed in callback to get instance
				'error': function(e) {
					instance = radio_player_event_instance(e, 'Error', 'amplitude');
					if (radio_player.debug) {console.log(e);}
					radio_player_event_handler('error', {instance:instance, script:'amplitude'});
					radio_player_player_fallback(instance, 'amplitude', 'Amplitude Error');
				}, */
			}
		});
		radio_data.players[instance] = radio_player_instance;
		radio_data.scripts[instance] = 'amplitude';

		/* set instance on audio source */
		audio = radio_player_instance.getAudio();
		if (radio_player.debug) {console.log('Amplitude Audio Element:'); console.log(audio);}
		audio.setAttribute('instance-id', instance);

		/* bind loaded to canplay event (as initialized callback not firing!) */
		audio.addEventListener('canplay', function(e) {
			radio_player.loading = false;
			instance = radio_player_event_instance(e, 'Loaded', 'amplitude');
			radio_player_event_handler('loaded', {instance:instance, script:'amplitude'});

			channel = radio_data.channels[instance];
			if (channel) {radio_player_set_state('channel', channel);}
			station = jQuery('#radio_player_'+instance).attr('station-id');
			if (station) {radio_player_set_state('station', station);}
		}, false);
	
		/* bind play(ing) event (as play callback not firing!) */
		audio.addEventListener('playing', function(e) {
			radio_player.loading = false;
			instance = radio_player_event_instance(e, 'Playing', 'amplitude');
			radio_player_event_handler('playing', {instance:instance, script:'amplitude'});
			radio_player_pause_others(instance);
		}, false);

		/* bind pause and stop events */
		 audio.addEventListener('pause', function(e) {
			instance = radio_player_event_instance(e, 'Paused', 'amplitude');
			radio_player_event_handler('paused', {instance:instance, script:'amplitude'});
		}, false);
		/* audio.addEventListener('stop', function(e) {}, false); */
		/* audio.addEventListener('volumechange', function(e) {}, false); */
		
		/* bind error event (as event not being passed in callback) */
		audio.addEventListener('error', function(e) {
			instance = radio_player_event_instance(e, 'Error', 'amplitude');
			if (radio_player.debug) {console.log(e);}
			radio_player_event_handler('error', {instance:instance, script:'amplitude'});
			radio_player_player_fallback(instance, 'amplitude', 'Amplitude Error');
		}, false);

		/* match script select dropdown value */
		if (jQuery('#'+container_id+' .rp-script-select').length) {
			jQuery('#'+container_id+' .rp-script-select').val('amplitude');
		}

		return radio_player_instance;
	}";

	// TODO: maybe set continue_next to true to use for fallback URL ?
	// TODO: recheck repeat off setting: 'repeat': false, ?

	/* ref: https://521dimensions.com/open-source/amplitudejs/docs/configuration/
	'station_art_url': stationartwork,
	'default_album_art': defaultartwork,
	'soundcloud_client': soundcloudkey,
	'debug': debug,
	'start_song': currentindex,
	'dynamic_mode': dynamic,
	'use_visualizations': visualizations,
	'visualization_backup': 'nothing',
	*/

	// --- filter and return ---
	if ( function_exists( 'apply_filters' ) ) {
		$js = apply_filters( 'radio_station_player_script_amplitude', $js );
	}
	return $js;
}

// --------------------
// Load Howler Function
// --------------------
// Howler Note: "A live stream can only be played through HTML5 Audio."
// "mp3", "opus", "ogg", "wav", "aac", "m4a", "mp4", "webm"
function radio_station_player_script_howler() {

	// --- load howler player ---
	$js = "function radio_player_howler(instance, url, format, fallback, fformat) {

		player_id = 'radio_player_'+instance;
		container_id = 'radio_container_'+instance;
		if (url == '') {url = radio_player.settings.url;}
		if (url == '') {return;}
		if (!format || (format == '')) {format = 'aac';}
		if (fallback == '') {fallback = radio_player.settings.fallback;}
		if (!fallback || !fformat || (fformat == '')) {fallback = ''; fformat = '';}

		/* check if already loaded with same source
		if ( (typeof radio_data.scripts[instance] != 'undefined') && (radio_data.scripts[instance] == 'howler')
		  && (typeof radio_player.previous_data != 'undefined') ) {
			pdata = radio_player.previous_data;
			if ( (pdata.url == url) && (pdata.format == format) && (pdata.fallback == fallback) && (pdata.fformat == fformat) ) {
				if (radio_player.debug) {console.log('Player already loaded with this stream data.');}
				return radio_data.players[instance];
			}
		} */

		/* set sources */
		sources = new Array(); formats = new Array();
		sources[0] = url; /* if (fallback != '') {sources[1] = fallback;} */
		formats[0] = format; /* if ((fallback != '') && (fformat != '')) {formats[1] = fformat;} */

		/* set volume */
		if (jQuery('#'+container_id+' .rp-volume-slider').hasClass('changed')) {
			volume = jQuery('#'+container_id+' .rp-volume-slider').val();
		} else if (typeof radio_data.state.volume != 'undefined') {volume = radio_data.state.volume;}
		else {volume = radio_player.settings.volume;}
		radio_player_volume_slider(instance, volume);
		volume = parseFloat(volume / 100);
		if (radio_player.debug) {console.log('Howler init Volume: '+volume);}

		/* intialize player */
		if (radio_player.debug) {console.log('Init Howler: '+instance+' : '+url+' : '+format+' : '+fallback+' : '+fformat);}
		radio_player_instance = new Howl({
			src: sources,
			format: formats,
			html5: true,
			autoplay: false,
			preload: false,
			volume: volume,
			onload: function(e) {
				/* possible bug: maybe not always being triggered ? */
				radio_player.loading = false;
				instance = radio_player_match_instance(this, e, 'howler');
				radio_player_event_handler('loaded', {instance:instance, script:'howler'});

				channel = radio_data.channels[instance];
				if (channel) {radio_player_set_state('channel', channel);}
				station = jQuery('#radio_player_'+instance).attr('station-id');
				if (station) {radio_player_set_state('station', station);}
			},
			onplay: function(e) {
				radio_player.loading = false;
				instance = radio_player_match_instance(this, e, 'howler');
				radio_player_event_handler('playing', {instance:instance, script:'howler'});
				radio_player_pause_others(instance);
			},
			onpause: function(e) {
				instance = radio_player_match_instance(this, e, 'howler');
				radio_player_event_handler('paused', {instance:instance, script:'howler'});
			},
			onstop: function(e) {
				instance = radio_player_match_instance(this, e, 'howler');
				radio_player_event_handler('stopped', {instance:instance, script:'howler'});
			},
			onvolume: function(e) {
				instance = radio_player_match_instance(this, e, 'howler');
				if (instance && (radio_data.scripts[instance] == 'howler')) {
					volume = this.volume() * 100;
					if (volume > 100) {volume = 100;}
					radio_player_player_volume(instance, 'howler', volume);
				}
			},
			onloaderror: function(id,e) {
				instance = radio_player_match_instance(this, e, 'howler');
				radio_player_event_handler('error', {instance:instance, script:'howler'});
				if (radio_player.debug) {console.log('Load Error, Howler Instance: '+instance+', Sound ID: '+id);}
				radio_player_player_fallback(instance, 'howler', 'Howler Load Error');
			},
			onplayerror: function(id,e) {
				instance = radio_player_match_instance(this, e, 'howler');
				radio_player_event_handler('error', {instance:instance, script:'howler'});
				if (radio_player.debug) {console.log('Play Error, Howler Instance: '+instance+', Sound ID: '+id);}
				radio_player_player_fallback(instance, 'howler', 'Howler Play Error');
			},
		});
		radio_data.players[instance] = radio_player_instance;
		radio_data.scripts[instance] = 'howler';

		/* match script select dropdown value */
		if (jQuery('#'+container_id+' .rp-script-select').length) {
			jQuery('#'+container_id+' .rp-script-select').val('howler');
		}

		return radio_player_instance;
	}";

	// --- filter and return ---
	if ( function_exists( 'apply_filters' ) ) {
		$js = apply_filters( 'radio_station_player_script_howler', $js );
	}
	return $js;
}

// ---------------------
// Load JPlayer Function
// ---------------------
// ref: http://www.jplayer.org/latest/developer-guide/
// Audio: mp3 / m4a, Video: m4v
// Extra formats:
// audio: webma, oga, wav, fla, rtmpa
// video: webmv, ogv, flv, rtmpv
function radio_station_player_script_jplayer() {

	// --- load jplayer ---
	$js = "function radio_player_jplayer(instance, url, format, fallback, fformat) {

		player_id = 'radio_player_'+instance;
		container_id = 'radio_container_'+instance;
		if (url == '') {url = radio_player.settings.url;}
		if (url == '') {return;}
		if (!format || (format == '') || (format == 'aac')) {format = 'm4a';}
		if (fallback == '') {fallback = radio_player.settings.fallback;}
		if (!fallback || !fformat || (fformat == '')) {fallback = ''; fformat = '';}
		if (fformat == 'aac') {fformat = 'm4a';}

		/* check if already loaded with same source
		if ( (typeof radio_data.scripts[instance] != 'undefined') && (radio_data.scripts[instance] == 'jplayer')
		  && (typeof radio_player.previous_data != 'undefined') ) {
			pdata = radio_player.previous_data;
			if ( (pdata.url == url) && (pdata.format == format) && (pdata.fallback == fallback) && (pdata.fformat == fformat) ) {
				if (radio_player.debug) {console.log('Player already loaded with this stream data.');}
				return radio_data.players[instance];
			}
		} */

		/* set volume */
		if (jQuery('#'+container_id+' .rp-volume-slider').hasClass('changed')) {
			volume = jQuery('#'+container_id+' .rp-volume-slider').val();
		} else if (typeof radio_data.state.volume != 'undefined') {volume = radio_data.state.volume;}
		else {volume = radio_player.settings.volume;}
		radio_player_volume_slider(instance, volume);
		volume = parseFloat(volume / 100);
		if (radio_player.debug) {console.log('jPlayer init Volume: '+volume);}

		media = {}; /* media.title = ''; */ media[format] = url; supplied = format;
		/* if (fallback && fformat) {media[fformat] = fallback; supplied += ', '+fformat;} */
		radio_player.jplayer_media = media;
		console.log(radio_player.jplayer_media);
		radio_player.jplayer_ready = false;

		/* load jplayer */
		if (radio_player.debug) {console.log('Init jPlayer: '+instance+' : '+url+' : '+format+' : '+fallback+' : '+fformat);}
		radio_player_instance = jQuery('#'+player_id).jPlayer({
			ready: function () {
				console.log('jPlayer Ready.');
				console.log(radio_player.jplayer_media);
				jQuery(this).jPlayer('setMedia', radio_player.jplayer_media);
				radio_player.jplayer_ready = true;
			},
			supplied: supplied,
			cssSelectorAncestor: '#'+container_id,
			swfPath: radio_player.settings.swf_path,
			idPrefix: 'rp',
			preload: 'none',
			volume: volume,
			globalVolume: true,
			useStateClassSkin: true,
			autoBlur: false,
			smoothPlayBar: true,
			keyEnabled: true,
			remainingDuration: false,
			toggleDuration: false,
			backgroundColor: 'transparent',
			/* cssSelector: {
				videoPlay: '.rp-video-play',
				play: '.rp-play',
				pause: '.rp-pause',
				stop: '.rp-stop',
				seekBar: '.rp-seek-bar',
				playBar: '.rp-play-bar',
				mute: '.rp-mute',
				unmute: '.rp-unmute',
				volumeBar: '.rp-volume-bar',
				volumeBarValue: '.rp-volume-bar-value',
				volumeMax: '.rp-volume-max',
				playbackRateBar: '.rp-playback-rate-bar',
				playbackRateBarValue: '.rp-playback-rate-bar-value',
				currentTime: '.rp-current-time',
				duration: '.rp-duration',
				title: '.rp-title',
				fullScreen: '.rp-full-screen',
				restoreScreen: '.rp-restore-screen',
				repeat: '.rp-repeat',
				repeatOff: '.rp-repeat-off',
				gui: '.rp-gui',
				noSolution: '.rp-no-solution'
			},
			stateClass: {
			  playing: 'rp-state-playing',
			  seeking: 'rp-state-seeking',
			  muted: 'rp-state-muted',
			  looped: 'rp-state-looped',
			  fullScreen: 'rp-state-full-screen',
			  noVolume: 'rp-state-no-volume'
			}, */
		});
		radio_data.players[instance] = radio_player_instance;
		radio_data.scripts[instance] = 'jplayer';

		/* bind load event */
		jQuery('#'+player_id).bind(jQuery.jPlayer.event.load, function(e) {
			radio_player.loading = false;
			instance = radio_player_event_instance(e, 'Loaded', 'jplayer');
			radio_player_event_handler('loaded', {instance:instance, script:'jplayer'});

			channel = radio_data.channels[instance];
			if (channel) {radio_player_set_state('channel', channel);}
			/* station = jQuery('#radio_player_'+instance).attr('station-id');
			if (station) {radio_player_set_state('station', station);} */
		});

		/* bind play event */
		jQuery('#'+player_id).bind(jQuery.jPlayer.event.play, function(e) {
			radio_player.loading = false;
			instance = radio_player_event_instance(e, 'Playing', 'jplayer');
			radio_player_event_handler('playing', {instance:instance, script:'jplayer'});
			radio_player_pause_others(instance);
		});

		/* bind pause and stop events */
		jQuery('#'+player_id).bind(jQuery.jPlayer.event.pause, function(e) {
			instance = radio_player_event_instance(e, 'Paused', 'jplayer');
			radio_player_event_handler('paused', {instance:instance, script:'jplayer'});
		});
		jQuery('#'+player_id).bind(jQuery.jPlayer.event.stop, function(e) {
			instance = radio_player_event_instance(e, 'Stopped', 'jplayer');
			radio_player_event_handler('stopped', {instance:instance, script:'jplayer'});
		});

		/* bind volume change event */
		jQuery('#'+player_id).bind(jQuery.jPlayer.event.volumechange, function(e) {
			instance = radio_player_event_instance(e, 'Volume', 'jplayer');
			if (instance && (radio_data.scripts[instance] == 'jplayer')) {
				radio_player_player_volume(instance, 'jplayer', volume);
			}
		});

		/* bind can play debug message */
		jQuery('#'+player_id).bind(jQuery.jPlayer.event.canplay, function(e) {
			instance = radio_player_event_instance(e, 'CanPlay', 'jplayer');
			console.log('jPlayer Instance '+instance+' Can Play');
		});

		/* bind player error event to fallback scripts */
		jQuery('#'+player_id).bind(jQuery.jPlayer.event.error, function(e) {
			radio_player.jplayer_ready = false;
			instance = radio_player_event_instance(e, 'Error', 'jplayer');
			radio_player_event_handler('error', {instance:instance, script:'jplayer'});
			radio_player_player_fallback(instance, 'jplayer', 'jPlayer Error');
		});

		/* match script select dropdown value */
		if (jQuery('#'+container_id+' .rp-script-select').length) {
			jQuery('#'+container_id+' .rp-script-select').val('jplayer');
		}

		return radio_player_instance;
    }";

	// --- filter and return ---
	if ( function_exists( 'apply_filters' ) ) {
		$js = apply_filters( 'radio_station_player_script_jplayer', $js );
	}
	return $js;
}

// ---------------------------
// Load Media Element Function
// ---------------------------
// Usage ref: https://github.com/mediaelement/mediaelement/blob/master/docs/usage.md
// API ref: https://github.com/mediaelement/mediaelement/blob/master/docs/api.md
// Audio support: MP3, WMA, WAV
// Video support: MP4, Ogg, WebM, WMV
function radio_station_player_script_mediaelements() {

	// --- load media elements ---
	$js = "function radio_player_mediaelements(instance, url, format, fallback, fformat) {

		if (url == '') {url = radio_player.settings.url;}
		if (!format || (format == '')) {format = 'mp3';}
		if (fallback == '') {fallback = radio_player.settings.fallback;}
		if (!fallback || !fformat || (fformat == '')) {fallback = ''; fformat = '';}

		radio_data.scripts[instance] = 'mediaelements';
		player_id = 'radio_player_'+instance;
		container_id = 'radio_container_'+instance;

		/* load media element player */
		/* TODO: only initialize on new media elements? */
		radio_player_instance = jQuery('#'+player_id).mediaelementplayer(rpSettings);
		radio_data.players[instance] = radio_player_instance;

		// radio_player.instance = jQuery('.rp-audio').not('.rp-container').filter(function () {
		// 	return !jQuery(this).parent().hasClass('rp-mediaelement');	})
		// .mediaelementplayer(rpSettings);

		/* TODO: bind to play, stop/pause and volumechange events */

		/* bind to play event */
		/* 'play' */
		/* TODO: get this player instance ID
		/* TODO: maybe pause existing player */
		/* if (typeof window.top.current_radio == 'object') {
			radio_player_pause_current();
		} */
		/* TODO: send pause message to other windows */
		/* radio_player_pause_others(instance); */

		/* bind to pause event */
		/* 'pause' */

		/* bind to stop event */
		/* 'ended' */

		/* bind to volume change event */
		/* 'volumechange'
		/* volume = radio_player.instance.getVolume(); */

		/* TODO: bind cannot play event to fallback format? */
	}";

	// ? add media element events ?
	// ref: https://stackoverflow.com/questions/23114963/mediaelement-js-trigger-event-if-some-specific-audio-file-has-ended

    /* maybe add container class ? */
    /* ref: https://www.cedaro.com/blog/customizing-mediaelement-wordpress/
	/* (function() {
		var settings = window._wpmejsSettings || {};
		settings.features = settings.features || mejs.MepDefaults.features;
		settings.features.push( 'exampleclass' );

		MediaElementPlayer.prototype.buildexampleclass = function( player ) {
			player.container.addClass( 'example-mejs-container' );
		};
	})(); */

	// --- filter and return ---
	if ( function_exists( 'apply_filters' ) ) {
		$js = apply_filters( 'radio_station_player_script_mediaelements', $js );
	}
	return $js;
}

// -------------------------
// Get Default Player Script
// -------------------------
function radio_station_player_get_default_script() {

	$script = 'amplitude'; // default
	if ( defined( 'RADIO_PLAYER_SCRIPT' ) ) {
		$script = RADIO_PLAYER_SCRIPT;
	} elseif ( function_exists( 'radio_station_get_setting' ) ) {
		$script = radio_station_get_setting( 'player_script' );
	}
	if ( function_exists( 'apply_filters' ) ) {
		$script = apply_filters( 'radio_station_player_script', $script );
	}
	if ( defined( 'RADIO_PLAYER_FORCE_SCRIPT' ) ) {
		$script = RADIO_PLAYER_FORCE_SCRIPT;
	}
	return $script;
}

// ---------------------
// Enqueue Player Styles
// ---------------------
function radio_station_player_enqueue_styles( $script = false, $skin = false ) {

	$suffix = '.min';
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$suffix = '';
	}

	// --- get default if not passed by shortcode attribute ---
	if ( !$script ) {
		radio_station_player_get_default_script();
	}

	// --- get default if not passed by shortcode attribute ---
	/* if ( !$skin ) {

		// --- get skin settings ---
		$skin = 'blue-monday'; // default
		if ( defined( 'RADIO_PLAYER_SKIN' ) ) {
			$skin = RADIO_PLAYER_SKIN;
		}
		if ( function_exists( 'radio_station_get_setting' ) ) {
			$skin = radio_station_get_setting( 'player_skin' );
		} elseif ( function_exists( 'apply_filters' ) ) {
			$skin = apply_filters( 'radio_station_player_skin', $skin );
		}
		if ( defined( 'RADIO_PLAYER_FORCE_SKIN' ) ) {
			$skin = RADIO_PLAYER_FORCE_SKIN;
		}
	} */

	// --- debug script / skin used ---
	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
		echo '<span style="display:none;">Script: ' . $script . ' - Skin: ' . $skin . '</span>';
	}

	// --- enqueue base player styles ---
	$suffix = ''; // DEV TEMP

	if ( defined( 'RADIO_STATION_DIR' ) ) {
		$path = RADIO_STATION_DIR . '/player/css/radio-player' . $suffix . '.css';
	} elseif ( defined( 'RADIO_PLAYER_DIR' ) ) {
		$path = RADIO_PLAYER_DIR . '/css/radio-player' . $suffix . '.css';
	} else {
		$path = dirname( __FILE__ ) . '/css/radio-player' . $suffix . '.css';
	}
	if ( defined( 'RADIO_PLAYER_DEBUG' ) && RADIO_PLAYER_DEBUG ) {
		echo '<span style="display:none;">Style Path: ' . $path . '</span>';
	}
	if ( file_exists( $path ) ) {
		$version = filemtime( $path );
		if ( function_exists( 'wp_enqueue_style' ) ) {
			if ( defined( 'RADIO_PLAYER_URL' ) ) {
				$url = RADIO_PLAYER_URL . 'css/radio-player' . $suffix. '.css';
			} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
				$url = plugins_url( 'player/css/radio-player' . $suffix . '.css', RADIO_STATION_FILE );
			} else {
				$url = plugins_url( 'css/radio-player.css', __FILE__ );
			}
			wp_enqueue_style( 'radio-player', $url, array(), $version, 'all' );

			// --- enqueue player control styles inline ---
			$control_styles = radio_station_player_control_styles( false );
			wp_add_inline_style( 'radio-player', $control_styles );

		} else {
			// --- output style tag directly ---
			$url = 'css/radio-player' . $suffix . '.css';
			if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
			radio_station_player_style_tag( 'radio-player', $url, $version );
		}

		// --- debug skin path / URL ---
		if ( isset( $_REQUEST['player-debug'] ) ) {
			echo '<span style="display:none;">Skin Path: ' . $path . '</span>';
			echo '<span style="display:none;">Skin URL: ' . $url . '</span>';
		}
		return;
	}

	// --- enqueue base jplayer styles ---
	$suffix = ''; // DEV TEMP

	if ( defined( 'RADIO_STATION_DIR' ) ) {
		$path = RADIO_STATION_DIR . '/player/css/jplayer' . $suffix . '.css';
	} else {
		$path = dirname( __FILE__ ) . '/css/jplayer' . $suffix . '.css';
	}
	if ( file_exists( $path ) ) {
		$version = filemtime( $path );
		if ( function_exists( 'wp_enqueue_style' ) ) {
			if ( defined( 'RADIO_PLAYER_URL' ) ) {
				$url = RADIO_PLAYER_URL . 'css/jplayer' . $suffix. '.css';
			} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
				$url = plugins_url( 'player/css/jplayer' . $suffix . '.css', RADIO_STATION_FILE );
			} else {
				$url = plugins_url( 'css/jplayer.css', __FILE__ );
			}
			wp_enqueue_style( 'rp-jplayer', $url, array(), $version, 'all' );
		} else {
			// --- output style tag directly ---
			$url = 'css/jplayer' . $suffix . '.css';
			if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
			radio_station_player_style_tag( 'rp-jplayer', $url, $version );
		}

		// --- debug skin path / URL ---
		if ( isset( $_REQUEST['player-debug'] ) ) {
			echo '<style="display:none;">Skin Path: ' . $path . '</span>';
			echo '<style="display:none;">Skin URL: ' . $url . '</span>';
		}
	}

	// --- JPlayer Skins ---
	// $skins = array( 'pink-flag', 'blue-monday' );
	// if ( in_array( $skin, $skins ) ) {

		// --- enqeueue player skin ---
		/* $skin_ref = '.' . str_replace( '-', '.', $skin );
		if ( defined( 'RADIO_STATION_DIR' ) ) {
			$path = RADIO_STATION_DIR . '/player/css/jplayer' . $skin_ref . $suffix . '.css';
		} else {
			$path = dirname( __FILE__ ) . '/css/jplayer' . $skin_ref . $suffix . '.css';
		}
		if ( file_exists( $path ) ) {
			$version = filemtime( $path );
			if ( function_exists( 'wp_enqueue_style' ) ) {
				if ( defined( 'RADIO_PLAYER_URL' ) ) {
					$url = RADIO_PLAYER_URL . 'css/jplayer' . $skin_ref . $suffix . '.css';
				} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
					$url = plugins_url( 'player/css/jplayer' . $skin_ref . $suffix . '.css', RADIO_STATION_FILE );
				} else {
					$url = plugins_url( 'css/jplayer' . $skin_ref . $suffix . '.css', __FILE__ );
				}
				// $deps = array();
				// if ( '' == $suffix ) {
					$deps = array( 'rp-jplayer' );
				// }
				wp_enqueue_style( 'rp-jplayer-' . $skin, $url, $deps, $version, 'all' );
			} else {
				// --- output style tag directly ---
				$url = 'css/jplayer' . $skin_ref . $suffix . '.css';
				if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
				radio_station_player_style_tag( 'rp-jplayer-skin', $url, $version );
			}

			// --- debug skin path / URL ---
			if ( isset( $_REQUEST['player-debug'] ) ) {
				echo '<span style="display:none;">Skin Path: ' . $path . '</span>';
				echo '<span style="display:none;">Skin URL: ' . $url . '</span>';
			}
		} */

	// }

	// --- Media Element Skins ---
	// (note: classes reprefixed to rp-)
	/* if ( 'mediaelements' == $script ) {

		$skins = array( 'wordpress', 'minimal' );
		if ( !in_array( $skin, $skins ) ) {
			$skin = 'wordpress';
		}
		if ( 'wordpress' == $skin ) {

			// --- WordPress Default ---
			if ( defined( 'RADIO_STATION_DIR' ) ) {
				$path = RADIO_STATION_DIR . '/player/css/rp-mediaelement.css';
			} else {
				$path = dirname( __FILE__ ) . '/player/css/rp-mediaelement.css';
			}
			$version = filemtime( $path );
			if ( function_exists( 'wp_enqueue_style' ) ) {
				if ( defined( 'RADIO_PLAYER_URL' ) ){
					$url = RADIO_PLAYER_URL . 'css/rp-mediaelement.css';
				} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
					$url = plugins_url( 'player/css/rp-mediaelement.css', RADIO_STATION_FILE );
				} else {
					$url = plugins_url( 'css/rp-mediaelement.css', __FILE__ );
				}
				wp_enqueue_style( 'rp-mediaelement', $url, array(), $version, 'all' );
			} else {
				// --- output style tag directly ---
				$url = 'css/rp-mediaelement.css';
				if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
				radio_station_player_style_tag( 'rp-mediaelement', $url, $version );
			}

		} elseif ( 'minimal' == $skin ) {

			// --- Minimal Style ---
			// ref: https://github.com/justintadlock/theme-mediaelement
			if ( defined( 'RADIO_STATION_DIR' ) ) {
				$path = RADIO_STATION_DIR . '/player/css/mediaelement.css';
			} else {
				$path = dirname( __FILE__ ) . '/css/mediaelement.css';
			}
			$version = filemtime( $path );
			if ( function_exists( 'wp_enqueue_style' ) ) {
				if ( defined( 'RADIO_PLAYER_URL' ) ) {
					$url = RADIO_PLAYER_URL . 'css/mediaelement.css';
				} elseif ( defined( 'RADIO_STATION_FILE' ) ) {
					$url = plugins_url( 'player/css/mediaelement.css', RADIO_STATION_FILE );
				} else {
					$url = plugins_url( 'css/mediaelement.css', __FILE__ );
				}
				wp_enqueue_style( 'rp-mediaelement', $url, array(), $version, 'all' );
			} else {
				// --- output style tag directly ---
				$url = 'css/mediaelement.css';
				if ( defined( 'RADIO_PLAYER_URL' ) ) {$url = RADIO_PLAYER_URL . $url;}
				radio_station_player_style_tag( 'rp-mediaelement', $url, $version );
			}
		}
		return;
	} */
}

// ---------------------
// Player Control Styles
// ---------------------
function radio_station_player_control_styles( $instance ) {

	// --- set default control colors ---
	$colors = array(
		'playing'	=> '#70E070',
		'buttons'	=> '#00A0E0',
		'track'		=> '#80C080',
		'thumb'		=> '#80C080',
	);

	// --- get color settings ---
	if ( function_exists( 'radio_station_get_setting' ) ) {
		$colors['playing'] = radio_station_get_setting( 'player_playing_color' );
		$colors['buttons'] = radio_station_get_setting( 'player_buttons_color' );
		$colors['thumb'] = radio_station_get_setting( 'player_thumb_color' );
		$colors['track'] = radio_station_get_setting( 'player_range_color' );
	} elseif ( function_exists( 'apply_filters' ) ) {
		$colors['playing'] = apply_filters( 'radio_station_player_playing_color', $colors['playing'], $instance );
		$colors['buttons'] = apply_filters( 'radio_station_player_buttons_color', $colors['buttons'], $instance );
		$colors['thumb'] = apply_filters( 'radio_station_player_thumb_color', $colors['thumb'], $instance );
		$colors['track'] = apply_filters( 'radio_station_player_range_color', $colors['track'], $instance );
	}

	// --- maybe set player container selector ---
	$container = '.radio-container';
	if ( $instance ) {
		$container = '#radio_container_' . $instance;
		// --- get colors for container instance ---
		if ( isset( $radio_player['instance-colors'][$instance] ) ) {
			$instance_colors = $radio_player['instance-colors'];
			foreach ( $instance_colors as $key => $instance_color ) {
				if ( $instance_color && ( '' != $instance_color ) ) {
					$colors[$key] = $instance_color;
				}
			}
		}
	}
	// 2.4.0.3: added missing function_exists wrapper
	if ( function_exists( 'apply_filters' ) ) {
		$colors = apply_filters( 'radio_station_player_control_colors', $colors, $instance );
	}

	// --- Play Button ---
	// 2.4.0.2: fix to glowingloading animation reference
	$css = "/* Playing Button */
" . $container . ".loaded .rp-play-pause-button-bg {background-color: " . $colors['buttons'] . ";}
" . $container . ".playing .rp-play-pause-button-bg {background-color: " . $colors['playing'] . ";}
" . $container . ".error .rp-play-pause-button-bg {background-color: #CC0000;}
" . $container . ".loading .rp-play-pause-button-bg {animation: glowingloading 1s infinite alternate;}
" . $container . ".playing .rp-play-pause-button-bg, 
" . $container . ".playing.loaded .rp-play-pause-button-bg {animation: glowingplaying 1s infinite alternate;}
@keyframes glowingloading {
	from {background-color: " . $colors['buttons'] . ";} to {background-color: " . $colors['buttons'] . "80;}
}
@keyframes glowingplaying {
	from {background-color: " . $colors['playing'] . ";} to {background-color: " . $colors['playing'] . "C0;}
}" . PHP_EOL;

	// --- Active Volume Buttons Color ---
	$css .= "/* Volume Buttons */
" . $container . " .rp-mute:hover, " . $container . ".muted .rp-mute, " . $container . ".muted .rp-mute:hover,
" . $container . " .rp-volume-max:focus, " . $container . " .rp-volume-max:hover, " . $container . ".maxed .rp-volume-max,
" . $container . " .rp-volume-up:focus, " . $container . " .rp-volume-up:hover,
" . $container . " .rp-volume-down:focus, " . $container . " .rp-volume-down:hover {
	background-color: " . $colors['buttons'] . ";
}" . PHP_EOL;

	// --- Volume Range Input and Container ---
	// ref: http://danielstern.ca/range.css/#/
	// ref: https://css-tricks.fcom/sliding-nightmare-understanding-range-input/
	// 2.4.0.4: added no border style to range input (border added on some themes)
	$css .= "/* Range Input */
" . $container . " .rp-volume-controls input[type=range] {";
	$css .= "margin: 0; background-color: transparent; vertical-align: middle; -webkit-appearance: none; border: none;}
" . $container . " .rp-volume-controls input[type=range]:focus {outline: none; box-shadow: none;}
" . $container . " .rp-volume-controls input[type=range]::-moz-focus-inner,
" . $container . " .rp-volume-controls input[type=range]::-moz-focus-outer {outline: none; box-shadow: none;}" . PHP_EOL;

	// --- Range Track (synced Background Div) ---
	// 2.4.0.3: add position absolute/top on slider background (cross-browser display fix)
	$css .= "/* Range Track */
" . $container . " .rp-volume-controls .rp-volume-slider-bg {
	position: absolute; top: 9px; overflow: hidden; height: 9px; margin-left: 9px; z-index: -1;
	border: 1px solid rgba(128, 128, 128, 0.5); border-radius: 3px; background: rgba(128, 128, 128, 0.5);
}
" . $container . ".playing .rp-volume-controls .rp-volume-slider-bg {background: " . $colors['track'] . ";}
" . $container . ".playing.muted .rp-volume-controls .rp-volume-slider-bg {background: rgba(128, 128, 128, 0.5);}" . PHP_EOL;

	// --- Slider Range Track (Clickable Transparent) ---
	$css .= "/* Range Track */
" . $container . " .rp-volume-controls input[type=range]::-webkit-slider-runnable-track {height: 9px; background: transparent; -webkit-appearance: none;}
" . $container . " .rp-volume-controls input[type=range]::-moz-range-track {height: 9px; background: transparent;}
" . $container . " .rp-volume-controls input[type=range]::-ms-track {height: 9px; color: transparent; background: transparent; border-color: transparent;}" . PHP_EOL;
// 2.4.0.3: remove float on range input (cross-browser display fix)
// " . $container . " .rp-volume-controls input[type=range] {float: left; margin-top: -9px;}

	// --- Slider Range Thumb ---
	$css .= "/* Range Thumb */
" . $container . " .rp-volume-controls input[type=range]::-webkit-slider-thumb {
	width: 18px; height: 18px; cursor: pointer; background: rgba(128, 128, 128, 1);
	border: 1px solid rgba(128, 128, 128, 0.5); border-radius: 9px;
	margin-top: -4.5px; -webkit-appearance: none;
}
" . $container . " .rp-volume-controls input[type=range]::-moz-range-thumb {
	width: 18px; height: 18px; cursor: pointer; background: rgba(128, 128, 128, 1);
	border: 1px solid rgba(128, 128, 128, 0.5); border-radius: 9px;
}
" . $container . " .rp-volume-controls input[type=range]::-ms-thumb {
	width: 18px; height: 18px; cursor: pointer; background: rgba(128, 128, 128, 1);
	border: 1px solid rgba(128, 128, 128, 0.5); border-radius: 9px; margin-top: 0px;
}
" . $container .".playing .rp-volume-controls input[type=range]::-webkit-slider-thumb {background: " . $colors['thumb'] . "};
" . $container .".playing .rp-volume-controls input[type=range]::-moz-range-thumb {background: " . $colors['thumb'] . "};
" . $container .".playing .rp-volume-controls input[type=range]::-ms-thumb {background: " . $colors['thumb'] . "};
@supports (-ms-ime-align:auto) {
  " . $container . " .rp-volume-controls input[type=range] {margin: 0;}
}";

	// --- dummy element style for thumb width ---
	// note: since *actual* range input thumb width is hard/impossible to get with jQuery,
	// if changing the thumb width style, override this style also for volume background to match!
	$css .= PHP_EOL . $container . " .rp-volume-thumb {display: none; width: 18px;}" . PHP_EOL;

	// --- get volume control display settings ---
	// 2.4.1.4: added volume control visibility options
	if ( function_exists( 'radio_station_get_setting' ) ) {
		$volumes = radio_station_get_setting( 'player_volumes' );
		if ( !is_array( $volumes ) ) {
			$volumes = array( 'slider', 'updown', 'mute', 'max' );
		}
	} elseif ( function_exists( 'apply_filters' ) ) {
		$volumes = array( 'slider', 'updown', 'mute', 'max' );
		$volumes = apply_filters( 'radio_station_player_volumes_display', $volumes );
	}
	if ( !in_array( 'slider', $volumes ) ) {
		$css .= PHP_EOL . $container . " .rp-volume-slider-container {display: none;}" . PHP_EOL;
	}
	if ( !in_array( 'updown', $volumes ) ) {
		$css .= PHP_EOL . $container . " .rp-volume-up, " . $container . " .rp-volume-down {display: none;}" . PHP_EOL;
	}
	if ( !in_array( 'mute', $volumes ) ) {
		$css .= PHP_EOL . $container . " .rp-mute {display: none;}" . PHP_EOL;
	}
	if ( !in_array( 'max', $volumes ) ) {
		$css .= PHP_EOL . $container . " .rp-volume-max {display: none;}" . PHP_EOL;
	}

	// --- filter and return ---
	// 2.4.0.3: added missing function_exists wrapper
	if ( function_exists( 'apply_filters' ) ) {
		$css = apply_filters( 'radio_station_player_control_styles', $css, $instance );
	}

	return $css;
}

// ------------------
// Debug Skin Loading
// ------------------
// add_filter( 'style_loader_tag', 'radio_station_player_debug_skin',10, 2 );
// function radio_station_player_debug_skin( $tag, $handle ) {
// 	if ( isset( $_REQUEST['player-debug'] ) && ( '1' == $_REQUEST['player-debug'] ) ) {
//		if ( 'rp-jplayer' == $handle ) {
//			echo "[!Radio Player JPlayer Handle Found!]";
//		}
//	}
//	return $tag;
// }


// ------------------------------------------
// === Standalone Compatibility Functions ===
// ------------------------------------------
// (for player use outside WordPress context)

// -----------------
// Output Script Tag
// -----------------
function radio_station_player_script_tag( $url, $version ) {
	$tag = '<script type="text/javascript" src="' . $url . '?' . $version . '"></script>';
	return $tag;
}

// ----------------
// Output Style Tag
// ----------------
function radio_station_player_style_tag( $id, $url, $version ) {
	$tag = '<link id="' . $id . '-css" href="' . $url . '?' . $version . '" rel="stylesheet" type="text/css" media="all">';
	return $tag;
}

// ----------------
// Validate Boolean
// ----------------
// copy of wp_validate_boolean
function radio_station_player_validate_boolean( $var ) {
	if ( is_bool( $var ) ) {
		return $var;
	}

	if ( is_string( $var ) && 'false' === strtolower( $var ) ) {
		return false;
	}

	return (bool) $var;
}

// ---------
// Escape JS
// ---------
if ( !function_exists( 'esc_js' ) ) {
 function esc_js( $js ) {
	return $js;
 }
}

// -----------
// Escape HTML
// -----------
if ( !function_exists( 'esc_html' ) ) {
 function esc_html( $html ) {
	return $html;
 }
}

// ----------
// Escape URL
// ----------
if ( !function_exists( 'esc_url' ) ) {
 function esc_url( $url ) {
	return $url;
 }
}
