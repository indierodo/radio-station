<?php

// ===========================
// === Plugin Loader Class ===
// ===========================
//
// --------------
// Version: 1.2.1
// --------------
// Note: Changelog and structure at end of file.
//
// ============
// Loader Usage
// ============
// 1. replace all occurrences of radio_station_ in this file with the plugin namespace prefix eg. my_plugin_
// 2. define plugin options, default settings, and setup arguments your main plugin file
// 3. require this file in the main plugin file and instantiate the loader class (see example below)
//
// ---------------
// Updating Loader
// ---------------
// Repeat Step 1 above with the new version of loader.php and replace in your plugin.
//
// -----------------------------------
// Example Plugin Options and Defaults
// -----------------------------------
// array of plugin option keys, with input types and defaults
// $options = array(
// 	'optionkey1'	=>	array(
//							'type' 		=> 'checkbox',
//							'default'	=> '1',
//							'value'		=> '1',
//						),
//	'optionkey2'	=>	array(
//							'type' 		=> 'radio',
//							'default'	=> 'on',
//							'options'	=> 'on/off',
//						),
//	'optionkey3'	=> array(
//							'type'		=> 'special',
//						),
// );

// -----------------------
// Example Plugin Settings
// -----------------------
// $slug = 'plugin-name';				// plugin slug (usually same as filename)
// $args = array(
//	// --- Plugin Info ---
//	'slug'			=> $slug,			// (uses slug above)
//	'file'			=> __FILE__,		// path to main plugin file (required!)
//	'version'		=> '0.0.1', 		// * rechecked later (pulled from plugin header) *
//
//	// --- Menus and Links ---
//	'title'			=> 'Plugin Name',	// plugin title
//	'parentmenu'	=> 'wordquest',		// parent menu slug
//	'home'			=> 'http://mysite.com/plugins/plugin/',
//	'support'		=> 'http://mysite.com/plugins/plugin/support/',
//	'ratetext'		=> __('Rate on WordPress.org'),		// (overrides default rate text)
//	'share'			=> 'http://mysites.com/plugins/plugin/#share', // (set sharing URL)
//	'sharetext'		=> __('Share the Plugin Love'),		// (overrides default sharing text)
//	'donate'		=> 'https://patreon.com/pagename',	// (overrides plugin Donate URI)
//	'donatetext'	=> __('Support this Plugin'),		// (overrides default donate text)
//	'readme'		=> false,			// to not link to popup readme in settings page header
//	'settingsmenu'	=> false,			// to not automatically add a settings menu [non-WQ]
//
//	// --- Options ---
//	'namespace'		=> 'plugin_name',	// plugin namespace (function prefix)
//	'settings'		=> 'pn',			// input settings prefix
//	'option'		=> 'plugin_key',	// plugin option key
//	'options'		=> $options,		// plugin options array set above
//
//	// --- WordPress.Org ---
//	'wporgslug'		=> 'plugin-slug',	// WordPress.org plugin slug
//	'wporg'			=> false, 			// * rechecked later (via presence of updatechecker.php) *
//	'textdomain'	=> 'text-domain',	// translation text domain (usually same as plugin slug)
//
//	// --- Freemius ---
//	'freemius_id'	=> '',				// Freemius plugin ID
//	'freemius_key'	=> '',				// Freemius public key
//	'hasplans'		=> false,			// if plugin has paid plans
//	'hasaddons'		=> false,			// if plugin has add ons
//	'plan'			=> 'free',	 		// * rechecked later (if premium version found) *
// );
//
// ------------------------------------
// Example Start Plugin Loader Instance
// ------------------------------------
// (add this to your main plugin file to run this loader)
// require(dirname(__FILE__).'/loader.php');				// requires this file!
// $instance = new radio_station_loader($args);				// instantiates loader class
// (ie. search and replace 'radio_station_' with 'my_plugin_' function namespace)


// ===========================
// --- Plugin Loader Class ---
// ===========================
// usage: change class prefix to the plugin function prefix
if ( !class_exists( 'radio_station_loader' ) ) {
	class radio_station_loader {

		public $args = null;
		public $namespace = null;
		public $data = null;
		public $version = null;

		public $options = null;
		public $defaults = null;
		public $tabs = array();
		public $sections = array();
		public $scripts = array();

		// 1.1.2: added menu added switch
		// 1.1.2: added debug switch
		public $menu_added = false;
		public $debug = false;

		// -----------------
		// Initialize Loader
		// -----------------
		public function __construct( $args ) {

			// --- set debug switch ---
			// 1.1.2: added debug switch check
			$prefix = '';
			if ( $args['settings'] ) {
				// 1.1.4: fix to debug prefix key
				$prefix = $args['settings'] . '-';
			}
			if ( isset( $_REQUEST[$prefix . 'debug'] ) ) {
				if ( ( '1' == $_REQUEST[$prefix . 'debug'] ) || ( 'yes' == $_REQUEST[$prefix . 'debug'] ) ) {
					$this->debug = true;
				}
			}

			// --- set plugin options ---
			// 1.0.6: added options filter
			$args['options'] = apply_filters( $args['namespace'] . '_options', $args['options'] );
			// 1.0.9: maybe get tabs and sections from options array
			if ( isset( $args['options']['tabs'] ) ) {
				$this->tabs = $args['options']['tabs'];
				unset( $args['options']['tabs'] );
			}
			if ( isset( $args['options']['sections'] ) ) {
				$this->sections = $args['options']['sections'];
				unset( $args['options']['sections'] );
			}
			$this->options = $args['options'];
			unset( $args['options'] );

			// --- set plugin args and namespace ---
			// 1.1.9: filter all arguments
			$args = apply_filters( $args['namespace'] . '_args', $args );
			$this->args = $args;
			$this->namespace = $args['namespace'];

			// --- setup plugin values ---
			$this->setup_plugin();

			// --- maybe transfer settings ---
			$this->maybe_transfer_settings();

			// --- load settings ---
			$this->load_settings();

			// --- load actions ---
			$this->add_actions();

			// --- load helper libraries ---
			$this->load_helpers();

			// --- autoset class instance global for accessibility ---
			$GLOBALS[$args['namespace'] . '_instance'] = $this;
		}

		// ------------
		// Setup Plugin
		// ------------
		public function setup_plugin() {

			$args = $this->args;
			$namespace = $this->namespace;

			// --- Read Plugin Header ---
			if ( !isset( $args['dir'] ) ) {
				$args['dir'] = dirname( $args['file'] );
			}
			$fh = fopen( $args['file'], 'r' );
			$data = fread( $fh, 2048 );
			$this->data = str_replace( "\r", "\n", $data );
			fclose( $fh );

			// --- Version ---
			$this->version = $args['version'] = $this->plugin_data( 'Version:' );

			// --- Title ---
			if ( !isset( $args['title'] ) ) {
				$args['title'] = $this->plugin_data( 'Plugin Name:' );
			}

			// --- Plugin Home ---
			if ( !isset( $args['home'] ) ) {
				$args['home'] = $this->plugin_data( 'Plugin URI:' );
			}

			// --- Author ---
			if ( !isset( $args['author'] ) ) {
				$args['author'] = $this->plugin_data( 'Author:' );
			}

			// --- Author URL ---
			if ( !isset( $args['author_url'] ) ) {
				$args['author_url'] = $this->plugin_data( 'Author URI:' );
			}

			// --- Pro Functions ---
			if ( !isset( $args['proslug'] ) ) {
				$proslug = $this->plugin_data( '@fs_premium_only' );
				// 1.0.1: if more than one file, extract pro slug based on the first filename
				if ( !strstr( $proslug, ',' ) ) {
					$profiles = array( $proslug );
					$proslug = trim( $proslug );
				} else {
					$profiles = explode( ',', $proslug );
					$proslug = trim( $profiles[0] );
				}
				$args['proslug'] = substr( $proslug, 0, - 4 ); // strips .php extension
				$args['profiles'] = $profiles;
			}

			// --- Update Loader Args ---
			$this->args = $args;
		}

		// ---------------
		// Get Plugin Data
		// ---------------
		public function plugin_data( $key ) {

			$data = $this->data;
			$value = null;
			$pos = strpos( $data, $key );
			if ( false !== $pos ) {
				$pos = $pos + strlen( $key ) + 1;
				$tmp = substr( $data, $pos );
				$pos = strpos( $tmp, "\n" );
				$value = trim( substr( $tmp, 0, $pos ) );
			}

			return $value;
		}

		// ------------------
		// Get Plugin Version
		// ------------------
		// 1.1.2: added get plugin version function
		public function plugin_version() {
			$args = $this->args;
			return $args['version'];
		}

		// -----------------
		// Set Pro Namespace
		// -----------------
		public function pro_namespace( $pronamespace ) {
			$this->args['pronamespace'] = $pronamespace;
		}


		// =======================
		// --- Plugin Settings ---
		// =======================

		// --------------------
		// Get Default Settings
		// --------------------
		public function default_settings( $dkey = false ) {

			// --- return defaults if already set ---
			$defaults = $this->defaults;
			if ( !is_null( $defaults ) ) {
				if ( $dkey && isset( $defaults[$dkey] ) ) {
					return $defaults[$dkey];
				}

				return $defaults;
			}

			// --- filter and store the plugin default settings ---
			// 1.1.2: fix to apply options filter
			$namespace = $this->namespace;
			$options = $this->options;
			$options = apply_filters( $namespace . '_options', $options );
			$defaults = array();
			foreach ( $options as $key => $values ) {
				// 1.0.9: set default to null if default value not set
				if ( isset( $values['default'] ) ) {
					$defaults[$key] = $values['default'];
				} else {
					$defaults[$key] = null;
				}
			}
			$defaults = apply_filters( $namespace . '_default_settings', $defaults );
			$this->defaults = $defaults;
			if ( $dkey && isset( $defaults[$dkey] ) ) {
				return $defaults[$dkey];
			}

			return $defaults;
		}

		// ------------
		// Add Settings
		// ------------
		public function add_settings() {

			// --- add the default plugin settings ---
			$args = $this->args;
			$defaults = $this->default_settings();
			$added = add_option( $args['option'], $defaults );

			// --- if newly added, make the defaults current settings ---
			if ( $added ) {
				$namespace = $this->namespace;
				foreach ( $defaults as $key => $value ) {
					$GLOBALS[$namespace][$key] = $value;
				}

				// --- record first installed version ---
				// 1.1.0: added record for tracking first install version
				add_option( $args['option'] . '_first_install', $args['version'] );
			}

			// 1.0.9: trigger add settings action
			// 1.2.1: fix to namespace typo (nsmespace)
			do_action( $args['namespace'] . '_add_settings', $args );
		}

		// -----------------------
		// Maybe Transfer Settings
		// -----------------------
		public function maybe_transfer_settings() {
			$namespace = $this->namespace;
			$funcname = $namespace . '_transfer_settings';

			// --- check for either function prefixed or class extended method ---
			if ( method_exists( $this, 'transfer_settings' ) ) {
				$this->transfer_settings();
			} elseif ( function_exists( $funcname ) ) {
				call_user_func( $funcname );
			}
		}

		// -----------------------
		// Get All Plugin Settings
		// -----------------------
		public function get_settings( $filter = true ) {
			$namespace = $this->namespace;
			$settings = $GLOBALS[$namespace];
			if ( $filter ) {
				// 1.0.8: only apply all settings filter if filter is true
				$settings = apply_filters( $namespace . '_settings', $settings );
				// 1.0.8: maybe apply all individual key value filters
				foreach ( $settings as $key => $value ) {
					$settings[$key] = apply_filters( $namespace . '_' . $key, $value );
				}
			}

			return $settings;
		}

		// ------------------
		// Get Plugin Setting
		// ------------------
		public function get_setting( $key, $filter = true ) {
			$args = $this->args;
			$namespace = $this->namespace;
			$settings = $GLOBALS[$namespace];
			$settings = apply_filters( $namespace . '_settings', $settings );

			// --- maybe strip settings prefix ---
			// 1.0.4: added for backwards compatibility
			if ( substr( $key, 0, strlen( $args['settings'] ) ) == $args['settings'] ) {
				$key = substr( $key, strlen( $args['settings'] ) + 1, strlen( $key ) );
			}

			// --- get plugin setting ---
			// 1.1.4: fix for weird isset failing glitch
			// if ( isset( $settings[$key] ) ) {
			if ( array_key_exists( $key, $settings ) ) {
				$value = $settings[$key];
			} else {
				$defaults = $this->default_settings();
				// 1.1.4: fix for weird isset failing glitch
				if ( array_key_exists( $key, $defaults ) ) {
					$value = $defaults[$key];
				} else {
					$value = null;
				}
			}
			if ( $filter ) {
				$value = apply_filters( $namespace . '_' . $key, $value );
			}

			return $value;
		}

		// ---------------------
		// Reset Plugin Settings
		// ---------------------
		public function reset_settings() {

			$args = $this->args;
			$namespace = $this->namespace;

			// --- check reset triggers ---
			// 1.0.2: fix to namespace key typo in isset check
			// 1.0.3: only use namespace not settings key
			// 1.0.9: check page is set and matches slug
			if ( !isset( $_REQUEST['page'] ) ) {
				return;
			}
			if ( $_REQUEST['page'] != $args['slug'] ) {
				return;
			}
			if ( !isset( $_POST[$args['namespace'] . '_update_settings'] ) ) {
				return;
			}
			if ( 'reset' != $_POST[$args['namespace'] . '_update_settings'] ) {
				return;
			}

			// --- check reset permissions ---
			$capability = apply_filters( $args['namespace'] . '_manage_options_capability', 'manage_options' );
			if ( !current_user_can( $capability ) ) {
				return;
			}

			// --- verify nonce ---
			// $noncecheck = wp_verify_nonce( $_POST['_wpnonce', $args['slug'].'_update_settings' );
			check_admin_referer( $args['slug'] . '_update_settings' );

			// --- reset plugin settings ---
			$defaults = $this->default_settings();
			$defaults['savetime'] = time();
			update_option( $args['option'], $defaults );

			// --- loop to remerge with settings global ---
			foreach ( $defaults as $key => $value ) {
				$GLOBALS[$namespace][$key] = $value;
			}

			// --- set settings reset message flag ---
			$_GET['updated'] = 'reset';
		}

		// ----------------------
		// Update Plugin Settings
		// ----------------------
		public function update_settings() {

			$args = $this->args;
			$namespace = $this->namespace;
			$settings = $GLOBALS[$namespace];

			// --- check update triggers ---
			// 1.0.2: fix to namespace key typo in isset check
			// 1.0.3: only use namespace not settings key
			// 1.0.9: check page is set and matches slug
			if ( !isset( $_REQUEST['page'] ) || ( $_REQUEST['page'] != $args['slug'] ) ) {
				return;
			}
			$updatekey = $args['namespace'] . '_update_settings';
			if ( !isset( $_POST[$updatekey] ) || ( 'yes' != $_POST[$args['namespace'] . '_update_settings'] ) ) {
				return;
			}

			// --- check update permissions ---
			$capability = apply_filters( $namespace . '_manage_options_capability', 'manage_options' );
			if ( !current_user_can( $capability ) ) {
				return;
			}

			// --- verify nonce ---
			// $noncecheck = wp_verify_nonce( $_POST['_wpnonce', $args['slug'].'_update_settings' );
			check_admin_referer( $args['slug'] . '_update_settings' );

			// --- get plugin options and default settings ---
			// 1.0.9: allow filtering of plugin options (eg. for Pro/Add Ons)
			$options = $this->options;
			$options = apply_filters( $namespace . '_options', $options );
			$defaults = $this->default_settings();

			// --- maybe use custom method or function ---
			$funcname = $namespace . '_process_settings';
			if ( method_exists( $this, 'process_settings' ) ) {

				// --- use class extended method if found ---
				$settings = $this->process_settings();

			} elseif ( function_exists( $funcname ) && is_callable( $funcname ) ) {

				// --- use namespace prefixed function if found ---
				$settings = call_user_func( $funcname );

			} else {

				// --- loop plugin options to get new settings ---
				foreach ( $options as $key => $values ) {

					// --- get option type and options ---
					$type = $values['type'];
					$valid = $validate_args = array();
					if ( isset( $values['options'] ) ) {
						$valid = $values['options'];
					}

					// --- get posted value ---
					// 1.0.6: set null value for unchecked checkbox fix
					$posted = null;
					$postkey = $args['settings'] . '_' . $key;
					if ( isset( $_POST[$postkey] ) ) {
						$posted = $_POST[$postkey];
					}
					$newsettings = null;

					// --- maybe validate special options ---
					// 1.0.9: check for special options to prepare
					if ( is_string( $valid ) ) {

						// --- maybe get public post type slugs ---
						if ( in_array( $valid, array( 'PUBLICTYPE', 'PUBLICTYPES' ) ) ) {
							$valid = array();
							if ( !isset( $public ) ) {
								$cpts = array( 'page', 'post' );
								$cptargs = array( 'public' => true, '_builtin' => false );
								$cptlist = get_post_types( $cptargs, 'names', 'and' );
								$public = array_merge( $cpts, $cptlist );
							}
							foreach ( $public as $cpt ) {
								$valid[$cpt] = '';
							}
						}

						// --- maybe get post type slugs ---
						if ( in_array( $valid, array( 'POSTTYPE', 'POSTTYPES' ) ) ) {
							$valid = array();
							if ( !isset( $cpts ) ) {
								$cpts = array( 'page', 'post' );
								$cptargs = array( 'public' => true, '_builtin' => false );
								$cptlist = get_post_types( $cptargs, 'names', 'and' );
								$cpts = array_merge( $cpts, $cptlist );
							}
							foreach ( $cpts as $cpt ) {
								$valid[$cpt] = '';
							}
						}

						// --- maybe get all post type slugs ---
						if ( in_array( $valid, array( 'ALLTYPE', 'ALLTYPES' ) ) ) {
							$valid = array();
							if ( !isset( $allcpts ) ) {
								$cptargs = array( '_builtin' => false );
								$allcpts = get_post_types( $cptargs, 'names', 'and' );
							}
							foreach ( $allcpts as $cpt ) {
								$valid[$cpt] = '';
							}
						}
					}

					if ( $this->debug ) {
						echo 'Saving Setting Key ' . $key . ' (' . $postkey . '): ' . print_r( $posted, true ) . '<br>';
						echo 'Type: ' . $type . ' - Valid Options ' . $key . ': ' . print_r( $valid, true ) . '<br>';
					}

					// --- sanitize value according to type ---
					if ( strstr( $type, '/' ) ) {

						// --- implicit radio / select ---
						$valid = explode( '/', $type );
						if ( in_array( $posted, $valid ) ) {
							$settings[$key] = $posted;
						}

					} elseif ( ( 'checkbox' == $type ) || ( 'toggle' == $type ) ) {

						// --- checkbox / toggle ---
						// 1.0.6: fix to new unchecked checkbox value
						// 1.0.9: maybe validate to specified checkbox value
						if ( isset( $values['value'] ) ) {
							$valid = array( $values['value'] );
						} else {
							$valid = array( 'yes', '1', 'checked', 'on' );
						}
						if ( in_array( $posted, $valid ) ) {
							$settings[$key] = $posted;
						} elseif ( is_null( $posted ) ) {
							$settings[$key] = '';
						}

					} elseif ( 'textarea' == $type ) {

						// --- text area ---
						// TODO: maybe use sanitize text field ?
						$posted = stripslashes( $posted );
						$settings[$key] = $posted;

					} elseif ( 'text' == $type ) {

						// --- text field (slug) ---
						// 1.0.9: move text field sanitization to validation
						if ( !is_string( $valid ) ) {
							$valid = 'TEXT';
						}
						$newsettings = $posted;

					} elseif ( ( 'number' == $type ) || ( 'numeric' == $type ) ) {

						// --- number field value ---
						// 1.0.9: added support for number step, minimum and maximum
						$newsettings = $posted;
						$valid = 'NUMERIC';
						if ( isset( $values['step'] ) ) {
							$validate_args['step'] = $values['step'];
						}
						if ( isset( $values['min'] ) ) {
							$validate_args['min'] = $values['min'];
						}
						if ( isset( $values['max'] ) ) {
							$validate_args['max'] = $values['max'];
						}

					} elseif ( 'multicheck' == $type ) {

						// --- process multicheck boxes ---
						// 1.0.9: added multicheck input type
						// note: needs defined options (but works with post types)
						$posted = array();
						foreach ( $valid as $option => $label ) {
							$optionkey = $args['settings'] . '_' . $key . '-' . $option;
							if ( isset( $_POST[$optionkey] ) ) {
								// 1.1.2: check for value if specified
								if ( ( isset( $values['value'] ) && ( $values['value'] == $_POST[$optionkey] ) )
								  || ( !isset( $values['value'] ) && ( 'yes' == $_POST[$optionkey] ) ) ) {
									// 1.1.0: fixed to save only array of key values
									$posted[] = $option;
								}
							}
						}
						$settings[$key] = $posted;

					} elseif ( 'csv' == $type ) {

						// -- comma separated values ---
						// 1.0.4: added comma separated values option
						if ( strstr( $posted, ',' ) ) {
							$posted = explode( ',', $posted );
						} else {
							$posted[0] = $posted;
						}
						foreach ( $posted as $i => $value ) {
							$posted[$i] = trim( $value );
						}
						if ( is_string( $valid ) ) {
							$newsettings = $posted;
						} elseif ( is_array( $valid ) && ( count( $valid ) > 0 ) ) {
							// 1.2.0: fix to check for empty valid array
							foreach ( $posted as $i => $value ) {
								if ( !in_array( $value, $valid ) ) {
									unset( $posted[$i] );
								}
							}
							$settings[$key] = implode( ',', $posted );
						} else {
							$settings[$key] = implode( ',', $posted );
						}

					} elseif ( ( 'radio' == $type ) || ( 'select' == $type ) ) {

						// --- explicit radio or select value ---
						if ( is_string( $valid ) ) {
							$newsettings = $posted;
						} elseif ( is_array( $valid ) && array_key_exists( $posted, $valid ) ) {
							$settings[$key] = $posted;
						}

					} elseif ( 'multiselect' == $type ) {

						// --- multiselect values ---
						// 1.0.9: added multiselect value saving
						$newsettings = array_values( $posted );

					} elseif ( 'image' == $type ) {

						// --- check attachment ID value ---
						// 1.1.7: add image attachment ID saving
						if ( '' != $posted ) {
							$attachment = wp_get_attachment_image_src( $posted, 'full' );
							if ( is_array( $attachment ) ) {
								$settings[$key] = $posted;
							}
						}

					} elseif ( ( 'color' == $type ) || ( 'coloralpha' == $type ) ) {

						// --- color or color alpha setting ---
						// 1.1.7: added color picker value saving
						// TODO: maybe validate this setting ?
						$settings[$key] = $posted;

					}

					if ( $this->debug ) {
						echo 'New Settings for Key ' . $key . ': ';
						// 1.2.0: added isset check for newsetting
						if ( !is_null( $newsettings ) ) {
							echo '(to-validate) ' . print_r( $newsettings, true ) . '<br>';
						} else {
							// 1.1.7 handle if (new) key not set yet
							if ( isset( $settings[$key] ) ) {
								echo '(validated) ' . print_r( $settings[$key], true ) . '<br>';
							} else {
								echo 'No setting yet for key ' . $key . '<br>';
							}
						}
					}

					// --- maybe validate new settings ---
					// 1.1.9: fix to allow saving of zero value
					// 1.2.1: fix to allow saving of empty value
					if ( !is_null( $newsettings ) ) {
						if ( is_array( $newsettings ) ) {

							// --- validate array of settings ---
							// 1.1.9: fix to allow saving of zero value
							// 1.2.1: fix to allow saving of empty value
							foreach ( $newsettings as $newkey => $newvalue ) {
								$newsetting = $this->validate_setting( $newvalue, $valid, $validate_args );
								if ( $this->debug ) {
									echo 'Validated Setting array value ' . $newvalue . ' to ' . $newsetting;
								}
								if ( $newsetting || ( '' == $newsetting ) ) {
									$newsettings[$newkey] = $newsetting;
								} elseif ( ( 0 == $newsetting ) || ( '0' == $newsetting ) ) {
									$newsettings[$newkey] = $newsetting;
								} else {
									unset( $newsettings[$newkey] );
								}
							}
							if ( 'csv' == $type ) {
								$settings[$key] = implode( ',', $newsettings );
							} else {
								$settings[$key] = $newsettings;
							}

						} elseif ( $newsettings || ( '' == $newsettings ) || ( 0 === $newsettings ) || ( '0' === $newsettings ) ) {

							// --- validate single setting ---
							if ( 'csv' == $type ) {
								// 1.1.5: fix to validate each of multiple CSV values
								$values = explode( ',', $newsettings );
								$newvalues = array();
								foreach ( $values as $value ) {
									$newvalue = $this->validate_setting( $value, $valid, $validate_args );
									$newvalues[] = $newvalue;
									if ( $this->debug ) {
										echo 'Validated Setting value ' . $value . ' to ' . $newvalue;
									}
								}
								$newsettings = implode( ',', $newvalues );
								$settings[$key] = $newsettings;
							} else {
								$newsetting = $this->validate_setting( $newsettings, $valid, $validate_args );
								// 1.1.9: fix to allow saving of zero value
								// 1.2.1: fix to allow saving of empty value
								if ( $this->debug ) {
									echo 'Validated Setting single value ' . $newsettings . ' to ' . $newsetting . '<br>';
								}
								if ( $newsetting || ( '' == $newsetting ) || ( 0 == $newsetting ) || ( '0' == $newsetting ) ) {
									$settings[$key] = $newsetting;
								}
							}
						}

						if ( $this->debug ) {
							echo 'Valid Options for Key ' . $key . ': ' . print_r( $valid, true ) . '<br>';
							echo 'Validated Settings for Key ' . $key . ': ' . print_r( $settings[$key], true ) . '<br>';
						}
					}

				}
			}

			// --- process special settings ---
			// 1.0.2: added for processing special settings separately
			$funcname = $namespace . '_process_special';
			if ( method_exists( $this, 'process_special' ) ) {

				// --- use class extended method if found ---
				$settings = $this->process_special( $settings );

			} elseif ( function_exists( $funcname ) && is_callable( $funcname ) ) {

				// --- use namespace prefixed function if found ---
				$settings = call_user_func( $funcname, $settings );
			}

			// --- output new settings ---
			if ( $this->debug ) {
				echo "<br><b>All New Settings:</b><br>";
				print_r( $settings );
				echo "<br><br>";
			}

			if ( $settings && is_array( $settings ) ) {

				// --- loop default keys to remove others ---
				$settings_keys = array_keys( $defaults );
				foreach ( $settings as $key => $value ) {
					if ( !in_array( $key, $settings_keys ) ) {
						unset( $settings[$key] );
					}
				}

				// --- save settings tab ---
				// 1.2.0: validate and save current settings tab
				if ( isset( $_POST['settingstab'] ) ) {
					$currenttab = $_POST['settingstab'];
					$tabs = array();
					foreach ( $options as $key => $option ) {
						if ( isset ( $option['tab'] ) && !in_array( $option['tab'], $tabs ) ) {
							$tabs[] = $option['tab'];
						}
					}
					if ( count( $tabs ) > 0 ) {
						if ( in_array( $currenttab, $tabs ) ) {
							$settings['settingstab'] = $currenttab;
						} elseif ( in_array( 'general', $tabs ) ) {
							$settings['settingstab'] = 'general';
						} else {
							$settings['settingstab'] = $tabs[0];
						}
					}
				}


				// --- update the plugin settings ---
				$settings['savetime'] = time();
				update_option( $args['option'], $settings );

				// --- merge with existing settings for pageload ---
				foreach ( $settings as $key => $value ) {
					$GLOBALS[$namespace][$key] = $value;
				}

				// --- set settings update message flag ---
				$_GET['updated'] = 'yes';

			} else {
				$_GET['updated'] = 'no';
			}

			// --- maybe trigger update of Pro settings ---
			if ( method_exists( $this, 'pro_update_settings' ) ) {
				$this->pro_update_settings();
			} else {
				if ( isset( $args['pronamespace'] ) ) {
					$funcname = $args['pronamespace'] . '_update_settings';
				} else {
					$funcname = $args['namespace'] . '_pro_update_settings';
				}
				if ( function_exists( $funcname ) ) {
					call_user_func( $funcname );
				}
			}

		}

		// -----------------------
		// Validate Plugin Setting
		// -----------------------
		// 1.1.5: plural options now use extra validation
		public function validate_setting( $posted, $valid, $args ) {

			// --- allow for clearing of a field ---
			// note: array values are cleared if empty, single values are set empty
			if ( trim( $posted ) == '' ) {
				return '';
			}

			// --- validate different data types ---
			if ( 'TEXT' == $valid ) {

				// --- sanitize text field ---
				$posted = sanitize_text_field( $posted );

				return $posted;

			} elseif ( 'ALPHABETIC' == $valid ) {

				// --- alphabetic ---
				// 1.0.9: added alphabetic-only for completeness
				$posted = trim( $posted );
				$checkposted = preg_match( '/^[a-zA-Z]+$/', $posted );
				if ( $checkposted ) {
					return $posted;
				}

			} elseif ( 'NUMERIC' == $valid ) {

				// --- number (numeric text) ---
				// note: step key is only used for controls, not for validation
				// 1.0.9: cast to integer - not absolute integer
				// TODO: validate step value match ?
				$posted = floatval( trim( $posted ) );
				if ( isset( $args['min'] ) && ( $posted < $args['min'] ) ) {
					return $args['min'];
				} elseif ( isset( $args['max'] ) && ( $posted > $args['max'] ) ) {
					return $args['max'];
				} elseif ( 0 === $posted ) {
					return 0;
				}

				return $posted;

			} elseif ( 'ALPHANUMERIC' == $valid ) {

				// --- alphanumeric text only ---
				$posted = trim( $posted );
				// TODO: maybe remove underscore from validation regex ?
				$checkposted = preg_match( '/^[a-zA-Z0-9_]+$/', $posted );
				if ( $checkposted ) {
					return $posted;
				}

			} elseif ( 'PHONE' == $valid ) {

				// --- phone number characters only ---
				$posted = trim( $posted );
				// $checkposted = preg_match( '/^[0-9+\(\)#\.\s\-]+$/', $posted );
				// if ( $checkposted ) {
				//	return $posted;
				// }
				if ( strlen( $posted ) > 0 ) {
					$posted = str_split( $posted, 1 );
					$posted = preg_filter( '/^[0-9+\(\)#\.\s\-]+$/', '$0', $posted );
					if ( count( $posted ) > 0 ) {
						$posted = implode( '', $posted );
						return $posted;
					} else {
						return '';
					}
				}

			} elseif ( in_array( $valid, array( 'URL', 'URLS' ) ) ) {

				// --- URL address ---
				// 1.0.6: fix to type variable typo (vtype)
				// 1.0.4: added validated URL option
				// 1.0.6: fix to posted variable type (vposted)
				// 1.0.9: remove check for http prefix to allow other protocols
				// 1.1.7: use FILTER_SANITIZE_URL not FILTER_SANITIZE_STRING
				// 1.2.1: allow for clearing URL by saving empty value
				$posted = trim( $posted );
				if ( '' != $posted ) {
					$posted = filter_var( $posted, FILTER_SANITIZE_URL );
				}

				// 1.1.7: remove FILTER_VALIDATE_URL check - not working!?
				if ( $this->debug ) {
					$check = filter_var( $url, FILTER_VALIDATE_URL );
					echo 'Validated URL: ' . print_r( $check, true ) . '<br>';
				}
				// if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
				//	$posted = '';
				// }
				return $posted;

			} elseif ( in_array( $valid, array( 'EMAIL', 'EMAILS' ) ) ) {

				// --- email address ---
				// 1.0.3: added email option type checking
				$email = sanitize_email( trim( $posted ) );
				if ( $email ) {
					$posted = $email;
				} else {
					$posted = '';
				}
				return $posted;

			} elseif ( in_array( $valid, array( 'USEREMAIL', 'USEREMAILS' ) ) ) {

				// --- email address with user validation ---
				// 1.1.5: added option for validated user emails
				$email = sanitize_email( trim( $posted ) );
				if ( !$email ) {
					return '';
				}
				$user = get_user_by( 'email', $email );
				if ( $user ) {
					$posted = $username;
				} else {
					$posted = '';
				}
				return $posted;

			} elseif ( 'USERNAME' == $valid ) {

				// --- username ---
				$username = sanitize_user( trim( $posted ) );
				if ( !$username ) {
					return '';
				}
				return $username;

			} elseif ( 'USERNAMES' == $valid ) {

				// --- username with check for existing user ---
				// 1.1.5: separated check from singular
				$username = sanitize_user( trim( $posted ) );
				if ( !$username ) {
					return '';
				}
				$user = get_user_by( 'login', $username );
				if ( $user ) {
					$posted = $username;
				} else {
					$posted = '';
				}
				return $posted;

			} elseif ( 'USERID' == $valid ) {

				// --- user ID ---
				$userid = intval( trim( $posted ) );
				if ( 0 === $userid ) {
					return '';
				}

			} elseif ( 'USERIDS' == $valid ) {

				// --- user ID with check for existing user ---
				// 1.1.5: separated check from singular
				$userid = intval( trim( $posted ) );
				if ( 0 === $userid ) {
					return '';
				}
				$user = get_user_by( 'ID', $userid );
				if ( $user ) {
					$posted = $userid;
				} else {
					$posted = '';
				}

				return $posted;

			} elseif ( in_array( $valid, array( 'SLUG', 'SLUGS' ) ) ) {

				// -- post slugs ---
				$posted = sanitize_title( trim( $posted ) );

				return $posted;

			} elseif ( in_array( $valid, array( 'PAGEID', 'POSTID' ) ) ) {

				$posted = intval( trim( $posted ) );
				if ( 0 === $posted ) {
					return '';
				}
				return $posted;

			} elseif ( in_array( $valid, array( 'PAGEIDS', 'POSTIDS' ) ) ) {

				$posted = intval( trim( $posted ) );
				if ( 0 === $posted ) {
					return '';
				}
				$post = get_post( $posted );
				if ( $post ) {
					return $posted;
				}
			}

			// TODO: return validation error ?
			return false;
		}

		// ---------------
		// Delete Settings
		// ---------------
		public function delete_settings() {

			// TODO: check for plugin settings flag to delete settings ?
			// $delete_settings = $this->get_setting( 'delete_settings' );
			// if ( $delete_settings ) {
			//	$args = $this->args;
			//	delete_option( $args['option'] );
			// }

			// TODO: check for plugin settings flag to delete data?
			// $delete_data = $this->get_setting( 'delete_data' );
			// if ( $delete_data ) {
			//	do_action( $this->namespace . '_delete_data' );
			// }
		}


		// ======================
		// --- Plugin Loading ---
		// ======================

		// --------------------
		// Load Plugin Settings
		// --------------------
		public function load_settings() {
			$args = $this->args;
			$namespace = $this->namespace;
			$GLOBALS[$namespace] = $args;
			$settings = get_option( $args['option'], false );
			if ( $settings && is_array( $settings ) ) {
				foreach ( $settings as $key => $value ) {
					$GLOBALS[$namespace][$key] = $value;
				}
			} else {
				$defaults = $this->default_settings();
				foreach ( $defaults as $key => $value ) {
					$GLOBALS[$namespace][$key] = $value;
				}
			}
		}

		// -----------
		// Add Actions
		// -----------
		public function add_actions() {

			$args = $this->args;
			$namespace = $this->namespace;

			// --- add settings on activation ---
			register_activation_hook( $args['file'], array( $this, 'add_settings' ) );

			// --- always check for update and reset of settings ---
			add_action( 'admin_init', array( $this, 'update_settings' ) );
			add_action( 'admin_init', array( $this, 'reset_settings' ) );

			// --- add plugin submenu ---
			add_action( 'admin_menu', array( $this, 'settings_menu' ), 1 );

			// --- add plugin settings page link ---
			add_filter( 'plugin_action_links', array( $this, 'plugin_links' ), 10, 2 );

			// --- maybe delete settings on deactivation ---
			register_deactivation_hook( $args['file'], array( $this, 'delete_settings' ) );

			// --- maybe load thickbox ---
			add_action( 'admin_enqueue_scripts', array( $this, 'maybe_load_thickbox' ) );

			// --- AJAX readme viewer ---
			add_action( 'wp_ajax_' . $namespace . '_readme_viewer', array( $this, 'readme_viewer' ) );

			// --- load Freemius (requires PHP 5.4+) ---
			// 1.2.1: move Freemius loading to plugins_loaded hook
			if ( version_compare( PHP_VERSION, '5.4.0' ) >= 0 ) {
				add_action( 'plugins_loaded', array( $this, 'load_freemius' ), 5 );
			}
		}

		// ---------------------
		// Load Helper Libraries
		// ---------------------
		public function load_helpers() {

			$args = $this->args;
			$file = $args['file'];
			$dir = $args['dir'];

			// --- Plugin Slug ---
			if ( !isset( $args['slug'] ) ) {
				$args['slug'] = substr( $file, 0, - 4 );
				$this->args = $args;
			}

			// --- Pro Functions ---
			// 1.0.2: added prototype auto-loading of Pro file(s)
			// 1.2.1: fix overriding of plan arg to free
			// (to work with @fs_premium_only file list)
			if ( isset( $args['profiles'] ) && count( $args['profiles'] ) > 0 ) {
				$included = get_included_files();
				foreach ( $args['profiles'] as $profile ) {
					// --- chech for php extension ---
					if ( substr( '.php' == $profile, - 4, 4 ) ) {
						$filepath = $dir . '/' . $profile;
						if ( file_exists( $filepath ) ) {
							$plan = 'premium';
							// 1.0.9: add check if file already included
							if ( !in_array( $filepath, $included ) ) {
								include $filepath;
							}
						}
					}
				}
			}
			// 1.2.0: only change plan setting if premium files found
			if ( isset( $plan ) ) {
				$args['plan'] = $plan;
				$this->args = $args;
			}

			// --- Plugin Update Checker ---
			// note: lack of updatechecker.php file indicates WordPress.Org SVN repo version
			// presence of updatechecker.php indicates direct site download or GitHub version
			$wporg = true;
			$updatechecker = $dir . '/updatechecker.php';
			if ( file_exists( $updatechecker ) ) {
				$wporg = false;
				$slug = $args['slug'];
				// note: requires $file and $slug to be defined
				include $updatechecker;
			}
			$args['wporg'] = $wporg;
			$this->args = $args;

			// --- Trigger Loader Helpers Action ---
			// 1.0.9: added action for extra loader helpers (eg. WordQuest)
			do_action( $args['namespace'] . '_loader_helpers', $args );

			// --- load Freemius (requires PHP 5.4+) ---
			// 1.2.1: moved to plugins_loaded action hook

		}

		// -------------------
		// Maybe Load Thickbox
		// -------------------
		public function maybe_load_thickbox() {
			$args = $this->args;
			if ( isset( $_REQUEST['page'] ) && ( $_REQUEST['page'] == $args['slug'] ) ) {
				add_thickbox();
			}
		}

		// ------------------
		// Readme Viewer AJAX
		// ------------------
		public function readme_viewer() {

			$args = $this->args;
			$dir = $args['dir'];

			echo "<html><body style='font-family: Consolas, \"Lucida Console\", Monaco, FreeMono, monospace'>";

			// 1.0.7: changed readme.php to reader.php (for Github)
			$readme = $dir . '/readme.txt';
			$contents = file_get_contents( $readme );
			$parser = $dir . '/reader.php';

			if ( file_exists( $parser ) ) {

				// --- include Markdown Readme Parser ---
				include $parser;

				// --- remove license info as causes breakage! ---
				// TODO: find line start and end to handle other possible licenses
				$contents = str_replace( 'License: GPLv2 or later', '', $contents );
				$contents = str_replace( 'License URI: http://www.gnu.org/licenses/gpl-2.0.html', '', $contents );

				// --- instantiate Parser class ---
				$readme = new WordPress_Readme_Parser();
				$parsed = $readme->parse_readme_contents( $contents );

				// --- output plugin info ---
				echo "<b>" . esc_html( __( 'Plugin Name' ) ) . "</b>: " . esc_html( $parsed['name'] ) . "<br>";
				// echo "<b>" . esc_html( __( 'Tags' ) ) . "</b>: " . esc_html( implode( ', ', $parsed['tags'] ) ) . "<br>";
				echo "<b>" . esc_html( __( 'Requires at least' ) ) . "</b>: " . esc_html( __( 'WordPress' ) ) . " v" . esc_html( $parsed['requires_at_least'] ) . "<br>";
				echo "<b>" . esc_html( __( 'Tested up to' ) ) . "</b>: " . esc_html( __( 'WordPress' ) ) . " v" . esc_html( $parsed['tested_up_to'] ) . "<br>";
				if ( isset( $parsed['stable_tag'] ) ) {
					echo "<b>" . esc_html( __( 'Stable Tag' ) ) . "</b>: " . esc_html( $parsed['stable_tag'] ) . "<br>";
				}
				echo "<b>" . esc_html( __( 'Contributors' ) ) . "</b>: " . esc_html( implode( ', ', $parsed['contributors'] ) ) . "<br>";
				// echo "<b>Donate Link</b>: <a href='".$parsed['donate_link']."' target=_blank>".$parsed['donate_link']."</a><br>";
				// phpcs:ignore WordPress.Security.OutputNotEscaped
				echo "<br>" . $parsed['short_description'] . "<br><br>";

				// --- output sections ---
				// possible sections: 'description', 'installation', 'frequently_asked_questions',
				// 'screenshots', 'changelog', 'change_log', 'upgrade_notice'
				$strip = array( 'installation', 'screenshots' );
				foreach ( $parsed['sections'] as $key => $section ) {
					if ( !empty( $section ) && !in_array( $key, $strip ) ) {
						if ( strstr( $key, '_' ) ) {
							$parts = explode( '_', $key );
						} else {
							$parts = array( $key );
						}
						foreach ( $parts as $i => $part ) {
							$parts[$i] = strtoupper( substr( $part, 0, 1 ) ) . substr( $part, 1 );
						}
						$title = implode( ' ', $parts );
						echo "<h3>" . esc_html( $title ) . "</h3>";
						// phpcs:ignore WordPress.Security.OutputNotEscaped
						echo $section;
					}
				}
				if ( isset( $parsed['remaining_content'] ) && !empty( $remaining_content ) ) {
					echo "<h3>" . esc_html( __( 'Extra Notes' ) ) . "</h3>";
					// phpcs:ignore WordPress.Security.OutputNotEscaped
					echo $parsed['remaining_content'];
				}

			} else {
				// --- fallback text-only display ---
				$contents = str_replace( "\n", "<br>", $contents );
				// phpcs:ignore WordPress.Security.OutputNotEscaped
				echo $contents;
			}

			echo "</body></html>";
			exit;
		}


		// =======================
		// --- Freemius Loader ---
		// =======================
		//
		// required settings keys:
		// -----------------------
		// freemius_id	- plugin ID from Freemius plugin dashboard
		// freemius_key	- public key from Freemius plugin dashboard
		//
		// optional settings keys:
		// -----------------------
		// plan 		- (string) curent plugin plan (value of 'free' or 'premium')
		// hasplans		- (boolean) switch for whether plugin has premium plans
		// hasaddons	- (boolean) switch for whether plugin has premium addons
		// wporg		- (boolean) switch for whether free plugin is WordPress.org compliant
		// contact		- (boolean) submenu switch for plugin Contact (defaults to on for premium only)
		// support		- (boolean) submenu switch for plugin Support (default on)
		// account		- (boolean) submenu switch for plugin Account (default on)
		// parentmenu	- (string) optional slug for plugin parent menu
		//
		// okay lets do this...
		// ====================
		public function load_freemius() {

			// 1.2.1: no need to load if not in admin area
			if ( !is_admin() ) {
			 	return;
			}		

			$args = $this->args;
			$namespace = $this->namespace;

			// --- check for required Freemius keys ---
			if ( !isset( $args['freemius_id'] ) || !isset( $args['freemius_key'] ) ) {
				return;
			}
			
			// --- check for free / premium plan ---
			// convert plan string value of 'free' or 'premium' to boolean premium switch
			// TODO: check for active addons also ?
			$premium = false;
			if ( isset( $args['plan'] ) && ( 'premium' == $args['plan'] ) ) {
				$premium = true;
			} else {
				// 1.2.1: added filter for premium init
				$premium = apply_filters( 'freemius_init_premium_' . $args['namespace'], $premium );
			}

			// --- maybe redirect link to plugin support forum ---
			// TODO: change to use new Freemius 2.3.0 support link filter ?
			if ( isset( $_REQUEST['page'] ) && ( $args['slug'] . '-wp-support-forum' == $_REQUEST['page'] ) && is_admin() ) {
				if ( !function_exists( 'wp_redirect' ) ) {
					include ABSPATH . WPINC . '/pluggable.php';
				}
				if ( isset( $args['support'] ) ) {
					// changes the support forum slug for premium based on the pro plugin file slug
					// 1.0.7: fix support URL undefined variable warning
					$support_url = $args['support'];
					// 1.2.1: removed in favour of filtering via Pro
					// if ( $premium && isset( $args['proslug'] ) ) {
					// 	$support_url = str_replace( $args['slug'], $args['proslug'], $support_url );
					// }
					$support_url = apply_filters( 'freemius_plugin_support_url_redirect', $support_url, $args['slug'] );
					wp_redirect( $support_url );
					exit;
				}
			}

			// --- do the Freemius Loading boogie ---
			if ( !isset( $args['freemius'] ) ) {

				// --- start the Freemius SDK ---
				if ( !class_exists( 'Freemius' ) ) {
					$freemiuspath = dirname( __FILE__ ) . '/freemius/start.php';
					if ( !file_exists( $freemiuspath ) ) {
						return;
					}
					require_once $freemiuspath;
				}

				// --- set defaults for optional key values ---
				if ( !isset( $args['type'] ) ) {
					$args['type'] = 'plugin';
				}
				if ( !isset( $args['wporg'] ) ) {
					$args['wporg'] = false;
				}
				if ( !isset( $args['hasplans'] ) ) {
					$args['hasplans'] = false;
				}
				if ( !isset( $args['hasaddons'] ) ) {
					$args['hasaddons'] = false;
				}
				// 1.2.1: add filter for addons init
				$args['hasaddons'] = apply_filters( 'freemius_init_addons_' . $args['namespace'], $args['hasaddons'] );

				// --- set defaults for options submenu key values ---
				// 1.0.2: fix to isset check keys
				// 1.0.5: fix to set args subkeys for support and account
				if ( !isset( $args['support'] ) ) {
					$args['support'] = true;
				}
				if ( !isset( $args['account'] ) ) {
					$args['account'] = true;
				}
				// by default, enable contact submenu item for premium plugins only
				if ( !isset( $args['contact'] ) ) {
					$args['contact'] = $premium;
				}

				// --- set Freemius settings from plugin settings ---
				// 1.1.1: remove admin_url wrapper on Freemius first-path value
				$first_path = add_query_arg( 'page', $args['slug'], 'admin.php' );
				$first_path = add_query_arg( 'welcome', 'true', $first_path );
				$settings = array(
					'type'             => $args['type'],
					'slug'             => $args['slug'],
					'id'               => $args['freemius_id'],
					'public_key'       => $args['freemius_key'],
					'has_addons'       => $args['hasaddons'],
					'has_paid_plans'   => $args['hasplans'],
					'is_org_compliant' => $args['wporg'],
					'is_premium'       => $premium,
					'menu'             => array(
						'slug'       => $args['slug'],
						'first-path' => $first_path,
						'contact'    => $args['contact'],
						'support'    => $args['support'],
						'account'    => $args['account'],
					),
				);

				// --- maybe add plugin submenu to parent menu ---
				if ( isset( $args['parentmenu'] ) ) {
					$settings['menu']['parent'] = array( 'slug' => $args['parentmenu'] );
				}

				// --- filter settings before initializing ---
				$settings = apply_filters( 'freemius_init_settings_' . $args['namespace'], $settings );
				if ( $this->debug ) {
					echo '<span style="display:none;">Freemius Settings: ' . print_r( $settings, true ) . '</span>';
				}
				if ( !$settings || !is_array( $settings ) ) {
					return;
				}

				// --- initialize Freemius now ---
				$freemius = $GLOBALS[$namespace . '_freemius'] = fs_dynamic_init( $settings );
				if ( $this->debug ) {
					echo '<span style="display:none;">Freemius Object: ' . print_r( $freemius, true ) . '</span>';
				}

				// --- set plugin basename ---
				// 1.0.1: set free / premium plugin basename
				if ( method_exists( $freemius, 'set_basename' ) ) {
					$freemius->set_basename( $premium, $args['file'] );
				}

				// --- add Freemius connect message filter ---
				$this->freemius_connect();

				// --- fire Freemius loaded action ---
				do_action( $args['namespace'] . '_loaded' );
			}
		}

		// -----------------------
		// Filter Freemius Connect
		// -----------------------
		public function freemius_connect() {
			$namespace = $this->args['namespace'];
			$freemius = $GLOBALS[$namespace . '_freemius'];
			if ( isset( $settings['freemius'] ) && is_object( $freemius ) && method_exists( $freemius, 'add_filter' ) ) {
				$freemius->add_filter( 'connect_message', array( $this, 'freemius_connect_message' ), WP_FS__DEFAULT_PRIORITY, 6 );
			}
		}

		// ------------------------
		// Freemius Connect Message
		// ------------------------
		public function freemius_connect_message( $message, $user_first_name, $plugin_title, $user_login, $site_link, $freemius_link ) {
			// default: 'Never miss an important update - opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking with %4$s.'
			// 1.0.9: fix to remove incorrect first argument in string replacement
			$message = __fs( 'hey-x' ) . '<br>';
			$message .= sprintf(
				__( "If you want to more easily access support and feedback for this plugins features and functionality, %s can connect your user, %s at %s, to %s" ),
				'<b>' . $plugin_title . '</b>',
				'<b>' . $user_login . '</b>',
				$site_link,
				$freemius_link
			);

			return $message;
		}

		// ----------------------
		// Connect Update Message
		// ----------------------
		// TODO: message for connect updates
		public function freemius_update_message( $message, $user_first_name, $plugin_title, $user_login, $site_link, $freemius_link ) {
			// default: 'Please help us improve %1$s! If you opt-in, some data about your usage of %1$s will be sent to %4$s. If you skip this, that\'s okay! %1$s will still work just fine.'
			// $message = freemius_message( $message, $user_first_name, $plugin_title, $user_login, $site_link, $freemius_link );
			// TODO: check if message needs return here ?
			return $message;
		}


		// ====================
		// --- Plugin Admin ---
		// ====================

		// -----------------
		// Add Settings Menu
		// -----------------
		public function settings_menu() {

			$args = $this->args;
			$namespace = $this->namespace;
			$settings = $GLOBALS[$namespace];

			// --- filter capability ---
			$args['capability'] = apply_filters( $args['namespace'] . '_manage_options_capability', 'manage_options' );
			if ( !isset( $args['pagetitle'] ) ) {
				$args['pagetitle'] = $args['title'];
			}
			if ( !isset( $args['menutitle'] ) ) {
				$args['menutitle'] = $args['title'];
			}
			// 1.1.9: added filters for page and menu titles
			$pagetitle = apply_filters( $namespace . '_settings_page_title', $args['pagetitle'] );
			$menutitle = apply_filters( $namespace . '_settings_menu_title', $args['menutitle'] );

			// --- trigger filter plugin menu action ---
			// (can hook into this to add an admin menu manually using the provided loader args)
			// return true from filter function to not add a submenu item in admin Settings menu
			// 1.0.8: change from function exists check
			// 1.0.9: change to filter usage to check if menu is manually added
			$menu_added = apply_filters( $args['namespace'] . '_admin_menu_added', false, $args );

			// 1.1.1: record to admin menu added switch
			$this->menu_added = $menu_added;

			// --- maybe auto-add standalone options page ---
			if ( !$menu_added ) {
				// 1.0.8: check settingsmenu switch that disables automatic settings menu adding
				if ( !isset( $args['settingsmenu'] ) || ( false !== $args['settingsmenu'] ) ) {
					add_options_page( $args['pagetitle'], $args['menutitle'], $args['capability'], $args['slug'], $args['namespace'] . '_settings_page' );
				}
			}

			// 1.1.0: add admin notices boxer
			add_action( 'all_admin_notices', array( $this, 'notice_boxer' ), 999 );
		}

		// -----------------
		// Plugin Page Links
		// -----------------
		public function plugin_links( $links, $file ) {

			$args = $this->args;
			if ( plugin_basename( $args['file'] ) == $file ) {

				// --- add settings link ---
				// 1.1.1: fix to settings page link URL
				// (depending on whether top level menu or Settings submenu item)
				$page = 'options-general.php';
				if ( $this->menu_added ) {$page = 'admin.php';}
				$settings_url = add_query_arg( 'page', $args['slug'], admin_url( $page ) );
				$settings_link = "<a href='" . esc_url( $settings_url ) . "'>" . esc_html( __( 'Settings' ) ) . "</a>";
				array_unshift( $links, $settings_link );

				// --- maybe add Pro upgrade link ---
				if ( isset( $args['hasplans'] ) && $args['hasplans'] ) {

					// 1.2.1: add check if premium is already installed
					if ( !isset( $args['plan'] ) || ( 'premium' != $args['plan'] ) ) {

						// -- internal upgrade link ---
						if ( isset( $args['upgrade_link'] ) ) {
							$upgrade_url = $args['upgrade_link'];
							$upgrade_target = '';
						} else {
							$upgrade_url = add_query_arg( 'page', $args['slug'] . '-pricing', admin_url( 'admin.php' ) );
							$upgrade_target = !strstr( $upgrade_url, '/wp-admin/' ) ? ' target="_blank"' : '';
						}
						$upgrade_link = "<b><a href='" . esc_url( $upgrade_url ) . "'" . $upgrade_target . ">" . esc_html( __('Upgrade' ) ) . "</a></b>";
						array_unshift( $links, $upgrade_link );

						// --- external pro link ---
						// 1.2.0: added separate pro details link
						if ( isset( $args['pro_link'] ) ) {
							$pro_target = !strstr( $args['pro_link'], '/wp-admin/' ) ? ' target="_blank"' : '';
							$pro_link = "<b><a href='" . esc_url( $args['pro_link'] ) . "'" . $pro_target . ">" . esc_html( __('Pro Details' ) ) . "</a></b>";
							array_unshift( $links, $pro_link );
						}
					}
				}

				// --- maybe add Addons link ---
				// 1.2.0: activated add-ons link
				// 1.2.2: remove duplication of addons link
				if ( !isset( $args['hasaddons'] ) || !$args['hasaddons'] ) {
					if ( isset( $args['addons_link'] ) ) {
						$addons_url = $args['addons_link'];
						$addons_target = !strstr( $addons_url, '/wp-admin/' ) ? ' target="_blank"' : '';
						$addons_link = "<a href='" . esc_url( $addons_url )."'" . $addons_target . ">" . esc_html( __( 'Add Ons' ) ) . "</a>";
						array_unshift( $links, $addons_link );
					}
				}
			}

			return $links;
		}

		// -----------
		// Message Box
		// -----------
		public function message_box( $message, $echo ) {
			$box = "<table style='background-color: lightYellow; border-style:solid; border-width:1px; border-color: #E6DB55; text-align:center;'>";
			$box .= "<tr><td>";
			$box .= "<div class='message' style='margin:0.25em;'><font style='font-weight:bold;'>";
			$box .= $message;
			$box .= "</font></div>";
			$box .= "</td></tr>";
			$box .= "</table>";
			if ( $echo ) {
				// phpcs:ignore WordPress.Security.OutputNotEscaped
				echo $box;
			} else {
				return $box;
			}
			return '';
		}

		// ------------
		// Notice Boxer
		// ------------
		// 1.1.0: added admin notices boxer to settings pages
		public function notice_boxer() {

			$args = $this->args;

			// --- bug out if not on radio station pages ---
			if ( !isset( $_REQUEST['page'] ) ) {
				return;
			}
			if ( $args['slug'] != substr( $_REQUEST['page'], 0, strlen( $args['slug'] ) ) ) {
				return;
			}

			// --- output notice box ---
			echo '<div style="width: 98%;" id="admin-notices-box" class="postbox">';
			echo '<h3 class="admin-notices-title" style="cursor:pointer; margin:7px 14px; font-size:16px;" onclick="settings_toggle_notices();">';
			echo '<span id="admin-notices-arrow" style="font-size:24px;">&#9656;</span> &nbsp; ';
			echo '<span id="admin-notices-title" style="vertical-align:top;">' . __( 'Notices' ) . '</span>  &nbsp; ';
			echo '<span id="admin-notices-count" style="vertical-align:top;"></span></h3>';

			echo '<div id="admin-notices-wrap" style="display:none";><h2 style="display:none;"></h2></div>';
			echo '</div>';

			// --- toggle notice box script ---
			echo "<script>function settings_toggle_notices() {
				if (document.getElementById('admin-notices-wrap').style.display == '') {
					document.getElementById('admin-notices-wrap').style.display = 'none';
					document.getElementById('admin-notices-arrow').innerHTML = '&#9656;';
				} else {
					document.getElementById('admin-notices-wrap').style.display = '';
					document.getElementById('admin-notices-arrow').innerHTML= '&#9662;';
				}
			} ";

			// --- modified from /wp-admin/js/common.js to move notices ---
			echo "jQuery(document).ready(function() {
				setTimeout(function() {
					jQuery('div.update-nag, div.updated, div.error, div.notice').not('.inline, .below-h2').insertAfter(jQuery('#admin-notices-wrap h2'));
					count = parseInt(jQuery('#admin-notices-wrap').children().length - 1);
					if (count > 0) {jQuery('#admin-notices-count').html('('+count+')');}
					else {jQuery('#admin-notices-box').hide();}
				}, 500);
			});</script>";

		}

		// ------------------
		// Plugin Page Header
		// ------------------
		public function settings_header() {

			$args = $this->args;
			$namespace = $this->namespace;
			$settings = $GLOBALS[$namespace];

			// --- output debug values ---
			if ( $this->debug ) {
				echo '<br><b>Current Settings:</b><br>';
				print_r( $settings );
				echo '<br><br>';

				echo '<br><b>Plugin Options:</b><br>';
				print_r( $this->options );
				echo '<br><br>';

				if ( isset( $_POST ) ) {
					echo '<br><b>Posted Values:</b><br>';
					foreach ( $_POST as $key => $value ) {
						echo esc_attr( $key ) . ': ' . print_r( $value, true ) . '<br>';
					}
				}
			}

			// --- check for animated gif icon with fallback to normal icon ---
			// 1.0.9: fix to check if PNG file exists
			$icon_url = false;
			if ( file_exists( $this->args['dir'] . '/images/' . $args['slug'] . '.gif' ) ) {
				$icon_url = plugins_url( 'images/' . $args['slug'] . '.gif', $args['file'] );
			} elseif ( file_exists( $this->args['dir'] . '/images/' . $args['slug'] . '.png' ) ) {
				$icon_url = plugins_url( 'images/' . $args['slug'] . '.png', $args['file'] );
			}
			$icon_url = apply_filters( $namespace . '_plugin_icon_url', $icon_url );

			// --- check for author icon based on provided author name ---
			// 1.0.2: check if author icon file exists and fallback
			$author_icon_url = false;
			$author_slug = strtolower( str_replace( ' ', '', $args['author'] ) );
			if ( file_exists( $this->args['dir'] . '/images/' . $author_slug . '.png' ) ) {
				$author_icon_url = plugins_url( 'images/' . $author_slug . '.png', $args['file'] );
			} elseif ( file_exists( $this->args['dir'] . '/images/wordquest.png' ) ) {
				$author_icon_url = plugins_url( 'images/wordquest.png', $args['file'] );
			}
			$author_icon_url = apply_filters( $namespace . '_author_icon_url', $author_icon_url );

			// --- open header table ---
			echo '<table class="plugin-settings-page-header"><tr>';

			// --- plugin icon ---
			// 1.1.9: add filter for plugin icon url
			$icon_url = apply_filters( $namespace . '_settings_page_icon_url', $icon_url );
			echo '<td>';
			if ( $icon_url ) {
				echo '<img class="plugin-settings-page-icon" src="' . esc_url( $icon_url ) . '" width="128" height="128">';
			}
			echo '</td>';

			echo '<td width="20"></td><td>';

			echo '<table><tr>';

			// --- plugin title ---
			// 1.1.9: add filter for plugin pagetitle
			$title = apply_filters( $namespace . '_settings_page_title', $args['title'] );
			echo '<td><h3 style="font-size:20px;">';
			echo '<a href="' . esc_url( $args['home'] ) . '" target="_blank" style="text-decoration:none;">' . esc_html( $title ) . '</a>';
			echo '</h3></td>';

			echo '<td width="20"></td>';

			// --- plugin version ---
			// 1.1.9: add filter for plugin version
			$version = apply_filters( $namespace . '_settings_page_version', 'v' . $args['version'] );
			echo '<td><h3 class="plugin-setttings-page-version">' . esc_html( $version ) . '</h3></td></tr>';

			// --- subtitle ---
			// 1.1.9: added optional subtitle filter display
			$subtitle = apply_filters( $namespace . '_settings_page_subtitle', '' );
			if ( '' != $subtitle ) {
				echo '<tr><td colspan="3" align="center">';
				echo '<h4 class="plugins-settings-page-subtitle" style="font-size:14px; margin-top:0;">' . esc_html( $subtitle ) . '</h4>';
				echo '</td></tr>';
			}

			echo '<tr><td colspan="3" align="center">';

			echo '<table><tr><td align="center">';

			// ---- plugin author ---
			// 1.0.8: check if author URL is set
			if ( isset( $args['author_url'] ) ) {
				echo '<font style="font-size:16px;">' . esc_html( __( 'by' ) ) . '</font> ';
				echo '<a href="' . esc_url( $args['author_url'] ) . '" target="_blank" style="text-decoration:none;font-size:16px;" target="_blank"><b>' . esc_html( $args['author'] ) . '</b></a><br><br>';
			}

			// --- readme / docs / support links ---
			// 1.0.8: use filtered links array with automatic separator
			// 1.1.0: added explicit plugin home link
			// 1.1.0: added title attributes to links
			$links = array();
			if ( isset( $args['home'] ) ) {
				$links[] = '<a href="' . esc_url( $args['home'] ) . '" class="pluginlink smalllink" title="' . esc_attr( __( 'Plugin Homepage' ) ) . '" target="_blank"><b>' . esc_html( __( 'Home' ) ) . '</b></a>';
			}
			if ( !isset( $args['readme'] ) || ( false !== $args['readme'] ) ) {
				$readme_url = add_query_arg( 'action', $namespace . '_readme_viewer', admin_url( 'admin-ajax.php' ) );
				$links[] = '<a href="' . esc_url( $readme_url ) . '" class="pluginlink smalllink thickbox" title="' . esc_attr( __( 'View Plugin' ) ) . ' readme.txt"><b>' . esc_html( __( 'Readme' ) ) . '</b></a>';
			}
			if ( isset( $args['docs'] ) ) {
				$links[] = '<a href="' . esc_url( $args['docs'] ) . '" class="pluginlink smalllink" title="' . esc_attr( __( 'Plugin Documentation' ) ) . '" target="_blank"><b>' . esc_html( __( 'Docs' ) ) . '</b></a>';
			}
			if ( isset( $args['support'] ) ) {
				$links[] = '<a href="' . esc_url( $args['support'] ) . '" class="pluginlink smalllink" title="' . esc_attr( __( 'Plugin Support' ) ) . '" target="_blank"><b>' . esc_html( __( 'Support' ) ) . '</b></a>';
			}
			if ( isset( $args['development'] ) ) {
				$links[] = '<a href="' . esc_url( $args['development'] ) . '" class="pluginlink smalllink" title="' . esc_attr( __( 'Plugin Development' ) ) . '" target="_blank"><b>' . esc_html( __( 'Dev' ) ) . '</b></a>';
			}

			// 1.0.9: change filter from _plugin_links to disambiguate
			$links = apply_filters( $args['namespace'] . '_plugin_admin_links', $links );
			if ( count( $links ) > 0 ) {
				// phpcs:ignore WordPress.Security.OutputNotEscaped
				echo implode( ' | ', $links );
			}

			// --- author icon ---
			if ( $author_icon_url ) {
				echo '</td><td>';

				// 1.0.8: check if author URL is set for link
				if ( isset( $args['author_url'] ) ) {
					echo '<a href="' . esc_url( $args['author_url'] ) . '" target="_blank">';
				}
				echo '<img src="' . esc_url( $author_icon_url ) . '" width="64" height="64" border="0">';
				if ( isset( $args['author_url'] ) ) {
					echo '</a>';
				}
			}

			echo '</td></tr></table>';

			echo '</td></tr></table>';

			echo '</td><td width="50"></td><td style="vertical-align:top;">';

			// --- plugin supporter links ---
			// 1.0.1: set rate/share/donate links and texts
			// 1.0.8: added filters for rate/share/donate links
			echo '<br><div class="plugin-settings-page-links">';

			// --- Rate link ---
			if ( isset( $args['wporgslug'] ) ) {
				if ( isset( $args['rate'] ) ) {
					$rate_url = $args['rate'];
				} elseif ( isset( $args['type'] ) && ( 'theme' == $args['type'] ) ) {
					$rate_url = 'https://wordpress.org/support/theme/' . $args['wporgslug'] . '/reviews/#new-post';
				} else {
					$rate_url = 'https://wordpress.org/plugins/' . $args['wporgslug'] . '/reviews/#new-post';
				}
				if ( isset( $args['ratetext'] ) ) {
					$rate_text = $args['ratetext'];
				} else {
					$rate_text = __( 'Rate on WordPress.Org' );
				}
				$rate_link = '<a href="' . esc_url( $rate_url ) . '" class="pluginlink" target="_blank">';
				$rate_link .= '<span style="font-size:24px; color:#FC5; margin-right:10px;" class="dashicons dashicons-star-filled"></span> ';
				$rate_link .= esc_html( $rate_text ) . '</a><br><br>';
				$rate_link = apply_filters( $args['namespace'] . '_rate_link', $rate_link, $args );
				if ( $rate_link ) {
					// phpcs:ignore WordPress.Security.OutputNotEscaped
					echo $rate_link;
				}
			}

			// --- Share link ---
			if ( isset( $args['share'] ) ) {
				if ( isset( $args['sharetext'] ) ) {
					$share_text = $args['sharetext'];
				} else {
					$share_text = __( 'Share the Plugin Love' );
				}
				$share_link = '<a href="' . esc_url( $args['share'] ) . '" class="pluginlink" target="_blank">';
				$share_link .= '<span style="font-size:24px; color:#E0E; margin-right:10px;" class="dashicons dashicons-share"></span> ';
				$share_link .= esc_html( $share_text ) . '</a><br><br>';
				$share_link = apply_filters( $args['namespace'] . '_share_link', $share_link, $args );
				if ( $share_link ) {
					// phpcs:ignore WordPress.Security.OutputNotEscaped
					echo $share_link;
				}
			}

			// --- Donate link ---
			if ( isset( $args['donate'] ) ) {
				if ( isset( $args['donatetext'] ) ) {
					$donate_text = $args['donatetext'];
				} else {
					$donate_text = __( 'Support this Plugin' );
				}
				$donate_link = '<a href="' . esc_url( $args['donate'] ) . '" class="pluginlink" target="_blank">';
				$donate_link .= '<span style="font-size:24px; color:#E00; margin-right:10px;" class="dashicons dashicons-heart"></span> ';
				$donate_link .= '<b>' . esc_html( $donate_text ) . '</b></a><br><br>';
				$donate_link = apply_filters( $args['namespace'] . '_donate_link', $donate_link, $args );
				if ( $donate_link ) {
					// phpcs:ignore WordPress.Security.OutputNotEscaped
					echo $donate_link;
				}
			}

			echo '</div></td></tr>';

			// --- output updated and reset messages ---
			if ( isset( $_GET['updated'] ) ) {
				if ( 'yes' == $_GET['updated'] ) {
					$message = $settings['title'] . ' ' . __( 'Settings Updated.' );
				} elseif ( 'no' == $_GET['updated'] ) {
					$message = __( 'Error! Settings NOT Updated.' );
				} elseif ( 'reset' == $_GET['updated'] ) {
					$message = $settings['title'] . ' ' . __( 'Settings Reset!' );
				}
				if ( isset( $message ) ) {
					echo '<tr><td></td><td></td><td align="center">';
					// phpcs:ignore WordPress.Security.OutputNotEscaped
					echo $this->message_box( $message, false );
					echo '</td></tr>';
				}
			} else {
				// --- maybe output welcome message ---
				if ( isset( $_REQUEST['welcome'] ) && ( 'true' == $_REQUEST['welcome'] ) ) {
					if ( isset( $args['welcome'] ) ) {
						echo '<tr><td colspan="3" align="center">';
						echo $this->message_box( $args['welcome'], false );
						echo '</td></tr>';
					}
				}
			}

			echo '</table><br>';
		}

		// -------------
		// Settings Page
		// -------------
		public function settings_page() {

			$namespace = $this->namespace;

			// --- open page wrapper ---
			echo '<div id="pagewrap" class="wrap" style="width:100%;margin-right:0px !important;">';

			do_action( $namespace . '_admin_page_top' );

			// --- output settings header ---
			$this->settings_header();

			do_action( $namespace . '_admin_page_middle' );

			// --- output settings table ---
			$this->settings_table();

			do_action( $namespace . '_admin_page_bottom' );

			// --- close page wrapper ---
			echo '</div>';
		}

		// --------------
		// Settings Table
		// --------------
		// 1.0.9: created automatic Plugin Settings page
		// (based on passed plugin options and default settings)
		public function settings_table() {

			// --- get all options and settings (unfiltered) ---
			$args = $this->args;
			$namespace = $this->namespace;
			$options = $this->options;

			// --- get plugin options and default settings ---
			// 1.1.2: fix for filtering of plugin options
			$options = $this->options;
			$options = apply_filters( $namespace . '_options', $options );

			// --- maybe enqueue media scripts ---
			// 1.1.7: added media gallery script enqueueing for image field
			// 1.1.7: added color picker and color picker alpha script enqueueing
			$enqueued_media = $enqueued_color_picker = $enqueued_color_picker_alpha = false;
			foreach ( $options as $option ) {
				if ( ( 'image' == $option['type'] ) && !$enqueued_media ) {
					wp_enqueue_media();
					$enqueued_media = true;
				} elseif ( ( 'color' == $option['type'] ) && !$enqueued_color_picker ) {
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );
					$enqueued_color_picker = true;
				} elseif ( ( 'coloralpha' == $option['type'] ) && !$enqueued_color_picker_alpha ) {
					wp_enqueue_style( 'wp-color-picker' );
					$suffix = '.min';
					if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
						$suffix = '';
					}
					$url = plugins_url( '/js/wp-color-picker-alpha' . $suffix . '.js', $args['file'] );
				    wp_enqueue_script( 'wp-color-picker-a', $url, array( 'wp-color-picker' ), '3.0.0', true );
				    $enqueued_color_picker = $enqueued_color_picker_alpha = true;
				}
			}

			$defaults = $this->default_settings();
			$settings = $this->get_settings( false );

			// --- output saved settings ---
			if ( $this->debug ) {
				echo "<br><b>Saved Settings:</b><br>";
				print_r( $settings );
				echo "<br><br>";
			}

			// --- get option tabs and sections ---
			$tabs = $this->tabs;
			$sections = $this->sections;

			$currenttab = '';
			if ( isset( $settings['settingstab'] ) ) {
				$currenttab = $settings['settingstab'];
			}

			// --- loop options to maybe get tabbed groupings ---
			$taboptions = array();
			foreach ( $options as $key => $option ) {
				if ( isset( $option['tab'] ) && array_key_exists( $option['tab'], $tabs ) ) {
					if ( ( count( $sections ) > 0 ) && isset( $option['section'] ) ) {
						$taboptions[$option['tab']][$option['section']][$key] = $option;
					} else {
						$taboptions[$option['tab']]['general'][$key] = $option;
					}
				} else {
					if ( ( count( $sections ) > 0 ) && isset( $option['section'] ) ) {
						$taboptions['general'][$option['section']][$key] = $option;
					} else {
						$taboptions['general']['general'][$key] = $option;
					}
				}
			}

			// --- remove unused tabs automatically ---
			// 1.1.7: added for cleaner interface while developing
			foreach ( $tabs as $tab => $tablabel ) {
				if ( !isset( $taboptions[$tab] ) ) {
					unset( $tabs[$tab] );
				}
			}

			// --- maybe push general section to top of tab ---
			foreach ( $taboptions as $tab => $tabsections ) {
				if ( isset( $tabsections['general'] ) && ( count( $tabsections ) > 1 ) ) {
					$general = $tabsections['general'];
					unset( $tabsections['general'] );
					$taboptions[$tab] = array_merge( $general, $tabsections );
				}
			}

			// --- maybe output tab groupings ---
			if ( count( $tabs ) > 0 ) {

				// --- output tab switcher script ---
				// 1.0.9: add to settings scripts
				$script = "function settings_display_tab(tab) {" . PHP_EOL;
				foreach ( $tabs as $tab => $label ) {
					$script .= "	document.getElementById('" . esc_js( $tab ) . "-tab-button').className = 'settings-tab-button inactive';" . PHP_EOL;
					$script .= "	document.getElementById('" . esc_js( $tab ) . "-tab').className = 'settings-tab inactive'; " . PHP_EOL;
				}
				$script .= "	document.getElementById(tab+'-tab-button').className = 'settings-tab-button active';" . PHP_EOL;
				$script .= "	document.getElementById(tab+'-tab').className = 'settings-tab active';" . PHP_EOL;
				$script .= "	document.getElementById('settings-tab').value = tab;" . PHP_EOL;
				$script .= "}";
				$this->scripts[] = $script;

				$i = 0;
				echo "<ul id='settings-tab-buttons'>";
				foreach ( $tabs as $tab => $tablabel ) {
					$class = 'inactive';
					if ( ( $tab == $currenttab ) || ( ( '' == $currenttab ) && ( 0 == $i ) ) ) {
						$class = 'active';
					}
					echo "<li id='" . esc_attr( $tab ) . "-tab-button' class='settings-tab-button " . esc_attr( $class ) . "' onclick='";
					echo 'settings_display_tab("' . esc_attr( $tab ) . '");';
					echo "'>" . esc_html( $tablabel ) . "</li>";
					$i ++;
				}
				echo "</ul>";
			} else {
				$tabs = array( 'general' => __( 'General' ) );
			}

			// --- reset to default script ---
			// 1.0.9: add to settings scripts
			$confirmreset = __( 'Are you sure you want to reset to default settings?' );
			$script = "function settings_reset_defaults() {
				agree = confirm('" . esc_js( $confirmreset ) . "'); if (!agree) {return false;}
				document.getElementById('settings-action').value = 'reset';
				document.getElementById('settings-form').submit();
			}";
			$this->scripts[] = $script;

			// --- start settings form ---
			// 1.2.0: remove unused prefix on settings tab name attribute
			echo "<form method='post' id='settings-form'>";
			echo "<input type='hidden' name='" . esc_attr( $namespace ) . "_update_settings' id='settings-action' value='yes'>";
			echo "<input type='hidden' name='settingstab' id='settings-tab' value='" . esc_attr( $currenttab ) . "'>";
			wp_nonce_field( $args['slug'] . '_update_settings' );

			// --- maybe set hidden debug input ---
			if ( $this->debug ) {
				echo "<input type='hidden' name='debug' value='yes'>";
			}

			// ---- open wrapbox ---
			echo "<div id='wrapbox' class='postbox' style='line-height:2em;'>";
			echo "<div class='inner' style='padding-left:20px;'>";

			// --- output tabbed sections ---
			$i = 0;
			foreach ( $tabs as $tab => $tablabel ) {

				// --- open tab table output ---
				$class = 'inactive';
				if ( ( $currenttab == $tab ) || ( ( '' == $currenttab ) && ( 0 == $i ) ) ) {
					$class = 'active';
				}
				echo "<div id='" . esc_attr( $tab ) . "-tab' class='settings-tab " . esc_attr( $class ) . "'>";

				do_action( $namespace . '_admin_page_tab_' . $tab . '_top' );

				echo "<table cellpadding='0' cellspacing='0'>";

				if ( count( $sections ) > 0 ) {

					$sectionheadings = array();
					foreach ( $sections as $section => $sectionlabel ) {

						if ( array_key_exists( $section, $taboptions[$tab] ) ) {

							// --- section top ---
							ob_start();
							do_action( $namespace . '_admin_page_section_' . $section . '_top');
							$output = ob_get_clean();
							if ( $output ) {
								echo "<tr class='setting-section-bottom'><td colspan='5'>";
								// phpcs:ignore WordPress.Security.OutputNotEscaped
								echo $output;
								echo "</td></tr>";
							}

							// --- section heading ---
							if ( !isset( $sectionheadings[$section] ) ) {
								echo "<tr class='setting-section'>";
								echo "<td colspan='5'><h3>" . esc_html( $sectionlabel ) . "</h3></td>";
								echo "</tr>";
								$sectionheadings[$section] = true;
							}

							// --- section setting rows ---
							foreach ( $taboptions[$tab][$section] as $key => $option ) {
								$option['key'] = $key;
								// phpcs:ignore WordPress.Security.OutputNotEscaped
								echo $this->setting_row( $option );
							}
							echo "<tr height='25'><td> </td></tr>";

							// --- section bottom hook ---
							ob_start();
							do_action( $namespace . '_admin_page_section_' . $section . '_bottom' );
							$output = ob_get_clean();
							if ( $output ) {
								echo "<tr class='setting-section-bottom'><td colspan='5'>";
								// phpcs:ignore WordPress.Security.OutputNotEscaped
								echo $output;
								echo "</td></tr>";
							}

						}

					}
				} else {
					foreach ( $taboptions[$tab]['general'] as $key => $option ) {
						$option['key'] = $key;
						echo "<tr height='25'><td> </td></tr>";
						// phpcs:ignore WordPress.Security.OutputNotEscaped
						echo $this->setting_row( $option );
						echo "<tr height='25'><td> </td></tr>";
					}
				}

				// --- reset/save settings buttons ---
				// (filtered so removable from any specific tab)
				$buttons = "<tr height='25'><td> </td></tr>";
				$buttons .= "<tr><td align='center'>";
				$buttons .= "<input type='button' class='button-secondary settings-button' onclick='return settings_reset_defaults();' value='" . esc_attr( __( 'Reset Settings' ) ) . "'>";
				$buttons .= "</td><td colspan='3'></td><td align='center'>";
				$buttons .= "<input type='submit' class='button-primary settings-button' value='" . esc_attr( __( 'Save Settings' ) ) . "'>";
				$buttons .= "</td></tr>";
				$buttons .= "<tr height='25'><td></td></tr>";
				$buttons = apply_filters( $namespace . '_admin_save_buttons', $buttons, $tab );
				if ( $buttons ) {
					// phpcs:ignore WordPress.Security.OutputNotEscaped
					echo $buttons;
				}

				// --- close table ---
				echo "</table>";

				// --- do below tab action ---
				do_action( $namespace . '_admin_page_tab_' . $tab . '_bottom' );

				// --- close tab output ---
				echo "</div>";

				$i ++;
			}

			// --- close wrapbox ---
			echo "</div></div>";

			// --- close settings form ---
			echo "</form>";

			// --- number input step script ---
			// 1.0.9: added to script array
			// 1.1.8: fix to check for no mix or max value
			$script = "function settings_number_step(updown, id, min, max, step) {
				if (updown == 'up') {multiplier = 1;} else if (updown == 'down') {multiplier = -1;}
				current = parseInt(document.getElementById(id).value);
				newvalue = current + (multiplier * parseInt(step));
				if ((min !== false) && (newvalue < parseInt(min))) {newvalue = min;}
				if ((max !== false) && (newvalue > parseInt(max))) {newvalue = max;}
				document.getElementById(id).value = newvalue;
			}";
			$this->scripts[] = $script;

			// --- image selection script ---
			// 1.1.7: added for image field type
			if ( $enqueued_media ) {
				$confirm_remove = __( 'Are you sure you want to remove this image?' );
				$script = "jQuery(function(){

					var mediaframe, parentdiv;

					/* Add Image on Click */
					jQuery('.upload-custom-image').on( 'click', function( event ) {

						event.preventDefault();
						parentdiv = jQuery(this).parent().parent();

						if (mediaframe) {mediaframe.open(); return;}
						mediaframe = wp.media({
							title: 'Select or Upload Image',
							button: {text: 'Use this Image'},
							multiple: false
						});

						mediaframe.on( 'select', function() {
							var attachment = mediaframe.state().get('selection').first().toJSON();
							image = '<img src=\"'+attachment.url+'\" alt=\"\" style=\"max-width:100%;\"/>';
							parentdiv.find('.custom-image-container').append(image);
							parentdiv.find('.custom-image-id').val(attachment.id);
							parentdiv.find('.upload-custom-image').addClass('hidden');
							parentdiv.find('.delete-custom-image').removeClass('hidden');
						});

						mediaframe.open();
						jQuery('.media-modal-close').on( 'click', function() {
							console.log('close click detected');
							mediaframe.close();
						});
					});

					/* Delete Image on Click */
					jQuery('.delete-custom-image').on( 'click', function( event ) {
						event.preventDefault();
						agree = confirm('" . esc_js( $confirm_remove ) . "');
						if (!agree) {return;}
						parentdiv = jQuery(this).parent().parent();
						parentdiv.find('.custom-image-container').html('');
						parentdiv.find('.custom-image-id').val('');
						parentdiv.find('.upload-custom-image').removeClass('hidden');
						parentdiv.find('.delete-custom-image').addClass('hidden');
					});

				});";
				$this->scripts[] = $script;
			}

			// --- color picker script ---
			if ( $enqueued_color_picker ) {
				$script = "jQuery(document).ready(function(){
					if (jQuery('.color-picker').length) {jQuery('.color-picker').wpColorPicker();}
				});";
				$this->scripts[] = $script;
			}

			// --- enqueue settings scripts ---
			add_action( 'admin_footer', array( $this, 'setting_scripts' ) );

			// --- enqueue settings styles ---
			add_action( 'admin_footer', array( $this, 'setting_styles' ) );

		}

		// -----------
		// Setting Row
		// -----------
		// 1.0.9: added for automatic Settings table generation
		public function setting_row( $option ) {

			// --- prepare setting keys ---
			$args = $this->args;
			$namespace = $this->namespace;
			$postkey = $args['settings'];
			$name = $postkey . '_' . $option['key'];
			$type = $option['type'];
			$setting = $this->get_setting( $option['key'], false );

			// --- convert old option type names ---
			if ( 'email' == $type ) {
				$type = 'text';
				$option['options'] = 'EMAIL';
			} elseif ( 'emails' == $type ) {
				$type = 'text';
				$option['options'] = 'EMAIL';
			} elseif ( 'url' == $type ) {
				$type = 'text';
				$option['options'] = 'URL';
			} elseif ( 'urls' == $type ) {
				$type = 'text';
				$option['options'] = 'URL';
			} elseif ( 'alpabetic' == $type ) {
				$type = 'text';
				$option['options'] = 'ALPHABETIC';
			} elseif ( 'alphanumeric' == $type ) {
				$type = 'text';
				$option['options'] = 'ALPHANUMERIC';
			} elseif ( 'numeric' == $type ) {
				$option['options'] = 'NUMERIC';
			} elseif ( 'numeric' == $type ) {
				$option['options'] = 'NUMERIC';
			} elseif ( 'usernames' == $type ) {
				$type = 'text';
				$option['options'] = 'USERNAME';
			} elseif ( 'csvslugs' == $type ) {
				$type = 'text';
				$option['options'] = 'SLUG';
			} elseif ( 'pageid' == $type ) {
				$type = 'select';
				$option['options'] = 'PAGEID';
			} elseif ( 'postid' == $type ) {
				$type = 'select';
				$option['options'] = 'POSTID';
			} elseif ( 'pageids' == $type ) {
				$type = 'multiselect';
				$option['options'] = 'PAGEIDS';
			} elseif ( 'postids' == $type ) {
				$type = 'multiselect';
				$option['options'] = 'POSTIDS';
			}

			// --- prepare row output ---
			$row = '<tr class="settings-row">';

			$row .= '<td class="settings-label">' . $option['label'];
			if ( 'multiselect' == $type ) {
				$row .= '<br><span>' . esc_html( __( 'Use Ctrl and Click to Select' ) ) . '</span>';
			}
			$row .= '</td><td width="25"></td>';

			// 1.0.9: added multiple cell spanning note type
			if ( ( 'note' == $type ) || ( 'info' == $type ) || ( 'helper' == $type ) ) {

				$row .= '<td class="settings-helper" colspan="3">';
				if ( isset( $option['helper'] ) ) {
					$row .= $option['helper'];
				}
				$row .= '</td>';

			} else {

				// TODO: add check if already Pro version ?
				// (currently done by removing pro key/value pair in Pro code)
				if ( isset( $option['pro'] ) && $option['pro'] ) {

					// --- Pro version setting (teaser) ---
					// 1.2.0: improved handling of upgrade links
					$row .= '<td class="settings-input setting-pro">';
					$upgrade_link = false;
					if ( ( isset( $args['hasplans'] ) && $args['hasplans'] )
					  || ( isset( $args['hasaddons'] ) && $args['hasaddons'] ) ) {
						$upgrade_link = add_query_arg( 'page', $args['slug'] . '-pricing', admin_url( 'admin.php' ) );
						$upgrade_target = '';
					}
					if ( isset( $args['upgrade_link'] ) ) {
						$upgrade_link = $args['upgrade_link'];
						$upgrade_target = !strstr( $upgrade_link, '/wp-admin/' ) ? ' target="_blank"' : '';
					}
					// 1.2.1: fix to check pro_link not upgrade_link
					if ( isset( $args['pro_link'] ) ) {
						$pro_link = $args['pro_link'];
						$pro_target = !strstr( $pro_link, '/wp-admin/' ) ? ' target="_blank"' : '';
					}
					if ( $upgrade_link || $pro_link ) {
						$row .= __( 'Available in Pro.' ) . '<br>';
						if ( $upgrade_link ) {
							$row .= '<a href="' . esc_url( $upgrade_link ) . '"' . $upgrade_target . '>' . esc_html( __( 'Upgrade Now' ) ) . '</a>';
						}
						if ( $upgrade_link && $pro_link ) {
							$row .= ' | ';
						}
						if ( $pro_link ) {
							$row .= '<a href="' . esc_url( $pro_link ) . '"' . $pro_target . '>' . esc_html( __( 'Pro Details' ) ) . '</a>';
						}
					} else {
						$row .= esc_html( __( 'Coming soon in Pro version!' ) );
					}
					$row .= '</td>';

				} else {

					$row .= '<td class="settings-input">';

					// --- maybe prepare special options ---
					if ( isset( $option['options'] ) && is_string( $option['options'] ) ) {

						// --- maybe prepare post/page options (once) ---
						if ( in_array( $option['options'], array( 'POSTID', 'POSTIDS', 'PAGEID', 'PAGEIDS' ) ) ) {

							$posttype = strtolower( substr( $option['options'], 0, 4 ) );
							if ( ( ( 'page' == $posttype ) && !isset( $pageoptions ) )
							     || ( ( 'post' == $posttype ) && !isset( $postoptions ) ) ) {
								$pageoptions = $postoptions = array( '' => '' );
								global $wpdb;
								$query = "SELECT ID,post_title,post_status FROM " . $wpdb->prefix . "posts ";
								$query .= " WHERE post_type = %s AND post_status != 'auto-draft'";
								$results = $wpdb->get_results(
									$wpdb->prepare( $query, $posttype ), ARRAY_A
								);
								if ( $results && ( count( $results ) > 0 ) ) {
									foreach ( $results as $result ) {
										if ( strlen( $result['post_title'] ) > 35 ) {
											$result['post_title'] = substr( $result['post_title'], 0, 35 ) . '...';
										}
										$label = $result['ID'] . ': ' . $result['post_title'];
										if ( 'publish' != $result['post_status'] ) {
											$label .= ' (' . $result['post_status'] . ')';
										}
										if ( 'page' == $posttype ) {
											$pageoptions[$result['ID']] = $label;
										} elseif ( 'post' == $posttype ) {
											$postoptions[$result['ID']] = $label;
										}
									}
								}
							}
							if ( 'page' == $posttype ) {
								$option['options'] = $pageoptions;
							} elseif ( 'post' == $posttype ) {
								$option['options'] = $postoptions;
							}
						}

						// --- maybe prepare public post type options (once) ---
						if ( in_array( $option['options'], array( 'PUBLICTYPE', 'PUBLICTYPES' ) ) ) {
							if ( !isset( $publicoptions ) ) {
								$cpts = array( 'page', 'post' );
								$args = array( 'public' => true, '_builtin' => false );
								$cptlist = get_post_types( $args, 'names', 'and' );
								$cpts = array_merge( $cpts, $cptlist );
								foreach ( $cpts as $cpt ) {
									$posttypeobject = get_post_type_object( $cpt );
									$label = $posttypeobject->labels->singular_name;
									$publicoptions[$cpt] = $label;
								}
							}
							$option['options'] = $publicoptions;
						}

						// --- maybe prepare post type options (once) ---
						if ( in_array( $option['options'], array( 'POSTTYPE', 'POSTTYPES' ) ) ) {
							if ( !isset( $cptoptions ) ) {
								$cpts = array( 'page', 'post' );
								$args = array( '_builtin' => false );
								$cptlist = get_post_types( $args, 'names', 'and' );
								$cpts = array_merge( $cpts, $cptlist );
								foreach ( $cpts as $cpt ) {
									$posttypeobject = get_post_type_object( $cpt );
									$label = $posttypeobject->labels->singular_name;
									$cptoptions[$cpt] = $label;
								}
							}
							$option['options'] = $cptoptions;
						}

						// --- maybe prepare all post type options (once) ---
						if ( in_array( $option['options'], array( 'ALLTYPE', 'ALLTYPES' ) ) ) {
							if ( !isset( $allcptoptions ) ) {
								$args = array( '_builtin' => true );
								$cpts = get_post_types( $args, 'names', 'and' );
								foreach ( $cpts as $cpt ) {
									$posttypeobject = get_post_type_object( $cpt );
									$label = $posttypeobject->labels->singular_name;
									$allcptoptions[$cpt] = $label;
								}
							}
							$option['options'] = $allcptoptions;
						}

						// --- maybe prepare user options (once) ---
						if ( in_array( $option['options'], array( 'USERID', 'USERIDS', 'USERNAME', 'USERNAMES' ) ) ) {
							if ( in_array( $option['options'], array( 'USERID', 'USERIDS' ) ) ) {
								$userkey = 'userid';
							} else {
								$userkey = 'username';
							}
							$option['options'] = array( '' => '' );
							global $wpdb;
							$query = "SELECT ID,user_login,display_name FROM " . $wpdb->prefix . "users";
							$results = $wpdb->get_results( $query, ARRAY_A );
							if ( $results && ( count( $results ) > 0 ) ) {
								foreach ( $results as $result ) {
									$label = $result['ID'] . ': ' . $result['display_name'];
									if ( $result['display_name'] != $result['user_login'] ) {
										$label .= ' (' . $result['user_login'] . ')';
									}
									if ( 'userid' == $userkey ) {
										$useroptions[$result['ID']] = $label;
									} elseif ( 'username' == $userkey ) {
										$useroptions[$result['user_login']] = $label;
									}
								}
							}
							$option['options'] = array_merge( $option['options'], $useroptions );
						}

					}

					// --- output option input types ---
					if ( 'toggle' == $type ) {

						// --- toggle ---
						// 1.0.9: add toggle input (styled checkbox)
						// 1.1.7: set default option value if not set
						$checked = '';
						if ( !isset( $option['value'] ) ) {
							$option['value'] = '1';
						}
						if ( $setting == $option['value'] ) {
							$checked = ' checked="checked"';
						}
						$row .= '<label for="' . esc_attr( $name ) . '" class="setting-toggle">';
						$row .= '<input type="checkbox" name="' . esc_attr( $name ) . '" class="setting-toggle" value="' . esc_attr( $option['value'] ) . '"' . $checked . '>';
						$row .= '<span class="setting-slider round"></span>';
						$row .= '</label>';
						if ( isset( $option['suffix'] ) ) {
							$row .= ' ' . $option['suffix'];
						}

					} elseif ( 'checkbox' == $type ) {

						// --- checkbox ---
						// 1.1.7: set default option value if not set
						$checked = '';
						if ( !isset( $option['value'] ) ) {
							$option['value'] = '1';
						}
						if ( $setting == $option['value'] ) {
							$checked = ' checked="checked"';
						}
						$row .= '<input type="checkbox" name="' . $name . '" class="setting-checkbox" value="' . esc_attr( $option['value'] ) . '"' . $checked . '>';
						if ( isset( $option['suffix'] ) ) {
							$row .= ' ' . $option['suffix'];
						}

					} elseif ( 'multicheck' == $type ) {

						// --- multicheck boxes ---
						$checkboxes = array();
						foreach ( $option['options'] as $key => $label ) {
							$checked = '';
							if ( is_array( $setting ) && in_array( $key, $setting ) ) {
								$checked = ' checked="checked"';
							}
							$checkboxes[] = '<input type="checkbox" name="' . esc_attr( $name ) . "-" . esc_attr( $key ) . '" class="setting-checkbox" value="yes"' . $checked . '> ' . esc_html( $label );
						}
						$row .= implode( '<br>', $checkboxes );

					} elseif ( 'radio' == $type ) {

						// --- radio buttons ---
						$radios = array();
						foreach ( $option['options'] as $value => $label ) {
							$checked = '';
							if ( $setting == $value ) {
								$checked = ' checked="checked"';
							}
							$radios[] = '<input type="radio" class="setting-radio" name="' . esc_attr( $name ) . "' value='" . esc_attr( $value ) . '"' . $checked . '> ' . esc_html( $label );
						}
						$row .= implode( '<br>', $radios );

					} elseif ( 'select' == $type ) {

						// --- select dropdown ---
						$row .= '<select class="setting-select" name="' . esc_attr( $name ) . '">';
						foreach ( $option['options'] as $value => $label ) {
							// 1.0.9: support option grouping (set unique key containing OPTGROUP-)
							if ( strstr( $value, '*OPTGROUP*' ) ) {
								$row .= '<optgroup label="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</optgroup>';
							} else {
								// 1.1.3: remove strict value checking
								if ( $setting == $value ) {
									$selected = ' selected="selected"';
								} else {
									$selected = '';
								}
								$row .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>';
							}
						}
						$row .= '</select>';
						if ( isset( $option['suffix'] ) ) {
							$row .= ' ' . $option['suffix'];
						}

					} elseif ( 'multiselect' == $type ) {

						// --- multiselect dropdown ---
						$row .= '<select multiple="multiple" class="setting-select" name="' . esc_attr( $name ) . '[]">';
						foreach ( $option['options'] as $value => $label ) {
							if ( '' != $value ) {
								// 1.1.3: check for OPTGROUP instead of *OPTGROUP*
								if ( strstr( $value, 'OPTGROUP' ) ) {
									$row .= '<optgroup label="' . esc_attr( $label ) . '">';
								} else {
									if ( is_array( $setting ) && in_array( $value, $setting ) ) {
										$selected = ' selected="selected"';
									} else {
										$selected = '';
									}
									$row .= '<option value="' . esc_attr( $value ) . '"' . $selected . ">" . esc_html( $label ) . '</option>';
								}
							}
						}
						$row .= '</select>';
						if ( isset( $option['suffix'] ) ) {
							$row .= ' ' . $option['suffix'];
						}

					} elseif ( ( 'text' == $type ) || ( 'csv' == $type ) ) {

						// 1.2.0: re-added missing csv field type

						// --- text inputs ---
						$class = 'setting-text';
						if ( 'text' != $type ) {
							$class .= ' setting-' . $type;
						}
						if ( isset( $option['placeholder'] ) ) {
							$placeholder = $option['placeholder'];
						} else {
							$placeholder = '';
						}
						// 1.1.7: fix to attribute quoting output
						$row .= '<input type="text" name="' . esc_attr( $name ) . '" class="' . esc_attr( $class ) . '" value="' . esc_attr( $setting ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
						if ( isset( $option['suffix'] ) ) {
							$row .= ' ' . $option['suffix'];
						}

					} elseif ( 'textarea' == $type ) {

						// --- textarea input ---
						if ( isset( $option['rows'] ) ) {
							$rows = $option['rows'];
						} else {
							$rows = '6';
						}
						if ( isset( $option['placeholder'] ) ) {
							$placeholder = $option['placeholder'];
						} else {
							$placeholder = '';
						}
						$row .= '<textarea class="setting-textarea" name="' . esc_attr( $name ) . '" rows="' . esc_attr( $rows ) . '" placeholder="' . esc_attr( $placeholder ) . '">' . $setting . '</textarea>';

					} elseif ( ( 'numeric' == $type ) || ( 'number' == $type ) ) {

						// --- numeric text input ---
						// note: step key is only used for controls, not for validation
						if ( isset( $option['placeholder'] ) ) {
							$placeholder = $option['placeholder'];
						} else {
							$placeholder = '';
						}
						if ( isset( $option['min'] ) ) {
							$min = $option['min'];
						} else {
							$min = 'false';
						}
						if ( isset( $option['max'] ) ) {
							$max = $option['max'];
						} else {
							$max = 'false';
						}
						if ( isset( $option['step'] ) ) {
							$step = $option['step'];
						} else {
							$step = 1;
						}
						// 1.1.7: remove esc_js from onclick attributes
						$onclickup = "settings_number_step('up', '" . esc_attr( $name ) . "', " . esc_attr( $min ) . ", " . esc_attr( $max ) . ", " . esc_attr( $step ) . ");";
						$onclickdown = "settings_number_step('down', '" . esc_attr( $name ) . "', " . esc_attr( $min ) . ", " . esc_attr( $max ) . ", " . esc_attr( $step ) . ");";
						$row .= '<input class="setting-button button-secondary" type="button" value="-" onclick="' . $onclickdown . '">';
						$row .= '<input class="setting-numeric" type="text" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="' . esc_attr( $setting ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
						$row .= '<input class="setting-button button-secondary" type="button" value="+" onclick="' . $onclickup . '">';
						if ( isset( $option['suffix'] ) ) {
							$row .= ' ' . $option['suffix'];
						}

					} elseif ( 'image' == $type ) {

						// 1.1.7: added image attachment selection from media library

						// --- get current image ---
						$image = wp_get_attachment_image_src( $setting, 'full' );
						$has_image = is_array( $image );

						// --- image container ---
						$row .= '<div class="custom-image-container">';
						if ( $has_image ) {
							$row .= '<img src="' . esc_url( $image[0] ) . '" alt="" style="max-width:100%;">';
						}
						$row .= '</div>';

						// --- add and remove links ---
						$upload_link = get_upload_iframe_src( 'image' );
						$row .= '<p class="hide-if-no-js">';
							$hidden = '';
							if ( $has_image ) {
								$hidden = ' hidden';
							}
							$row .= '<a class="upload-custom-image' . esc_attr( $hidden ) . '" href="' . esc_url( $upload_link ) . '">';
							$row .= esc_html( __( 'Add Image' ) );
							$row .= '</a>';

							$hidden = '';
							if ( !$has_image ) {
								$hidden = ' hidden';
							}
							$row .= '<a class="delete-custom-image' . esc_attr( $hidden ) . '" href="#">';
							$row .= esc_html( __( 'Remove Image' ) );
							$row .= '</a>';
						$row .= '</p>';

						// --- hidden input for image ID ---
						$row .= '<input class="custom-image-id" name="' . esc_attr( $name ) . '" type="hidden" value="' . esc_attr( $setting ) . '">';

					} elseif ( 'color' == $type ) {

						// 1.1.7: added color picker field
						$row .= '<input type="text" class="color-picker" data-default-color="' . esc_attr( $option['default'] ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $setting ) . '">';

					} elseif ( 'coloralpha' == $type ) {

						// 1.1.7: added color picker alpha field
						$row .= '<input type="text" class="color-picker" data-alpha-enabled="true" data-default-color="' . esc_attr( $option['default'] ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $setting ) . '">';

					}

					$row .= '</td>';
				}

				// --- setting helper text ---
				if ( isset( $option['helper'] ) ) {
					$row .= '<td width="25"></td>';
					$row .= '<td class="settings-helper">' . esc_html( $option['helper'] ) . '</td>';
				}
			}

			$row .= '</tr>';

			// --- settings row spacer ---
			$row .= '<tr class="settings-spacer"><td> </td></tr>';

			// --- filter and return setting row ---
			$row = apply_filters( $namespace . '_setting_row', $row, $option );

			return $row;
		}

		// ---------------
		// Setting Scripts
		// ---------------
		// 1.0.9: added settings page scripts
		public function setting_scripts() {

			$scripts = $this->scripts;
			if ( count( $scripts ) > 0 ) {
				echo "<script>";
				foreach ( $scripts as $script ) {
					// phpcs:ignore WordPress.Security.OutputNotEscaped
					echo $script . PHP_EOL;
				}
				echo "</script>";
			}
		}

		// --------------
		// Setting Styles
		// --------------
		public function setting_styles() {

			$styles = array();

			// --- page styles ---
			$styles[] = '#wrapbox {margin-right: 20px;}';

			// --- plugin header styles ---
			// 1.2.0: moved from plugin header section
			$styles[] = '.pluginlink {text-decoration:none;}';
			$styles[] = '.smalllink {font-size:11px;}';
			$styles[] = '.readme:hover {text-decoration:underline;}';

			// --- settings tab styles ---
			// 1.1.0: added max-width:100% to select input
			$styles[] = '.settings-tab-button {display:inline-block; font-size:15px; padding:7px 14px; margin-right:20px; border-radius:7px;}';
			$styles[] = '.settings-tab-button.active {font-weight:bold; background-color:#0073aa; color:#FFF; border:1px solid #FFF;}';
			$styles[] = '.settings-tab-button.inactive {font-weight:bold; background-color:#F5F5F5; color:#0073aa; border:1px solid #000;}';
			$styles[] = '.settings-tab-button.inactive:hover {background-color:#FFFFFF; color:#00a0d2;}';
			$styles[] = '.settings-tab.active {display:block;}';
			$styles[] = '.settings-tab.inactive {display:none;}';

			// --- setting row styles ---
			$styles[] = '.settings-label {vertical-align:top; font-weight:bold; min-width:100px; max-width:200px;}';
			$styles[] = '.settings-label span {font-weight: normal; font-size:10px;}';
			$styles[] = '.settings-helper {vertical-align:top; font-style:italic; min-width:200px; max-width:300px;}';
			$styles[] = '.settings-spacer {height: 7px;}';

			// --- setting input styles ---
			$styles[] = '.settings-input {vertical-align:top; min-width:100px; max-width:300px;}';
			$styles[] = '.settings-input input.setting-radio {}';
			$styles[] = '.settings-input input.setting-checkbox {}';
			$styles[] = '.settings-input input.setting-text {width:100%;}';
			$styles[] = '.settings-input input.setting-numeric {display:inline-block; width:50%; text-align:center;}';
			$styles[] = '.settings-input input.setting-button {display:inline-block; padding:0px 5px;}';
			$styles[] = '.settings-input input.setting-textarea {width:100%;}';
			$styles[] = '.settings-input select.setting-select {min-width:100px; max-width:100%;}';

			// --- toggle input styles ---
			// Ref: https://www.w3schools.com/howto/howto_css_switch.asp
			$styles[] = '
			.setting-toggle {position:relative; display:inline-block; width:30px; height:17px;}
			.setting-toggle input {opacity:0; width:0; height:0;}
			.setting-slider {position:absolute; cursor:pointer;
			  top:0; left:0; right:0; bottom:0; background-color:#ccc;
			  -webkit-transition:.4s; transition:.4s;
			}
			.setting-slider:before {position:absolute; content:""; height:13px; width:13px;
			  left:2px; bottom:2px; background-color:white; -webkit-transition:.4s; transition:.4s;
			}
			input:checked + .setting-slider {background-color: #2196F3;}
			input:focus + .setting-slider {box-shadow: 0 0 1px #2196F3;}
			input:checked + .setting-slider:before {
			  -webkit-transform:translateX(13px); -ms-transform:translateX(13px); transform:translateX(13px);
			}
			.setting-slider.round {border-radius: 17px;}
			.setting-slider.round:before {border-radius: 50%;}
			';

			// --- filter and output styles ---
			$namespace = $this->namespace;
			$styles = apply_filters( $namespace . '_admin_page_styles', $styles );
			echo "<style>";
			// phpcs:ignore WordPress.Security.OutputNotEscaped
			echo implode( "\n", $styles );
			echo "</style>";

		}

	}
} // end Plugin Loader Class


// ----------------------------------
// Load Namespaced Prefixed Functions
// ----------------------------------
// [Optional] rename functions prefix to your plugin namespace
// these functions will then be available within your plugin
// to more easily call the matching plugin loader class methods

// 1.0.3: added priority of 0 to prefixed function loading action
add_action( 'plugins_loaded', 'radio_station_load_prefixed_functions', 0 );

if ( !function_exists( 'radio_station_load_prefixed_functions' ) ) {
	function radio_station_load_prefixed_functions() {

		// ------------------
		// Get Namespace Slug
		// ------------------
		// --- Auto-magic Mamespacing Slug Note ---
		// the below functions use the function name to grab and load the corresponding class method
		// all function name suffixes here must be two words for the magic namespace grabber to work
		// ie. _add_settings, because the namespace is taken from *before the second-last underscore*
		if ( !function_exists( 'radio_station_get_namespace_from_function' ) ) {
			function radio_station_get_namespace_from_function( $f ) {
				return substr( $f, 0, strrpos( $f, '_', ( strrpos( $f, '_' ) - strlen( $f ) - 1 ) ) );
			}
		}

		// -------------------
		// Get Loader Instance
		// -------------------
		// 2.3.0: added function for getting loader class instance
		if ( !function_exists( 'radio_station_loader_instance' ) ) {
			function radio_station_loader_instance() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );

				return $GLOBALS[$namespace . '_instance'];
			}
		}

		// ---------------------
		// Get Freemius Instance
		// ---------------------
		// 2.3.0: added function for getting Freemius class instance
		if ( !function_exists( 'radio_station_freemius_instance' ) ) {
			function radio_station_freemius_instance() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );

				return $GLOBALS[$namespace . '_freemius'];
			}
		}

		// ---------------
		// Get Plugin Data
		// ---------------
		// 1.1.1: added function for getting plugin data
		if ( !function_exists( 'radio_station_plugin_data' ) ) {
			function radio_station_plugin_data() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];

				return $instance->plugin_data();
			}
		}

		// ------------------
		// Get Plugin Version
		// ------------------
		// 1.1.2: added function for getting plugin version
		if ( !function_exists( 'radio_station_plugin_version' ) ) {
			function radio_station_plugin_version() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];

				return $instance->plugin_version();
			}
		}

		// -----------------
		// Set Pro Namespace
		// -----------------
		if ( !function_exists( 'radio_station_pro_namespace' ) ) {
			function radio_station_pro_namespace( $pronamespace ) {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->pro_namespace( $pronamespace );
			}
		}

		// ===============
		// Plugin Settings
		// ===============

		// ------------
		// Add Settings
		// ------------
		if ( !function_exists( 'radio_station_add_settings' ) ) {
			function radio_station_add_settings() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->add_settings();
			}
		}

		// ------------
		// Get Defaults
		// ------------
		if ( !function_exists( 'radio_station_default_settings' ) ) {
			function radio_station_default_settings( $key = false ) {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];

				return $instance->default_settings( $key );
			}
		}

		// -----------
		// Get Options
		// -----------
		if ( !function_exists( 'radio_station_get_options' ) ) {
			function radio_station_get_options() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];

				return $instance->options;
			}
		}

		// -----------
		// Get Setting
		// -----------
		if ( !function_exists( 'radio_station_get_setting' ) ) {
			function radio_station_get_setting( $key, $filter = true ) {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];

				return $instance->get_setting( $key, $filter );
			}
		}

		// ----------------
		// Get All Settings
		// ----------------
		// 1.0.9: added missing get_settings prefixed function
		if ( !function_exists( 'radio_station_get_settings' ) ) {
			function radio_station_get_settings( $filter = true ) {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];

				return $instance->get_settings( $filter );
			}
		}

		// --------------
		// Reset Settings
		// --------------
		if ( !function_exists( 'radio_station_reset_settings' ) ) {
			function radio_station_reset_settings() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->reset_settings();
			}
		}

		// ---------------
		// Update Settings
		// ---------------
		if ( !function_exists( 'radio_station_update_settings' ) ) {
			function radio_station_update_settings() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->update_settings();
			}
		}

		// ---------------
		// Delete Settings
		// ---------------
		if ( !function_exists( 'radio_station_delete_settings' ) ) {
			function radio_station_delete_settings() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->delete_settings();
			}
		}



		// -----------
		// Message Box
		// -----------
		if ( !function_exists( 'radio_station_message_box' ) ) {
			function radio_station_message_box( $message, $echo = false ) {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];

				return $instance->message_box( $message, $echo );
			}
		}

		// ---------------
		// Settings Header
		// ---------------
		if ( !function_exists( 'radio_station_settings_header' ) ) {
			function radio_station_settings_header() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->settings_header();
			}
		}

		// -------------
		// Settings Page
		// -------------
		if ( !function_exists( 'radio_station_settings_page' ) ) {
			function radio_station_settings_page() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->settings_page();
			}
		}

		// --------------
		// Settings Table
		// --------------
		// 1.0.9: added for standalone setting table output
		if ( !function_exists( 'radio_station_settings_table' ) ) {
			function radio_station_settings_table() {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->settings_table();
			}
		}

		// ------------
		// Settings Row
		// ------------
		// 1.0.9: added for standalone setting row output
		if ( !function_exists( 'radio_station_settings_row' ) ) {
			function radio_station_settings_row( $option, $setting ) {
				$namespace = radio_station_get_namespace_from_function( __FUNCTION__ );
				$instance = $GLOBALS[$namespace . '_instance'];
				$instance->settings_row( $option, $setting );
			}
		}

	}
}

// fully loaded
// ------------


// =========
// STRUCTURE
// =========
//
// === Loader Class ===
// - Initialize Loader
// - Setup Plugin
// - Get Plugin Data
// - Get Plugin Version
// - Set Pro Namespace
// === Plugin Settings ===
// - Get Default Settings
// - Add Settings
// - Maybe Transfer Settings
// - Get All Plugin Settings
// - Get Plugin Setting
// - Reset Plugin Settings
// - Update Plugin Settings
// - Validate Plugin Setting
// === Plugin Loading ===
// - Load Plugin Settings
// - Add Actions
// - Load Helper Libraries
// - Maybe Load Thickbox
// - Readme Viewer AJAX
// === Freemius Loading ===
// - Load Freemius
// - Filter Freemius Connect
// - Freemius Connect Message
// - Connect Update Message
// === Plugin Admin ===
// - Add Settings Menu
// - Plugin Page Links
// - Message Box
// - Notice Boxer
// - Plugin Page Header
// - Settings Page
// - Settings Table
// - Setting Row
// - Settings Scripts
// - Settings Styles
// === Namespaced Functions ===


// =========
// CHANGELOG
// =========

// == 1.2.1 ==
// - added filters for premium and addons init
// - fix overriding of plan arg to free
// - add check if premium already installed
// - fix namespace typo in add settings action

// == 1.2.0 ==
// - fix missing CSV field type row output condition
// - fix to saving of current settings tab (if any)
// - improved handling of Pro Upgrade and Details links

// == 1.1.9 ==
// - fix to allow saving of zero value
// - added filters for plugin page settings header sections

// == 1.1.8 ==
// - fix to number step if no min or max value

// == 1.1.7 ==
// - added media library upload image field type
// - added color picker and color picker alpha field types
// - automatically remove unused settings tabs
// - fix to text field attribute quoting
// - fix to not escape number step button function
// - remove FILTER_VALIDATE_URL from URL saving (not working)

// == 1.1.6 ==
// - added phone number character validation

// == 1.1.5 ==
// - fix to validate multiple CSV values

// == 1.1.4 ==
// - fix for debug prefix key dash
// - fix for weird get_settings glitch bug (isset failing?!)

// == 1.1.3 ==
// - remove strict value checking on select input
// - change OPTGROUP string check in select options

// == 1.1.2 ==
// - fix for filtering of plugin options
// - fix for plugin page link URL
// - added menu added and debug switches
// - added get plugin version function

// == 1.1.1 ==
// - remove admin_url wrapper on Freemius first-path value

// == 1.1.0 ==
// - fix to saving multicheck as single array of values
// - added admin notices boxer to settings pages
// - added record for tracking first install version
// - added explicit home link to plugin page links

// == 1.0.9 ==
// - added automated Settings table and Admin Page output
// - added tab and section support to options array
// - added tab and section bottom and top action hooks
// - added multicheck, multiselect and toggle input types
// - added post type and post ID input validation
// - added _loader_instance and _get_settings prefixed functions
// - added helper function to get loader and Freemius instances
// - added development link to plugin page header links
// - added missing output sanitization wrappers
// - removed WordQuest specific functionality from loader
// - refactor separate setting update inputs and validation
// - fix to check if plugin icon PNG exists

// == 1.0.8 ==
// - added WordQuest plugin flag (for use by non-WQ plugins)
// - apply settings key filters when getting all settings
// - added filters for rate/share/donate links
// - added page ID to settings input types

// == 1.0.7 ==
// - fix support URL undefined variable warning
// - change readme.php to reader.php (for GitHub)

// == 1.0.6 ==
// - added global options filter
// - added 'emails' option type for multiple email saving
// - fix for new unchecked checkbox value
// - fix for typos in URL option type saving

// == 1.0.5 ==
// - fix for undefined account and support variables

// == 1.0.4 ==
// - added 'url' option type checking and saving
// - added 'csv' option type checking and saving
// - added 'csvslugs' option type checking and saving

// == 1.0.3 ==
// - added priority of 0 to prefixed function loading action
// - only use namespace not settings key for updates
// - added 'email' option type checking and save
// - added 'usernames' option type checking and save

// == 1.0.2 ==
// - fix some Freemius loading argument checks
// - fix namespace typo in update/reset triggers
// - add check for author icon file and fileback
// - add allowance for special settings processing
// - add allowance for auto-load of multiple Pro files

// == 1.0.1 ==
// - set_basename for Freemius free / premium plugins
// - set rate / share / donate links and text anchors
// - check for multiple Pro filenames and use first one

// == 1.0.0 ==
// - Working Release version

// == 0.9.0 ==
// - Development Version


// -----------------
// Development TODOs
// -----------------
// - use sanitize text field on textarea ?
