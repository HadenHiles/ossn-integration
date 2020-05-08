<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/HadenHiles
 * @author						Haden Hiles
 * @since             1.0.0
 * @package           Ossn_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       OSSN Integation
 * Plugin URI:        ossn-integration
 * Description:       This is a plugin to keep OSSN user data updated with latest wordpress info (for now just email address)
 * Version:           1.0.0
 * Author:            Haden Hiles
 * Author URI:        https://github.com/HadenHiles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ossn-integration
 * Domain Path:       /languages
 */

 /**
  * custom option and settings
  */
 function ossn_settings_init() {
  // register a new setting for "ossn" page
  register_setting( 'ossn', 'ossn_options' );

  // register a new section in the "ossn" page
  add_settings_section(
  'ossn_section_developers',
  __( 'OSSN API Credentials', 'ossn' ),
  'ossn_section_developers_cb',
  'ossn'
  );

  // register a new field in the "ossn_section_developers" section, inside the "ossn" page
  add_settings_field(
  'ossn_field_api_key', // as of WP 4.6 this value is used only internally
  // use $args' label_for to populate the id inside the callback
  __( 'API Key', 'ossn' ),
  'ossn_field_api_key_cb',
  'ossn',
  'ossn_section_developers',
  [
  'label_for' => 'ossn_field_api_key',
  'class' => 'ossn_row',
  'ossn_custom_data' => 'custom',
  ]
  );
 }

 /**
  * register our ossn_settings_init to the admin_init action hook
  */
 add_action( 'admin_init', 'ossn_settings_init' );

 /**
  * custom option and settings:
  * callback functions
  */

 // developers section cb

 // section callbacks can accept an $args parameter, which is an array.
 // $args have the following keys defined: title, id, callback.
 // the values are defined at the add_settings_section() function.
 function ossn_section_developers_cb( $args ) {
  ?>
  <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Enter your OSSN api credentials here.', 'ossn' ); ?></p>
  <?php
 }

 // pill field cb

 // field callbacks can accept an $args parameter, which is an array.
 // $args is defined at the add_settings_field() function.
 // wordpress has magic interaction with the following keys: label_for, class.
 // the "label_for" key value is used for the "for" attribute of the <label>.
 // the "class" key value is used for the "class" attribute of the <tr> containing the field.
 // you can add custom key value pairs to be used inside your callbacks.
 function ossn_field_api_key_cb( $args ) {
  // get the value of the setting we've registered with register_setting()
  $options = get_option( 'ossn_options' );
  // output the field
  ?>
  <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
  data-custom="<?php echo esc_attr( $args['ossn_custom_data'] ); ?>"
  name="ossn_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $options[ $args['label_for'] ?>" />
  <p class="description">
  <?php esc_html_e( 'API Key', 'ossn' ); ?>
  </p>
  <?php
 }

 /**
  * top level menu
  */
 function ossn_options_page() {
  // add top level menu page
  add_menu_page(
  'OSSN',
  'OSSN Options',
  'manage_options',
  'ossn',
  'ossn_options_page_html'
  );
 }

 /**
  * register our ossn_options_page to the admin_menu action hook
  */
 add_action( 'admin_menu', 'ossn_options_page' );

 /**
  * top level menu:
  * callback functions
  */
 function ossn_options_page_html() {
  // check user capabilities
  if ( ! current_user_can( 'manage_options' ) ) {
  return;
  }

  // add error/update messages

  // check if the user have submitted the settings
  // wordpress will add the "settings-updated" $_GET parameter to the url
  if ( isset( $_GET['settings-updated'] ) ) {
  // add settings saved message with the class of "updated"
  add_settings_error( 'ossn_messages', 'ossn_message', __( 'OSSN Settings Saved', 'ossn' ), 'updated' );
  }

  // show error/update messages
  settings_errors( 'ossn_messages' );
  ?>
  <div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
  <form action="options.php" method="post">
  <?php
  // output security fields for the registered setting "ossn"
  settings_fields( 'ossn' );
  // output setting sections and their fields
  // (sections are registered for "ossn", each field is registered to a specific section)
  do_settings_sections( 'ossn' );
  // output save settings button
  submit_button( 'Save Settings' );
  ?>
  </form>
  </div>
  <?php
 }
